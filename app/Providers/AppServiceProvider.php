<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Laravel's default pagination view assumes Tailwind is loaded (it isn't
        // in this project - all styling here is plain CSS) and ships unstyled SVG
        // chevron icons that render at their native, oversized dimensions without
        // it. This swaps in resources/views/vendor/pagination/mwana.blade.php,
        // a plain-text/CSS pagination bar that matches the rest of the app.
        Paginator::defaultView('vendor.pagination.mwana');
        Paginator::defaultSimpleView('vendor.pagination.mwana');
    }
    }

    use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
