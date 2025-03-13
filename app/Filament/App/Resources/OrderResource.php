<?php

namespace App\Filament\App\Resources;

use App\Enums\OrderStatus;
use App\Filament\App\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
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
        return $page->generateNavigationItems([
            Pages\ViewOrder::class,
            Pages\EditOrder::class,
        ]);
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
            ->schema([
                Placeholder::make('item_number')
                    ->label(__('messages.no'))
                    ->content(function () {
                        static $index = 0;
                        return ++$index;
                    })
                    ->dehydrated(false),

                Select::make('product_id')
                    ->placeholder(__('messages.select_product'))
                    ->label(__('messages.product_name'))
                    ->options(self::getProducts())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $product = Product::find($state);

                        if ($product) {
                            $set('price', $product->price);
                            $set('sub_total', $get('qty') * $get('price'));
                            $set('product_type', $product->product_type);
                        } else {
                            $set('price', 0);
                            $set('sub_total', 0);
                            $set('product_type', '');
                        }
                    })
                    ->afterStateHydrated(function ($state, Set $set) {
                        if ($state) {
                            $product = Product::find($state);
                            $set('product_type', $product->product_type);
                        }
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                TextInput::make('product_type')
                    ->label(__('messages.product_type'))
                    ->required()
                    ->dehydrated(false),

                TextInput::make('qty')
                    ->label(__('messages.quantity'))
                    ->integer()
                    ->live()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(function (?Model $record, Get $get) {
                        Redis::set('product_' . $get('product_id'), $get('qty'));
                        $redisQty = Redis::get('product_' . $get('product_id'));

                        if (!is_null($redisQty)) {
                            return $record ? $record->qty + $redisQty : $redisQty;
                        } else {
                            $product = Product::find($get('product_id'));
                            if ($product) {
                                return $record ? $record->qty + $product->qty : $product->qty;
                            }
                        }

                        return $get('qty');
                    })
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $qty = $get('qty') ?: 0;
                        $set('sub_total', $qty * $get('price'));
                    })
                    ->extraAttributes([
                        'min' => 1,
                    ])
                    ->required(),

                TextInput::make('price')
                    ->label(__('messages.price'))
                    ->disabled()
                    ->numeric()
                    ->dehydrated()
                    ->required(),

                Hidden::make('sub_total')
                    ->label(__('messages.sub_total'))
                    ->dehydrated()
                    ->required(),
            ])
            ->dehydrated()
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns(5)
            ->required();
    }

    public static function getProducts()
    {
        return Product::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('name', 'id');
    }
}
