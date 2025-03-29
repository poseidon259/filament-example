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

    private $endFirstTableRow;
    private $endRowSecondTable;

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

        $this->firstTable($sheet);
        $this->secondTable($sheet);

        $this->rowDimension($sheet);
    }

    private function firstTable(Worksheet $sheet)
    {
        $this->headerFirstTable($sheet);
        $this->orderDetailFirstTable($sheet);
        $total = 0;
        $this->orderItemFirstTable($sheet, $total);
        $this->orderPriceFirstTable($sheet, $total);
    }

    private function secondTable(Worksheet $sheet)
    {
        $this->headerSecondTable($sheet);
        $this->orderDetailSecondTable($sheet);
        $total = 0;
        $this->orderItemSecondTable($sheet, $total);
        $this->orderPriceSecondTable($sheet, $total);
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
        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(10);
        $sheet->getRowDimension('4')->setRowHeight(20);
        $sheet->getRowDimension('5')->setRowHeight(20);
        $sheet->getRowDimension('6')->setRowHeight(10);
        $sheet->getRowDimension('7')->setRowHeight(20);
        for ($row = 8; $row < $this->endRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        $sheet->getRowDimension($this->endRow)->setRowHeight(10);
        $sheet->getRowDimension($this->endFirstTableRow)->setRowHeight(10);

        for ($row = $this->endFirstTableRow + 9; $row <= $this->endRowSecondTable; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    private function headerFirstTable(Worksheet $sheet)
    {
        // A1:T1
        $sheet->mergeCells('F1:M1');
        $sheet->setCellValue('F1', '納品書');
        $sheet->getStyle('F1:M1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('F1:M1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

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

    private function orderDetailFirstTable(Worksheet $sheet)
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
        $sheet->setCellValue('D4', Carbon::parse($this->order->order_date)->format('Y-m-d'));

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
        $sheet->setCellValue('B5', $this->order->project_name);

        // I5:J5
        $sheet->mergeCells('I5:J5');
        $sheet->setCellValue('I5', '納入社名');

        // K5:R5
        $sheet->mergeCells('K5:R5');
        $sheet->setCellValue('K5', 'ユニ金属株式会社');

        // S4:T5
        $sheet->mergeCells('S4:T5');
    }

    private function orderItemFirstTable(Worksheet $sheet, &$total)
    {
        // A6:T6
        $sheet->mergeCells('A6:T6');
        $sheet->getStyle('A6:T6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A6:T6')->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        // A7:T7
        $sheet->getStyle('A7:T7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('A7:T7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A7:T7")->getFont()->setBold(true)->setSize(9);
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

    private function orderPriceFirstTable(Worksheet $sheet, $total)
    {
        $sheet->mergeCells("A{$this->endRow}:T{$this->endRow}");
        $sheet->getStyle("A{$this->endRow}:T{$this->endRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$this->endRow}:T{$this->endRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $baseRow = $this->endRow + 1;
        $startRow = $this->endRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
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
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$startRow}");

        $sheet->mergeCells("C{$startRow}:F{$startRow}");
        $sheet->setCellValue("C{$startRow}", '');

        $sheet->mergeCells("G{$startRow}:I{$startRow}");
        $sheet->setCellValue("G{$startRow}", '');

        $sheet->mergeCells("J{$startRow}:M{$startRow}");
        $sheet->setCellValue("J{$startRow}", $total);

        $sheet->mergeCells("N{$startRow}:P{$startRow}");
        $sheet->setCellValue("N{$startRow}", $total * 0.1);

        $sheet->mergeCells("Q{$startRow}:T{$startRow}");
        $sheet->setCellValue("Q{$startRow}", $total + ($total * 0.1));

        $sheet->mergeCells("A{$baseRow}:B{$startRow}");
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $startRow++;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
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
        $sheet->setCellValue("O{$startRow}", $this->order->order_no);

        $sheet->mergeCells("S{$startRow}:T{$startRow}");
        $sheet->setCellValue("S{$startRow}", '備考');

        //
        $startRow++;
        $endRow = $startRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getRowDimension($endRow)->setRowHeight(20);

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
        $sheet->getRowDimension($startRow)->setRowHeight(20);

        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("I{$startRow}:P{$startRow}");
        $sheet->setCellValue("I{$startRow}", '軽減税率対象には※をつけてください');

        $sheet->mergeCells("Q{$startRow}:S{$startRow}");
        $sheet->setCellValue("Q{$startRow}", '2023/7/4');

        $sheet->getStyle("T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("T{$startRow}", '改訂');

        $this->endFirstTableRow = $startRow;
    }

    private function headerSecondTable(Worksheet $sheet)
    {
        // A1:T1
        $startRow = $this->endFirstTableRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(20);

        $startRow = $this->endFirstTableRow + 2;
        $sheet->mergeCells("F{$startRow}:M{$startRow}");
        $sheet->setCellValue("F{$startRow}", '請求明細書');
        $sheet->getStyle("F{$startRow}:M{$startRow}")->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle("F{$startRow}:M{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Add double underline to F1:M1
        $sheet->getStyle("F{$startRow}:M{$startRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle("F{$startRow}:M{$startRow}")->getBorders()->getBottom()->getColor()->setRGB('000000');

        $sheet->getStyle("P{$startRow}:S{$startRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("P{$startRow}:S{$startRow}")->getBorders()->getBottom()->getColor()->setRGB('000000');
        $sheet->setCellValue("P{$startRow}", 'NO.');
        $sheet->mergeCells("Q{$startRow}:S{$startRow}");
        $sheet->setCellValue("Q{$startRow}", 2000000 + $this->order->id);
        $sheet->getStyle("Q{$startRow}:S{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // A2:T2
        $startRow = $this->endFirstTableRow + 3;
        $sheet->getRowDimension($startRow)->setRowHeight(10);
        $sheet->mergeCells("A{$startRow}:H{$startRow}");
        $sheet->getStyle("A{$startRow}:H{$startRow}")->getFont()->setBold(true)->setSize(18);
        $sheet->setCellValue("A{$startRow}", 'フクシマガリレイ株式会社　宛');
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        $sheet->setCellValue("P{$startRow}", 'T-');
        $sheet->mergeCells("Q{$startRow}:S{$startRow}");
        $sheet->getStyle("Q{$startRow}:S{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("Q{$startRow}", '3122001007876');
    }

    private function orderDetailSecondTable(Worksheet $sheet)
    {
        $startRow = $this->endFirstTableRow + 4;
        // A3:T3
        $sheet->getRowDimension($startRow)->setRowHeight(10);
        // A4:T4
        $startRow = $this->endFirstTableRow + 5;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // A4:C4
        $sheet->mergeCells("A{$startRow}:C{$startRow}");
        $sheet->setCellValue("A{$startRow}", '納　入　日');

        // D4:H4
        $sheet->mergeCells("D{$startRow}:H{$startRow}");
        $sheet->setCellValue("D{$startRow}", Carbon::parse($this->order->order_date)->format('Y-m-d'));

        // I4:L4
        $sheet->mergeCells("I{$startRow}:L{$startRow}");
        $sheet->setCellValue("I{$startRow}", '納入者コード');

        // M4:R4
        $sheet->mergeCells("M{$startRow}:R{$startRow}");
        $sheet->setCellValue("M{$startRow}", '04347');

        // A5:T5
        $startRow = $this->endFirstTableRow + 6;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // A5
        $sheet->setCellValue("A{$startRow}", '物件名');

        // B5:H5
        $sheet->mergeCells("B{$startRow}:H{$startRow}");
        $sheet->setCellValue("B{$startRow}", $this->order->project_name);

        // I5:J5
        $sheet->mergeCells("I{$startRow}:J{$startRow}");
        $sheet->setCellValue("I{$startRow}", '納入社名');

        // K5:R5
        $sheet->mergeCells("K{$startRow}:R{$startRow}");
        $sheet->setCellValue("K{$startRow}", 'ユニ金属株式会社');

//        // S4:T5
        $lastRow = $startRow - 1;
        $sheet->mergeCells("S{$lastRow}:T{$startRow}");
    }

    private function orderItemSecondTable(Worksheet $sheet, &$total)
    {
        $startRow = $this->endFirstTableRow + 7;
        $sheet->getRowDimension($startRow)->setRowHeight(10);

        // A7:T7
        $startRow = $this->endFirstTableRow + 8;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getFont()->setBold(true)->setSize(9);
        // A7:B7
        $sheet->mergeCells("A{$startRow}:B{$startRow}");
        $sheet->setCellValue("A{$startRow}", '分類コード');

        // C7:H7
        $sheet->mergeCells("C{$startRow}:H{$startRow}");
        $sheet->setCellValue("C{$startRow}", '品　　　名　・　型　　　名');

        // I7
        $sheet->setCellValue("I{$startRow}", '数量');

        // J7:M7
        $sheet->mergeCells("J{$startRow}:M{$startRow}");
        $sheet->setCellValue("J{$startRow}", '単　　価　(税抜)');

        // N7:Q7
        $sheet->mergeCells("N{$startRow}:Q{$startRow}");
        $sheet->setCellValue("N{$startRow}", '金　　額');

        // R7:S7
        $sheet->mergeCells("R{$startRow}:S{$startRow}");
        $sheet->setCellValue("R{$startRow}", '摘　　要');

        // T7
        $sheet->setCellValue("T{$startRow}", '※');

        // Items
        $items = $this->order->items;
        $row = $this->endFirstTableRow + 9;
        $itemCount = count($items);

        // Min item rows
        $minRows = 8;
        $this->endRowSecondTable = $row + max($itemCount, $minRows) - 1;

        $sheet->getStyle("A{$row}:T{$this->endRowSecondTable}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$row}:T{$this->endRowSecondTable}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Handle order items
        foreach ($items as $item) {
            $this->fillItemRow($sheet, $row, $item);
            $total += $item->sub_total;
            $row++;
        }

        // Add empty rows
        while ($row < ($this->endRowSecondTable + 1)) {
            $this->fillEmptyRow($sheet, $row);
            $row++;
        }
    }

    private function orderPriceSecondTable(Worksheet $sheet, $total)
    {
        $sheet->getRowDimension($this->endRowSecondTable + 1)->setRowHeight(10);

        //
        $baseRow = $this->endRowSecondTable + 2;
        $startRow = $this->endRowSecondTable + 2;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
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
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("B{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("A{$startRow}:B{$startRow}");

        $sheet->mergeCells("C{$startRow}:F{$startRow}");
        $sheet->setCellValue("C{$startRow}", '');

        $sheet->mergeCells("G{$startRow}:I{$startRow}");
        $sheet->setCellValue("G{$startRow}", '');

        $sheet->mergeCells("J{$startRow}:M{$startRow}");
        $sheet->setCellValue("J{$startRow}", $total);

        $sheet->mergeCells("N{$startRow}:P{$startRow}");
        $sheet->setCellValue("N{$startRow}", $total * 0.1);

        $sheet->mergeCells("Q{$startRow}:T{$startRow}");
        $sheet->setCellValue("Q{$startRow}", $total + ($total * 0.1));

        $sheet->mergeCells("A{$baseRow}:B{$startRow}");
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$baseRow}:B{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');

        //
        $startRow++;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
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
        $sheet->setCellValue("O{$startRow}", $this->order->order_no);

        $sheet->mergeCells("S{$startRow}:T{$startRow}");
        $sheet->setCellValue("S{$startRow}", '備考');

        //
        $startRow++;
        $endRow = $startRow + 1;
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getRowDimension($endRow)->setRowHeight(20);

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
        $sheet->getRowDimension($startRow)->setRowHeight(20);

        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getBorders()->getAllBorders()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$startRow}:T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("I{$startRow}:P{$startRow}");
        $sheet->setCellValue("I{$startRow}", '軽減税率対象には※をつけてください');

        $sheet->mergeCells("Q{$startRow}:S{$startRow}");
        $sheet->setCellValue("Q{$startRow}", '2023/7/4');

        $sheet->getStyle("T{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
        $drawing->setWidth(120);
        $drawing->setHeight(70);

        $drawing->setOffsetX(3);
        $drawing->setOffsetY(10);

        $itemCount = count($this->order->items);
        $minRows = 8;
        $row = 8;
        $endRow = $row + max($itemCount, $minRows);
        $stampRow = $endRow + 11;

        $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing2->setName('Stamp');
        $drawing2->setDescription('Stamp');

        $imagePath = public_path('/assets/images/stamp.png');
        $drawing2->setPath($imagePath);
        $drawing2->setCoordinates("S{$stampRow}");
        $drawing2->setWidth(120);
        $drawing2->setHeight(70);
        $drawing2->setOffsetX(3);
        $drawing2->setOffsetY(10);

        return [$drawing, $drawing2];
    }
}
