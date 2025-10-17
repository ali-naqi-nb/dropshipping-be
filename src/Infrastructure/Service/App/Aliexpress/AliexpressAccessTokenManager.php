<?php

namespace App\Infrastructure\Service\App\Aliexpress;

use App\Application\Service\App\AppAccessTokenManagerInterface;
use App\Domain\Model\Tenant\App;
use App\Domain\Model\Tenant\AppId;
use App\Domain\Model\Tenant\Tenant;
use App\Domain\Model\Tenant\TenantRepositoryInterface;
use App\Infrastructure\Exception\AliexpressAccessTokenManagerException;
use App\Infrastructure\Exception\TenantIdException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AliexpressAccessTokenManager implements AppAccessTokenManagerInterface
{
    private const API_URL = 'https://api-sg.aliexpress.com/rest';
    private const GENERATE_TOKEN_ACTION = '/auth/token/create';
    private const REFRESH_TOKEN_ACTION = '/auth/token/refresh';
    private const SIGN_METHOD = 'sha256';

    private ?Tenant $tenant;
    private ?App $app;

    public function __construct(
        private readonly string $appKey,
        private readonly string $appSecret,
        private readonly HttpClientInterface $client,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TenantIdException|AliexpressAccessTokenManagerException
     */
    public function exchangeTemporaryTokenWithAccessToken(string $tenantId, string $token): ?App
    {
        $tenant = $this->getTenant($tenantId);
        $app = $this->getApp($tenant);

        return $this->makeRequest($tenant, $app, self::GENERATE_TOKEN_ACTION, ['code' => $token]);
    }

    /**
     * @throws TenantIdException|AliexpressAccessTokenManagerException
     */
    public function refreshAccessToken(string $tenantId): ?App
    {
        $tenant = $this->getTenant($tenantId);
        $app = $this->getApp($tenant);

        $refreshToken = $app->getConfig()['refreshToken'] ?? throw new AliexpressAccessTokenManagerException('Refresh token missing');

        return $this->makeRequest($tenant, $app, self::REFRESH_TOKEN_ACTION, ['refresh_token' => $refreshToken]);
    }

    /**
     * @throws TenantIdException
     */
    public function isAccessTokenExpired(string $tenantId): bool
    {
        $app = $this->getApp($this->getTenant($tenantId));
        $currentConfig = $app->getConfig();

        return $currentConfig['accessTokenExpireAtTimeStamp'] < (new DateTimeImmutable())->getTimestamp();
    }

    /**
     * @throws TenantIdException
     * @throws AliexpressAccessTokenManagerException
     */
    public function getAccessToken(string $tenantId): string
    {
        $tenant = $this->getTenant($tenantId);
        $app = $this->getApp($tenant);
        $currentConfig = $app->getConfig();

        if (!isset($currentConfig['accessToken'])) {
            throw new AliexpressAccessTokenManagerException(message: 'Access token missing');
        }

        if ($this->isAccessTokenExpired($tenantId)) {
            $this->refreshAccessToken($tenantId);
            $app = $this->getApp($tenant); // Refresh the app configuration after token refresh
            $currentConfig = $app->getConfig();
        }

        return $currentConfig['accessToken'];
    }

    /**
     * @throws TenantIdException
     */
    private function getTenant(string $tenantId): Tenant
    {
        return $this->tenant ??= $this->tenantRepository->findOneById($tenantId)
            ?? throw new TenantIdException('Tenant is not found');
    }

    /**
     * @throws TenantIdException
     */
    private function getApp(Tenant $tenant): App
    {
        return $this->app ??= $tenant->getApp(AppId::AliExpress)
            ?? throw new TenantIdException('Missing configuration');
    }

    /**
     * @throws AliexpressAccessTokenManagerException
     */
    private function makeRequest(Tenant $tenant, App $app, string $action, array $body): ?App
    {
        $currentTimestamp = (new DateTimeImmutable())->getTimestamp();
        $query = array_merge($body, [
            'app_key' => $this->appKey,
            'timestamp' => "{$currentTimestamp}000",
            'sign_method' => self::SIGN_METHOD,
        ]);
        $query['sign'] = $this->signData($action, $query);

        try {
            $response = $this->client->request(Request::METHOD_POST, self::API_URL.$action, [
                'body' => $body,
                'query' => $query,
            ]);

            $this->logger->info('Token exchange', [
                'action' => $action,
                'response' => $response->getContent(),
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logger->error('Token exchange failed', [
                    'body' => $body,
                    'response' => $response->getContent(),
                ]);
                throw new AliexpressAccessTokenManagerException('Aliexpress service error');
            }

            $data = $response->toArray();

            if (isset($data['code']) && '0' !== $data['code']) {
                $this->logger->error('Token exchange failed', [
                    'body' => $body,
                    'response' => $response->getContent(),
                ]);
                throw new AliexpressAccessTokenManagerException('Non-zero response code');
            }

            $existingSeller = $this->tenantRepository->findOneByAliexpressSellerId($data['seller_id']);
            if (null !== $existingSeller && null !== $this->tenant && $this->tenant->getId() !== $existingSeller->getId()) {
                throw new AliexpressAccessTokenManagerException('This seller has already registered on the platform');
            }

            $config = [
                'accessToken' => $data['access_token'],
                'sellerId' => $data['seller_id'],
                'refreshToken' => $data['refresh_token'],
                'accessTokenExpireAtTimeStamp' => $currentTimestamp + $data['expires_in'],
                'refreshTokenExpireAtTimeStamp' => $currentTimestamp + $data['refresh_expires_in'],
            ];

            $app->setConfig(array_merge($app->getConfig(), $config));
            $tenant->populateApp($app);
            $this->tenantRepository->save($tenant);

            return $app;
        } catch (
            DecodingExceptionInterface|
            TransportExceptionInterface|
            ServerExceptionInterface|
            ClientExceptionInterface|
            RedirectionExceptionInterface $e
        ) {
            $this->logger->error('Token exchange failed', [
                'body' => $body,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function signData(string $action, array $bodyData): string
    {
        $data = $action;
        ksort($bodyData);

        foreach ($bodyData as $key => $value) {
            $data .= $key.$value;
        }

        return strtoupper(hash_hmac(self::SIGN_METHOD, $data, $this->appSecret));
    }
}
