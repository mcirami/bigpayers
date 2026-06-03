<?php

namespace App\Providers;

use App\Company;
use App\Observers\UserObserver;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use LeadMax\TrackYourStats\System\NavBar;
use LeadMax\TrackYourStats\System\Notifications;
use LeadMax\TrackYourStats\System\Session;
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
        View::share([
            'accountTypeLabel' => config('branding.account.singular'),
            'accountTypeLabelPlural' => config('branding.account.plural'),
            'affiliateTypeLabel' => config('branding.affiliate.singular'),
            'affiliateTypeLabelPlural' => config('branding.affiliate.plural'),
        ]);
        view()->composer(['layouts.master', 'layouts.dashboard-shell'], function (\Illuminate\View\View $view) {
            $currentUser = Session::userData();
            $currentUserId = Session::userID();
            $currentUserType = Session::userType();
            $currentPermissions = Session::permissions();
            $navBar = new NavBar($currentUserType, $currentPermissions);
            $notifications = new Notifications($currentUserId);
            $notifications->fetchUsersNotifications();
            $view->with([
                'company' => Company::instance()->first(),
                'currentPermissions' => $currentPermissions,
                'currentUser' => $currentUser,
                'currentUserId' => $currentUserId,
                'currentUserType' => $currentUserType,
                'navBar' => $navBar,
                'notifications' => $notifications,
            ]);
        });
        view()->composer(['errors.403', 'errors.404', 'errors.500'], function (\Illuminate\View\View $view) {
            $view->with([
                'company' => Company::instance()->first(),
                'currentUserId' => Session::userID(),
            ]);
        });
        User::observe(UserObserver::class);

	    Paginator::defaultView('vendor/pagination/default');
	    Paginator::defaultSimpleView('default');
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
