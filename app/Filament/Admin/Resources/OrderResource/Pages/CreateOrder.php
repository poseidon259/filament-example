<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Redis;

class CreateOrder extends CreateRecord
{

    protected static string $resource = OrderResource::class;
    protected ?bool $hasDatabaseTransactions = true;
    protected static bool $canCreateAnother = false;

    public ?OrderStatus $status = OrderStatus::Draft;

    public function create(bool $another = false, bool $reduceStock = false): void
    {
        $data = $this->form->getState();

        if ($reduceStock) {
            $items = collect($data['items'] ?? []);
            $productIds = $items->pluck('product_id')->toArray();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['qty'] ?? 0;

                if (isset($products[$productId]) && $quantity > 0) {
                    $product = $products[$productId];
                    $product->decrement('qty', $quantity);

                    Redis::set('product_' . $productId, $product->qty);
                }
            }
        }

        parent::create($another);
    }

    public function getTitle(): Htmlable|string
    {
        return __('messages.create_order');
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Section::make(__('messages.order_details'))
                    ->schema([
                        DatePicker::make('order_date')
                            ->label(__('messages.order_date'))
                            ->required()
                            ->placeholder('2025-02-02')
                            ->date()
                            ->native(false)
                            ->displayFormat('Y-m-d'),

                        Select::make('status')
                            ->label(__('messages.status'))
                            ->required()
                            ->options([
                                OrderStatus::Confirmed->value => __('messages.confirmed'),
                                OrderStatus::Exported->value => __('messages.exported'),
                                OrderStatus::OBICRegistered->value => __('messages.obic_registered'),
                                OrderStatus::ShipmentArranged->value => __('messages.shipment_arranged'),
                                OrderStatus::SpecifiedInvoiceExported->value => __('messages.specified_invoice_exported'),
                            ])
                            ->default(OrderStatus::Confirmed->value)
                            ->searchable(),

                        TextInput::make('customer_name')
                            ->label(__('messages.customer_name'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(),

                        TextInput::make('sales_representative')
                            ->label(__('messages.sales_representative'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(),

                        TextInput::make('project_name')
                            ->label(__('messages.project_name'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(),

                        TextInput::make('order_no')
                            ->label(__('messages.order_no'))
                            ->placeholder('EE-000000-H0000')
                            ->string()
                            ->required(),

                        DateTimePicker::make('delivery_date')
                            ->label(__('messages.delivery_date'))
                            ->required()
                            ->placeholder('2025-02-01 13:00')
                            ->native(false)
                            ->displayFormat('Y-m-d H:i'),

                        DatePicker::make('expected_inspection_month')
                            ->label(__('messages.expected_inspection_month'))
                            ->required()
                            ->placeholder('2025-02')
                            ->date()
                            ->native(false)
                            ->displayFormat('Y-m'),

                        TextInput::make('delivery_destination')
                            ->label(__('messages.delivery_destination'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(),

                        TextInput::make('delivery_destination_phone')
                            ->label(__('messages.delivery_destination_phone'))
                            ->placeholder('080-0000-0000')
                            ->numeric()
                            ->required(),

                        TextInput::make('delivery_destination_zip_code')
                            ->label(__('messages.delivery_destination_zip_code'))
                            ->placeholder('000-0000')
                            ->numeric()
                            ->required(),

                        TextInput::make('delivery_destination_address')
                            ->label(__('messages.delivery_destination_address'))
                            ->placeholder('A県B市C町1-1-1')
                            ->string()
                            ->required(),

                        TextInput::make('receiver_person_in_charge')
                            ->label(__('messages.receiver_person_in_charge'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(),

                        TextInput::make('receiver_phone_number')
                            ->label(__('messages.receiver_phone_number'))
                            ->placeholder('080-0000-0000')
                            ->numeric()
                            ->required(),

                        Hidden::make('total')
                            ->label(__('messages.total'))
                            ->dehydrated()
                            ->required()
                    ])
                    ->columns(2),

                Section::make()->schema([
                    OrderResource::getItemsRepeater()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $total = collect($get('items'))->sum('sub_total');
                            $set('display_total', $total . ' 円');
                            $set('total', $total);
                        })
                        ->addAction(function (Get $get, Set $set) {
                            $total = collect($get('items'))->sum('sub_total');
                            $set('display_total', $total . ' 円');
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
}
