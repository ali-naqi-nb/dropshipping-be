<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model\Product;

use App\Domain\Model\Product\CreateAliexpressProductGroupValidatorInterface;
use App\Infrastructure\Domain\Model\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class CreateAliexpressProductGroupValidator extends AbstractValidator implements CreateAliexpressProductGroupValidatorInterface
{
    public function __construct(
        SymfonyValidatorInterface $validator,
    ) {
        parent::__construct($validator);
    }

    protected function getFields(?string $group = null): array
    {
        return [
            'products' => [
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Collection([
                        'fields' => [
                            'aeProductId' => new Assert\PositiveOrZero(),
                            'aeSkuId' => new Assert\PositiveOrZero(),
                            'name' => new Assert\NotBlank(),
                            'description' => new Assert\Type('string'),
                            'sku' => new Assert\Type('string'),
                            'price' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'stock' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'mainCategoryId' => [new Assert\NotBlank(), new Assert\Uuid()],
                            'additionalCategories' => [new Assert\Optional(), new Assert\All([new Assert\Uuid()])],
                            'barcode' => new Assert\Type('string'),
                            'weight' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'length' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'height' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'width' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'costPerItem' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                            'productTypeName' => new Assert\NotBlank(),
                            'attributes' => new Assert\All([
                                new Assert\Collection([
                                    'fields' => [
                                        'name' => new Assert\NotBlank(),
                                        'type' => new Assert\NotBlank(),
                                        'value' => new Assert\NotBlank(),
                                    ],
                                ]),
                            ]),
                            'images' => new Assert\All([
                                new Assert\NotBlank(),
                            ]),
                            'shippingOption' => new Assert\Collection([
                                'fields' => [
                                    'code' => new Assert\NotBlank(),
                                    'shipsFrom' => new Assert\NotBlank(),
                                    'minDeliveryDays' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                                    'maxDeliveryDays' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                                    'shippingFeePrice' => [new Assert\Type('integer'), new Assert\PositiveOrZero()],
                                    'shippingFeeCurrency' => new Assert\NotBlank(),
                                    'isFreeShipping' => new Assert\Type('bool'),
                                ],
                            ]),
                        ],
                    ]),
                ]),
            ],
        ];
    }
}
