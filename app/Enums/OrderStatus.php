<?php

namespace App\Enums;

enum OrderStatus : int
{
    case Placed = 1;
    case Processing = 2;
    case Delivering = 3;
    case Delivered = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Processing => 'order_processing',
            self::Delivering => 'order_delivering',
            self::Delivered => 'order_delivered',
            default => 'order_placed',
        };
    }
}
