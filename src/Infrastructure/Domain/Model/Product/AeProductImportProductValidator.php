<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Application\Service\AliExpress\AeUtil;
use App\Application\Service\TranslatorInterface;
use App\Domain\Model\Product\AeProductImportProductValidatorInterface;
use App\Infrastructure\Domain\Model\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class AeProductImportProductValidator extends AbstractValidator implements AeProductImportProductValidatorInterface
{
    public function __construct(
        SymfonyValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct($validator);
    }

    protected function getFields(?string $group = null): array
    {
        return [
            'aeProductUrl' => [
                new Assert\Sequentially([
                    new Assert\NotBlank(),
                    new Assert\Regex(
                        pattern: AeUtil::AE_PRODUCT_URL_PATTERN,
                        message: $this->translator->trans('Invalid AliExpress product URL'),
                        match: true,
                    ),
                ]),
            ],
            'aeProductShipsTo' => [
                new Assert\Sequentially([
                    new Assert\NotBlank(),
                    new Assert\Country(),
                ]),
            ],
        ];
    }
}
