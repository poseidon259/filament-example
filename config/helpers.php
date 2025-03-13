<?php

if (!function_exists('_getStatusColor')) {
    function _getStatusColor($status)
    {
        return match ($status) {
            \App\Enums\OrderStatus::Confirmed->value => 'confirmed',
            \App\Enums\OrderStatus::PreparingToShip->value => 'preparing_to_ship',
            \App\Enums\OrderStatus::Shipped->value => 'shipped',
            \App\Enums\OrderStatus::Unpaid->value => 'unpaid',
            \App\Enums\OrderStatus::PaymentCompleted->value => 'payment_completed',
            default => 'draft',
        };
    }
}
