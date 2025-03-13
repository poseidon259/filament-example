<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\ActionGroup;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected ?bool $hasDatabaseTransactions = true;
    protected static ?string $title = '注文編集';

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
            ActionGroup::make([
                ActionGroup::make([
                    Action::make('exportOrderPdf')
                        ->label(__('messages.export_pdf_order'))
                        ->icon('heroicon-s-arrow-down-on-square'),
                ])->dropdown(false),
                ActionGroup::make([
                    Action::make('exportTargetPdf')
                        ->label(__('messages.export_pdf_target'))
                        ->icon('heroicon-s-arrow-down-on-square'),
                ])->dropdown(false),
            ])
                ->label(__('messages.export_pdf'))
                ->icon('heroicon-s-arrow-down-on-square')
                ->button()
        ];
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

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Tabs::make()
                    ->schema([
                        Tab::make(__('messages.order_details'))
                            ->schema([
                                DatePicker::make('order_date')
                                    ->label(__('messages.order_date'))
                                    ->required()
                                    ->placeholder('2025-02-02')
                                    ->date()
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->locale(getenv('DATE_PICKER_LOCALE')),

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
                                    ->displayFormat('Y-m-d H:i')
                                    ->locale(getenv('DATE_PICKER_LOCALE')),

                                DatePicker::make('expected_inspection_month')
                                    ->label(__('messages.expected_inspection_month'))
                                    ->required()
                                    ->placeholder('2025-02')
                                    ->date()
                                    ->native(false)
                                    ->displayFormat('Y-m')
                                    ->locale(getenv('DATE_PICKER_LOCALE')),

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
                        Tab::make(__('messages.order_items'))
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
                            ]),
                    ])
            ])
            ->columns(1);
    }
}
