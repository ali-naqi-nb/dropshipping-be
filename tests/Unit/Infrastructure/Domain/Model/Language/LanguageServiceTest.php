<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Domain\Model\Language;

use App\Domain\Model\Language\LanguageServiceInterface;
use App\Infrastructure\Domain\Model\Language\LanguageService;
use App\Tests\Shared\Factory\LocaleFactory;
use App\Tests\Unit\UnitTestCase;

final class LanguageServiceTest extends UnitTestCase
{
    public function testIsSupported(): void
    {
        $service = new LanguageService([LocaleFactory::BG, LocaleFactory::EN]);

        $this->assertInstanceOf(LanguageServiceInterface::class, $service);
        $this->assertTrue($service->isSupported(LocaleFactory::BG));
        $this->assertTrue($service->isSupported(LocaleFactory::EN));
        $this->assertFalse($service->isSupported(LocaleFactory::NOT_SUPPORTED));
    }

    public function testGetSupported(): void
    {
        $service = new LanguageService([LocaleFactory::EN, LocaleFactory::BG]);

        $this->assertSame(LocaleFactory::getSupportedLocales(), $service->getSupported());
    }
}
