<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Domain\Model\Session;

use App\Infrastructure\Domain\Model\Session\SessionUser;
use App\Tests\Shared\Factory\SessionUserFactory;
use App\Tests\Unit\UnitTestCase;

final class SessionUserTest extends UnitTestCase
{
    public function testGettersReturnCorrectData(): void
    {
        $sessionUser = new SessionUser(
            SessionUserFactory::USER_ID,
            SessionUserFactory::EMAIL,
            SessionUserFactory::PERMISSIONS,
            false,
            false,
            [SessionUserFactory::COMPANY_ID]
        );

        $this->assertSame(SessionUserFactory::USER_ID, $sessionUser->getUserId());
        $this->assertSame(SessionUserFactory::EMAIL, $sessionUser->getEmail());
        $this->assertSame(SessionUserFactory::EMAIL, $sessionUser->getUserIdentifier());
        $this->assertSame(SessionUserFactory::EMAIL, $sessionUser->getUsername());
        $this->assertSame(SessionUserFactory::PERMISSIONS, $sessionUser->getPermissions());
        $this->assertSame(SessionUserFactory::PERMISSIONS, $sessionUser->getRoles());
        $this->assertFalse($sessionUser->isSuperAdmin());
        $this->assertFalse($sessionUser->isNbEmployee());
        $this->assertIsArray($sessionUser->getCompaniesIds());
        $this->assertContainsOnly('string', $sessionUser->getCompaniesIds(), true);
        $this->assertNull($sessionUser->getPassword());
        $this->assertNull($sessionUser->getSalt());
    }

    public function testHasAccessToCompanyNoSuperAdmin(): void
    {
        $sessionUser = new SessionUser(
            SessionUserFactory::USER_ID,
            SessionUserFactory::EMAIL,
            SessionUserFactory::PERMISSIONS,
            false,
            false,
            [SessionUserFactory::COMPANY_ID]
        );

        $this->assertTrue($sessionUser->hasAccessToCompany(SessionUserFactory::COMPANY_ID));
        $this->assertFalse($sessionUser->hasAccessToCompany(SessionUserFactory::SECOND_COMPANY_ID));
    }

    public function testHasAccessToCompanySuperAdmin(): void
    {
        $sessionUser = new SessionUser(
            SessionUserFactory::USER_ID,
            SessionUserFactory::EMAIL,
            SessionUserFactory::PERMISSIONS,
            true,
            false,
            []
        );

        $this->assertTrue($sessionUser->hasAccessToCompany(SessionUserFactory::COMPANY_ID));
        $this->assertTrue($sessionUser->hasAccessToCompany(SessionUserFactory::SECOND_COMPANY_ID));
    }

    public function testEraseCredentials(): void
    {
        $sessionUser = new SessionUser(
            SessionUserFactory::USER_ID,
            SessionUserFactory::EMAIL,
            SessionUserFactory::PERMISSIONS,
            true,
            false,
            []
        );

        $this->expectNotToPerformAssertions();

        $sessionUser->eraseCredentials();
    }
}
