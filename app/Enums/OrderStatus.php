<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus : string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Exported = 'exported';
    case OBICRegistered = 'obic_registered';
    case ShipmentArranged = 'shipment_arranged';
    case SpecifiedInvoiceExported = 'specified_invoice_exported';


    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'draft',
            self::Confirmed => 'confirmed',
            self::Exported => 'exported',
            self::OBICRegistered => 'obic',
            self::ShipmentArranged => 'shipment',
            self::SpecifiedInvoiceExported => 'specified_invoice',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('messages.draft'),
            self::Confirmed => __('messages.confirmed'),
            self::Exported => __('messages.exported'),
            self::OBICRegistered => __('messages.obic_registered'),
            self::ShipmentArranged => __('messages.shipment_arranged'),
            self::SpecifiedInvoiceExported => __('messages.specified_invoice_exported'),
        };
    }
}
