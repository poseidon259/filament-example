<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource;
use App\Infolists\Components\TextWithBorderBottom;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = '基本情報';

    public function getTitle(): string|Htmlable
    {
        return __('messages.order_details');
    }

    public function infoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('messages.order_details'))
                    ->schema([
                        TextWithBorderBottom::make('order_date')
                            ->label(__('messages.order_date')),
                        TextEntry::make('status')
                            ->label(__('messages.status'))
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
                        TextWithBorderBottom::make('customer_name')
                            ->label(__('messages.customer_name')),
                        TextWithBorderBottom::make('sales_representative')
                            ->label(__('messages.sales_representative')),
                        TextWithBorderBottom::make('project_name')
                            ->label(__('messages.project_name')),
                        TextWithBorderBottom::make('order_no')
                            ->label(__('messages.order_no')),
                        TextWithBorderBottom::make('delivery_date')
                            ->label(__('messages.delivery_date')),
                        TextWithBorderBottom::make('expected_inspection_month')
                            ->label(__('messages.expected_inspection_month')),
                        TextWithBorderBottom::make('delivery_destination')
                            ->label(__('messages.delivery_destination')),
                        TextWithBorderBottom::make('delivery_destination_phone')
                            ->label(__('messages.delivery_destination_phone')),
                        TextWithBorderBottom::make('delivery_destination_zip_code')
                            ->label(__('messages.delivery_destination_zip_code')),
                        TextWithBorderBottom::make('delivery_destination_address')
                            ->label(__('messages.delivery_destination_address')),
                        TextWithBorderBottom::make('receiver_person_in_charge')
                            ->label(__('messages.receiver_person_in_charge')),
                        TextWithBorderBottom::make('receiver_phone_number')
                            ->label(__('messages.receiver_phone_number')),
                    ])
                ->columns(),
                Section::make(__('messages.order_items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('no')
                                    ->label(__('messages.no'))
                                    ->getStateUsing(function () {
                                        static $index = 1;
                                        return $index++;
                                    }),
                                TextEntry::make('product.name')
                                    ->label(__('messages.product_name')),
                                TextEntry::make('product.product_type')
                                    ->label(__('messages.product_type')),
                                TextEntry::make('qty')
                                    ->label(__('messages.quantity')),
                                TextEntry::make('price')
                                    ->label(__('messages.price')),
                                TextEntry::make('sub_total')
                                    ->label(__('messages.sub_total'))
                            ])
                            ->columns(6)
                            ->hiddenLabel(),
                        Grid::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextEntry::make('total')
                                            ->label(__('messages.total'))
                                            ->extraAttributes([
                                                'class' => 'total-label'
                                            ])
                                    ])
                                    ->columnSpan(1)
                                    ->columnStart(2)
                            ]),
                    ])
            ])
            ->columns(1);
    }
}
