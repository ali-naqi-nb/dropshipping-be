<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

final class DsOrderCreatedData
{
    /**
     * @param array{firstName: string, lastName: string, phone: string, country: string, area: ?string, city: string, phBarangay: ?string, postCode: string, address: string, addressAdditions: ?string, officeId: ?string, companyName: ?string, companyVat: ?string} $shippingAddress
     * @param array<array{productId: string, name: string, quantity: int}>                                                                                                                                                                                             $orderProducts
     */
    public function __construct(
        private readonly string $orderId,
        private readonly array $shippingAddress,
        private readonly array $orderProducts,
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return array{firstName: string, lastName: string, phone: string, country: string, area: ?string, city: string, phBarangay: ?string, postCode: string, address: string, addressAdditions: ?string, officeId: ?string, companyName: ?string, companyVat: ?string}
     */
    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    /**
     * @return array<array{productId: string, name: string, quantity: int}>
     */
    public function getOrderProducts(): array
    {
        return $this->orderProducts;
    }
}
