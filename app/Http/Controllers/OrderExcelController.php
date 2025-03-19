<?php

namespace App\Http\Controllers;

use App\Exports\OrderExport;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderExcelController extends Controller
{
    public function viewExcel(Order $order, Request $request)
    {
        $fileContents = Excel::raw(new OrderExport($order), \Maatwebsite\Excel\Excel::HTML);
//        return Excel::download(new OrderExport($order), "order_{$order->order_no}.xlsx");
        return response($fileContents)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="order.html"');
    }
}
