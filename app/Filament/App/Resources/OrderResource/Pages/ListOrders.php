<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource;
use App\Tables\Columns\StatusBagdeColumn;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('messages.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('status')
                    ->label('')
                ->badge(),

                TextColumn::make('order_date')
                    ->label(__('messages.order_date'))
                    ->sortable(['order_date']),

                TextColumn::make('project_name')
                    ->label(__('messages.project_name'))
                    ->searchable(),

                TextColumn::make('order_no')
                    ->label(__('messages.order_no')),

                TextColumn::make('delivery_date')
                    ->label(__('messages.delivery_date'))
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
                    ->options(OrderStatus::class)
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
