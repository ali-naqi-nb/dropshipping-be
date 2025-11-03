<?php

declare(strict_types=1);

namespace App\Application\Service;

interface TranslatorInterface
{
    /**
     * @param array<mixed,string> $parameters
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string;

    public function getLocale(): string;

    public function setLocale(string $locale): self;
}
