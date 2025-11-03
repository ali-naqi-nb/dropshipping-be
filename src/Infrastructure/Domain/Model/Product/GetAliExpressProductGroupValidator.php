<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\GetAliExpressProductGroupValidatorInterface;
use App\Infrastructure\Domain\Model\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class GetAliExpressProductGroupValidator extends AbstractValidator implements GetAliExpressProductGroupValidatorInterface
{
    public function __construct(
        SymfonyValidatorInterface $validator,
    ) {
        parent::__construct($validator);
    }

    protected function getFields(?string $group = null): array
    {
        return [
            'id' => [
                new Assert\NotBlank(message: 'Product group ID is required.'),
                new Assert\Uuid(message: 'Product group ID must be a valid UUID.'),
            ],
        ];
    }
}
