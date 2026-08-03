<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
