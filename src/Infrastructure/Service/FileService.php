<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Service\FileServiceInterface;
use App\Infrastructure\Rpc\Client\RpcCommandClientInterface;

final class FileService implements FileServiceInterface
{
    private const SERVICE = 'files_manager';
    private const COMMAND_SEND_DS_PRODUCT_IMAGES_IMPORT = 'dsProductImagesImport';

    public function __construct(
        private readonly RpcCommandClientInterface $commandClient
    ) {
    }

    /**
     * @param array<int, array{dsVariantId: int|string, images: string[]}> $products
     */
    public function sendDsProductImagesImport(int|string $dsProductId, array $products, string $dsProvider = 'AliExpress'): array
    {
        $result = $this->commandClient->call(
            service: self::SERVICE,
            command: self::COMMAND_SEND_DS_PRODUCT_IMAGES_IMPORT,
            arguments: [
                [
                    'dsProductId' => $dsProductId,
                    'dsProvider' => $dsProvider,
                    'products' => $products,
                ],
            ]
        );

        return $result->getResult();
    }
}
