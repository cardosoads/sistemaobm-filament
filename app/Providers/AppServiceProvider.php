<?php

namespace App\Providers;

use App\Services\OmieService;
use App\Models\Orcamento;
use App\Observers\OrcamentoObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar o OmieService como singleton
        $this->app->singleton(OmieService::class, function ($app) {
            return new OmieService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('auth', \App\Http\Middleware\Authenticate::class);

        // Registrar observer de Orçamentos
        Orcamento::observe(OrcamentoObserver::class);

        // Configuração dos rate limiters para Omie API
        RateLimiter::for('omie', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('omie-test', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
