<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Filament\Navigation\MenuItem;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Illuminate\Support\Facades\Auth;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(asset('images/logo-light.svg'))
            ->darkModeBrandLogo((asset('images/logo-dark.svg')))
            ->brandLogoHeight('6rem')
            ->colors([
                'primary' => '#f50404',
                'success' => '#00c853',
                'info' => '#0288d1',
                'warning' => '#ff9800',
                'danger' => '#b71c1c',
            ])
            ->font('Montserrat')
            ->favicon(asset('images/favicon.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Operaciones')
                     ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make()
                     ->label('Contabilidad')
                     ->icon('heroicon-o-calculator')
                     ->collapsed(),
                NavigationGroup::make()
                    ->label('Empresa')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Usuario')
                    ->icon('heroicon-o-user')
                    ->collapsed(),
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
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user?->name ?? 'Perfil';
                    })
                    ->url(function (): string {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return EditProfilePage::getUrl();
                    })
                    ->icon('heroicon-m-user-circle')
                    //If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user !== null && $user->exists;
                    }),
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('Mi Perfil')
                    ->setNavigationLabel('Mi Perfil')
                    ->setNavigationGroup('Usuario')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    )
            ]);
    }
}
