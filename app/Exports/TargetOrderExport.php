<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetOrderExport implements WithStyles, WithCustomStartCell, WithEvents
{

    use Exportable;

    protected $order;

    private $endRow;

    const DEFAULT_ROW_HEIGHT = 30;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:F1')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');


        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2:B2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:B2')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        $sheet->getStyle('C2:F2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('C2:F2')->getBorders()->getAllBorders()->getColor()->setRGB('000000');

        $sheet->getStyle('C2:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $this->orderDate($sheet);
        $this->orderContact($sheet);
        $this->orderInfo($sheet);
        $this->orderItem($sheet);
        $this->note($sheet);

        $this->rowDimension($sheet);
    }

    public function rowDimension(Worksheet $sheet)
    {
        // height
        $sheet->getRowDimension('1')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('2')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('3')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('4')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('5')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('6')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('7')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('8')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('9')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('10')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('11')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('12')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('13')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('14')->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->getRowDimension('15')->setRowHeight(self::DEFAULT_ROW_HEIGHT);

        for ($i = 16; $i <= $this->endRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        }

        // width
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(30);
    }

    private function orderDate(Worksheet $sheet)
    {
        $sheet->getStyle("C2:D2")->getFont()->setBold(true);
        $sheet->mergeCells('C2:D2');
        $sheet->setCellValue('C2', '注文日');

        $sheet->mergeCells('E2:F2');
        $sheet->setCellValue('E2', Carbon::parse($this->order->order_date)->format('Y-m-d'));
    }

    private function orderContact(Worksheet $sheet)
    {
        //
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:F3')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');


        //
        $sheet->getStyle('B4:B7')->getAlignment()->setVertical(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B4:B7')->getFont()->setBold(true);

        $sheet->getStyle('A4:A7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:A7')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        $sheet->getStyle('B4:F7')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('B4:F7')->getBorders()->getLeft()->getColor()->setRGB('000000');

        $sheet->getStyle('B4:F7')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('B4:F7')->getBorders()->getBottom()->getColor()->setRGB('000000');

        $sheet->getStyle('B4:F7')->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('B4:F7')->getBorders()->getTop()->getColor()->setRGB('000000');

        $sheet->getStyle('B4:F7')->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('B4:F7')->getBorders()->getRight()->getColor()->setRGB('000000');


        $sheet->getStyle('B4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B4')->getFont()->setBold(true)->setSize(18);
        $sheet->mergeCells("B4:C4");
        $sheet->setCellValue('B4', '　フクシマガリレリレイ株式会社');

        $sheet->mergeCells('D4:F4');
        $sheet->setCellValue('D4', '　東日本工事部　SB事業部');


        //
        $sheet->mergeCells("B5:F5");
        $sheet->setCellValue('B5', '　住所： 〒 273-0028　千葉県船橋市海神町東1-1014-3');

        //
        $sheet->getStyle('C6:C7')->getAlignment()->setVertical(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C6:C7')->getFont()->setBold(true);

        $sheet->setCellValue('B6', '　TEL：047-419-6496');
        $sheet->setCellValue('C6', '　FAX：047-427-1191');
        $sheet->setCellValue('D6', '');

        //
        $sheet->setCellValue('B7', '　注文者：'. $this->order->customer_name);

        $sheet->getStyle("C7:F7")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells("C7:F7");
        $sheet->setCellValue('C7', '　営業担当者：'. $this->order->sales_representative);
    }

    private function orderInfo(Worksheet $sheet)
    {
        //
        $sheet->mergeCells('A8:F8');
        $sheet->getStyle('A8:F8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:F8')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $sheet->getStyle('A9:F13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A9:F13')->getBorders()->getAllBorders()->getColor()->setRGB('000000');
        $sheet->getStyle('A9:A13')->getFont()->setBold(true);
        $sheet->getStyle('D9:D13')->getFont()->setBold(true);
        $sheet->getStyle('A9:F13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        //
        $sheet->setCellValue('A9', '物件名');
        $sheet->mergeCells('B9:C9')->setCellValue('B9', $this->order->project_name);
        $sheet->mergeCells('D9:E9')->setCellValue('D9', '注文No');
        $sheet->setCellValue('F9', $this->order->order_no);

        //
        $sheet->setCellValue('A10', '納期');
        $sheet->mergeCells('B10:C10')->setCellValue('B10', Carbon::parse($this->order->delivery_date)->format('Y-m-d H:i'));
        $sheet->mergeCells('D10:E10')->setCellValue('D10', '検収予定月');
        $sheet->setCellValue('F10', Carbon::parse($this->order->expected_inspection_month)->format('Y-m'));

        //
        $sheet->setCellValue('A11', '納入先');
        $sheet->mergeCells('B11:C11')->setCellValue('B11', $this->order->delivery_destination);
        $sheet->mergeCells('D11:E11')->setCellValue('D11', '納入先TEL');
        $sheet->setCellValue('F11', $this->order->delivery_destination_phone);

        //
        $sheet->setCellValue('A12', '納入先住所');
        $sheet->mergeCells('B12:F12')->setCellValue('B12', $this->order->delivery_destination_zip_code . ' ' . $this->order->delivery_destination_address);

        //
        $sheet->setCellValue('A13', '納入先担当者');
        $sheet->mergeCells('B13:C13')->setCellValue('B13', $this->order->receiver_person_in_charge);
        $sheet->mergeCells('D13:E13')->setCellValue('D13', '担当者TEL');
        $sheet->setCellValue('F13', $this->order->receiver_phone_number);
    }

    private function orderItem(Worksheet $sheet)
    {
        //
        $sheet->mergeCells('A14:F14');
        $sheet->getStyle('A14:F14')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A14:F14')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $sheet->getStyle('A15:F15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A15:F15')->getBorders()->getAllBorders()->getColor()->setRGB('000000');
        $sheet->getStyle('A15:F15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // title
        $sheet->setCellValue('A15', '商品コード');
        $sheet->setCellValue('B15', '商品名・規格');
        $sheet->setCellValue('C15', '数量');
        $sheet->setCellValue('D15', '重量(kg)');
        $sheet->setCellValue('E15', '単価');
        $sheet->setCellValue('F15', '金額（税抜)');

        $sheet->getStyle('A15:F15')->getFont()->setBold(true);

        // Items
        $total = 0;
        $items = $this->order->items;
        $row = 16;
        $itemCount = count($items);

        $minRows = 8;
        $this->endRow = $row + max($itemCount, $minRows);

        $sheet->getStyle("A16:F{$this->endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A16:F{$this->endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Handle order items
        foreach ($items as $item) {
            $this->fillItemRow($sheet, $row, $item);
            $row++;
            $total += $item->sub_total;
        }

        $sheet->getStyle("A{$this->endRow}:B{$this->endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$this->endRow}:B{$this->endRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        $sheet->mergeCells("A{$this->endRow}:B{$this->endRow}");
        $sheet->setCellValue("A{$this->endRow}", '※上記金額には消費税は含まれておりません');

        $sheet->setCellValue("C{$this->endRow}", '合計重量');
        $sheet->getStyle("C{$this->endRow}")->getFont()->setBold(true);


        $sheet->setCellValue("E{$this->endRow}", '合計金額');
        $sheet->getStyle("E{$this->endRow}")->getFont()->setBold(true);

        $sheet->setCellValue("F{$this->endRow}", $total);
    }

    private function fillItemRow(Worksheet $sheet, $row, $item)
    {
        $sheet->setCellValue("A{$row}", $item->product->product_code);

        $sheet->setCellValue("B{$row}", $item->product->name . ' ' . $item->product->product_type);

        $sheet->setCellValue("C{$row}", $item->qty);

        $sheet->setCellValue("D{$row}", number_format($item->product->weight * $item->qty, 2));

        $sheet->setCellValue("E{$row}", $item->product->price);

        $sheet->setCellValue("F{$row}", $item->sub_total);
    }

    private function note(Worksheet $sheet)
    {
        $start = $this->endRow + 1;
        $sheet->getRowDimension($start)->setRowHeight(self::DEFAULT_ROW_HEIGHT);

        //
        $sheet->mergeCells("A{$start}:F{$start}");
        $sheet->getStyle("A{$start}:F{$start}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$start}:F{$start}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $start++;
        $sheet->getStyle("A{$start}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($start)->setRowHeight(self::DEFAULT_ROW_HEIGHT);
        $sheet->mergeCells("A{$start}:F{$start}");
        $sheet->getStyle("A{$start}:F{$start}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$start}:F{$start}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');
        $sheet->setCellValue("A{$start}", '備考');
        $sheet->getStyle("A{$start}")->getFont()->setBold(true);

        //
        $start++;
        $sheet->getRowDimension($start)->setRowHeight(60);
        $end = $start + 3;

        $sheet->getStyle("A{$start}:F{$end}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells("A{$start}:F{$end}");
        $sheet->getStyle("A{$start}:F{$end}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$start}:F{$end}")->getBorders()->getAllBorders()->getColor()->setRGB('000000');
        $sheet->setCellValue("A{$start}", $this->order->note);
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $writer = $event->writer;
                $spreadsheet = $writer->getDelegate();

                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    $sheet->getStyle($sheet->calculateWorksheetDimension())
                        ->getFont()
                        ->setName('sun-exta');
                }
            },
        ];
    }
}
