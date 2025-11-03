<?php

declare(strict_types=1);

namespace App\Domain\Model\Order;

enum ProcessingStatus: string
{
    case New = 'new';

    case Processing = 'processing';

    case Shipped = 'shipped';

    case Delivered = 'delivered';

    case Canceled = 'canceled';

    public static function getAeMappingOrderStatus(string $aeOrderStatus): ?self
    {
        return match ($aeOrderStatus) {
            'paymentFailedEvent' => self::New,
            'OrderCreated' => self::New,
            'OrderClosed' => self::Canceled,
            'PaymentAuthorized' => self::Processing,
            'OrderShipped' => self::Shipped,
            'OrderConfirmed' => self::Delivered,
            default => null,
        };
    }
}
