<?php

namespace App\Providers;

use App\Company;
use App\Observers\UserObserver;
use App\Services\BrandingLabels;
use App\Support\CurrentUserSession;
use App\Support\LegacyNavBar as NavBar;
use App\Support\Notifications;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('webroot', getWebRoot());
        View::share(BrandingLabels::viewData());
        view()->composer('layouts.dashboard-shell', function (\Illuminate\View\View $view) {
            $currentUserContext = CurrentUserSession::snapshot();
            $navBar = new NavBar($currentUserContext->type, $currentUserContext->permissions);
            $notifications = new Notifications($currentUserContext->id);
            $notifications->fetchUsersNotifications();
            $view->with([
                'company' => Company::instance()->first(),
                'currentPermissions' => $currentUserContext->permissions,
                'currentUser' => $currentUserContext->data,
                'currentUserId' => $currentUserContext->id,
                'currentUserType' => $currentUserContext->type,
                'navBar' => $navBar,
                'notifications' => $notifications,
            ]);
        });
        view()->composer('report.*', function (\Illuminate\View\View $view) {
            $currentUserContext = CurrentUserSession::snapshot();

            $view->with([
                'canCreateManagers' => $currentUserContext->can('create_managers'),
                'canViewFraudData' => $currentUserContext->can('view_fraud_data'),
                'canViewPayouts' => $currentUserContext->can('view_payouts'),
                'isAdmin' => $currentUserContext->type === \App\Privilege::ROLE_ADMIN,
                'isAffiliate' => $currentUserContext->type === \App\Privilege::ROLE_AFFILIATE,
                'isGod' => $currentUserContext->type === \App\Privilege::ROLE_GOD,
                'isManager' => $currentUserContext->type === \App\Privilege::ROLE_MANAGER,
                'sessionUserType' => $currentUserContext->type,
            ]);
        });
        view()->composer(['errors.403', 'errors.404', 'errors.500'], function (\Illuminate\View\View $view) {
            $view->with([
                'company' => Company::instance()->first(),
                'currentUserId' => CurrentUserSession::id(),
            ]);
        });
        User::observe(UserObserver::class);

	    Paginator::defaultView('vendor/pagination/default');
	    Paginator::defaultSimpleView('vendor/pagination/simple-default');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
