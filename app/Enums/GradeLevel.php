<?php

namespace App\Enums;

enum GradeLevel: string
{
    case FirstSecondary = '1st_secondary';
    case FirstBac = '1st_bac';
    case SecondBac = '2nd_bac';

    public function label(): string
    {
        return match ($this) {
            self::FirstSecondary => __('1st Secondary'),
            self::FirstBac => __('1st Baccalaureate'),
            self::SecondBac => __('2nd Baccalaureate'),
        };
    }
}
