<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Model\AuthToken\User\UserAuthTokenRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class AdminTokenAuthenticator extends AbstractAuthenticator
{
    public const AUTH_TOKEN_PREFIX = 'Bearer';

    public function __construct(private readonly UserAuthTokenRepositoryInterface $authTokenRepository)
    {
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('authorization') && $request->headers->has('x-consumer-custom-id');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $token = $this->cleanToken((string) $request->headers->get('authorization'));
        $userId = (string) $request->headers->get('x-consumer-custom-id');
        $authTokenId = $this->authTokenRepository->findNextUserId($userId, $token);
        $authTokenValue = $this->authTokenRepository->findValueById($authTokenId);

        if (null === $authTokenValue) {
            throw new CustomUserMessageAuthenticationException('Provided token is invalid.');
        }

        return new SelfValidatingPassport(new UserBadge($authTokenId, fn () => $authTokenValue));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse($exception->getMessage(), 401);
    }

    private function cleanToken(string $token): string
    {
        if (0 === stripos($token, self::AUTH_TOKEN_PREFIX)) {
            $token = str_ireplace(self::AUTH_TOKEN_PREFIX, '', $token);
        }

        return trim($token);
    }
}
