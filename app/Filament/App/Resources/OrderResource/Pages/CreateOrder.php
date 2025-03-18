<?php

namespace App\Filament\App\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Redis;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;
    protected ?bool $hasDatabaseTransactions = true;
    protected static bool $canCreateAnother = false;

    public ?OrderStatus $status = OrderStatus::Draft;

    public function getHeaderActions(): array
    {
        return [
            Action::make('saveAsDraft')
                ->label(__('messages.save_as_draft'))
                ->color('draft')
                ->action(function () {
                    $this->data['status'] = OrderStatus::Draft->value;
                    $this->create();
                }),

            Action::make('saveAsConfirmed')
                ->label(__('messages.save_as_confirmed'))
                ->color('primary')
                ->action(function () {
                    $this->data['status'] = OrderStatus::Confirmed->value;
                    $this->create(reduceStock: true);
                }),
        ];
    }

    public function create(bool $another = false, bool $reduceStock = false): void
    {
        $data = $this->form->getState();
        parent::create($another);

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
                            ->required(fn(Get $get) => !$this->isDraft($get))
                            ->placeholder('2025-02-01')
                            ->date()
                            ->default(now())
                            ->dehydrated()
                            ->native(false)
                            ->displayFormat('Y-m-d'),

                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options([
                                OrderStatus::Draft->value => __('messages.draft'),
                                OrderStatus::Confirmed->value => __('messages.confirmed'),
                            ])
                            ->native(false)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(OrderStatus::Draft->value),

                        TextInput::make('customer_name')
                            ->label(__('messages.customer_name'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('sales_representative')
                            ->label(__('messages.sales_representative'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('project_name')
                            ->label(__('messages.project_name'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(),

                        TextInput::make('order_no')
                            ->label(__('messages.order_no'))
                            ->placeholder('EE-000000-H0000')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        DateTimePicker::make('delivery_date')
                            ->label(__('messages.delivery_date'))
                            ->required(fn(Get $get) => !$this->isDraft($get))
                            ->placeholder('2025-02-01 13:00')
                            ->native(false)
                            ->displayFormat('Y-m-d H:i'),

                        DatePicker::make('expected_inspection_month')
                            ->label(__('messages.expected_inspection_month'))
                            ->required(fn(Get $get) => !$this->isDraft($get))
                            ->placeholder('2025-02')
                            ->date()
                            ->native(false)
                            ->displayFormat('Y-m'),

                        TextInput::make('delivery_destination')
                            ->label(__('messages.delivery_destination'))
                            ->placeholder('ABC店')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('delivery_destination_phone')
                            ->label(__('messages.delivery_destination_phone'))
                            ->placeholder('080-0000-0000')
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('delivery_destination_zip_code')
                            ->label(__('messages.delivery_destination_zip_code'))
                            ->placeholder('000-0000')
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('delivery_destination_address')
                            ->label(__('messages.delivery_destination_address'))
                            ->placeholder('A県B市C町1-1-1')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('receiver_person_in_charge')
                            ->label(__('messages.receiver_person_in_charge'))
                            ->placeholder('山田　太郎')
                            ->string()
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('receiver_phone_number')
                            ->label(__('messages.receiver_phone_number'))
                            ->placeholder('080-0000-0000')
                            ->required(fn(Get $get) => !$this->isDraft($get)),

                        TextInput::make('note')
                            ->label(__('messages.note'))
                            ->placeholder('備考')
                            ->columnSpan(2)
                            ->string(),

                        Hidden::make('total')
                            ->label(__('messages.total'))
                            ->dehydrated()
                            ->required(fn(Get $get) => !$this->isDraft($get))
                    ])->columns(2),

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

    public function isDraft(Get $get)
    {
        return $get('status') === OrderStatus::Draft->value;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification();
    }
}
