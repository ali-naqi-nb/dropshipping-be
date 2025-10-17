<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Service\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

final class Translator implements TranslatorInterface
{
    /** @param \Symfony\Component\Translation\Translator $symfonyTranslator */
    public function __construct(private readonly SymfonyTranslatorInterface $symfonyTranslator)
    {
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->symfonyTranslator->trans($id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->symfonyTranslator->getLocale();
    }

    public function setLocale(string $locale): self
    {
        $this->symfonyTranslator->setLocale($locale);

        return $this;
    }
}
