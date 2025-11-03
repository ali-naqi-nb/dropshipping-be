<?php

declare(strict_types=1);

namespace App\Application\Shared\Product;

use App\Application\Service\AliExpress\AeUtil;

final class AeShippingOptionResponse
{
    private function __construct(
        private readonly string $code,
        private readonly string $shipsFrom,
        private readonly int $minDeliveryDays,
        private readonly int $maxDeliveryDays,
        private readonly int $shippingFeePrice,
        private readonly ?string $shippingFeeCurrency,
        private readonly bool $isFreeShipping = false,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getShipsFrom(): string
    {
        return $this->shipsFrom;
    }

    public function getMinDeliveryDays(): int
    {
        return $this->minDeliveryDays;
    }

    public function getMaxDeliveryDays(): int
    {
        return $this->maxDeliveryDays;
    }

    public function getShippingFeePrice(): int
    {
        return $this->shippingFeePrice;
    }

    public function getShippingFeeCurrency(): ?string
    {
        return $this->shippingFeeCurrency;
    }

    public function getIsFreeShipping(): bool
    {
        return $this->isFreeShipping;
    }

    /**
     * @param array<string, mixed> $aeDeliveryOption
     */
    public static function fromAeDeliveryOption(array $aeDeliveryOption): self
    {
        $isFreeShipping = $aeDeliveryOption['free_shipping'];

        return new self(
            code: $aeDeliveryOption['code'],
            shipsFrom: $aeDeliveryOption['ship_from_country'],
            minDeliveryDays: $aeDeliveryOption['min_delivery_days'],
            maxDeliveryDays: $aeDeliveryOption['max_delivery_days'],
            shippingFeePrice: $isFreeShipping ? 0 : AeUtil::toBase100($aeDeliveryOption['shipping_fee_cent'].''),
            shippingFeeCurrency: $isFreeShipping ? null : $aeDeliveryOption['shipping_fee_currency'],
            isFreeShipping: $isFreeShipping,
        );
    }
}
