<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\AuthToken\User;

use App\Domain\Model\AuthToken\User\UserAuthToken;
use App\Domain\Model\Session\SessionUserInterface;
use App\Tests\Shared\Factory\AuthTokenFactory;
use App\Tests\Shared\Factory\SessionUserFactory;
use App\Tests\Unit\UnitTestCase;

final class UserAuthTokenTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $authToken = new UserAuthToken(
            AuthTokenFactory::ID,
            SessionUserFactory::getSessionUser(),
            AuthTokenFactory::TOKEN,
        );

        $this->assertSame(AuthTokenFactory::ID, $authToken->getId());
        $this->assertInstanceOf(SessionUserInterface::class, $authToken->getValue());
        $this->assertSame(AuthTokenFactory::TOKEN, $authToken->getToken());
        $this->assertEquals(new \DateInterval('PT30M'), $authToken->getExpiresAfter());
    }
}
