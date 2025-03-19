<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/order/{id}', function ($id) {
    $order = \App\Models\Order::find($id);
    return \Maatwebsite\Excel\Facades\Excel::raw(new \App\Exports\TargetOrderExport($order), \Maatwebsite\Excel\Excel::HTML);
});

require __DIR__.'/auth.php';
