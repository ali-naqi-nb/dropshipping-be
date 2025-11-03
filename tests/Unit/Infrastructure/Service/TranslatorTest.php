<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\Translator;
use App\Tests\Unit\UnitTestCase;
use Symfony\Component\Translation\Translator  as SymfonyTranslator;

final class TranslatorTest extends UnitTestCase
{
    private mixed $symfonyTranslator;
    private Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->symfonyTranslator = $this->createMock(SymfonyTranslator::class);
        $this->translator = new Translator($this->symfonyTranslator);
    }

    public function testTransWorks(): void
    {
        $id = 'test msg';
        $parameters = ['test' => 'test'];
        $domain = 'test';
        $locale = 'bg_BG';
        $translation = 'awesome test msg.';

        $this->symfonyTranslator->expects($this->once())
            ->method('trans')
            ->with($id, $parameters, $domain, $locale)
            ->willReturn($translation);

        $this->assertSame($translation, $this->translator->trans($id, $parameters, $domain, $locale));
    }
}
