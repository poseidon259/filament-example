<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Blade;
use function Symfony\Component\Translation\t;

enum OrderStatus : string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case PreparingToShip = 'preparing_to_ship';
    case Shipped = 'shipped';
    case Unpaid = 'unpaid';
    case PaymentCompleted = 'payment_completed';


    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'draft',
            self::Confirmed => 'confirmed',
            self::PreparingToShip => 'preparing_to_ship',
            self::Shipped => 'shipped',
            self::Unpaid => 'unpaid',
            self::PaymentCompleted => 'payment_completed',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('messages.draft'),
            self::Confirmed => __('messages.confirmed'),
            self::PreparingToShip => __('messages.preparing_to_ship'),
            self::Shipped => __('messages.shipped'),
            self::Unpaid => __('messages.unpaid'),
            self::PaymentCompleted => __('messages.payment_completed'),
        };
    }
}
