<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Resma\FilamentAwinTheme\FilamentAwinTheme;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->login()
            ->authGuard('web')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/vitta-favicon.svg'))
            ->brandName('Sistema OBM')
            ->colors([
                'primary' => [
                    50 => '#f8f9fa',
                    100 => '#f1f3f4',
                    200 => '#e8eaed',
                    300 => '#dadce0',
                    400 => '#bdc1c6',
                    500 => '#F8AB14',
                    600 => '#e09900',
                    700 => '#c78600',
                    800 => '#ad7300',
                    900 => '#8a5a00',
                    950 => '#5c3c00',
                ],
                'secondary' => [
                    50 => '#f0f4f8',
                    100 => '#d9e2ec',
                    200 => '#bcccdc',
                    300 => '#9fb3c8',
                    400 => '#829ab1',
                    500 => '#1E3951',
                    600 => '#1a3248',
                    700 => '#162b3f',
                    800 => '#122436',
                    900 => '#0e1d2d',
                    950 => '#0a1624',
                ],
            ])
            ->darkMode()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()->label('Orçamentos'),
                \Filament\Navigation\NavigationGroup::make()->label('Operações'),
                \Filament\Navigation\NavigationGroup::make()->label('Recursos Humanos'),
                \Filament\Navigation\NavigationGroup::make()->label('Frotas e Veículos'),
                \Filament\Navigation\NavigationGroup::make()->label('Cadastros'),
            ])
            // Descoberta automática de widgets desativada para evitar widgets adicionais no dashboard
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\OrcamentosStatsWidget::class,
                \App\Filament\Widgets\ClientesFornecedoresWidget::class,
                \App\Filament\Widgets\FrotaStatusWidget::class,
                \App\Filament\Widgets\OperacoesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // AuthenticateSession::class, // TEMPORARIAMENTE REMOVIDO PARA TESTE
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->globalSearch(false)
            ->plugins([
            ]);
    }
}
