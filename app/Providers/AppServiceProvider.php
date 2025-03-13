<?php

namespace App\Providers;

use App\Filament\Color as CustomColor;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'draft' => Color::Gray,
            'confirmed' => Color::Blue,
            'preparing_to_ship' => Color::Green,
            'shipped' => Color::Yellow,
            'unpaid' => Color::Orange,
            'payment_completed' => Color::Purple,
        ]);

        Wizard::configureUsing(function (Wizard $wizard) {
            $wizard
                ->nextAction(fn(Action $action) => $action->label(__('messages.next')))
                ->previousAction(fn(Action $action) => $action->label(__('messages.back')));
        });
    }
}
