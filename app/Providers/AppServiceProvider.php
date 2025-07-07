<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('layouts.index', function ($view) {
            $showDebtHistory = \App\Models\SubConfiguration::where('code', 'P-PAY-03')
                ->where('status', 1)
                ->exists();

            $view->with('showDebtHistory', $showDebtHistory);
        });

        Blade::directive('currency', function ($expression) {
            return "Rp. <?php echo number_format($expression,0,',','.'); ?>";
        });
    }
}
