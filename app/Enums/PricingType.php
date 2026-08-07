<?php

namespace App\Enums;

enum PricingType: string
{
    case Lifetime = 'lifetime';
    case PerMonth = 'per_month';

    public function label(): string
    {
        return match ($this) {
            self::Lifetime => __('Lifetime'),
            self::PerMonth => __('Per Month'),
        };
    }
}
