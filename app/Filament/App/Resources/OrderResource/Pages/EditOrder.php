<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Redis;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected ?bool $hasDatabaseTransactions = true;
    protected static ?string $title = '注文編集';

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Section::make(__('messages.order_details'))
                    ->schema([
                        DatePicker::make('order_date')
                            ->label(__('messages.order_date'))
                            ->disabled()
                            ->placeholder('2025-02-01')
                            ->date()
                            ->native(false)
                            ->dehydrated()
                            ->required()
                            ->displayFormat('Y-m-d'),

                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options(function ($record) {
                                $options = [
                                    OrderStatus::Draft->value => __('messages.draft'),
                                ];

                                if ($record && $record->status !== OrderStatus::Draft->value) {
                                    $options[OrderStatus::Confirmed->value] = __('messages.confirmed');
                                }

                                return $options;
                            })
                            ->afterStateHydrated(function (Select $component, $state, $record) {
                                if ($record && $state !== OrderStatus::Draft->value) {
                                    $component->state(OrderStatus::Confirmed->value);
                                }
                            })
                            ->disabled()
                            ->required(),

                        TextInput::make('customer_name')
                            ->label(__('messages.customer_name'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('sales_representative')
                            ->label(__('messages.sales_representative'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('project_name')
                            ->label(__('messages.project_name'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('order_no')
                            ->label(__('messages.order_no'))
                            ->placeholder('EE-000000-H0000')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        DateTimePicker::make('delivery_date')
                            ->label(__('messages.delivery_date'))
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value)
                            ->placeholder('2025-02-01 13:00')
                            ->native(false)
                            ->displayFormat('Y-m-d H:i'),

                        DatePicker::make('expected_inspection_month')
                            ->label(__('messages.expected_inspection_month'))
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value)
                            ->placeholder('2025-02')
                            ->date()
                            ->native(false)
                            ->displayFormat('Y-m'),

                        TextInput::make('delivery_destination')
                            ->label(__('messages.delivery_destination'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('delivery_destination_phone')
                            ->label(__('messages.delivery_destination_phone'))
                            ->placeholder('080-0000-0000')
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('delivery_destination_zip_code')
                            ->label(__('messages.delivery_destination_zip_code'))
                            ->placeholder('000-0000')
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('delivery_destination_address')
                            ->label(__('messages.delivery_destination_address'))
                            ->placeholder('A県B市C町1-1-1')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('receiver_person_in_charge')
                            ->label(__('messages.receiver_person_in_charge'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        TextInput::make('receiver_phone_number')
                            ->label(__('messages.receiver_phone_number'))
                            ->placeholder('080-0000-0000')
                            ->required(fn(Get $get) => $get('status') != OrderStatus::Draft->value),

                        Hidden::make('total')
                            ->label(__('messages.total'))
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(),
                Section::make(__('messages.order_items'))
                    ->schema([
                        OrderResource::getItemsRepeater()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = collect($get('items'))->sum('sub_total');
                                $set('display_total', $total);
                                $set('total', $total);
                            })
                            ->addAction(function (Get $get, Set $set) {
                                $total = collect($get('items'))->sum('sub_total');
                                $set('display_total', $total);
                                $set('total', $total);
                            }),
                        Grid::make()
                            ->schema([
                                TextInput::make('display_total')
                                    ->label(__('messages.total'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1)
                                    ->columnStart(2)
                            ]),
                    ])
            ])
            ->columns(1);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(__('messages.save')),
            $this->getCancelFormAction()
                ->label(__('messages.cancel')),
        ];
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        // Get order record
        $order = $this->getRecord();

        if ($order->status != OrderStatus::Draft) {
            // Get old items
            $oldItems = $order->items()->get()->keyBy('product_id');

            // Get data form
            $data = $this->form->getState();

            // Get new items
            $newItems = collect($data['items'] ?? [])->keyBy('product_id');

            // Get all product ids
            $allProductIds = $oldItems->keys()->merge($newItems->keys())->unique()->toArray();

            // Get all products
            $products = Product::whereIn('id', $allProductIds)->get()->keyBy('id');

            // Get quantity changes
            $quantityChanges = [];

            // Handle increase/decrease quantity of products
            foreach ($newItems as $productId => $newItem) {
                $oldQuantity = isset($oldItems[$productId]) ? $oldItems[$productId]->qty : 0;
                $newQuantity = $newItem['qty'] ?? 0;

                if ($oldQuantity != $newQuantity) {
                    $difference = $newQuantity - $oldQuantity;
                    $quantityChanges[$productId] = ($quantityChanges[$productId] ?? 0) - $difference;
                }
            }

            // Handle remove products
            foreach ($oldItems as $productId => $oldItem) {
                if (!$newItems->has($productId)) {
                    $quantityChanges[$productId] = ($quantityChanges[$productId] ?? 0) + $oldItem->quantity;
                }
            }

            // Update product quantity
            foreach ($quantityChanges as $productId => $change) {
                if (isset($products[$productId]) && $change != 0) {
                    $product = $products[$productId];

                    // Update product quantity when quantity changes
                    if ($change > 0) {
                        $product->increment('qty', $change);
                    } else {
                        $product->decrement('qty', abs($change));
                    }

                    Redis::set('product_' . $productId, $product->qty);
                }
            }
        }

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }
}
