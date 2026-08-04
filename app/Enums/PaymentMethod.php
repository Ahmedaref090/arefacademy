<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VodafoneCash = 'vodafone_cash';
    case InstaPay = 'instapay';
    case BankTransfer = 'bank_transfer';
    case Fawry = 'fawry';

    public function label(): string
    {
        return match ($this) {
            self::VodafoneCash => 'فودافون كاش',
            self::InstaPay => 'إنستاباي',
            self::BankTransfer => 'تحويل بنكي',
            self::Fawry => 'فوري',
        };
    }
}
