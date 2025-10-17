<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Domain\Model\Validation;

use App\Infrastructure\Domain\Model\AbstractValidator;
use Symfony\Component\Validator\Constraints\NotNull;

final class DummyValidator extends AbstractValidator
{
    protected function getFields(?string $group = null): array
    {
        return [
            'foo' => [new NotNull()],
        ];
    }
}
