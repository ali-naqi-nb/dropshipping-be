<?php

declare(strict_types=1);

namespace App\Application\Command\Product\SyncAliExpressProducts;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Domain\Model\Bus\Event\EventBusInterface;
use App\Domain\Model\Log\LogLevel;
use App\Domain\Model\Log\MainLog;
use App\Domain\Model\Log\MainLogRepositoryInterface;
use App\Domain\Model\Log\TenantLog;
use App\Domain\Model\Log\TenantLogRepositoryInterface;
use App\Domain\Model\Product\AeProductImportProduct;
use App\Domain\Model\Product\AeProductImportProductRepositoryInterface;
use App\Domain\Model\Product\DsProduct;
use App\Domain\Model\Product\DsProductUpdated;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Domain\Model\Tenant\TenantServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Persistence\Connection\DoctrineTenantConnection;
use Exception;
use NextBasket\ProcessPoolBundle\Events\ProcessFinished;
use NextBasket\ProcessPoolBundle\Events\ProcessStarted;
use NextBasket\ProcessPoolBundle\ProcessPool;
use Psr\Log\LoggerInterface;
use SplObjectStorage;
use Symfony\Component\Process\Process;

/**
 * Handler for syncing stock and supplier prices from AliExpress across all tenants.
 *
 * This handler:
 * - Iterates through all tenants with AliExpress app installed
 * - For each tenant, fetches all distinct AliExpress product IDs
 * - For each product, fetches fresh data from AliExpress API
 * - Updates stock (sku_available_stock) and supplier price/cost (offer_sale_price)
 * - Skips updates if values haven't changed
 * - Handles errors gracefully (API failures, bad data, missing SKUs)
 */
final class SyncAliExpressProductsCommandHandler
{
    private const DEFAULT_SHIP_TO_COUNTRY = 'US'; // Fallback country for price/stock queries
    private const TENANT_CHUNK_SIZE = 100; // Process tenants in chunks

    public function __construct(
        private readonly AeProductImportProductRepositoryInterface $importProductRepository,
        private readonly DropshipperServiceInterface $dropshipperService,
        private readonly TenantRepositoryInterface  $tenantRepository,
        private readonly TenantStorageInterface     $tenantStorage,
        private readonly LoggerInterface            $logger,
        private readonly EventBusInterface          $eventBus,
        private readonly MainLogRepositoryInterface $mainLogRepository,
        private readonly TenantLogRepositoryInterface $tenantLogRepository,
        private readonly TenantServiceInterface     $tenantService,
        private readonly DoctrineTenantConnection   $doctrineTenantConnection,
    )
    {
    }

    /**
     * Log message to both PSR logger and main database (cross-tenant logs).
     */
    private function logMain(string $level, string $message, array $context = [], ?string $tenantId = null): void
    {
        match ($level) {
            LogLevel::DEBUG->value => $this->logger->debug($message, $context),
            LogLevel::INFO->value => $this->logger->info($message, $context),
            LogLevel::NOTICE->value => $this->logger->notice($message, $context),
            LogLevel::WARNING->value => $this->logger->warning($message, $context),
            LogLevel::ERROR->value => $this->logger->error($message, $context),
            LogLevel::CRITICAL->value => $this->logger->critical($message, $context),
            LogLevel::ALERT->value => $this->logger->alert($message, $context),
            LogLevel::EMERGENCY->value => $this->logger->emergency($message, $context),
            default => $this->logger->log($level, $message, $context),
        };

        $mainLog = new MainLog(
            level: $level,
            message: $message,
            context: $context,
            channel: 'aliexpress_sync',
            source: self::class,
            tenantId: $tenantId
        );

        $this->mainLogRepository->save($mainLog);
    }

