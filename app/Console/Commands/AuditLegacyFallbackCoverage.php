<?php

namespace App\Console\Commands;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

class AuditLegacyFallbackCoverage extends Command
{
    protected $signature = 'legacy:audit-fallback-coverage';

    protected $description = 'Verify retired legacy PHP URLs stay documented and unrouted.';

    private array $intentionallyUnrouted = [
        '404.php' => 'Legacy error template, not a public workflow.',
        '500.php' => 'Legacy error template, not a public workflow.',
        'activate_affiliate.php' => 'Retired legacy redirect marker; use /user/pending/{id}/activate.',
        'add_new_ip_blacklist.php' => 'Retired legacy redirect marker; use /ip-blacklist/create.',
        'add_offer_url.php' => 'Retired legacy redirect marker; use /offer/urls/create.',
        'add_referral.php' => 'Retired legacy redirect marker; use /user/{id}/referrals/create.',
        'add_sale.php' => 'Retired legacy redirect marker; use /sales/add.',
        'aff_add.php' => 'Retired legacy redirect marker; use /user/create.',
        'aff_add_ref.php' => 'Retired legacy redirect marker; use /user/{id}/referrals.',
        'aff_details.php' => 'Retired legacy redirect marker; use /user/{id}/edit.',
        'aff_edit_ref.php' => 'Retired legacy redirect marker; use /user/{id}/referrals.',
        'aff_help.php' => 'Retired legacy redirect marker; use /forgot-password.',
        'aff_permissions.php' => 'Retired legacy redirect marker; use /admin/report-permissions.',
        'aff_update.php' => 'Retired legacy redirect marker; use /user/{id}/edit.',
        'approve_offer_request.php' => 'Retired legacy redirect marker; use /offer/{id}/approve-request/{user}.',
        'ban_user.php' => 'Retired legacy redirect marker; use /user/{id}/ban.',
        'ban_user_edit.php' => 'Retired legacy redirect marker; use /user/{id}/ban/edit.',
        'banned_users.php' => 'Retired legacy redirect marker; use /user/banned.',
        'bonus.php' => 'Retired legacy redirect marker; use /bonuses.',
        'bonus_assign.php' => 'Retired legacy redirect marker; use /bonuses/{bonus}/assign.',
        'bonus_edit.php' => 'Retired legacy redirect marker; use /bonuses/{bonus}/edit.',
        'campaign_create.php' => 'Retired legacy redirect marker; use /advertisers/create.',
        'campaign_edit.php' => 'Retired legacy redirect marker; use /advertisers/{id}/edit.',
        'campaign_manage.php' => 'Retired legacy redirect marker; use /advertisers.',
        'clicksearch.php' => 'Retired legacy redirect marker; use /click-search.',
        'create_bonus.php' => 'Retired legacy redirect marker; use /bonuses/create.',
        'create_none_unique.php' => 'Retired legacy redirect marker; use /offer/rules/{id}/none-unique/create.',
        'create_notification.php' => 'Retired legacy redirect marker; use /notifications/create.',
        'dontaskdonttell.php' => 'Retired legacy redirect marker; use /click-id-tool.',
        'edit_blacklisted_ip.php' => 'Retired legacy redirect marker; use /ip-blacklist/{id}/edit.',
        'edit_none_unique.php' => 'Retired legacy redirect marker; use /offer/rules/none-unique/{rule}/edit.',
        'edit_offer_url.php' => 'Retired legacy redirect marker; use /offer/urls/{id}/edit.',
        'edit_salaries.php' => 'Retired legacy redirect marker; use /salaries/edit.',
        'edit_salary.php' => 'Retired legacy redirect marker; use /user/{id}/salary/showUpdate.',
        'edit_sale_log.php' => 'Retired legacy redirect marker; use /chat-log/view/{id}.',
        'footer.php' => 'Retired legacy support include.',
        'global_postback.php' => 'Retired legacy redirect marker; use /global-postback.',
        'header.php' => 'Retired legacy support include.',
        'home.php' => 'Retired legacy redirect marker; use /dashboard.',
        'index.php' => 'Root route is handled by Laravel; legacy marker file redirects to /.',
        'ip_black_list.php' => 'Retired legacy redirect marker; use /ip-blacklist.',
        'log_sale.php' => 'Retired legacy redirect marker; use /chat-log/add/{pendingConversionId}.',
        'login.php' => 'Retired legacy redirect marker; use /login.',
        'logout.php' => 'Retired legacy redirect marker; use /logout.',
        'mass_assign_pb.php' => 'Retired legacy redirect marker; use /account/mass-postback.',
        'notifications.php' => 'Retired legacy notification compatibility redirect; use /notifications.',
        'offer_access.php' => 'Retired legacy redirect marker; use /offer/{id}/access.',
        'offer_add.php' => 'Retired legacy redirect marker; use /offer/create.',
        'offer_details.php' => 'Retired legacy redirect marker; use /offer/view/{id}.',
        'offer_edit_pb.php' => 'Retired legacy redirect marker; use /offer/{id}/postback.',
        'offer_edit_rules.php' => 'Retired legacy redirect marker; use /offer/rules/{id}.',
        'offer_update.php' => 'Retired legacy redirect marker; use /offer/edit/{id}.',
        'offer_urls.php' => 'Retired legacy redirect marker; use /offer/urls.',
        'salaries.php' => 'Retired legacy redirect marker; use /salaries.',
        'sale_log_edit.php' => 'Retired legacy redirect marker; use /chat-log/view/{id}.',
        'sale_log_view.php' => 'Retired legacy redirect marker; use /chat-log/view/{id}.',
        'scripts/affiliate_signup.php' => 'Legacy AJAX endpoint used only by retired legacy signup form; Laravel signup routes are explicit.',
        'scripts/offer/request_offer.php' => 'Legacy offer request AJAX endpoint; modern offer request route is /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo/{rule}.',
        'scripts/process_bonuses.php' => 'Retired legacy redirect marker; use /bonuses/process.',
        'scripts/sale_log.php' => 'Retired legacy AJAX endpoint; use /chat-log/view/{saleLogId}/delete.',
        'scripts/update_geoip.php' => 'Retired legacy GeoIP updater; use the provisioning/ops workflow.',
        'settings.php' => 'Retired legacy redirect marker; use /settings.',
        'setup.php' => 'Retired legacy redirect marker; use /admin/setup.',
        'signup.php' => 'Retired legacy redirect marker; use /signup.',
        'signup_success.php' => 'Retired legacy redirect marker; use /signup-success.',
        'update_databases.php' => 'Retired legacy redirect marker; use /admin/database-updates.',
        'upload_favicon.php' => 'Retired legacy upload endpoint; upload favicons through /settings.',
        'upload_logo.php' => 'Retired legacy upload endpoint; upload logos through /settings.',
        'view_pending_affiliates.php' => 'Retired legacy redirect marker; use /user/pending.',
    ];

    private array $allowedPublicPhp = [
        'index.php' => 'Laravel front controller.',
    ];

    private array $legacyRedirectStubs = [
        'aff_add.php' => '/user/create',
        'aff_add_ref.php' => '/user/',
        'aff_help.php' => '/forgot-password',
        'aff_permissions.php' => '/admin/report-permissions',
        'aff_edit_ref.php' => '/user/',
        'add_new_ip_blacklist.php' => '/ip-blacklist/create',
        'add_offer_url.php' => '/offer/urls/create',
        'add_referral.php' => '/user/',
        'add_sale.php' => '/sales/add',
        'activate_affiliate.php' => '/user/pending/',
        'approve_offer_request.php' => '/offer/',
        'ban_user.php' => '/user/',
        'ban_user_edit.php' => '/user/',
        'banned_users.php' => '/user/banned',
        'bonus.php' => '/bonuses',
        'bonus_assign.php' => '/bonuses/',
        'bonus_edit.php' => '/bonuses/',
        'campaign_edit.php' => '/advertisers/',
        'campaign_create.php' => '/advertisers/create',
        'campaign_manage.php' => '/advertisers',
        'clicksearch.php' => '/click-search',
        'create_bonus.php' => '/bonuses/create',
        'create_notification.php' => '/notifications/create',
        'create_none_unique.php' => '/offer/rules/',
        'dontaskdonttell.php' => '/click-id-tool',
        'edit_blacklisted_ip.php' => '/ip-blacklist/',
        'edit_none_unique.php' => '/offer/rules/none-unique/',
        'edit_salary.php' => '/user/',
        'edit_sale_log.php' => '/chat-log/view/',
        'edit_salaries.php' => '/salaries/edit',
        'global_postback.php' => '/global-postback',
        'aff_details.php' => '/user/',
        'aff_update.php' => '/user/',
        'home.php' => '/dashboard',
        'index.php' => '/',
        'ip_black_list.php' => '/ip-blacklist',
        'login.php' => '/login',
        'logout.php' => '/logout',
        'mass_assign_pb.php' => '/account/mass-postback',
        'notifications.php' => '/notifications',
        'offer_add.php' => '/offer/create',
        'offer_access.php' => '/offer/',
        'edit_offer_url.php' => '/offer/urls/',
        'offer_details.php' => '/offer/view/',
        'offer_edit_pb.php' => '/offer/',
        'offer_edit_rules.php' => '/offer/rules/',
        'offer_update.php' => '/offer/edit/',
        'offer_urls.php' => '/offer/urls',
        'salaries.php' => '/salaries',
        'sale_log_edit.php' => '/chat-log/view/',
        'sale_log_view.php' => '/chat-log/view/',
        'scripts/process_bonuses.php' => '/bonuses/process',
        'settings.php' => '/settings',
        'setup.php' => '/admin/setup',
        'signup.php' => '/signup',
        'signup_success.php' => '/signup-success',
        'log_sale.php' => '/chat-log/add/',
        'update_databases.php' => '/admin/database-updates',
        'upload_favicon.php' => '/settings',
        'upload_logo.php' => '/settings',
        'view_pending_affiliates.php' => '/user/pending',
    ];

