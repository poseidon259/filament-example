<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;

class ProductPriceImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product_code')
                ->label(__('messages.product_code'))
                ->requiredMapping()
                ->guess(['商品コード']),
            ImportColumn::make('price')
                ->label(__('messages.price'))
                ->requiredMapping()
                ->guess(['売上単重']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $product = Product::query()
            ->where('product_code', $this->data['product_code'])
            ->first();

        if ($product) {
            $product->update([
                'price' => $this->data['price'],
            ]);
        }

        return null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
