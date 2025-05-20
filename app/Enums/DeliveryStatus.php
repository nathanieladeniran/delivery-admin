<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'Pending';
    case AWAITING_PICKUP = 'Awaiting-Pickup';
    case DECLINED = 'Declined';
    case DELIVERED = 'Delivered';
    case IN_TRANSIT = 'In-Transit';
    case CANCELLED = 'Cancelled';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