    private array $retiredScriptEndpoints = [
        'scripts/affiliate_signup.php' => 'Use the Laravel signup routes.',
        'scripts/offer/request_offer.php' => 'Use /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Use POST /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Use GET|POST /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Use POST /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Use GET|POST /offer/rules/geo/{rule}.',
        'scripts/sale_log.php' => 'Use the Laravel chat-log routes.',
        'scripts/update_geoip.php' => 'Use the provisioning/ops workflow; the web updater is retired.',
    ];

    private array $frontControllerForbiddenPatterns = [
        '../legacy' => 'public/index.php must not include files from the legacy directory.',
        'legacy/index.php' => 'public/index.php must not execute legacy/index.php.',
        'legacy_loader.php' => 'public/index.php must not load the retired legacy bootstrap.',
        'is_file($file)' => 'public/index.php must not dynamically check request paths for executable files.',
        'include($file)' => 'public/index.php must not dynamically include request-matched files.',
    ];

    private array $runtimeBootstrapRequiredPatterns = [
        'private static bool $bootstrapped = false;' => 'RuntimeBootstrap is missing its idempotency guard.',
        'session_status() === PHP_SESSION_NONE' => 'RuntimeBootstrap must guard native session startup.',
        '$connection->setConnection();' => 'RuntimeBootstrap must initialize tenant connection constants.',
        'Company::loadFromSession()->setSession();' => 'RuntimeBootstrap must restore company session context.',
    ];

