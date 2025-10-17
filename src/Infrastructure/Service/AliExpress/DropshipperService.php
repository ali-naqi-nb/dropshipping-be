<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\AliExpress;

use App\Application\Service\AliExpress\DropshipperServiceInterface;
use App\Domain\Model\Tenant\TenantStorageInterface;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use App\Infrastructure\Service\App\Aliexpress\AliexpressAccessTokenManager;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DropshipperService implements DropshipperServiceInterface
{
    private const API_URL = 'https://api-sg.aliexpress.com/sync';
    private const SIGN_METHOD = 'sha256';
    private const SDK_VERSION = 'iop-sdk-php-20220608';
    private const HTTP_METHOD = 'POST';

    public function __construct(
        private readonly string $appKey,
        private readonly string $appSecret,
        private readonly TenantStorageInterface $tenantStorage,
        private readonly AliexpressAccessTokenManager $tokenManager,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    private function getAccessToken(): string
    {
        $tenantId = $this->tenantStorage->getId();

        if (null === $tenantId) {
            throw new TenantIdException('Invalid tenant ID');
        }

        return $this->tokenManager->getAccessToken($tenantId);
    }

    private function generateSignature(array $parameters): string
    {
        $data = '';

        ksort($parameters);
        foreach ($parameters as $key => $value) {
            $data .= $key.$value;
        }

        return strtoupper(hash_hmac(self::SIGN_METHOD, $data, $this->appSecret));
    }

    private function getResponseKey(string $apiName): string
    {
        return str_replace('.', '_', $apiName).'_response';
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws AliexpressAccessTokenManagerException
     * @throws TenantIdException
     */
    private function makeRequest(string $apiName, array $parameters): ?array
    {
        $currentTimestamp = (new DateTimeImmutable())->getTimestamp();

        $systemParameters = [
            'app_key' => $this->appKey,
            'sign_method' => self::SIGN_METHOD,
            'timestamp' => "{$currentTimestamp}000",
            'method' => $apiName,
            'partner_id' => self::SDK_VERSION,
            'simplify' => 'false',
            'format' => 'json',
            'session' => $this->getAccessToken(),
        ];
        $systemParameters['sign'] = $this->generateSignature(array_merge($parameters, $systemParameters));

        try {
            $response = $this->client->request(self::HTTP_METHOD, self::API_URL, [
                'body' => $parameters,
                'query' => $systemParameters,
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logger->error('AliExpress request failed.', [
                    'parameters' => $parameters,
                    'systemParameters' => $systemParameters,
                    'statusCode' => $response->getStatusCode(),
                    'response' => $response->getContent(),
                ]);

                return null;
            }

            $data = $response->toArray();

            if (!array_key_exists($this->getResponseKey($apiName), $data)) {
                $this->logger->error('AliExpress request failed.', [
                    'parameters' => $parameters,
                    'systemParameters' => $systemParameters,
                    'response' => $data,
                ]);

                return null;
            }

            return $data[$this->getResponseKey($apiName)] ?? null;
        } catch (
            DecodingExceptionInterface|
            TransportExceptionInterface|
            ServerExceptionInterface|
            ClientExceptionInterface|
            RedirectionExceptionInterface $e
        ) {
            $this->logger->error('AliExpress request failed.', [
                'parameters' => $parameters,
                'systemParameters' => $systemParameters,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     *
     * @return ?array<string, mixed>
     */
    public function getProduct(
        string $shipToCountry,
        int|string $productId,
        string $targetCurrency,
        string $targetLanguage,
    ): ?array {
        $response = $this->makeRequest('aliexpress.ds.product.get', [
            'ship_to_country' => $shipToCountry,
            'product_id' => "$productId",
            'target_currency' => $targetCurrency,
            'target_language' => $targetLanguage,
        ]);

        $data = $response['result'] ?? null;

        if (null == $data) {
            $this->logger->error('AliExpress request failed.', [
                'method' => 'aliexpress.ds.product.get',
                'parameters' => [
                    'ship_to_country' => $shipToCountry,
                    'product_id' => "$productId",
                    'target_currency' => $targetCurrency,
                    'target_language' => $targetLanguage,
                ],
                'response' => $response,
            ]);
        }

        return $data;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     *
     * @return ?array<array<string, mixed>>
     */
    public function getCategory(int $categoryId, string $language): ?array
    {
        $response = $this->makeRequest('aliexpress.ds.category.get', [
            'categoryId' => "$categoryId",
            'language' => $language,
        ]);

        $data = $response['resp_result']['result']['categories']['category'] ?? null;

        if (null === $data) {
            $this->logger->error('AliExpress request failed.', [
                'method' => 'aliexpress.ds.category.get',
                'parameters' => [
                    'categoryId' => "$categoryId",
                    'language' => $language,
                ],
                'response' => $response,
            ]);
        }

        return $data;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     *
     * @return ?array<array<string, mixed>>
     */
    public function queryFreight(
        int $quantity,
        string $shipToCountry,
        int $productId,
        string $language,
        string $source,
        string $locale,
        string $selectedSkuId,
        string $currency
    ): ?array {
        $request = [
            'quantity' => $quantity,
            'shipToCountry' => $shipToCountry,
            'productId' => "$productId",
            'language' => $language,
            'source' => $source,
            'locale' => $locale,
            'selectedSkuId' => $selectedSkuId,
            'currency' => $currency,
        ];

        $response = $this->makeRequest('aliexpress.ds.freight.query', [
            'queryDeliveryReq' => json_encode($request),
        ]);

        $data = $response['result']['delivery_options']['delivery_option_d_t_o'] ?? null;

        if (null === $data) {
            $this->logger->error('AliExpress request failed.', [
                'method' => 'aliexpress.ds.freight.query',
                'parameters' => [
                    'queryDeliveryReq' => [
                        'quantity' => $quantity,
                        'shipToCountry' => $shipToCountry,
                        'productId' => "$productId",
                        'language' => $language,
                        'source' => $source,
                        'locale' => $locale,
                        'selectedSkuId' => $selectedSkuId,
                        'currency' => $currency,
                    ],
                ],
                'response' => $response,
            ]);
        }

        return $data;
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     *
     * @return ?array<array<string, mixed>>
     */
    public function createOrder(array $payload): ?array
    {
        $apiName = 'aliexpress.trade.buy.placeorder';

        $response = $this->makeRequest($apiName, [
            'param_place_order_request4_open_api_d_t_o' => json_encode($payload),
        ]);

        $data = $response['result']['order_list']['number'] ?? null;

        if (null === $data) {
            $this->logger->error('AliExpress request failed.', [
                'method' => $apiName,
                'parameters' => $payload,
                'response' => $response,
            ]);
        }

        return $data;
    }
}
