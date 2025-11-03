<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Security;

use App\Domain\Model\AuthToken\User\UserAuthTokenRepositoryInterface;
use App\Infrastructure\Domain\Model\Session\SessionUser;
use App\Infrastructure\Security\AdminTokenAuthenticator;
use App\Tests\Shared\Factory\AuthTokenFactory;
use App\Tests\Shared\Factory\UserFactory;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class AdminTokenAuthenticatorTest extends UnitTestCase
{
    public function testAuthenticationWithValidData(): void
    {
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $sessionUserMock = $this->createMock(SessionUser::class);
        $authTokenRepositoryMock->expects($this->once())
            ->method('findNextUserId')
            ->willReturn(AuthTokenFactory::ID);
        $authTokenRepositoryMock->expects($this->once())
            ->method('findValueById')
            ->with(AuthTokenFactory::ID)
            ->willReturn($sessionUserMock);

        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $request = new Request();
        $request->headers->set('authorization', 'Bearer '.AuthTokenFactory::TOKEN);
        $request->headers->set('x-consumer-custom-id', UserFactory::ADMIN_ID);

        $response = $authenticator->authenticate($request);
        $this->assertInstanceOf(SelfValidatingPassport::class, $response);
        $this->assertInstanceOf(SessionUser::class, $response->getUser());
    }

    public function testAuthenticationWithInvalidData(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Provided token is invalid.');
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $request = new Request();

        $authenticator->authenticate($request);
    }

    public function testAuthenticationNoFoundInAuthTokenRepo(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Provided token is invalid.');
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $authTokenRepositoryMock->expects($this->once())
            ->method('findNextUserId')
            ->willReturn(AuthTokenFactory::ID);
        $authTokenRepositoryMock->expects($this->once())
            ->method('findValueById')
            ->with(AuthTokenFactory::ID)
            ->willReturn(null);

        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $request = new Request();
        $request->headers->set('authorization', 'Bearer '.AuthTokenFactory::TOKEN);
        $request->headers->set('x-consumer-custom-id', UserFactory::ADMIN_ID);

        $authenticator->authenticate($request);
    }

    public function testAuthenticationSuccess(): void
    {
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $request = new Request();
        $tokenMock = $this->createMock(TokenInterface::class);

        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);

        $this->assertNull($authenticator->onAuthenticationSuccess($request, $tokenMock, 'admin'));
    }

    public function testAuthenticationFailure(): void
    {
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $exception = $this->createMock(AuthenticationException::class);
        $request = new Request();

        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $response = $authenticator->onAuthenticationFailure($request, $exception);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testSupportsReturnsTrueAuthorizationHeadersSet(): void
    {
        $request = new Request();
        $request->headers->set('authorization', 'Bearer '.AuthTokenFactory::TOKEN);
        $request->headers->set('x-consumer-custom-id', UserFactory::ADMIN_ID);

        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $this->assertTrue($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseAnonymousHeaderSet(): void
    {
        $authTokenRepositoryMock = $this->createMock(UserAuthTokenRepositoryInterface::class);
        $authenticator = new AdminTokenAuthenticator($authTokenRepositoryMock);
        $this->assertFalse($authenticator->supports(new Request()));
    }
}
