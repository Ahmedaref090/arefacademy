<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VodafoneCash = 'vodafone_cash';
    case InstaPay = 'instapay';

    public function label(): string
    {
        return match ($this) {
            self::VodafoneCash => 'فودافون كاش',
            self::InstaPay => 'إنستاباي',
        };
    }
}
