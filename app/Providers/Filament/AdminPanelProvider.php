<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\ServiceProvider; 
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Asmit\ResizedColumn\ResizedColumnPlugin;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->topNavigation()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('favicon.ico')) // <-- Tambahin ini brooo
            ->brandLogo(fn () => view('components.filament.brand-logo'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
                \App\Filament\Pages\AdminDashboard::class, // Ganti dari Pages\Dashboard
            ])
            ->navigationGroups([
                NavigationGroup::make('User Management'),
                NavigationGroup::make('Inventory'),
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Customer Analysis'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\TopCustomerBarChart::class,
                \App\Filament\Widgets\SalesChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->plugins([
                FilamentShieldPlugin::make(),
                
                
                // ... other plugins
                ResizedColumnPlugin::make()
                    ->preserveOnDB() // Enable database storage (optional)
            ]);
    }

    public function boot(): void
    {
        Filament::registerRenderHook(
            'auth.login.form.after',
            fn () => view('auth.forgot-link'),
        );
    }
}