    private array $retiredCompanySessionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Company' => 'Use App\\Company instead of the legacy company class.',
        'Company::loadFromSession()' => 'Use App\\Company current-company helpers instead of the legacy session company loader.',
    ];

    private array $retiredCompanySessionAllowedFiles = [
        'app/Support/RuntimeBootstrap.php' => 'The Laravel-owned boundary that restores legacy company context during request startup.',
    ];

    private array $legacySessionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Session' => 'Use App\\Support\\CurrentUserSession instead of importing the legacy session class directly.',
    ];

    private array $legacySessionAllowedFiles = [
        'app/Support/CurrentUserSession.php' => 'The dedicated boundary around the legacy session class.',
    ];

    private array $nativeSessionForbiddenPatterns = [
        '$_SESSION' => 'Use App\\Support\\NativeSession instead of reading or writing the native session superglobal directly.',
        '$_GET' => 'Use Illuminate\\Http\\Request instead of reading query parameters from the native request superglobal directly.',
        '$_POST' => 'Use App\\Support\\NativeRequest for explicit legacy POST bridges instead of writing the native request superglobal directly.',
        '$_COOKIE' => 'Use Illuminate\\Http\\Request cookie helpers instead of reading cookies from the native request superglobal directly.',
        '$_SERVER' => 'Use Illuminate\\Http\\Request server helpers instead of reading server values from the native request superglobal directly.',
    ];

    private array $nativeSessionAllowedFiles = [
        'app/Support/NativeSession.php' => 'The dedicated boundary around native PHP session superglobal access.',
        'app/Support/NativeRequest.php' => 'The dedicated boundary around native PHP request superglobal bridges for legacy classes.',
    ];

    private array $runtimeEnvForbiddenPatterns = [
        'env(' => 'Use Laravel config values instead of reading environment variables directly at runtime.',
    ];

    private array $legacyPermissionsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Permissions' => 'Use App\\Support\\LegacyPermissions instead of importing the legacy permissions class directly.',
        'Permissions::loadFromSession()' => 'Use App\\Support\\CurrentUserSession::permissions() instead of loading permissions from the legacy session directly.',
    ];

    private array $legacyPermissionsAllowedFiles = [
        'app/Support/LegacyPermissions.php' => 'The dedicated boundary around the legacy permissions class.',
    ];

    private array $legacyClickGeoForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickGeo' => 'The legacy ClickGeo class is retired; use App\\Support\\ClickGeo.',
        'App\\Support\\LegacyClickGeo' => 'The LegacyClickGeo wrapper is retired; use App\\Support\\ClickGeo.',
    ];

    private array $legacyClickForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\Click;' => 'Use App\\Support\\LegacyClick instead of importing the legacy click class directly.',
        'LeadMax\\TrackYourStats\\Clicks\\Click as ' => 'Use App\\Support\\LegacyClick instead of importing the legacy click class directly.',
        'LeadMax\\TrackYourStats\\Clicks\\Cookie' => 'The legacy click cookie class is retired; use App\\Support\\Tracking\\ClickCookie.',
    ];

    private array $legacyClickAllowedFiles = [
        'app/Support/LegacyClick.php' => 'The dedicated boundary around the legacy click class.',
    ];

    private array $legacyClickVarsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickVars' => 'The legacy click vars class is retired; use App\\Support\\ClickVariables.',
    ];

    private array $legacyClickSearcherForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickSearcher' => 'The legacy ClickSearcher class is retired; use App\\Support\\ClickSearcher.',
        'App\\Support\\LegacyClickSearcher' => 'The LegacyClickSearcher wrapper is retired; use App\\Support\\ClickSearcher.',
    ];

    private array $legacyConversionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\Conversion' => 'Use App\\Support\\LegacyConversion instead of importing the legacy conversion class directly.',
    ];

    private array $legacyConversionAllowedFiles = [
        'app/Support/LegacyConversion.php' => 'The dedicated boundary around the legacy conversion class.',
    ];

    private array $legacyPendingConversionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\PendingConversion' => 'The legacy PendingConversion class is retired; use App\\Support\\PendingConversion.',
        'App\\Support\\LegacyPendingConversion' => 'The LegacyPendingConversion wrapper is retired; use App\\Support\\PendingConversion.',
    ];

    private array $legacyPostBackUrlEventHandlerForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler' => 'The legacy postback URL event handler is retired; use App\\Support\\Tracking\\PostBackUrlEventHandler.',
        'App\\Support\\LegacyPostBackURLEventHandler' => 'The LegacyPostBackURLEventHandler wrapper is retired; use App\\Support\\Tracking\\PostBackUrlEventHandler.',
    ];

    private array $legacyClickRegistrationEventForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\URLEvents\\' => 'The legacy URL event namespace is retired; use App\\Support\\Tracking\\Events.',
        'App\\Support\\LegacyClickRegistrationEvent' => 'The LegacyClickRegistrationEvent wrapper is retired; use App\\Support\\Tracking\\Events\\ClickRegistrationEvent.',
    ];

    private array $legacyUidForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\UID' => 'The legacy UID class is retired; use App\\Support\\ClickIdCodec.',
    ];

    private array $legacyTrackingParametersForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\TrackingParameters' => 'The legacy tracking parameters class is retired; use App\\Support\\TrackingParameters.',
    ];

    private array $legacyLanderForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Lander' => 'Use App\\Support\\LegacyLander instead of importing the legacy lander class directly.',
    ];

    private array $legacyLanderAllowedFiles = [
        'app/Support/LegacyLander.php' => 'The dedicated boundary around the legacy lander class.',
    ];

    private array $legacyNavBarForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\NavBar' => 'Use App\\Support\\LegacyNavBar instead of importing the legacy navigation class directly.',
    ];

    private array $legacyNavBarAllowedFiles = [
        'app/Support/LegacyNavBar.php' => 'The dedicated boundary around the legacy navigation class.',
    ];

    private array $legacyIpBlackListForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\IPBlackList' => 'Use App\\Support\\LegacyIPBlackList instead of importing the legacy IP blacklist class directly.',
    ];

    private array $legacyIpBlackListAllowedFiles = [
        'app/Support/LegacyIPBlackList.php' => 'The dedicated boundary around the legacy IP blacklist class.',
    ];

    private array $legacyImagesUploaderForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader' => 'Use App\\Support\\LegacyImagesUploader instead of importing the legacy image uploader class directly.',
    ];

    private array $legacyImagesUploaderAllowedFiles = [
        'app/Support/LegacyImagesUploader.php' => 'The dedicated boundary around the legacy image uploader class.',
    ];

    private array $legacyNotificationsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Notifications' => 'Use App\\Support\\LegacyNotifications instead of importing the legacy notifications class directly.',
    ];

    private array $legacyNotificationsAllowedFiles = [
        'app/Support/LegacyNotifications.php' => 'The dedicated boundary around the legacy notifications class.',
    ];

    private array $legacyPayoutsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\Payouts' => 'Use App\\Support\\LegacyPayouts instead of importing the legacy payouts class directly.',
    ];

    private array $legacyPayoutsAllowedFiles = [
        'app/Support/LegacyPayouts.php' => 'The dedicated boundary around the legacy payouts class.',
    ];

    private array $legacyOfferDomainForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\Offer' => 'Use App\\Support\\LegacyOffer instead of importing the legacy offer class directly.',
        'LeadMax\\TrackYourStats\\Offer\\RepHasOffer' => 'Use App\\Support\\LegacyRepHasOffer instead of importing the legacy offer-assignment class directly.',
    ];

    private array $legacyOfferDomainAllowedFiles = [
        'app/Support/LegacyOffer.php' => 'The dedicated boundary around the legacy offer class.',
        'app/Support/LegacyRepHasOffer.php' => 'The dedicated boundary around the legacy offer-assignment class.',
    ];

    private array $legacyOfferSupportForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\Caps' => 'Use App\\Support\\LegacyCaps instead of importing the legacy offer caps class directly.',
        'LeadMax\\TrackYourStats\\Offer\\Campaigns' => 'Use App\\Support\\LegacyCampaigns instead of importing the legacy campaigns class directly.',
        'LeadMax\\TrackYourStats\\Offer\\CreateOffer' => 'Use App\\Support\\LegacyCreateOffer instead of importing the legacy create-offer class directly.',
        'LeadMax\\TrackYourStats\\Offer\\FreeSignUp' => 'Use App\\Support\\LegacyFreeSignUp instead of importing the legacy free-signup class directly.',
        'LeadMax\\TrackYourStats\\Offer\\View' => 'Use App\\Support\\LegacyOfferView instead of referencing the legacy offer view class directly.',
    ];

    private array $legacyOfferSupportAllowedFiles = [
        'app/Support/LegacyCaps.php' => 'The dedicated boundary around the legacy offer caps class.',
        'app/Support/LegacyCampaigns.php' => 'The dedicated boundary around the legacy campaigns class.',
        'app/Support/LegacyCreateOffer.php' => 'The dedicated boundary around the legacy create-offer class.',
        'app/Support/LegacyFreeSignUp.php' => 'The dedicated boundary around the legacy free-signup class.',
        'app/Support/LegacyOfferView.php' => 'The dedicated boundary around the legacy offer view class.',
    ];

    private array $legacyOfferRulesForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\Rules' => 'Use App\\Support legacy offer-rule wrappers instead of referencing legacy offer-rule classes directly.',
    ];

    private array $legacyOfferRulesAllowedFiles = [
        'app/Support/LegacyOfferRules.php' => 'The dedicated boundary around the legacy offer rules collection.',
        'app/Support/LegacyOfferRuleGeo.php' => 'The dedicated boundary around the legacy offer geo-rule helper.',
        'app/Support/LegacyGeoRuleHandler.php' => 'The dedicated boundary around the legacy geo-rule handler.',
        'app/Support/LegacyDeviceRuleHandler.php' => 'The dedicated boundary around the legacy device-rule handler.',
        'app/Support/LegacyNoneUniqueRuleHandler.php' => 'The dedicated boundary around the legacy none-unique-rule handler.',
    ];

    private array $legacyAdjustmentsLogForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog' => 'Use App\\Support\\LegacyAdjustmentsLog instead of importing the legacy adjustments log class directly.',
    ];

    private array $legacyAdjustmentsLogAllowedFiles = [
        'app/Support/LegacyAdjustmentsLog.php' => 'The dedicated boundary around the legacy adjustments log class.',
    ];

    private array $legacySaleLogForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Offer\\SaleLog' => 'Use App\\Support\\LegacySaleLog instead of importing the legacy sale log class directly.',
    ];

    private array $legacySaleLogAllowedFiles = [
        'app/Support/LegacySaleLog.php' => 'The dedicated boundary around the legacy sale log class.',
    ];

    private array $legacyDateForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\Date' => 'The legacy date class is retired; use App\\Support\\DateHelper.',
        '$_COOKIE["timezone"]' => 'Use App\\Support\\NativeRequest::cookie() instead of reading the timezone cookie directly.',
    ];

    private array $legacyPaginateForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\Paginate' => 'The legacy paginate class is retired; use App\\Support\\PaginationHelper.',
    ];

    private array $legacyAssignmentsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\Assignments' => 'The legacy assignments class is retired; use App\\Support\\QueryAssignments.',
    ];

    private array $legacyTreeForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Tree' => 'Use App\\Support\\LegacyTree instead of importing the legacy tree class directly.',
    ];

    private array $legacyTreeAllowedFiles = [
        'app/Support/LegacyTree.php' => 'The dedicated boundary around the legacy tree class.',
    ];

    private array $legacyUserForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\User' => 'Use App\\Support\\LegacyUser instead of importing the legacy user class directly.',
    ];

    private array $legacyUserAllowedFiles = [
        'app/Support/LegacyUser.php' => 'The dedicated boundary around the legacy user class.',
    ];

    private array $legacyLoginForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Login' => 'Use App\\Support\\LegacyLogin instead of importing the legacy login class directly.',
    ];

    private array $legacyLoginAllowedFiles = [
        'app/Support/LegacyLogin.php' => 'The dedicated boundary around the legacy login class.',
    ];

    private array $legacyAffiliateSignUpForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\AffiliateSignUp' => 'Use App\\Support\\LegacyAffiliateSignUp instead of importing the legacy affiliate signup class directly.',
    ];

    private array $legacyAffiliateSignUpAllowedFiles = [
        'app/Support/LegacyAffiliateSignUp.php' => 'The dedicated boundary around the legacy affiliate signup class.',
    ];

    private array $legacyUserDomainForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Bonus' => 'Use App\\Support\\LegacyBonus instead of importing the legacy bonus class directly.',
        'LeadMax\\TrackYourStats\\User\\Salary' => 'Use App\\Support\\LegacySalary instead of importing the legacy salary class directly.',
        'LeadMax\\TrackYourStats\\User\\PostBackUrl' => 'The legacy global postback URL class is retired; use the Laravel user_postbacks query instead.',
        'LeadMax\\TrackYourStats\\User\\Privileges' => 'Use App\\Support\\LegacyPrivileges instead of importing the legacy privileges class directly.',
        'LeadMax\\TrackYourStats\\User\\Referrals' => 'Use App\\Support\\LegacyReferrals instead of importing the legacy referrals class directly.',
        'LeadMax\\TrackYourStats\\User\\ReportPermissions' => 'Use App\\Support\\LegacyReportPermissions instead of importing the legacy report permissions class directly.',
    ];

    private array $legacyUserDomainAllowedFiles = [
        'app/Support/LegacyBonus.php' => 'The dedicated boundary around the legacy bonus class.',
        'app/Support/LegacySalary.php' => 'The dedicated boundary around the legacy salary class.',
        'app/Support/LegacyPrivileges.php' => 'The dedicated boundary around the legacy privileges class.',
        'app/Support/LegacyReferrals.php' => 'The dedicated boundary around the legacy referrals class.',
        'app/Support/LegacyReportPermissions.php' => 'The dedicated boundary around the legacy report permissions class.',
    ];

    private array $legacyOfferPostBackUrlForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\PostBackURLs\\ConversionPostBackURL' => 'Use App\\Support\\LegacyConversionPostBackURL instead of referencing the legacy conversion postback URL class directly.',
        'LeadMax\\TrackYourStats\\User\\PostBackURLs\\FreePostBackURL' => 'Use App\\Support\\LegacyFreePostBackURL instead of referencing the legacy free-signup postback URL class directly.',
        'LeadMax\\TrackYourStats\\User\\PostBackURLs\\DeductionPostBackURL' => 'Use App\\Support\\LegacyDeductionPostBackURL instead of referencing the legacy deduction postback URL class directly.',
    ];

    private array $legacyOfferPostBackUrlAllowedFiles = [
        'app/Support/LegacyConversionPostBackURL.php' => 'The dedicated boundary around the legacy conversion postback URL class.',
        'app/Support/LegacyFreePostBackURL.php' => 'The dedicated boundary around the legacy free-signup postback URL class.',
        'app/Support/LegacyDeductionPostBackURL.php' => 'The dedicated boundary around the legacy deduction postback URL class.',
    ];

    private array $legacyAdminLoginForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\AdminLogin' => 'The legacy admin-login class is retired; use Laravel request and middleware boundaries.',
    ];

    private array $legacyNotifyForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Notify' => 'The legacy notify class is retired; render notifications through Laravel views.',
    ];

    private array $legacyCompanyUpdaterForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Database\\CompanyUpdater' => 'Use App\\Support\\LegacyCompanyUpdater instead of importing the legacy company updater class directly.',
    ];

    private array $legacyCompanyUpdaterAllowedFiles = [
        'app/Support/LegacyCompanyUpdater.php' => 'The dedicated boundary around the legacy company updater class.',
    ];

    private array $legacyConnectionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Connection' => 'Use App\\Support\\LegacyConnection instead of importing the legacy connection class directly.',
    ];

    private array $legacyConnectionAllowedFiles = [
        'app/Support/LegacyConnection.php' => 'The dedicated boundary around the legacy connection class.',
    ];

    private array $legacyReportHtmlForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Formats\\HTML' => 'Use App\\Support\\LegacyReportHtml instead of importing the legacy report HTML formatter directly.',
    ];

    private array $legacyReportHtmlAllowedFiles = [
        'app/Support/LegacyReportHtml.php' => 'The dedicated boundary around the legacy report HTML formatter.',
    ];

    private array $legacyReporterForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Reporter' => 'Use App\\Support\\LegacyReporter instead of importing the legacy reporter class directly.',
    ];

    private array $legacyReporterAllowedFiles = [
        'app/Support/LegacyReporter.php' => 'The dedicated boundary around the legacy reporter class.',
    ];

    private array $legacyReportFiltersForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Filters' => 'Use App\\Support legacy report filter wrappers instead of importing legacy report filters directly.',
    ];

    private array $legacyReportFiltersAllowedFiles = [
        'app/Support/LegacyClickLinkFilter.php' => 'The dedicated boundary around the legacy click-link report filter.',
        'app/Support/LegacyDeductionColumnFilter.php' => 'The dedicated boundary around the legacy deduction-column report filter.',
        'app/Support/LegacyDollarSignFilter.php' => 'The dedicated boundary around the legacy dollar-sign report filter.',
        'app/Support/LegacyEarningPerClickFilter.php' => 'The dedicated boundary around the legacy earning-per-click report filter.',
        'app/Support/LegacyTotalFilter.php' => 'The dedicated boundary around the legacy total report filter.',
        'app/Support/LegacyUserToolTipFilter.php' => 'The dedicated boundary around the legacy user-tooltip report filter.',
    ];

    private array $legacyReportObjectsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\ReportBase' => 'The legacy report base class is retired; use App\\Support\\ReportBase.',
        'LeadMax\\TrackYourStats\\Report\\Affiliate;' => 'The legacy affiliate report is retired in Laravel-owned code; use the Laravel report queries instead.',
        'LeadMax\\TrackYourStats\\Report\\AffiliatePayout' => 'Use App\\Support\\LegacyAffiliatePayoutReport instead of importing the legacy affiliate payout report directly.',
        'LeadMax\\TrackYourStats\\Report\\BlackList' => 'The legacy blacklist report is retired; use the Laravel blacklist query instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\BlackListRepository' => 'The legacy blacklist repository is retired; use the Laravel blacklist query instead.',
    ];

    private array $legacyReportObjectsAllowedFiles = [
        'app/Support/LegacyAffiliatePayoutReport.php' => 'The dedicated boundary around the legacy affiliate payout report.',
    ];

    private array $legacyDatabaseConnectionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Database\\DatabaseConnection' => 'Use App\\Support\\LegacyDatabaseConnection instead of referencing the legacy database connection class directly.',
    ];

    private array $legacyDatabaseConnectionAllowedFiles = [
        'app/Support/LegacyDatabaseConnection.php' => 'The dedicated boundary around the legacy database connection class.',
    ];

    private array $legacyOfferReportRepositoriesForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\AdminOfferRepository' => 'Use App\\Support\\LegacyAdminOfferRepository instead of importing the legacy admin offer repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\AffiliateOfferRepository' => 'Use App\\Support\\LegacyAffiliateOfferRepository instead of importing the legacy affiliate offer repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\GodOfferRepository' => 'Use App\\Support\\LegacyGodOfferRepository instead of importing the legacy god offer repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\ManagerOfferRepository' => 'Use App\\Support\\LegacyManagerOfferRepository instead of importing the legacy manager offer repository directly.',
    ];

    private array $legacyOfferReportRepositoriesAllowedFiles = [
        'app/Support/LegacyAdminOfferRepository.php' => 'The dedicated boundary around the legacy admin offer repository.',
        'app/Support/LegacyAffiliateOfferRepository.php' => 'The dedicated boundary around the legacy affiliate offer repository.',
        'app/Support/LegacyGodOfferRepository.php' => 'The dedicated boundary around the legacy god offer repository.',
        'app/Support/LegacyManagerOfferRepository.php' => 'The dedicated boundary around the legacy manager offer repository.',
    ];

    private array $legacyEmployeeReportRepositoriesForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Repository' => 'Avoid typehinting the legacy base report repository directly in modern report controllers.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\AdminEmployeeRepository' => 'Use App\\Support\\LegacyAdminEmployeeRepository instead of importing the legacy admin employee repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\GodEmployeeRepository' => 'Use App\\Support\\LegacyGodEmployeeRepository instead of importing the legacy god employee repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\ManagerEmployeeRepository' => 'Use App\\Support\\LegacyManagerEmployeeRepository instead of importing the legacy manager employee repository directly.',
    ];

    private array $legacyEmployeeReportRepositoriesAllowedFiles = [
        'app/Support/LegacyAdminEmployeeRepository.php' => 'The dedicated boundary around the legacy admin employee repository.',
        'app/Support/LegacyGodEmployeeRepository.php' => 'The dedicated boundary around the legacy god employee repository.',
        'app/Support/LegacyManagerEmployeeRepository.php' => 'The dedicated boundary around the legacy manager employee repository.',
    ];

    private array $legacyMiscReportRepositoriesForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AdjustmentsLogRepository' => 'The legacy adjustments-log repository is retired; use the Laravel adjustments report query instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AdvertiserRepository' => 'The legacy advertiser repository is retired; use Laravel report queries instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AffiliateChatLogRepository' => 'The legacy affiliate chat-log repository is retired; use the Laravel chat-log detail query instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AggregateReportRepository' => 'The legacy aggregate report repository is retired; use the Laravel aggregate report query instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\PayoutLogRepository' => 'The legacy payout-log repository is retired; use the App\\PayoutLog model instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\SaleLogRepository' => 'The legacy sale-log summary repository is retired; use the Laravel chat-log summary query instead.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\SubVarRepository' => 'The legacy sub-var repository is retired; use the Laravel sub report query instead.',
    ];

    private array $legacyMiscReportRepositoriesAllowedFiles = [];

    private array $legacyMailForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Mail' => 'Use App\\Support\\LegacyMail instead of importing the legacy mail class directly.',
    ];

    private array $legacyMailAllowedFiles = [
        'app/Support/LegacyMail.php' => 'The dedicated boundary around the legacy mail class.',
    ];

    private array $malformedLegacyNamespaceForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\LeadMax\\TrackYourStats' => 'Remove the duplicated legacy namespace segment.',
    ];

    private array $legacyBoundaryAllowedDirectories = [
        'app/Support' => 'Dedicated wrappers around legacy classes.',
    ];

    public function handle(): int
    {
        $legacyFiles = $this->legacyPhpFiles();
        $routeUris = $this->routeUrisFromRegisteredRoutes();

        $unexpectedIntentionalRoutes = $this->intentionallyUnroutedRouteErrors($routeUris);

        if ($unexpectedIntentionalRoutes->isNotEmpty()) {
            $this->error('Legacy files marked intentionally unrouted are registered as Laravel routes:');
            $unexpectedIntentionalRoutes->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $registeredPhpRouteErrors = $this->registeredPhpRouteInventoryErrors($routeUris);

        if ($registeredPhpRouteErrors->isNotEmpty()) {
            $this->error('Unexpected PHP compatibility routes remain registered:');
            $registeredPhpRouteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyFilePresenceErrors = $this->legacyFilePresenceErrors($legacyFiles);

        if ($legacyFilePresenceErrors->isNotEmpty()) {
            $this->error('Legacy PHP files should not exist:');
            $legacyFilePresenceErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $intentionalInventoryErrors = $this->intentionallyUnroutedInventoryErrors();

        if ($intentionalInventoryErrors->isNotEmpty()) {
            $this->error('Retired legacy PHP URL inventory is incomplete:');
            $intentionalInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $unexpectedPublicPhp = $this->unexpectedPublicPhpEntrypoints();

        if ($unexpectedPublicPhp->isNotEmpty()) {
            $this->error('Unexpected public PHP entrypoints:');
            $unexpectedPublicPhp->each(fn ($file) => $this->line(" - public/{$file}"));

            return self::FAILURE;
        }

        $publicPhpEntrypointErrors = $this->allowedPublicPhpInventoryErrors();

        if ($publicPhpEntrypointErrors->isNotEmpty()) {
            $this->error('Allowed public PHP entrypoint inventory is stale or incomplete:');
            $publicPhpEntrypointErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $rewriteErrors = $this->publicRewriteHardeningErrors();

        if ($rewriteErrors->isNotEmpty()) {
            $this->error('Public rewrite hardening is incomplete:');
            $rewriteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $frontControllerErrors = $this->frontControllerFallbackErrors();

        if ($frontControllerErrors->isNotEmpty()) {
            $this->error('Front controller fallback hardening is incomplete:');
            $frontControllerErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $runtimeBootstrapErrors = $this->runtimeBootstrapHardeningErrors();

        if ($runtimeBootstrapErrors->isNotEmpty()) {
            $this->error('Runtime bootstrap hardening is incomplete:');
            $runtimeBootstrapErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $modernScriptReferenceErrors = $this->modernRetiredScriptReferenceErrors();

        if ($modernScriptReferenceErrors->isNotEmpty()) {
            $this->error('Modern app code still references retired legacy script endpoints:');
            $modernScriptReferenceErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $modernLegacyPhpUrlReferenceErrors = $this->modernLegacyPhpUrlReferenceErrors();

        if ($modernLegacyPhpUrlReferenceErrors->isNotEmpty()) {
            $this->error('Modern views or public assets still reference legacy PHP compatibility URLs:');
            $modernLegacyPhpUrlReferenceErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $retiredCompanySessionDependencyErrors = $this->retiredCompanySessionDependencyErrors();

        if ($retiredCompanySessionDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired legacy company session dependencies:');
            $retiredCompanySessionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySessionDependencyErrors = $this->legacySessionDependencyErrors();

        if ($legacySessionDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still imports the legacy session class directly:');
            $legacySessionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $nativeSessionDependencyErrors = $this->nativeSessionDependencyErrors();

        if ($nativeSessionDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still reads native PHP superglobals directly:');
            $nativeSessionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $runtimeEnvDependencyErrors = $this->runtimeEnvDependencyErrors();

        if ($runtimeEnvDependencyErrors->isNotEmpty()) {
            $this->error('Runtime source still reads environment variables directly:');
            $runtimeEnvDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySourceNativeSuperglobalErrors = $this->legacySourceNativeSuperglobalErrors();

        if ($legacySourceNativeSuperglobalErrors->isNotEmpty()) {
            $this->error('Legacy source classes still read native PHP superglobals directly:');
            $legacySourceNativeSuperglobalErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPermissionsDependencyErrors = $this->legacyPermissionsDependencyErrors();

        if ($legacyPermissionsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy permissions class directly:');
            $legacyPermissionsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickGeoDependencyErrors = $this->legacyClickGeoDependencyErrors();

        if ($legacyClickGeoDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired click geo classes:');
            $legacyClickGeoDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickDependencyErrors = $this->legacyClickDependencyErrors();

        if ($legacyClickDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy click class directly:');
            $legacyClickDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickVarsDependencyErrors = $this->legacyClickVarsDependencyErrors();

        if ($legacyClickVarsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy click vars class directly:');
            $legacyClickVarsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickSearcherDependencyErrors = $this->legacyClickSearcherDependencyErrors();

        if ($legacyClickSearcherDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired click searcher classes:');
            $legacyClickSearcherDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyConversionDependencyErrors = $this->legacyConversionDependencyErrors();

        if ($legacyConversionDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy conversion class directly:');
            $legacyConversionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPendingConversionDependencyErrors = $this->legacyPendingConversionDependencyErrors();

        if ($legacyPendingConversionDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired pending conversion classes:');
            $legacyPendingConversionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPostBackUrlEventHandlerDependencyErrors = $this->legacyPostBackUrlEventHandlerDependencyErrors();

        if ($legacyPostBackUrlEventHandlerDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired postback URL event handler classes:');
            $legacyPostBackUrlEventHandlerDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickRegistrationEventDependencyErrors = $this->legacyClickRegistrationEventDependencyErrors();

        if ($legacyClickRegistrationEventDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired tracking URL event classes:');
            $legacyClickRegistrationEventDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyUidDependencyErrors = $this->legacyUidDependencyErrors();

        if ($legacyUidDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy UID class directly:');
            $legacyUidDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyTrackingParametersDependencyErrors = $this->legacyTrackingParametersDependencyErrors();

        if ($legacyTrackingParametersDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy tracking parameters class directly:');
            $legacyTrackingParametersDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyLanderDependencyErrors = $this->legacyLanderDependencyErrors();

        if ($legacyLanderDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy lander class directly:');
            $legacyLanderDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyNavBarDependencyErrors = $this->legacyNavBarDependencyErrors();

        if ($legacyNavBarDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy navigation class directly:');
            $legacyNavBarDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyIpBlackListDependencyErrors = $this->legacyIpBlackListDependencyErrors();

        if ($legacyIpBlackListDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy IP blacklist class directly:');
            $legacyIpBlackListDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyImagesUploaderDependencyErrors = $this->legacyImagesUploaderDependencyErrors();

        if ($legacyImagesUploaderDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy image uploader class directly:');
            $legacyImagesUploaderDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyNotificationsDependencyErrors = $this->legacyNotificationsDependencyErrors();

        if ($legacyNotificationsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy notifications class directly:');
            $legacyNotificationsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyOfferDomainDependencyErrors = $this->legacyOfferDomainDependencyErrors();

        if ($legacyOfferDomainDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy offer-domain helpers directly:');
            $legacyOfferDomainDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyOfferSupportDependencyErrors = $this->legacyOfferSupportDependencyErrors();

        if ($legacyOfferSupportDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy offer support helpers directly:');
            $legacyOfferSupportDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyOfferRulesDependencyErrors = $this->legacyOfferRulesDependencyErrors();

        if ($legacyOfferRulesDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still references legacy offer-rule classes directly:');
            $legacyOfferRulesDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPayoutsDependencyErrors = $this->legacyPayoutsDependencyErrors();

        if ($legacyPayoutsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy payouts class directly:');
            $legacyPayoutsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyAdjustmentsLogDependencyErrors = $this->legacyAdjustmentsLogDependencyErrors();

        if ($legacyAdjustmentsLogDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy adjustments log class directly:');
            $legacyAdjustmentsLogDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySaleLogDependencyErrors = $this->legacySaleLogDependencyErrors();

        if ($legacySaleLogDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy sale log class directly:');
            $legacySaleLogDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyDateDependencyErrors = $this->legacyDateDependencyErrors();

        if ($legacyDateDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy date class directly:');
            $legacyDateDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPaginateDependencyErrors = $this->legacyPaginateDependencyErrors();

        if ($legacyPaginateDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy paginate class directly:');
            $legacyPaginateDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyAssignmentsDependencyErrors = $this->legacyAssignmentsDependencyErrors();

        if ($legacyAssignmentsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy assignments class directly:');
            $legacyAssignmentsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyTreeDependencyErrors = $this->legacyTreeDependencyErrors();

        if ($legacyTreeDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy tree class directly:');
            $legacyTreeDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyUserDependencyErrors = $this->legacyUserDependencyErrors();

        if ($legacyUserDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy user class directly:');
            $legacyUserDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyLoginDependencyErrors = $this->legacyLoginDependencyErrors();

        if ($legacyLoginDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy login class directly:');
            $legacyLoginDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyAffiliateSignUpDependencyErrors = $this->legacyAffiliateSignUpDependencyErrors();

        if ($legacyAffiliateSignUpDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy affiliate signup class directly:');
            $legacyAffiliateSignUpDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyUserDomainDependencyErrors = $this->legacyUserDomainDependencyErrors();

        if ($legacyUserDomainDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy user-domain helpers directly:');
            $legacyUserDomainDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyOfferPostBackUrlDependencyErrors = $this->legacyOfferPostBackUrlDependencyErrors();

        if ($legacyOfferPostBackUrlDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still references legacy offer postback URL classes directly:');
            $legacyOfferPostBackUrlDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyAdminLoginDependencyErrors = $this->legacyAdminLoginDependencyErrors();

        if ($legacyAdminLoginDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy admin-login class directly:');
            $legacyAdminLoginDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyNotifyDependencyErrors = $this->legacyNotifyDependencyErrors();

        if ($legacyNotifyDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy notify class directly:');
            $legacyNotifyDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyCompanyUpdaterDependencyErrors = $this->legacyCompanyUpdaterDependencyErrors();

        if ($legacyCompanyUpdaterDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy company updater class directly:');
            $legacyCompanyUpdaterDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyConnectionDependencyErrors = $this->legacyConnectionDependencyErrors();

        if ($legacyConnectionDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy connection class directly:');
            $legacyConnectionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyReportHtmlDependencyErrors = $this->legacyReportHtmlDependencyErrors();

        if ($legacyReportHtmlDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy report HTML formatter directly:');
            $legacyReportHtmlDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyReporterDependencyErrors = $this->legacyReporterDependencyErrors();

        if ($legacyReporterDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy reporter class directly:');
            $legacyReporterDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyReportFiltersDependencyErrors = $this->legacyReportFiltersDependencyErrors();

        if ($legacyReportFiltersDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy report filters directly:');
            $legacyReportFiltersDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyReportObjectsDependencyErrors = $this->legacyReportObjectsDependencyErrors();

        if ($legacyReportObjectsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy report objects directly:');
            $legacyReportObjectsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyDatabaseConnectionDependencyErrors = $this->legacyDatabaseConnectionDependencyErrors();

        if ($legacyDatabaseConnectionDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy database connection class directly:');
            $legacyDatabaseConnectionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyOfferReportRepositoriesDependencyErrors = $this->legacyOfferReportRepositoriesDependencyErrors();

        if ($legacyOfferReportRepositoriesDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy offer report repositories directly:');
            $legacyOfferReportRepositoriesDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyEmployeeReportRepositoriesDependencyErrors = $this->legacyEmployeeReportRepositoriesDependencyErrors();

        if ($legacyEmployeeReportRepositoriesDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy employee report repositories directly:');
            $legacyEmployeeReportRepositoriesDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyMiscReportRepositoriesDependencyErrors = $this->legacyMiscReportRepositoriesDependencyErrors();

        if ($legacyMiscReportRepositoriesDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports legacy report repositories directly:');
            $legacyMiscReportRepositoriesDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyMailDependencyErrors = $this->legacyMailDependencyErrors();

        if ($legacyMailDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy mail class directly:');
            $legacyMailDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $malformedLegacyNamespaceErrors = $this->malformedLegacyNamespaceErrors();

        if ($malformedLegacyNamespaceErrors->isNotEmpty()) {
            $this->error('Source code still contains malformed legacy namespace references:');
            $malformedLegacyNamespaceErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyBoundaryDependencyErrors = $this->legacyBoundaryDependencyErrors();

        if ($legacyBoundaryDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still references legacy classes outside App\Support boundaries:');
            $legacyBoundaryDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySupportWrapperShapeErrors = $this->legacySupportWrapperShapeErrors();

        if ($legacySupportWrapperShapeErrors->isNotEmpty()) {
            $this->error('Legacy support wrappers should remain simple boundary aliases:');
            $legacySupportWrapperShapeErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySupportWrapperInventoryErrors = $this->legacySupportWrapperInventoryErrors();

        if ($legacySupportWrapperInventoryErrors->isNotEmpty()) {
            $this->error('Legacy support wrappers are missing specific audit allow-list coverage:');
            $legacySupportWrapperInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySupportDirectReferenceInventoryErrors = $this->legacySupportDirectReferenceInventoryErrors();

        if ($legacySupportDirectReferenceInventoryErrors->isNotEmpty()) {
            $this->error('Support files with direct legacy references are missing specific audit allow-list coverage:');
            $legacySupportDirectReferenceInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $csrfExceptionErrors = $this->legacyPostCsrfExceptionErrors();

        if ($csrfExceptionErrors->isNotEmpty()) {
            $this->error('Legacy POST PHP routes or PHP CSRF exceptions remain registered:');
            $csrfExceptionErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $boundaryAllowedPathInventoryErrors = $this->boundaryAllowedPathInventoryErrors();

        if ($boundaryAllowedPathInventoryErrors->isNotEmpty()) {
            $this->error('Legacy boundary allow-list paths are missing or stale:');
            $boundaryAllowedPathInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $this->info('No legacy PHP files exist.');
        $this->info(count($this->intentionallyUnrouted) . ' retired legacy PHP URLs are documented.');
        $publicEntrypointCount = count($this->allowedPublicPhp);
        $publicEntrypointSummary = $publicEntrypointCount === 1
            ? '1 public PHP entrypoint is an expected front controller.'
            : "{$publicEntrypointCount} public PHP entrypoints are expected front controllers.";
        $this->info($publicEntrypointSummary);
        $this->info('Public webserver rewrites route direct PHP file requests through Laravel.');
        $this->info('Front controller has no dynamic legacy file fallback.');
        $this->info('Laravel middleware initializes the legacy runtime boundary once per request.');
        $this->info('Retired legacy marker URLs are blocked from modern views and assets.');
        $this->info('Retired legacy PHP URLs are not registered as Laravel routes.');
        $this->info('Allowed public PHP entrypoints exist and have documented reasons.');
        $this->info('Modern views and assets do not reference retired legacy script endpoints.');
        $this->info('Modern views and public assets do not reference legacy PHP compatibility URLs.');
        $this->info('Retired legacy script endpoint URLs are blocked from modern source.');
        $this->info('No legacy POST PHP routes or PHP CSRF exceptions remain registered.');
        $this->info('No PHP compatibility routes remain registered.');
        $this->info('Runtime code does not reference the retired legacy company session loader.');
        $this->info('Runtime code reads current user/session state through CurrentUserSession.');
        $this->info('Modern Laravel code reads native PHP superglobals through NativeSession, NativeRequest, or request boundaries.');
        $this->info('Runtime source reads environment-backed values through Laravel config.');
        $this->info('Legacy source classes read native PHP superglobals through NativeSession or NativeRequest boundaries.');
        $this->info('Modern Laravel code reads legacy permission metadata through LegacyPermissions.');
        $this->info('Runtime code resolves click geography through App\\Support\\ClickGeo.');
        $this->info('Modern Laravel code resolves legacy click writes through LegacyClick.');
        $this->info('Runtime code resolves click variables through App\\Support\\ClickVariables.');
        $this->info('Runtime code resolves click search queries through App\\Support\\ClickSearcher.');
        $this->info('Modern Laravel code resolves legacy conversion helpers through LegacyConversion.');
        $this->info('Runtime code handles pending conversions through App\\Support\\PendingConversion.');
        $this->info('Runtime code handles postback URLs through App\\Support\\Tracking\\PostBackUrlEventHandler.');
        $this->info('Runtime code handles tracking events through App\\Support\\Tracking\\Events.');
        $this->info('Runtime code encodes click IDs through App\\Support\\ClickIdCodec.');
        $this->info('Runtime code normalizes tracking query parameters through App\\Support\\TrackingParameters.');
        $this->info('Modern Laravel code loads legacy landers through LegacyLander.');
        $this->info('Modern Laravel code builds dashboard navigation through LegacyNavBar.');
        $this->info('Modern Laravel code manages IP blacklist records through LegacyIPBlackList.');
        $this->info('Modern Laravel code uploads sale-log images through LegacyImagesUploader.');
        $this->info('Modern Laravel code reads and sends notifications through LegacyNotifications.');
        $this->info('Modern Laravel code resolves legacy offer-domain helpers through App\Support boundaries.');
        $this->info('Modern Laravel code resolves legacy offer support helpers through App\Support boundaries.');
        $this->info('Modern Laravel code resolves legacy offer-rule helpers through App\Support boundaries.');
        $this->info('Modern Laravel code resolves legacy payout helpers through LegacyPayouts.');
        $this->info('Modern Laravel code writes adjustment logs through LegacyAdjustmentsLog.');
        $this->info('Modern Laravel code writes sale logs through LegacySaleLog.');
        $this->info('Runtime code resolves date helpers through App\\Support\\DateHelper.');
        $this->info('Runtime code resolves pagination through App\\Support\\PaginationHelper.');
        $this->info('Runtime code resolves query assignments through App\\Support\\QueryAssignments.');
        $this->info('Modern Laravel code rebuilds user trees through LegacyTree.');
        $this->info('Modern Laravel code resolves legacy users through LegacyUser.');
        $this->info('Modern login flows use LegacyLogin for legacy login constants.');
        $this->info('Modern signup flows use LegacyAffiliateSignUp.');
        $this->info('Modern Laravel code resolves legacy user-domain helpers through App\Support boundaries.');
        $this->info('Modern offer postback URL flows use App\Support boundaries.');
        $this->info('Modern layouts preserve admin-login state through Laravel request boundaries.');
        $this->info('Legacy admin-login and notify classes are retired from runtime source.');
        $this->info('Modern database update screens run through LegacyCompanyUpdater.');
        $this->info('Modern Laravel code resolves legacy connections through LegacyConnection.');
        $this->info('Modern report views render through LegacyReportHtml.');
        $this->info('Modern report controllers coordinate reports through LegacyReporter.');
        $this->info('Modern report controllers format reports through legacy report filter wrappers.');
        $this->info('Modern payout reports use the audited legacy payout report boundary.');
        $this->info('Modern report controllers resolve legacy database connections through LegacyDatabaseConnection.');
        $this->info('Modern offer report controllers resolve legacy offer repositories through App\Support boundaries.');
        $this->info('Modern employee report controllers and commands resolve legacy employee repositories through App\Support boundaries.');
        $this->info('Modern report controllers resolve remaining legacy report repositories through App\Support boundaries.');
        $this->info('Modern Laravel code sends legacy mail through LegacyMail.');
        $this->info('Source code has no malformed duplicated legacy namespace references.');
        $this->info('Modern Laravel source keeps direct legacy class references inside audited App\Support or bootstrap boundaries.');
        $this->info('Legacy support wrappers remain simple boundary aliases.');
        $this->info('Legacy support wrappers have specific audit allow-list coverage.');
        $this->info('Support files with direct legacy references have specific audit allow-list coverage.');
        $this->info('Legacy boundary allow-list paths exist.');

        return self::SUCCESS;
    }

    private function legacyPhpFiles()
    {
        if (!File::isDirectory(base_path('legacy'))) {
            return collect();
        }

        return collect(File::allFiles(base_path('legacy')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->sort()
            ->values();
    }

    private function routeUrisFromRegisteredRoutes(): array
    {
        return collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('web', (array) $route->getAction('middleware'), true))
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function legacyFilePresenceErrors($legacyFiles)
    {
        return $legacyFiles
            ->map(fn ($file) => "{$file}: remove retired legacy PHP file.")
            ->values();
    }

    private function intentionallyUnroutedInventoryErrors()
    {
        return collect($this->intentionallyUnrouted)
            ->flatMap(function ($reason, $file) {
                $errors = [];

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "{$file}: intentionally unrouted reason is blank.";
                }

                return $errors;
            })
            ->values();
    }

    private function intentionallyUnroutedRouteErrors(array $routeUris)
    {
        return collect(array_keys($this->intentionallyUnrouted))
            ->filter(fn ($file) => in_array($file, $routeUris, true))
            ->map(fn ($file) => "{$file}: {$this->intentionallyUnrouted[$file]}")
            ->values();
    }

    private function registeredPhpRouteInventoryErrors(array $routeUris)
    {
        return collect($routeUris)
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->map(fn ($uri) => "{$uri}: registered PHP route should be removed.")
            ->values();
    }

    private function unexpectedPublicPhpEntrypoints()
    {
        return collect(File::allFiles(public_path()))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->reject(fn ($file) => array_key_exists($file, $this->allowedPublicPhp))
            ->sort()
            ->values();
    }

    private function allowedPublicPhpInventoryErrors()
    {
        return collect($this->allowedPublicPhp)
            ->flatMap(function ($reason, $file) {
                $errors = [];

                if (!File::exists(public_path($file))) {
                    $errors[] = "public/{$file}: allowed public PHP entrypoint does not exist.";
                }

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "public/{$file}: allowed public PHP entrypoint reason is blank.";
                }

                return $errors;
            })
            ->values();
    }

    private function publicRewriteHardeningErrors()
    {
        $errors = collect();
        $htaccess = public_path('.htaccess');
        $webConfig = public_path('web.config');

        if (!File::exists($htaccess) || !str_contains(File::get($htaccess), 'Route Direct PHP Entrypoints Through Laravel')) {
            $errors->push('public/.htaccess is missing the direct PHP entrypoint rewrite rule.');
        }

        if (!File::exists($webConfig) || !str_contains(File::get($webConfig), 'Route Direct PHP Files Through Laravel')) {
            $errors->push('public/web.config is missing the direct PHP entrypoint rewrite rule.');
        }

        return $errors;
    }

    private function frontControllerFallbackErrors()
    {
        $errors = collect();
        $frontController = public_path('index.php');

        if (!File::exists($frontController)) {
            return $errors->push('public/index.php is missing.');
        }

        $contents = File::get($frontController);

        $this->appendForbiddenPatternErrors($errors, $contents, $this->frontControllerForbiddenPatterns);

        return $errors;
    }

    private function runtimeBootstrapHardeningErrors()
    {
        $errors = collect();
        $runtimeBootstrap = app_path('Support/RuntimeBootstrap.php');

        if (!File::exists($runtimeBootstrap)) {
            return $errors->push('app/Support/RuntimeBootstrap.php is missing.');
        }

        $contents = File::get($runtimeBootstrap);

        $this->appendMissingPatternErrors($errors, $contents, $this->runtimeBootstrapRequiredPatterns);

        return $errors;
    }

    private function modernRetiredScriptReferenceErrors()
    {
        $errors = collect();
        $directories = [
            'resources/views',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['js', 'php'], true)) {
                    continue;
                }

                $contents = File::get($file->getPathname());
                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                foreach ($this->retiredScriptEndpoints as $endpoint => $replacement) {
                    if (str_contains($contents, $endpoint)) {
                        $errors->push("{$relativePath} references {$endpoint}. {$replacement}");
                    }
                }
            }
        }

        return $errors;
    }

    private function modernLegacyPhpUrlReferenceErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'resources/views',
            'public/css',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['css', 'js', 'php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());
                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->modernLegacyPhpUrlReferenceErrorsFor($sourceFiles);
    }

    private function modernLegacyPhpUrlReferenceErrorsFor($sourceFiles)
    {
        $legacyPhpUrls = collect(array_keys($this->legacyRedirectStubs))
            ->merge(array_keys($this->retiredScriptEndpoints))
            ->reject(fn (string $url) => str_contains($url, '{'))
            ->unique()
            ->values();

        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) use ($legacyPhpUrls) {
                return $legacyPhpUrls
                    ->filter(fn (string $url) => str_contains($contents, $url))
                    ->map(fn (string $url) => "{$relativePath}: replace legacy PHP URL {$url} with its modern Laravel route.")
                    ->all();
            })
            ->values();
    }

    private function retiredCompanySessionDependencyErrors()
    {
        return $this->retiredCompanySessionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function retiredCompanySessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->retiredCompanySessionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->retiredCompanySessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacySessionDependencyErrors()
    {
        return $this->legacySessionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacySessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacySessionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacySessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function nativeSessionDependencyErrors()
    {
        return $this->nativeSessionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'config',
            'database',
            'public',
            'resources/views',
            'routes',
        ]));
    }

    private function nativeSessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->nativeSessionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->nativeSessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function runtimeEnvDependencyErrors()
    {
        return $this->runtimeEnvDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function runtimeEnvDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->runtimeEnvForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacySourceNativeSuperglobalErrors()
    {
        $sourceFiles = collect();
        $path = base_path('src');

        if (!File::isDirectory($path)) {
            return $sourceFiles;
        }

        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = 'src/' . str_replace('\\', '/', $file->getRelativePathname());

            $sourceFiles[$relativePath] = File::get($file->getPathname());
        }

        return $this->legacySourceNativeSuperglobalErrorsFor($sourceFiles);
    }

    private function legacySourceNativeSuperglobalErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->nativeSessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyMailDependencyErrors()
    {
        return $this->legacyMailDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'database',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyMailDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyMailAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyMailForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyBoundaryDependencyErrors()
    {
        return $this->legacyBoundaryDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources/views',
            'routes',
        ]));
    }

    private function malformedLegacyNamespaceErrors()
    {
        return $this->malformedLegacyNamespaceErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'src',
        ]));
    }

    private function sourceFilesFromDirectories(array $directories, array $extensions = ['php'])
    {
        $sourceFiles = collect();

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), $extensions, true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $sourceFiles;
    }

    private function malformedLegacyNamespaceErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->malformedLegacyNamespaceForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyBoundaryDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(function (string $contents, string $relativePath) {
                foreach (array_keys($this->legacyBoundaryAllowedDirectories) as $allowedDirectory) {
                    if (str_starts_with($relativePath, $allowedDirectory . '/')) {
                        return true;
                    }
                }

                return false;
            })
            ->filter(fn (string $contents) => str_contains($contents, 'LeadMax\\TrackYourStats'))
            ->keys()
            ->map(fn (string $relativePath) => "{$relativePath}: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.")
            ->values();
    }

    private function legacySupportWrapperShapeErrors()
    {
        $sourceFiles = collect(File::glob(base_path('app/Support/Legacy*.php')))
            ->mapWithKeys(fn (string $path) => [
                'app/Support/' . basename($path) => File::get($path),
            ]);

        return $this->legacySupportWrapperShapeErrorsFor($sourceFiles);
    }

    private function legacySupportWrapperShapeErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];
                $className = pathinfo($relativePath, PATHINFO_FILENAME);

                if (!str_contains($contents, 'namespace App\\Support;')) {
                    $errors[] = "{$relativePath}: legacy support wrapper must live in the App\\Support namespace.";
                }

                preg_match_all('/^use LeadMax\\\\TrackYourStats\\\\[^;]+\\\\([^\\\\;]+);$/m', $contents, $legacyImports);

                if (count($legacyImports[0]) !== 1) {
                    $errors[] = "{$relativePath}: legacy support wrapper must import exactly one legacy class.";
                }

                $legacyClassName = $legacyImports[1][0] ?? null;

                if ($legacyClassName !== null) {
                    $legacyClass = str_replace('use ', '', rtrim($legacyImports[0][0], ';'));
                    $legacyClassPath = 'src/' . str_replace('\\', '/', substr($legacyClass, strlen('LeadMax\\TrackYourStats\\'))) . '.php';

                    if (!File::isFile(base_path($legacyClassPath))) {
                        $errors[] = "{$relativePath}: imported legacy class file {$legacyClassPath} does not exist.";
                    }

                    $classPattern = '/class\s+' . preg_quote($className, '/') . '\s+extends\s+' . preg_quote($legacyClassName, '/') . '\b/';

                    if (!preg_match($classPattern, $contents)) {
                        $errors[] = "{$relativePath}: legacy support wrapper must extend its imported legacy class directly.";
                    }
                }

                if (preg_match('/\bfunction\s+\w+\s*\(/', $contents)) {
                    $errors[] = "{$relativePath}: legacy support wrapper must not define behavior; add a dedicated adapter if behavior is needed.";
                }

                return $errors;
            })
            ->values();
    }

    private function legacySupportWrapperInventoryErrors()
    {
        return $this->legacySupportWrapperInventoryErrorsFor(
            collect(File::glob(base_path('app/Support/Legacy*.php')))
                ->map(fn (string $path) => 'app/Support/' . basename($path))
                ->all()
        );
    }

    private function legacySupportWrapperInventoryErrorsFor(array $wrapperFiles)
    {
        $allowedFiles = $this->specificAuditAllowedFiles();

        return collect($wrapperFiles)
            ->reject(fn (string $relativePath) => in_array($relativePath, $allowedFiles, true))
            ->map(fn (string $relativePath) => "{$relativePath}: legacy support wrapper is not listed in a specific audit allow-list.")
            ->values();
    }

    private function legacySupportDirectReferenceInventoryErrors()
    {
        $sourceFiles = collect(File::glob(base_path('app/Support/*.php')))
            ->mapWithKeys(fn (string $path) => [
                'app/Support/' . basename($path) => File::get($path),
            ]);

        return $this->legacySupportDirectReferenceInventoryErrorsFor($sourceFiles);
    }

    private function legacySupportDirectReferenceInventoryErrorsFor($sourceFiles)
    {
        $allowedFiles = $this->specificAuditAllowedFiles();

        return collect($sourceFiles)
            ->filter(fn (string $contents) => str_contains($contents, 'LeadMax\\TrackYourStats'))
            ->reject(fn (string $contents, string $relativePath) => in_array($relativePath, $allowedFiles, true))
            ->keys()
            ->map(fn (string $relativePath) => "{$relativePath}: support file references legacy classes but is not listed in a specific audit allow-list.")
            ->values();
    }

    private function specificAuditAllowedFiles(): array
    {
        $allowedFiles = [];

        foreach (get_object_vars($this) as $propertyName => $paths) {
            if (!is_array($paths) || !str_ends_with($propertyName, 'AllowedFiles')) {
                continue;
            }

            $allowedFiles = array_merge($allowedFiles, array_keys($paths));
        }

        return array_values(array_unique($allowedFiles));
    }

    private function boundaryAllowedPathInventoryErrors()
    {
        $properties = get_object_vars($this);
        $allowedFiles = [];
        $allowedDirectories = [];

        foreach ($properties as $propertyName => $paths) {
            if (!is_array($paths)) {
                continue;
            }

            if (str_ends_with($propertyName, 'AllowedFiles')) {
                $allowedFiles = array_merge($allowedFiles, array_keys($paths));
            }

            if (str_ends_with($propertyName, 'AllowedDirectories')) {
                $allowedDirectories = array_merge($allowedDirectories, array_keys($paths));
            }
        }

        return $this->boundaryAllowedPathInventoryErrorsFor(
            array_unique($allowedFiles),
            array_unique($allowedDirectories)
        );
    }

    private function boundaryAllowedPathInventoryErrorsFor(array $allowedFiles, array $allowedDirectories = [])
    {
        $fileErrors = collect($allowedFiles)
            ->reject(fn (string $relativePath) => File::isFile(base_path($relativePath)))
            ->map(fn (string $relativePath) => "{$relativePath}: audited legacy boundary allow-list file does not exist.");

        $directoryErrors = collect($allowedDirectories)
            ->reject(fn (string $relativePath) => File::isDirectory(base_path($relativePath)))
            ->map(fn (string $relativePath) => "{$relativePath}: audited legacy boundary allow-list directory does not exist.");

        return $fileErrors
            ->merge($directoryErrors)
            ->values();
    }

    private function legacyPermissionsDependencyErrors()
    {
        return $this->legacyPermissionsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyPermissionsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPermissionsAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPermissionsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickGeoDependencyErrors()
    {
        return $this->legacyClickGeoDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyClickGeoDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickGeoForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickDependencyErrors()
    {
        return $this->legacyClickDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyClickDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickVarsDependencyErrors()
    {
        return $this->legacyClickVarsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyClickVarsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickVarsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickSearcherDependencyErrors()
    {
        return $this->legacyClickSearcherDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyClickSearcherDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickSearcherForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyConversionDependencyErrors()
    {
        return $this->legacyConversionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyConversionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyConversionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyConversionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPendingConversionDependencyErrors()
    {
        return $this->legacyPendingConversionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyPendingConversionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPendingConversionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPostBackUrlEventHandlerDependencyErrors()
    {
        return $this->legacyPostBackUrlEventHandlerDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyPostBackUrlEventHandlerDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPostBackUrlEventHandlerForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickRegistrationEventDependencyErrors()
    {
        return $this->legacyClickRegistrationEventDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyClickRegistrationEventDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickRegistrationEventForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyUidDependencyErrors()
    {
        return $this->legacyUidDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyUidDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyUidForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyTrackingParametersDependencyErrors()
    {
        return $this->legacyTrackingParametersDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyTrackingParametersDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyTrackingParametersForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyLanderDependencyErrors()
    {
        return $this->legacyLanderDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyLanderDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyLanderAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyLanderForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyNavBarDependencyErrors()
    {
        return $this->legacyNavBarDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyNavBarDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyNavBarAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyNavBarForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyIpBlackListDependencyErrors()
    {
        return $this->legacyIpBlackListDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyIpBlackListDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyIpBlackListAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyIpBlackListForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyImagesUploaderDependencyErrors()
    {
        return $this->legacyImagesUploaderDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyImagesUploaderDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyImagesUploaderAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyImagesUploaderForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyNotificationsDependencyErrors()
    {
        return $this->legacyNotificationsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyNotificationsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyNotificationsAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyNotificationsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPayoutsDependencyErrors()
    {
        return $this->legacyPayoutsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyPayoutsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPayoutsAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPayoutsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyOfferDomainDependencyErrors()
    {
        return $this->legacyOfferDomainDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyOfferDomainDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyOfferDomainAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyOfferDomainForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyOfferSupportDependencyErrors()
    {
        return $this->legacyOfferSupportDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyOfferSupportDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyOfferSupportAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyOfferSupportForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyOfferRulesDependencyErrors()
    {
        return $this->legacyOfferRulesDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyOfferRulesDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyOfferRulesAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyOfferRulesForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyAdjustmentsLogDependencyErrors()
    {
        return $this->legacyAdjustmentsLogDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyAdjustmentsLogDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyAdjustmentsLogAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyAdjustmentsLogForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacySaleLogDependencyErrors()
    {
        return $this->legacySaleLogDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacySaleLogDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacySaleLogAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacySaleLogForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyDateDependencyErrors()
    {
        return $this->legacyDateDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyDateDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyDateForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPaginateDependencyErrors()
    {
        return $this->legacyPaginateDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyPaginateDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPaginateForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyAssignmentsDependencyErrors()
    {
        return $this->legacyAssignmentsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyAssignmentsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyAssignmentsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyTreeDependencyErrors()
    {
        return $this->legacyTreeDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyTreeDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyTreeAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyTreeForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyUserDependencyErrors()
    {
        return $this->legacyUserDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyUserDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyUserAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyUserForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyLoginDependencyErrors()
    {
        return $this->legacyLoginDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyLoginDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyLoginAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyLoginForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyAffiliateSignUpDependencyErrors()
    {
        return $this->legacyAffiliateSignUpDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyAffiliateSignUpDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyAffiliateSignUpAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyAffiliateSignUpForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyUserDomainDependencyErrors()
    {
        return $this->legacyUserDomainDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyUserDomainDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyUserDomainAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyUserDomainForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyOfferPostBackUrlDependencyErrors()
    {
        return $this->legacyOfferPostBackUrlDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyOfferPostBackUrlDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyOfferPostBackUrlAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyOfferPostBackUrlForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyAdminLoginDependencyErrors()
    {
        return $this->legacyAdminLoginDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyAdminLoginDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyAdminLoginForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyNotifyDependencyErrors()
    {
        return $this->legacyNotifyDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyNotifyDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyNotifyForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyCompanyUpdaterDependencyErrors()
    {
        return $this->legacyCompanyUpdaterDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyCompanyUpdaterDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyCompanyUpdaterAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyCompanyUpdaterForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyConnectionDependencyErrors()
    {
        return $this->legacyConnectionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyConnectionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyConnectionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyConnectionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyReportHtmlDependencyErrors()
    {
        return $this->legacyReportHtmlDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyReportHtmlDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyReportHtmlAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyReportHtmlForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyReporterDependencyErrors()
    {
        return $this->legacyReporterDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyReporterDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyReporterAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyReporterForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyReportFiltersDependencyErrors()
    {
        return $this->legacyReportFiltersDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyReportFiltersDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyReportFiltersAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyReportFiltersForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyReportObjectsDependencyErrors()
    {
        return $this->legacyReportObjectsDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
            'src',
        ]));
    }

    private function legacyReportObjectsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyReportObjectsAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyReportObjectsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyDatabaseConnectionDependencyErrors()
    {
        return $this->legacyDatabaseConnectionDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyDatabaseConnectionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyDatabaseConnectionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyDatabaseConnectionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyOfferReportRepositoriesDependencyErrors()
    {
        return $this->legacyOfferReportRepositoriesDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyOfferReportRepositoriesDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyOfferReportRepositoriesAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyOfferReportRepositoriesForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyEmployeeReportRepositoriesDependencyErrors()
    {
        return $this->legacyEmployeeReportRepositoriesDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyEmployeeReportRepositoriesDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyEmployeeReportRepositoriesAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyEmployeeReportRepositoriesForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyMiscReportRepositoriesDependencyErrors()
    {
        return $this->legacyMiscReportRepositoriesDependencyErrorsFor($this->sourceFilesFromDirectories([
            'app',
            'resources/views',
            'routes',
        ]));
    }

    private function legacyMiscReportRepositoriesDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyMiscReportRepositoriesAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyMiscReportRepositoriesForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPostCsrfExceptionErrors()
    {
        return $this->legacyPostCsrfExceptionErrorsFor(
            $this->legacyPostRouteUris(),
            $this->csrfExceptionUris()
        );
    }

    private function legacyPostRouteUris()
    {
        return collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('web', (array) $route->getAction('middleware'), true))
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->unique()
            ->sort()
            ->values();
    }

    private function legacyPostCsrfExceptionErrorsFor($legacyPostRoutes, array $csrfExceptions)
    {
        $normalizedCsrfExceptions = collect($csrfExceptions)
            ->map(fn ($uri) => trim($uri, '/'))
            ->unique()
            ->values();

        $missingExceptions = $legacyPostRoutes
            ->reject(fn ($uri) => $normalizedCsrfExceptions->contains($uri))
            ->map(fn ($uri) => "{$uri}: legacy POST PHP route is missing from VerifyCsrfToken exceptions.")
            ->values();

        $staleExceptions = $normalizedCsrfExceptions
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->reject(fn ($uri) => $legacyPostRoutes->contains($uri))
            ->map(fn ($uri) => "{$uri}: VerifyCsrfToken exception does not match a registered legacy POST PHP route.")
            ->values();

        return $missingExceptions
            ->merge($staleExceptions)
            ->values();
    }

    private function csrfExceptionUris(): array
    {
        $middleware = app(VerifyCsrfToken::class);
        $reflection = new ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        return collect($property->getValue($middleware))
            ->map(fn ($uri) => trim($uri, '/'))
            ->all();
    }

    private function appendMissingPatternErrors($errors, string $contents, array $requiredPatterns): void
    {
        foreach ($requiredPatterns as $pattern => $message) {
            if (!str_contains($contents, $pattern)) {
                $errors->push($message);
            }
        }
    }

    private function appendForbiddenPatternErrors($errors, string $contents, array $forbiddenPatterns): void
    {
        foreach ($forbiddenPatterns as $pattern => $message) {
            if (str_contains($contents, $pattern)) {
                $errors->push($message);
            }
        }
    }
}
