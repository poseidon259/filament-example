<?php

namespace App\Exports\Sheets;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderDeliverySheet implements WithStyles, WithCustomStartCell, WithDrawings
{
    protected $order;
    private $endRow;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Set default font size to 11 for the entire sheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setSize(11);;

        $this->adjustColumnWidths($sheet);

        $this->header($sheet);
        $this->orderDetail($sheet);
        $total = 0;
        $this->orderItem($sheet, $total);
        $this->orderPrice($sheet, $total);

        $this->rowDimension($sheet);
    }

    private function adjustColumnWidths(Worksheet $sheet)
    {
        for ($col = 'A'; $col <= 'T'; $col++) {
            $sheet->getColumnDimension($col)->setWidth(5);
        }

        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(8);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(8);
    }

    public function rowDimension(Worksheet $sheet)
    {
        $sheet->getRowDimension('1')->setRowHeight(40);
        $sheet->getRowDimension('2')->setRowHeight(40);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(40);
        $sheet->getRowDimension('5')->setRowHeight(40);
        $sheet->getRowDimension('6')->setRowHeight(20);
        $sheet->getRowDimension('7')->setRowHeight(40);
        for ($row = 8; $row < $this->endRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }

        $sheet->getRowDimension($this->endRow)->setRowHeight(20);
    }

    private function header(Worksheet $sheet)
    {
        // A1:T1
        $sheet->mergeCells('F1:M1');
        $sheet->setCellValue('F1', '納品書');
        $sheet->getStyle('F1:M1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('F1:M1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1:T1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:T1')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        // Add double underline to F1:M1
        $sheet->getStyle('F1:M1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle('F1:M1')->getBorders()->getBottom()->getColor()->setRGB('000000');

        $sheet->getStyle('P1:S1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('P1:S1')->getBorders()->getBottom()->getColor()->setRGB('000000');
        $sheet->setCellValue('P1', 'NO.');
        $sheet->mergeCells('Q1:S1');
        $sheet->setCellValue('Q1', 2000000 + $this->order->id);
        $sheet->getStyle('Q1:S1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // A2:T2
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2:H2')->getFont()->setBold(true)->setSize(18);
        $sheet->setCellValue('A2', 'フクシマガリレイ株式会社　宛');
        $sheet->getStyle('A2:T2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:T2')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        $sheet->setCellValue('P2', 'T-');
        $sheet->mergeCells('Q2:S2');
        $sheet->getStyle('Q2:S2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue('Q2', '3122001007876');
    }

    private function orderDetail(Worksheet $sheet)
    {
        // A3:T3
        $sheet->mergeCells('A3:T3');
        $sheet->getStyle('A3:T3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:T3')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');
        // A4:T4
        $sheet->getStyle('A4:T4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A4:T4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // A4:C4
        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', '納　入　日');

        // D4:H4
        $sheet->mergeCells('D4:H4');
        $sheet->setCellValue('D4', $this->order->project_name);

        // I4:L4
        $sheet->mergeCells('I4:L4');
        $sheet->setCellValue('I4', '納入者コード');

        // M4:R4
        $sheet->mergeCells('M4:R4');
        $sheet->setCellValue('M4', '04347');

        // A5:T5
        $sheet->getStyle('A5:T5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A5:T5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // A5
        $sheet->setCellValue('A5', '物件名');

        // B5:H5
        $sheet->mergeCells('B5:H5');
        $sheet->setCellValue('B5', Carbon::parse($this->order->delivery_date)->format('Y-m-d H:i'));

        // I5:J5
        $sheet->mergeCells('I5:J5');
        $sheet->setCellValue('I5', '納入社名');

        // K5:R5
        $sheet->mergeCells('K5:R5');
        $sheet->setCellValue('K5', 'ユニ金属株式会社');

        // S4:T5
        $sheet->mergeCells('S4:T5');
    }

    private function orderItem(Worksheet $sheet, &$total)
    {
        // A6:T6
        $sheet->mergeCells('A6:T6');
        $sheet->getStyle('A6:T6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:T6')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        // A7:T7
        $sheet->getStyle('A7:T7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A7:T7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // A7:B7
        $sheet->mergeCells('A7:B7');
        $sheet->setCellValue('A7', '分類コード');

        // C7:H7
        $sheet->mergeCells('C7:H7');
        $sheet->setCellValue('C7', '品　　　名　・　型　　　名');

        // I7
        $sheet->setCellValue('I7', '数量');

        // J7:M7
        $sheet->mergeCells('J7:M7');
        $sheet->setCellValue('J7', '単　　価　(税抜)');

        // N7:Q7
        $sheet->mergeCells('N7:Q7');
        $sheet->setCellValue('N7', '金　　額');

        // R7:S7
        $sheet->mergeCells('R7:S7');
        $sheet->setCellValue('R7', '摘　　要');

        // T7
        $sheet->setCellValue('T7', '※');

        // Items
        $items = $this->order->items;
        $row = 8;
        $itemCount = count($items);

        // Min item rows
        $minRows = 8;
        $this->endRow = $row + max($itemCount, $minRows);

        $sheet->getStyle("A8:T{$this->endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A8:T{$this->endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Handle order items
        foreach ($items as $item) {
            $this->fillItemRow($sheet, $row, $item);
            $total += $item->sub_total;
            $row++;
        }

        // Add empty rows
        while ($row < ($this->endRow + 1)) {
            $this->fillEmptyRow($sheet, $row);
            $row++;
        }
    }

    private function fillItemRow(Worksheet $sheet, $row, $item)
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 2200);

        $sheet->mergeCells("C{$row}:H{$row}");
        $sheet->setCellValue("C{$row}", $item->product->name . ' ' . $item->product->product_type);

        $sheet->setCellValue("I{$row}", $item->qty);

        $sheet->mergeCells("J{$row}:M{$row}");
        $sheet->setCellValue("J{$row}", $item->price);

        $sheet->mergeCells("N{$row}:Q{$row}");
        $sheet->setCellValue("N{$row}", $item->sub_total);

        $sheet->mergeCells("R{$row}:S{$row}");
    }

    // Hàm phụ để điền dòng trống
    private function fillEmptyRow(Worksheet $sheet, $row)
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->mergeCells("C{$row}:H{$row}");
        $sheet->mergeCells("J{$row}:M{$row}");
        $sheet->mergeCells("N{$row}:Q{$row}");
        $sheet->mergeCells("R{$row}:S{$row}");
    }

    private function orderPrice(Worksheet $sheet, $total)
    {
        $sheet->mergeCells("A{$this->endRow}:T{$this->endRow}");
        $sheet->getStyle("A{$this->endRow}:T{$this->endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$this->endRow}:T{$this->endRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $baseRow = $this->endRow + 1;
        $startRow = $this->endRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(30);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$startRow}");

        $sheet->mergeCells("C{$startRow}:F{$startRow}");
        $sheet->setCellValue("C{$startRow}", '8％対象小計	');

        $sheet->mergeCells("G{$startRow}:I{$startRow}");
        $sheet->setCellValue("G{$startRow}", '8%消費税');

        $sheet->mergeCells("J{$startRow}:M{$startRow}");
        $sheet->setCellValue("J{$startRow}", '10％対象小計');

        $sheet->mergeCells("N{$startRow}:P{$startRow}");
        $sheet->setCellValue("N{$startRow}", '10%消費税');

        $sheet->mergeCells("Q{$startRow}:T{$startRow}");
        $sheet->setCellValue("Q{$startRow}", '合計金額');

        //
        $startRow++;
        $sheet->getRowDimension($startRow)->setRowHeight(30);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$startRow}");

        $sheet->mergeCells("C{$startRow}:F{$startRow}");
        $sheet->setCellValue("C{$startRow}", '');

        $sheet->mergeCells("G{$startRow}:I{$startRow}");
        $sheet->setCellValue("G{$startRow}", '');

        $sheet->mergeCells("J{$startRow}:M{$startRow}");
        $sheet->setCellValue("J{$startRow}", '');

        $sheet->mergeCells("N{$startRow}:P{$startRow}");
        $sheet->setCellValue("N{$startRow}", '');

        $sheet->mergeCells("Q{$startRow}:T{$startRow}");
        $sheet->setCellValue("Q{$startRow}", number_format($total, 2));

        $sheet->mergeCells("A{$baseRow}:B{$startRow}");
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $startRow++;
        $sheet->getRowDimension($startRow)->setRowHeight(30);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$startRow}");
        $sheet->setCellValue("A{$startRow}", '入力者');

        $sheet->mergeCells("C{$startRow}:D{$startRow}");
        $sheet->setCellValue("C{$startRow}", '技術');

        $sheet->mergeCells("E{$startRow}:F{$startRow}");
        $sheet->setCellValue("E{$startRow}", '購買');

        $sheet->mergeCells("G{$startRow}:H{$startRow}");
        $sheet->setCellValue("G{$startRow}", '上長');

        $sheet->mergeCells("I{$startRow}:J{$startRow}");
        $sheet->setCellValue("I{$startRow}", '上長');

        $sheet->mergeCells("K{$startRow}:L{$startRow}");
        $sheet->setCellValue("K{$startRow}", '担当者	');

        $sheet->mergeCells("M{$startRow}:N{$startRow}");
        $sheet->setCellValue("M{$startRow}", '注文NO.');

        $sheet->mergeCells("O{$startRow}:R{$startRow}");
        $sheet->setCellValue("O{$startRow}", '物件名');

        $sheet->mergeCells("S{$startRow}:T{$startRow}");
        $sheet->setCellValue("S{$startRow}", '備考');

        //
        $startRow++;
        $endRow = $startRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(30);
        $sheet->getRowDimension($endRow)->setRowHeight(30);

        $sheet->getStyle("A{$startRow}:T{$endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$endRow}");
        $sheet->mergeCells("C{$startRow}:D{$endRow}");
        $sheet->mergeCells("E{$startRow}:F{$endRow}");
        $sheet->mergeCells("G{$startRow}:H{$endRow}");
        $sheet->mergeCells("I{$startRow}:J{$endRow}");
        $sheet->mergeCells("K{$startRow}:L{$endRow}");

        $sheet->mergeCells("M{$startRow}:N{$startRow}");
        $sheet->setCellValue("M{$startRow}", '整理NO.	');
        $sheet->mergeCells("O{$startRow}:R{$startRow}");

        $sheet->mergeCells("M{$endRow}:N{$endRow}");
        $sheet->setCellValue("M{$endRow}", '検収月	');
        $sheet->mergeCells("O{$endRow}:R{$endRow}");

        $sheet->mergeCells("S{$startRow}:T{$endRow}");

        //
        $startRow = $endRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(30);

        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("I{$startRow}:P{$startRow}");
        $sheet->setCellValue("I{$startRow}", '軽減税率対象には※をつけてください');

        $sheet->mergeCells("Q{$startRow}:S{$startRow}");
        $sheet->setCellValue("Q{$startRow}", '2023/7/4');

        $sheet->setCellValue("T{$startRow}", '改訂');
    }

    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Stamp');
        $drawing->setDescription('Stamp');

        $imagePath = public_path('/assets/images/stamp.png');

        $drawing->setPath($imagePath);
        $drawing->setCoordinates('S4');
        $drawing->setWidth(150);
        $drawing->setHeight(90);

        $drawing->setOffsetX(3);
        $drawing->setOffsetY(10);

        return $drawing;
    }
}
