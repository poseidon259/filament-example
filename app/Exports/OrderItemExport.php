<?php

namespace App\Exports;

use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderItemExport implements FromCollection, WithHeadings, WithMapping, ShouldQueue
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $orderIds = $this->data->pluck('id');
        return OrderItem::whereIn('order_id', $orderIds)->get();
    }


    public function headings(): array
    {
        return [
            '注文日',
            '注文者',
            '物件名',
            '注文№',
            '納期',
            '検収予定月',
            '納入先',
            '納入先住所',
            '納入先担当者',
            '担当者TEL',
            '商品名',
            '規格',
            '数量',
            '重量',
            '単価',
            '金額'
        ];
    }

    public function map($row): array
    {
        return [
            $row->order->order_date,
            $row->order->customer_name,
            $row->order->project_name,
            $row->order->order_no,
            $row->order->delivery_date,
            $row->order->expected_inspection_month,
            $row->order->delivery_destination,
            $row->order->delivery_destination_address,
            $row->order->receiver_person_in_charge,
            $row->order->receiver_phone_number,
            $row->product->name,
            $row->product->product_type,
            $row->qty,
            $row->weight ?? '',
            $row->price,
            $row->sub_total
        ];
    }
}
