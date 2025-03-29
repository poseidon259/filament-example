<?php

namespace App\Exports;

use App\Exports\Sheets\OrderDeliveryDuplicateSheet;
use App\Exports\Sheets\OrderDeliverySheet;
use App\Exports\Sheets\OrderInvoiceSheet;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeWriting;

class OrderExport implements WithMultipleSheets, WithEvents
{
    use Exportable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new OrderDeliverySheet($this->order),
//            new OrderInvoiceSheet($this->order),
            new OrderDeliveryDuplicateSheet($this->order),
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $writer = $event->writer;
                $spreadsheet = $writer->getDelegate();

                foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
                    if ($index == 0) {
                        $sheet->getPageMargins()->setTop(0.2);
                    }

                    $sheet->getStyle($sheet->calculateWorksheetDimension())
                        ->getFont()
                        ->setName('sun-exta');
                }
            },
        ];
    }
}
