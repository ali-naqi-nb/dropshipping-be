<?php

declare(strict_types=1);

namespace App\Tests\Shared\Service;

use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Factory\AppFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class MockHttpClientFactory
{
    private string $externalDir;
    private const ALIEXPRESS_CREATE_TOKEN = '/https:\/\/api-sg\.aliexpress\.com\/rest\/auth\/token\/create/';
    private const ALIEXPRESS_REFRESH_TOKEN = '/https:\/\/api-sg\.aliexpress\.com\/rest\/auth\/token\/refresh/';
    private const ALIEXPRESS_SYNC_API = '/https:\/\/api-sg\.aliexpress\.com\/sync/';

    public function __construct(string $projectDir)
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->externalDir = implode($ds, [$projectDir, 'tests', 'Shared', 'File']).$ds;
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     */
    public function __invoke(string $method, string $uri, array $options): ResponseInterface
    {
        if (preg_match(self::ALIEXPRESS_CREATE_TOKEN, $uri)) {
            $token = explode('=', $options['body'])[1];

            if (AppFactory::ALI_EXPRESS_FAILED_TOKEN === $token) {
                return $this->getAliExpressErrorResponse();
            }

            return $this->getAliexpressCreateTokenResponse();
        }

        if (preg_match(self::ALIEXPRESS_REFRESH_TOKEN, $uri)) {
            return $this->getAliexpressRefreshTokenResponse();
        }

        if (preg_match(self::ALIEXPRESS_SYNC_API, $uri)) {
            $apiName = $options['query']['method'] ?? null;

            if (null !== $apiName) {
                return $this->getAliExpressCommandResponse($apiName, $options);
            }
        }

        /** @var string $body */
        $body = json_encode([sprintf('Method not found: %s: %s', $method, $uri)]);

        return new MockResponse($body, ['http_code' => 500]);
    }

    private function getAliexpressCreateTokenResponse(): MockResponse
    {
        /** @var string $body */
        $body = file_get_contents($this->externalDir.'Aliexpress'.DIRECTORY_SEPARATOR.'create.json');

        return new MockResponse($body, ['http_code' => 200]);
    }

    private function getAliexpressRefreshTokenResponse(): MockResponse
    {
        /** @var string $body */
        $body = file_get_contents($this->externalDir.'Aliexpress'.DIRECTORY_SEPARATOR.'refresh.json');

        return new MockResponse($body, ['http_code' => 200]);
    }

    private function shouldReturnEmptyForAliExpress(array $options): bool
    {
        return
            str_contains($options['body'] ?? null, AeProductImportProductFactory::AE_PRODUCT_TEST_ERROR.'') ||
            str_contains($options['body'] ?? null, AeProductImportProductFactory::AE_CATEGORY_TEST_ERROR.'') ||
            str_contains($options['body'] ?? null, AeProductImportProductFactory::AE_SKU_TEST_ERROR.'')
        ;
    }

    private function getAliExpressCommandResponse(string $apiName, array $options): MockResponse
    {
        if ($this->shouldReturnEmptyForAliExpress($options)) {
            return new MockResponse('{}', ['http_code' => 200]);
        }

        if (str_contains($options['body'] ?? null, AeProductImportProductFactory::AE_PRODUCT_404_ERROR.'')) {
            return new MockResponse('Not found!', ['http_code' => 404]);
        }

        $responseFile = $this->externalDir.'Aliexpress'.DIRECTORY_SEPARATOR.$apiName.'.json';

        if (file_exists($responseFile)) {
            /** @var string $body */
            $body = file_get_contents($responseFile);
        } else {
            $body = '{}';
        }

        return new MockResponse($body, ['http_code' => 200]);
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     */
    private function getAliExpressErrorResponse(): MockResponse
    {
        return new MockResponse('{}', ['http_code' => 400]);
    }
}
