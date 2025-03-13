<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Imports\ProductPriceImporter;
use App\Filament\Imports\ProductQuantityImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\File;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                ActionGroup::make([
                    ImportAction::make('importProductPriceCsv')
                        ->label(__('messages.import_csv_price'))
                        ->importer(ProductPriceImporter::class)
                        ->fileRules([
                            File::types(['csv'])
                        ])
                        ->modalHeading(__('messages.import_csv_price'))
                        ->modalDescription('')
                        ->modalSubmitActionLabel(__('messages.import'))
                        ->modalCancelActionLabel(__('messages.cancel'))
                        ->form(fn ($form) => parent::form($form)
                            ->schema([
                                FileUpload::make('file')
                                    ->label(__('messages.file'))
                                    ->placeholder(__('messages.drag_and_drop_file_here'))
                                    ->uploadingMessage(__('messages.uploading'))
                                    ->required()
                                    ->storeFiles(false)
                                    ->required()
                                    ->hiddenLabel(),
                                Hidden::make('columnMap')
                                ->default([
                                    'product_code' => '商品コード',
                                    'price' => '売上単重',
                                ])
                            ])
                            ->columns(1)
                        )
                ])->dropdown(false),
                ActionGroup::make([
                    ImportAction::make('importProductQtyCsv')
                        ->label(__('messages.import_csv_qty'))
                        ->importer(ProductQuantityImporter::class)
                        ->fileRules([
                            File::types(['csv'])
                        ])
                        ->modalHeading(__('messages.import_csv_qty'))
                        ->modalDescription('')
                        ->modalSubmitActionLabel(__('messages.import'))
                        ->modalCancelActionLabel(__('messages.cancel'))
                        ->form(fn ($form) => parent::form($form)
                            ->schema([
                                FileUpload::make('file')
                                    ->label(__('messages.file'))
                                    ->placeholder(__('messages.drag_and_drop_file_here'))
                                    ->uploadingMessage(__('messages.uploading'))
                                    ->required()
                                    ->storeFiles(false)
                                    ->required()
                                    ->hiddenLabel(),
                                Hidden::make('columnMap')
                                    ->default([
                                        'product_code' => '商品コード',
                                        'qty' => '引当可能数',
                                    ])
                            ])
                            ->columns(1)
                        )
                ])->dropdown(false),
            ])
                ->label(__('messages.import_csv'))
                ->icon('heroicon-s-pencil-square')
                ->button()
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('no')
                    ->label(__('messages.no'))
                    ->state(function ($record, $rowLoop) {
                        // Get the current page from the request
                        $page = $this->getPage() ?? 1;
                        // Get the per-page setting from the table
                        $perPage = $this->getTableRecordsPerPage() ?? 10;

                        // Calculate the sequential number
                        return (($page - 1) * $perPage) + $rowLoop->iteration;
                    }),

                TextColumn::make('name')
                    ->label(__('messages.product_name')),

                TextColumn::make('product_code')
                    ->label(__('messages.product_code')),

                TextColumn::make('product_type')
                    ->label(__('messages.product_type')),

                TextColumn::make('qty')
                    ->label(__('messages.quantity'))
                    ->numeric(locale: 'en'),

                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->numeric(locale: 'en'),
            ]);
    }
}
