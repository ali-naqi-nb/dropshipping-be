<?php

declare(strict_types=1);

namespace App\Application\Command\Order\AliexpressSyncOrderStatus;

use App\Application\Command\AbstractCommand;

final class AliexpressSyncOrdersStatusCommand extends AbstractCommand
{
    /** @var array{buyerId: int|string, orderId: int|string, orderStatus: string, sellerId?: string|int|null} */
    private array $data;
    private int $messageType;
    private string|int $sellerId;
    private string $site;
    private int $timestamp;

    /**
     * @param array{buyerId: int|string, orderId: int|string, orderStatus: string, sellerId?: string|int|null} $data
     */
    public function __construct(
        array $data,
        int $message_type,
        ?string $seller_id,
        string $site,
        int $timestamp
    ) {
        $this->data = $data;
        $this->messageType = $message_type;
        $this->sellerId = $data['sellerId'] ?? $seller_id ?? '';
        $this->site = $site;
        $this->timestamp = $timestamp;
    }

    /**
     * @return array{buyerId: int|string, orderId: int|string, orderStatus: string, sellerId?: string|int|null}
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function getSellerId(): string|int
    {
        return $this->sellerId;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
