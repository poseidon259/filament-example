<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Admin\Resources\OrderResource;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getTableEmptyStateIcon(): ?string
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->emptyStateHeading('')
            ->modifyQueryUsing(function ($query) {
                $query->where('status', '!=', OrderStatus::Draft);
            })
            ->columns([
                TextColumn::make('status')
                    ->label('')
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
                        OrderStatus::Confirmed->value => __('messages.confirmed'),
                        OrderStatus::Exported->value => __('messages.exported'),
                        OrderStatus::OBICRegistered->value => __('messages.obic_registered'),
                        OrderStatus::ShipmentArranged->value => __('messages.shipment_arranged'),
                        OrderStatus::SpecifiedInvoiceExported->value => __('messages.specified_invoice_exported'),
                    ])
                    ->preload()
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-s-ellipsis-vertical')
                    ->hiddenLabel(),
            ])
            ->searchPlaceholder(__('messages.search_orders'));
    }
}
