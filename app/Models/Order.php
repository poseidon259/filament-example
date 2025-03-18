<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status',
        'order_date',
        'customer_name',
        'sales_representative',
        'project_name',
        'order_no',
        'delivery_date',
        'expected_inspection_month',
        'delivery_destination',
        'delivery_destination_phone',
        'delivery_destination_zip_code',
        'delivery_destination_address',
        'receiver_person_in_charge',
        'receiver_phone_number',
        'total',
        'exported_note',
        'obic_registered_at',
        'shipment_arranged_at',
        'specified_invoice_exported_note',
        'note'
    ];

    protected $casts = [
        'status' => OrderStatus::class
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('created_at')->orderBy('id');
    }
}
