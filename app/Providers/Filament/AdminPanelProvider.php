<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Ads\AdsResource;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\PublicDocuments\PublicDocumentResource;
use App\Filament\Resources\PublicDocumentUserViews\PublicDocumentUserViewResource;
use App\Filament\Resources\Settings\SettingsResource;
use App\Filament\Resources\SiteAlerts\SiteAlertResource;
use App\Filament\Resources\Topics\TopicResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\OverviewStatsWidget;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->sidebarCollapsibleOnDesktop()
            ->brandName('go2forum ･ ადმინისტრატორის პანელი')
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $dashboardItem = self::navigationItemFor(Dashboard::class);
                $usersItem = self::navigationItemFor(UserResource::class);
                $topicsItem = self::navigationItemFor(TopicResource::class);
                $conversationsItem = self::navigationItemFor(ConversationResource::class)
                    ->childItems([
                        self::navigationItemFor(MessageResource::class),
                    ]);

                $categoriesItem = self::navigationItemFor(CategoryResource::class)
                    ->childItems([
                        self::navigationItemFor(AdsResource::class),
                    ]);
                $documentsItem = self::navigationItemFor(PublicDocumentResource::class)
                    ->childItems([
                        self::navigationItemFor(PublicDocumentUserViewResource::class),
                    ]);
                $bannersItem = self::navigationItemFor(BannerResource::class);
                $siteAlertsItem = self::navigationItemFor(SiteAlertResource::class);
                $settingsItem = self::navigationItemFor(SettingsResource::class);

                return $builder->groups([
                    NavigationGroup::make()
                        ->items([
                            $dashboardItem,
                            $usersItem,
                            $topicsItem,
                            $conversationsItem,
                        ]),
                    NavigationGroup::make()
                        ->items([
                            $categoriesItem,
                            $documentsItem,
                            $bannersItem,
                            $siteAlertsItem,
                        ]),
                    NavigationGroup::make()
                        ->items([
                            $settingsItem,
                        ]),
                ]);
            })
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                OverviewStatsWidget::class,
            ])->userMenuItems([
                    Action::make('home')
                        ->label('მთავარი')
                        ->url(fn(): string => route('page.home'))
                        ->icon('heroicon-o-home'),
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
            ]);
    }

    /**
     * @param  class-string  $class
     */
    protected static function navigationItemFor(string $class): NavigationItem
    {
        return $class::getNavigationItems()[0];
    }
}
