<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Language;

use App\Domain\Model\Language\LanguageServiceInterface;

final class LanguageService implements LanguageServiceInterface
{
    /**
     * @param string[] $supportedLanguages
     */
    public function __construct(private readonly array $supportedLanguages)
    {
    }

    public function isSupported(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }

    /** @return string[] */
    public function getSupported(): array
    {
        return $this->supportedLanguages;
    }
}
