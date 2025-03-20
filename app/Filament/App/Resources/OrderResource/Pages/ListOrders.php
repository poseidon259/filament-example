<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Exports\OrderItemExport;
use App\Filament\App\Resources\OrderResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_order_selected_header')
                ->label(__('messages.export_order_selected'))
                ->icon('heroicon-s-arrow-down-on-square')
                ->color(Color::Stone)
                ->action(function () {
                    $this->js(
                        <<<JS
                            const buttons = document.querySelectorAll('[data-bulk-action]');
                            const exportButton = Array.from(buttons).find(btn =>
                                btn.dataset.bulkAction === 'export_order_selected'
                            );

                            if (exportButton) {
                                exportButton.click();
                            }
                        JS
                    );
                }),
            Actions\CreateAction::make()
                ->label(__('messages.create'))
                ->icon('heroicon-o-plus')
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->emptyStateHeading('')
            ->modifyQueryUsing(function ($query) {
                return $query->orderBy('id', 'desc');
            })
            ->columns([
                TextColumn::make('status')
                    ->label('')
                    ->formatStateUsing(function ($state) {
                        if ($state === OrderStatus::Draft) {
                            return __('messages.draft');
                        }

                        return __('messages.confirmed');
                    })
                    ->color(function ($state) {
                        if ($state === OrderStatus::Draft) {
                            return 'draft';
                        }

                        return 'confirmed';
                    })
                    ->badge(),

                TextColumn::make('order_date')
                    ->label(__('messages.order_date'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::parse($state)->format('Y-m-d') : null;
                    })
                    ->sortable(['order_date']),

                TextColumn::make('project_name')
                    ->label(__('messages.project_name'))
                    ->searchable(),

                TextColumn::make('order_no')
                    ->label(__('messages.order_no')),

                TextColumn::make('delivery_date')
                    ->label(__('messages.delivery_date'))
                    ->formatStateUsing(function ($state) {
                        return $state ? Carbon::parse($state)->format('Y-m-d H:i') : null;
                    })
                    ->sortable(['delivery_date']),

                TextColumn::make('delivery_destination')
                    ->label(__('messages.delivery_destination')),

                TextColumn::make('receiver_person_in_charge')
                    ->label(__('messages.receiver_person_in_charge')),

                TextColumn::make('receiver_phone_number')
                    ->label(__('messages.receiver_phone_number')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.status'))
                    ->placeholder(__('messages.filter_by_status'))
                    ->searchable()
                    ->options([
                        OrderStatus::Draft->value => __('messages.draft'),
                        OrderStatus::Confirmed->value => __('messages.confirmed'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] == OrderStatus::Draft->value) {
                            return $query->where('status', '=', OrderStatus::Draft);
                        }

                        return $query->where('status', '!=', OrderStatus::Draft);
                    })
                    ->preload()
                    ->columnSpan(2),
                Filter::make('order_date')
                    ->form([
                        Grid::make()
                            ->schema([
                                DatePicker::make('order_date_start')
                                    ->label(__('messages.order_date'))
                                    ->placeholder('2025-02-01')
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),
                                DatePicker::make('order_date_end')
                                    ->label(__('messages.order_date'))
                                    ->placeholder('2025-02-28')
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_date_start'],
                                fn($query) => $query->where('order_date', '>=', $data['order_date_start'])
                            )
                            ->when(
                                $data['order_date_end'],
                                fn($query) => $query->where('order_date', '<=', $data['order_date_end'])
                            );
                    })
                    ->indicateUsing(function ($data) {
                        $orderDateStart = $data['order_date_start'] ? Carbon::parse($data['order_date_start'])->format('Y-m-d') : null;
                        $orderDateEnd = $data['order_date_end'] ? Carbon::parse($data['order_date_end'])->format('Y-m-d') : null;

                        if ($orderDateStart || $orderDateEnd) {
                            return __('messages.order_date') . ': ' . $orderDateStart . ' - ' . $orderDateEnd;
                        }

                        return null;
                    })
                    ->columnSpan(2),

                Filter::make('delivery_date')
                    ->form([
                        Grid::make()
                            ->schema([
                                DatePicker::make('delivery_date_start')
                                    ->label(__('messages.delivery_date'))
                                    ->placeholder('2025-02-01')
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),
                                DatePicker::make('delivery_date_end')
                                    ->label(__('messages.delivery_date'))
                                    ->placeholder('2025-02-28')
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),
                            ])
                    ])
                    ->indicateUsing(function ($data) {
                        $deliveryDateStart = $data['delivery_date_start'] ? Carbon::parse($data['delivery_date_start'])->format('Y-m-d') : null;
                        $deliveryDateEnd = $data['delivery_date_end'] ? Carbon::parse($data['delivery_date_end'])->format('Y-m-d') : null;

                        if ($deliveryDateStart || $deliveryDateEnd) {
                            return __('messages.delivery_date') . ': ' . $deliveryDateStart . ' - ' . $deliveryDateEnd;
                        }

                        return null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivery_date_start'],
                                fn($query) => $query->where('delivery_date', '>=', $data['delivery_date_start'])
                            )
                            ->when(
                                $data['delivery_date_end'],
                                fn($query) => $query->where('delivery_date', '<=', $data['delivery_date_end'])
                            );
                    })
                    ->columnSpan(2),

            ])
            ->filtersFormWidth(MaxWidth::ExtraSmall)
            ->filtersFormColumns(2)
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-s-ellipsis-vertical')
                    ->hiddenLabel(),
            ])
            ->searchPlaceholder(__('messages.search_orders'))
            ->bulkActions([
                BulkAction::make('export_order_selected')
                    ->label(__('messages.export_order_selected'))
                    ->icon('heroicon-s-arrow-down-on-square')
                    ->color(Color::Stone)
                    ->action(function ($records) {
                        return Excel::download(
                            new OrderItemExport($records),
                            now() . '_orders.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    })
                    ->extraAttributes([
                        'class' => 'hidden',
                        'data-bulk-action' => 'export_order_selected',
                    ])
            ])
            ->selectable();
    }
}
