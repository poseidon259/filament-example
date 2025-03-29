<?php

namespace App\Filament\App\Resources;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = '';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getLabel(): ?string
    {
        return __('messages.order_history');
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        $items = [Pages\ViewOrder::class];

        if ($record->status === OrderStatus::Draft) {
            $items[] = Pages\EditOrder::class;
        }

        return $page->generateNavigationItems($items);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship('items')
            ->addActionLabel(__('messages.add_item'))
            ->schema([
                Placeholder::make('item_number')
                    ->label(__('messages.no'))
                    ->content(function () {
                        static $index = 0;
                        return ++$index;
                    })
                    ->dehydrated(false),

                Select::make('product_id')
                    ->label(__('messages.product_code'))
                    ->placeholder('')
                    ->options(self::getProducts())
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value)
                    ->reactive()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $product = Product::find($state);

                        if ($product) {
                            $subTotal = $get('qty') * $product->price;
                            $set('price', $product->price);
                            $set('display_price', $product->price . ' 円');
                            $set('display_sub_total', $subTotal . ' 円');
                            $set('sub_total', $subTotal);
                            $set('product_type', $product->product_type);
                            $set('product_name', $product->name);
                            $set('weight', $product->weight);
                            $set('display_weight', $product->weight);
                        } else {
                            $set('price', 0);
                            $set('display_price', 0 . ' 円');
                            $set('display_sub_total', 0 . ' 円');
                            $set('sub_total', 0);
                            $set('product_type', '');
                            $set('product_name', '');
                            $set('weight', '');
                            $set('display_weight', '');
                        }
                    })
                    ->afterStateHydrated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $product = Product::find($state);
                            $set('product_type', $product->product_type);
                            $set('product_name', $product->name);
                            $set('price', $product->price);
                            $set('display_price', $product->price . ' 円');
                            $subTotal = $get('qty') * $product->price;
                            $set('display_sub_total', $subTotal . ' 円');
                            $set('sub_total', $subTotal);
                            $set('weight', $product->weight);
                            $set('display_weight', $product->weight);
                        }
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                Select::make('product_name')
                    ->label(__('messages.product_name'))
                    ->placeholder('')
                    ->options(function (Get $get) {

                        $products = Product::query();
                        if ($get('product_type')) {
                            return $products->where('product_type', $get('product_type'))
                                ->pluck('name', 'name');
                        }

                        return $products->pluck('name', 'name');
                    })
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value)
                    ->reactive()
                    ->preload()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $products = Product::query()
                            ->where('name', $state);
                        if ($get('product_type')) {
                            $products->where('product_type', $get('product_type'))
                                ->get();
                        } else {
                            $products->get();
                        }

                        if ($products->count() > 1) {
                            $set('product_id', '');
                            $set('product_type', '');
                        } else if ($products->count() == 1) {
                            $product = $products->first();
                            $set('product_id', $product->id);
                            $set('product_type', $product->product_type);

                            $subTotal = $get('qty') * $product->price;
                            $displayWeight = number_format($product->weight * $get('qty'), 2);
                            $set('price', $product->price);
                            $set('display_price', $product->price . ' 円');
                            $set('display_sub_total', $subTotal . ' 円');
                            $set('sub_total', $subTotal);
                            $set('weight', $product->weight);
                            $set('display_weight', $displayWeight);
                        }
                    })
                    ->searchable()
                    ->dehydrated(false),

                Select::make('product_type')
                    ->label(__('messages.product_type'))
                    ->placeholder('')
                    ->options(function (Get $get) {
                        $query = Product::query();

                        if ($get('product_name')) {
                            return $query
                                ->where('name', $get('product_name'))
                                ->pluck('product_type', 'product_type');
                        }

                        return $query->pluck('product_type', 'product_type');
                    })
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value)
                    ->reactive()
                    ->preload()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {

                        if (empty($state)) {
                            $set('product_id', '');
                            $set('product_name', '');
                            $set('price', 0);
                            $set('display_price', 0 . ' 円');
                            $set('display_sub_total', 0 . ' 円');
                            $set('sub_total', 0);
                            return;
                        }

                        $products = Product::query()
                            ->where('product_type', $state);

                        if ($get('product_name')) {
                            $products->where('name', $get('product_name'))
                                ->get();
                        } else {
                            $products->get();
                        }

                        if ($products->count() > 1) {
                            $set('product_id', '');
                            $set('product_name', '');
                        } else if ($products->count() == 1) {
                            $product = $products->first();
                            $set('product_id', $product->id);
                            $set('product_name', $product->name);

                            $subTotal = $get('qty') * $product->price;
                            $displayWeight = number_format($product->weight * $get('qty'), 2);
                            $set('price', $product->price);
                            $set('display_price', $product->price . ' 円');
                            $set('display_sub_total', $subTotal . ' 円');
                            $set('sub_total', $subTotal);
                            $set('weight', $product->weight);
                            $set('display_weight', $displayWeight);
                        }
                    })
                    ->searchable()
                    ->dehydrated(false),

                TextInput::make('qty')
                    ->label(__('messages.quantity'))
                    ->integer()
                    ->live()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(function (?Model $record, Get $get) {
                        $redisQty = Redis::get('product_' . $get('product_id'));
                        $maxQty = 0;

                        if (strlen($redisQty) > 0) {
                            $maxQty = $record ? $record->qty + $redisQty : $redisQty;
                        } else {
                            $product = Product::find($get('product_id'));
                            if ($product) {
                                $maxQty = $record ? $record->qty + $product->qty : $product->qty;
                                Redis::set('product_' . $get('product_id'), $product->qty ?? 0);
                            }
                        }

                        return $maxQty;
                    })
                    ->validationMessages([
                        'max' => __('messages.max_quantity_error'),
                    ])
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $qty = $get('qty') ?: 0;
                        $subTotal = $qty * $get('price');
                        $displayWeight = $get('weight') * $qty;
                        $set('display_sub_total', $subTotal . '円');
                        $set('sub_total', $subTotal);
                        $set('display_weight', $displayWeight);
                    })
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value),

                TextInput::make('display_weight')
                    ->label(__('messages.weight'))
                    ->disabled()
                    ->dehydrated(false),

                Hidden::make('weight')
                    ->label(__('messages.weight'))
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('display_price')
                    ->label(__('messages.price'))
                    ->disabled()
                    ->dehydrated(false)
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value),

                Hidden::make('price')
                    ->label(__('messages.price'))
                    ->disabled()
                    ->dehydrated()
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value),

                TextInput::make('display_sub_total')
                    ->label(__('messages.sub_total'))
                    ->disabled()
                    ->dehydrated(false),

                Hidden::make('sub_total')
                    ->label(__('messages.sub_total'))
                    ->dehydrated()
                    ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value),
            ])
            ->dehydrated()
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns(8)
            ->required(fn(Get $get) => $get('../../status') != OrderStatus::Draft->value);
    }

    public static function getProducts()
    {
        return Product::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('product_code', 'id')->toArray();
    }
}
