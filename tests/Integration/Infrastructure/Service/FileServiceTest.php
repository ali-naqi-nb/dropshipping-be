<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service;

use App\Infrastructure\Rpc\RpcCommand;
use App\Infrastructure\Service\FileService;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportFactory;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\DsProviderFactory;
use App\Tests\Shared\Factory\RpcResultFactory;

final class FileServiceTest extends IntegrationTestCase
{
    private const COMMAND_SEND_DS_PRODUCT_IMAGES_IMPORT = 'dsProductImagesImport';

    private FileService $fileService;
    private array $result;

    public function setUp(): void
    {
        parent::setUp();

        /** @var FileService $fileService */
        $fileService = self::getContainer()->get(FileService::class);
        $this->fileService = $fileService;

        $this->result = [
            'dsProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'dsProvider' => DsProviderFactory::ALI_EXPRESS,
            'products' => [
                [
                    'dsVariantId' => AeProductImportProductFactory::AE_SKU_ID,
                    'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
                ],
            ],
            'status' => 'ack',
        ];

        $this->mockRpcResponse(
            function (RpcCommand $rpcCommand) {
                if ($rpcCommand->getCommand() !== 'files_manager.'.self::COMMAND_SEND_DS_PRODUCT_IMAGES_IMPORT) {
                    return false;
                }

                return true;
            },
            RpcResultFactory::getRpcCommandResult(result: $this->result),
        );
    }

    public function testSendDsProductImagesImportWorks(): void
    {
        $result = $this->fileService->sendDsProductImagesImport(
            AeProductImportFactory::AE_PRODUCT_ID,
            [
                [
                    'dsVariantId' => AeProductImportProductFactory::AE_SKU_ID,
                    'images' => [AeProductImportProductFactory::AE_IMAGE_URL],
                ],
            ]
        );

        $this->assertIsArray($result);
        $this->assertSame($this->result, $result);
    }
}