    /**
     * Log message to both PSR logger and tenant database (tenant-specific logs).
     */
    private function logTenant(string $level, string $message, array $context = []): void
    {
        match ($level) {
            LogLevel::DEBUG->value => $this->logger->debug($message, $context),
            LogLevel::INFO->value => $this->logger->info($message, $context),
            LogLevel::NOTICE->value => $this->logger->notice($message, $context),
            LogLevel::WARNING->value => $this->logger->warning($message, $context),
            LogLevel::ERROR->value => $this->logger->error($message, $context),
            LogLevel::CRITICAL->value => $this->logger->critical($message, $context),
            LogLevel::ALERT->value => $this->logger->alert($message, $context),
            LogLevel::EMERGENCY->value => $this->logger->emergency($message, $context),
            default => $this->logger->log($level, $message, $context),
        };

        $tenantLog = new TenantLog(
            level: $level,
            message: $message,
            context: $context,
            channel: 'aliexpress_sync',
            source: self::class
        );

        $this->tenantLogRepository->save($tenantLog);
    }

    public function __invoke(SyncAliExpressProductsCommand $command): SyncAliExpressProductsResult
    {
        $result = new SyncAliExpressProductsResult();

        try {
            if (null !== $command->getTenantId()) {
                $this->logMain(LogLevel::INFO->value, '[AE Sync] Starting sync for specific tenant', [
                    'tenantId' => $command->getTenantId(),
                    'dryRun' => $command->isDryRun(),
                    'timeoutMinutes' => $command->getTimeoutMinutes(),
                ], $command->getTenantId());

                $tenant = $this->tenantRepository->findOneById($command->getTenantId());

                if (null === $tenant) {
                    throw new Exception('Tenant not found: ' . $command->getTenantId());
                }

                if (!$tenant->isAppInstalled(AppId::AliExpress)) {
                    throw new Exception('Tenant does not have AliExpress app installed: ' . $command->getTenantId());
                }

                $this->syncTenant($tenant, $command, $result);
            } else {
                $this->logMain(LogLevel::INFO->value, '[AE Sync] Starting daily sync across all tenants (parallel mode)', [
                    'dryRun' => $command->isDryRun(),
                    'concurrency' => SyncAliExpressProductsCommand::DEFAULT_CONCURRENCY,
                    'timeoutMinutes' => $command->getTimeoutMinutes(),
                ]);

                $chunk = 0;
                do {
                    $tenants = $this->tenantRepository->findTenantsWithAppInstalled(
                        AppId::AliExpress,
                        $chunk++,
                        self::TENANT_CHUNK_SIZE
                    );

                    if (!empty($tenants)) {
                        $this->syncTenantsInParallel($tenants, $command);
                    }
                } while (!empty($tenants));

                $this->logMain(LogLevel::INFO->value, '[AE Sync] All tenant batches dispatched for parallel processing');
            }

            $this->logMain(LogLevel::INFO->value, '[AE Sync] Daily sync completed across all tenants', [
                'totalTenants' => $result->getTotalTenantsProcessed(),
                'successfulTenants' => $result->getSuccessfulTenants(),
                'failedTenants' => $result->getFailedTenants(),
                'totalProducts' => $result->getTotalProductsProcessed(),
                'successfulProducts' => $result->getSuccessfulProducts(),
                'failedProducts' => $result->getFailedProducts(),
                'variantsUpdated' => $result->getVariantsUpdated(),
                'variantsSkipped' => $result->getVariantsSkipped(),
                'variantsWithErrors' => $result->getVariantsWithErrors(),
            ]);
        } catch (Exception $e) {
            $this->logMain(LogLevel::ERROR->value, '[AE Sync] Fatal error during sync', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $result;
    }

    private function syncTenant(Tenant $tenant, SyncAliExpressProductsCommand $command, SyncAliExpressProductsResult $result): void
    {
        $this->tenantStorage->setId($tenant->getId());

        try {
            $dbConfig = $this->tenantService->getDbConfig($tenant->getId());
            if (null === $dbConfig) {
                throw new Exception('Could not retrieve database config for tenant: ' . $tenant->getId());
            }
            $this->doctrineTenantConnection->create($dbConfig);

            $this->logTenant(LogLevel::INFO->value, '[AE Sync] Syncing tenant', [
                'tenantId' => $tenant->getId(),
                'currency' => $tenant->getDefaultCurrency(),
            ]);

            $aeProductIds = $this->importProductRepository->findAllDistinctAeProductIds(null);

            if (empty($aeProductIds)) {
                $this->logTenant(LogLevel::INFO->value, '[AE Sync] No products found for tenant', [
                    'tenantId' => $tenant->getId(),
                ]);
                $result->incrementSkippedTenants();

                return;
            }

            $tenantHadUpdates = false;

            foreach ($aeProductIds as $aeProductId) {
                try {
                    if ($this->syncProduct($aeProductId, $command->isDryRun(), $result, $tenant)) {
                        $tenantHadUpdates = true;
                    }
                } catch (Exception $e) {
                    $result->incrementFailedProducts();
                    $this->logTenant(LogLevel::ERROR->value, '[AE Sync] Failed to sync product', [
                        'tenantId' => $tenant->getId(),
                        'aeProductId' => $aeProductId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($tenantHadUpdates) {
                $result->incrementSuccessfulTenants();
            } else {
                $result->incrementFailedTenants();
            }

            $this->logTenant(LogLevel::INFO->value, '[AE Sync] Tenant sync completed', [
                'tenantId' => $tenant->getId(),
                'productsProcessed' => count($aeProductIds),
            ]);
        } finally {
            if ($this->doctrineTenantConnection->isConnected()) {
                $this->doctrineTenantConnection->close();
            }
        }
    }

    /**
     * Sync a single product.
     *
     * @return bool True if at least one variant was updated
     */
    private function syncProduct(int $aeProductId, bool $dryRun, SyncAliExpressProductsResult $result, Tenant $tenant): bool
    {
        $aeProductData = $this->fetchAliExpressProduct(
            $aeProductId,
            $tenant->getDefaultCurrency(),
            $tenant->getDefaultLanguage() ?? 'en_US'
        );

        if (null === $aeProductData) {
            $result->incrementFailedProducts();

            return false;
        }

        $importProducts = $this->importProductRepository->findAllByAeProductId($aeProductId);

        if (empty($importProducts)) {
            $this->logTenant(LogLevel::WARNING->value, '[AE Sync] No variants found in database', [
                'aeProductId' => $aeProductId,
            ]);
            $result->incrementFailedProducts();

            return false;
        }

        $skuData = $this->extractSkuData($aeProductData);

        if (empty($skuData)) {
            $this->logTenant(LogLevel::WARNING->value, '[AE Sync] No SKU data in AliExpress response', [
                'aeProductId' => $aeProductId,
            ]);
            $result->incrementFailedProducts();

            return false;
        }

        $hasUpdates = false;
        foreach ($importProducts as $importProduct) {
            $aeSkuId = (string)$importProduct->getAeSkuId();

            if (!isset($skuData[$aeSkuId])) {
                $this->logTenant(LogLevel::DEBUG->value, '[AE Sync] SKU not found in AliExpress data', [
                    'aeProductId' => $aeProductId,
                    'aeSkuId' => $aeSkuId,
                ]);
                $result->incrementVariantsWithErrors();
                continue;
            }

            $sku = $skuData[$aeSkuId];

            if ($this->updateVariant($importProduct, $sku, $dryRun, $tenant)) {
                $result->incrementVariantsUpdated();
                $hasUpdates = true;
            } else {
                $result->incrementVariantsSkipped();
            }
        }

        if ($hasUpdates) {
            $result->incrementSuccessfulProducts();
        } else {
            $result->incrementFailedProducts();
        }

        return $hasUpdates;
    }

    /**
     * Fetch product data from AliExpress API.
     */
    private function fetchAliExpressProduct(int $aeProductId, string $defaultCurrency, string $defaultLanguage): ?array
    {
        try {
            $productData = $this->dropshipperService->getProduct(
                shipToCountry: self::DEFAULT_SHIP_TO_COUNTRY,
                productId: $aeProductId,
                targetCurrency: $defaultCurrency,
                targetLanguage: $defaultLanguage,
            );

            if (null === $productData) {
                $this->logTenant(LogLevel::WARNING->value, '[AE Sync] AliExpress API returned null', [
                    'aeProductId' => $aeProductId,
                ]);
            }

            return $productData;
        } catch (Exception $e) {
            $this->logTenant(LogLevel::ERROR->value, '[AE Sync] AliExpress API request failed', [
                'aeProductId' => $aeProductId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract SKU data from AliExpress product response.
     *
     * @return array<string, array<string, mixed>> Keyed by sku_id
     */
    private function extractSkuData(array $aeProductData): array
    {
        $skuInfos = $aeProductData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? [];

        $skuData = [];
        foreach ($skuInfos as $sku) {
            $skuId = (string)$sku['sku_id'];
            $skuData[$skuId] = $sku;
        }

        return $skuData;
    }

    /**
     * Update a single variant with fresh stock and price data.
     *
     * This updates the COST (supplier price), not the retail price.
     *
     * @return bool True if variant was updated, false if skipped (no changes or invalid data)
     */
    private function updateVariant(AeProductImportProduct $importProduct, array $sku, bool $dryRun, Tenant $tenant): bool
    {
        if (!isset($sku['sku_available_stock']) || !isset($sku['offer_sale_price']) || !isset($sku['currency_code'])) {
            $this->logTenant(LogLevel::WARNING->value, '[AE Sync] Missing required SKU fields', [
                'aeProductId' => $importProduct->getAeProductId(),
                'aeSkuId' => $importProduct->getAeSkuId(),
                'sku' => $sku,
            ]);

            return false;
        }

        $newStock = $sku['sku_available_stock'];
        $newPriceRaw = $sku['offer_sale_price'];
        $newCurrencyCode = $sku['currency_code'];

        if (!is_numeric($newStock) || $newStock < 0) {
            $this->logTenant(LogLevel::WARNING->value, '[AE Sync] Invalid stock value', [
                'aeProductId' => $importProduct->getAeProductId(),
                'aeSkuId' => $importProduct->getAeSkuId(),
                'stock' => $newStock,
            ]);

            return false;
        }

        if (!is_numeric($newPriceRaw) || $newPriceRaw < 0) {
            $this->logTenant(LogLevel::WARNING->value, '[AE Sync] Invalid price value', [
                'aeProductId' => $importProduct->getAeProductId(),
                'aeSkuId' => $importProduct->getAeSkuId(),
                'price' => $newPriceRaw,
            ]);

            return false;
        }

        $newPrice = AeUtil::toBase100((string)$newPriceRaw);
        $newStockInt = (int)$newStock;

        $stockChanged = $importProduct->getAeProductStock() !== $newStockInt;
        $priceChanged = $importProduct->getAeOfferSalePrice() !== $newPrice;
        $currencyChanged = $importProduct->getAeSkuCurrencyCode() !== $newCurrencyCode;

        if (!$stockChanged && !$priceChanged && !$currencyChanged) {
            $this->logTenant(LogLevel::DEBUG->value, '[AE Sync] No changes for variant', [
                'aeProductId' => $importProduct->getAeProductId(),
                'aeSkuId' => $importProduct->getAeSkuId(),
            ]);

            return false;
        }

        $changes = [];

        if ($stockChanged) {
            $changes['stock'] = [
                'old' => $importProduct->getAeProductStock(),
                'new' => $newStockInt,
            ];
            if (!$dryRun) {
                $importProduct->setAeProductStock($newStockInt);
            }
        }

        if ($priceChanged) {
            $changes['offerSalePrice (COST)'] = [
                'old' => $importProduct->getAeOfferSalePrice(),
                'new' => $newPrice,
            ];
            if (!$dryRun) {
                $importProduct->setAeOfferSalePrice($newPrice);
            }
        }

        if ($currencyChanged) {
            $changes['currencyCode'] = [
                'old' => $importProduct->getAeSkuCurrencyCode(),
                'new' => $newCurrencyCode,
            ];
            if (!$dryRun) {
                $importProduct->setAeSkuCurrencyCode($newCurrencyCode);
            }
        }

        $this->logTenant(LogLevel::INFO->value, '[AE Sync] Updating variant', [
            'aeProductId' => $importProduct->getAeProductId(),
            'aeSkuId' => $importProduct->getAeSkuId(),
            'changes' => $changes,
            'dryRun' => $dryRun,
        ]);

        if (!$dryRun) {
            $this->importProductRepository->save($importProduct);

            $nbProductId = $importProduct->getNbProductId();
            if (null !== $nbProductId) {
                $this->dispatchProductUpdatedEvent(
                    $tenant,
                    $importProduct,
                    $newStockInt,
                    $newPrice,
                    $newCurrencyCode
                );
            }
        }

        return true;
    }

    /**
     * Dispatch DsProductUpdated event to notify the products service.
     */
    private function dispatchProductUpdatedEvent(
        Tenant $tenant,
        AeProductImportProduct $importProduct,
        int $stock,
        int $cost,
        string $currencyCode
    ): void
    {
        $nbProductId = $importProduct->getNbProductId();

        if (null === $nbProductId) {
            return;
        }

        $dsProduct = new DsProduct(
            productId: $nbProductId,
            stock: $stock,
            cost: $cost,
            currencyCode: $currencyCode
        );

        $event = new DsProductUpdated(
            tenantId: $tenant->getId(),
            dsProvider: 'aliexpress',
            product: $dsProduct
        );

        $this->eventBus->publish($event);

        $this->logTenant(LogLevel::INFO->value, '[AE Sync] DsProductUpdated event dispatched', [
            'tenantId' => $tenant->getId(),
            'productId' => $nbProductId,
            'aeProductId' => $importProduct->getAeProductId(),
            'aeSkuId' => $importProduct->getAeSkuId(),
            'stock' => $stock,
            'cost' => $cost,
            'currencyCode' => $currencyCode,
        ]);
    }

    /**
     * Process multiple tenants in parallel using separate OS processes.
     *
     * @param Tenant[] $tenants
     */
    private function syncTenantsInParallel(array $tenants, SyncAliExpressProductsCommand $command): void
    {
        if (empty($tenants)) {
            return;
        }

        $processes = new SplObjectStorage();
        $tenantsMap = [];

        foreach ($tenants as $tenant) {
            $cmd = [
                'php',
                'bin/console',
                'app:sync-aliexpress-products',
                '--tenant-id=' . $tenant->getId(),
            ];

            if ($command->isDryRun()) {
                $cmd[] = '--dry-run';
            }

            $cmd[] = '--timeout=' . $command->getTimeoutMinutes();

            $process = new Process(
                command: $cmd,
                timeout: $command->getTimeoutSeconds()
            );

            $processes->attach($process);
            $tenantsMap[spl_object_hash($process)] = $tenant;
        }

        /** @phpstan-ignore-next-line */
        $processPool = new ProcessPool($processes);
        $processPool->setConcurrency(SyncAliExpressProductsCommand::DEFAULT_CONCURRENCY);

        $processPool->onProcessStarted(function (ProcessStarted $event) use ($tenantsMap) {
            $process = $event->getProcess();
            $hash = spl_object_hash($process);
            if (isset($tenantsMap[$hash])) {
                $tenant = $tenantsMap[$hash];
                $this->logMain(LogLevel::INFO->value, '[AE Sync] Parallel process started', [
                    'tenantId' => $tenant->getId(),
                    'command' => $process->getCommandLine(),
                ], $tenant->getId());
            }
        });

        $processPool->onProcessFinished(function (ProcessFinished $event) use ($tenantsMap) {
            $process = $event->getProcess();
            $hash = spl_object_hash($process);

            if (isset($tenantsMap[$hash])) {
                $tenant = $tenantsMap[$hash];

                if (0 !== $process->getExitCode()) {
                    $this->logMain(LogLevel::ERROR->value, '[AE Sync] Parallel process failed', [
                        'tenantId' => $tenant->getId(),
                        'exitCode' => $process->getExitCode(),
                        'command' => $process->getCommandLine(),
                        'error' => $process->getErrorOutput(),
                    ], $tenant->getId());
                } else {
                    $this->logMain(LogLevel::INFO->value, '[AE Sync] Parallel process completed', [
                        'tenantId' => $tenant->getId(),
                        'output' => $process->getOutput(),
                    ], $tenant->getId());
                }
            }
        });

        $processPool->wait();

        $this->logMain(LogLevel::INFO->value, '[AE Sync] Parallel batch completed', [
            'tenantsInBatch' => count($tenants),
            'concurrency' => SyncAliExpressProductsCommand::DEFAULT_CONCURRENCY,
        ]);
    }
}
