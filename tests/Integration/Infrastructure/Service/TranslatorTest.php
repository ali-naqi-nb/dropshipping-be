<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service;

use App\Infrastructure\Service\Translator;
use App\Tests\Integration\IntegrationTestCase;

final class TranslatorTest extends IntegrationTestCase
{
    private Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Translator $translator */
        $translator = self::getContainer()->get(Translator::class);
        $this->translator = $translator;
    }

    public function testSetAndGetLocal(): void
    {
        $locale = 'bg_BG';
        $this->translator->setLocale($locale);

        $this->assertSame($locale, $this->translator->getLocale());
    }
}
