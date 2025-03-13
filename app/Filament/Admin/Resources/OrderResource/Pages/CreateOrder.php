<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions\Action;
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

        ];
    }

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
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction()->label(__('messages.cancel')))
                    ->submitAction($this->getSubmitFormAction()->label(__('messages.submit')))
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(1);
    }

    /** @return Step[] */
    protected function getSteps(): array
    {
        return [
            Step::make(__('messages.order_details'))
                ->schema([
                    Section::make()->schema([
                        Section::make()
                            ->schema([
                                DatePicker::make('order_date')
                                    ->label(__('messages.order_date'))
                                    ->required()
                                    ->placeholder('2025-02-01')
                                    ->date()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('Y-m-d'),

                                Select::make('status')
                                    ->label(__('messages.status'))
                                    ->required()
                                    ->options([
                                        OrderStatus::Confirmed->value => __('messages.confirmed'),
                                        OrderStatus::PreparingToShip->value => __('messages.preparing_to_ship'),
                                        OrderStatus::Shipped->value => __('messages.shipped'),
                                        OrderStatus::Unpaid->value => __('messages.unpaid'),
                                        OrderStatus::PaymentCompleted->value => __('messages.payment_completed'),
                                    ])
                                    ->default(OrderStatus::Confirmed->value)
                                    ->searchable(),

                                TextInput::make('customer_name')
                                    ->label(__('messages.customer_name'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('sales_representative')
                                    ->label(__('messages.sales_representative'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('project_name')
                                    ->label(__('messages.project_name'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('order_no')
                                    ->label(__('messages.order_no'))
                                    ->default(uniqid())
                                    ->required(),

                                DateTimePicker::make('delivery_date')
                                    ->label(__('messages.delivery_date'))
                                    ->required()
                                    ->placeholder('2025-02-01 13:00')
                                    ->native(false)
                                    ->default(now())
                                    ->displayFormat('Y-m-d H:i'),

                                DatePicker::make('expected_inspection_month')
                                    ->label(__('messages.expected_inspection_month'))
                                    ->required()
                                    ->placeholder('2025-02')
                                    ->date()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('Y-m'),

                                TextInput::make('delivery_destination')
                                    ->label(__('messages.delivery_destination'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('delivery_destination_phone')
                                    ->label(__('messages.delivery_destination_phone'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('delivery_destination_zip_code')
                                    ->label(__('messages.delivery_destination_zip_code'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('delivery_destination_address')
                                    ->label(__('messages.delivery_destination_address'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('receiver_person_in_charge')
                                    ->label(__('messages.receiver_person_in_charge'))
                                    ->default('Developer')
                                    ->required(),

                                TextInput::make('receiver_phone_number')
                                    ->label(__('messages.receiver_phone_number'))
                                    ->default('Developer')
                                    ->required(),

                                Hidden::make('total')
                                    ->label(__('messages.total'))
                                    ->dehydrated()
                                    ->required()
                            ])
                            ->columns(2)
                    ])->columns(2),
                ]),

            Step::make(__('messages.order_items'))
                ->schema([
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
                    ]),
                ]),
        ];
    }
}
