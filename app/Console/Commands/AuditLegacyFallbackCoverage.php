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

    protected $description = 'Verify legacy PHP files are explicitly routed or intentionally unrouted.';

    private array $intentionallyUnrouted = [
        '404.php' => 'Legacy error template, not a public workflow.',
        '500.php' => 'Legacy error template, not a public workflow.',
        'footer.php' => 'Legacy support include.',
        'header.php' => 'Legacy support include.',
        'index.php' => 'Root route is handled by Laravel.',
        'scripts/affiliate_signup.php' => 'Legacy AJAX endpoint used only by retired legacy signup form; Laravel signup routes are explicit.',
        'scripts/offer/request_offer.php' => 'Legacy offer request AJAX endpoint; modern offer request route is /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo/{rule}.',
    ];

    private array $allowedPublicPhp = [
        'index.php' => 'Laravel front controller.',
    ];

    private array $allowedNonLegacyPhpRoutes = [
        'alogin.php' => 'Legacy admin-login alias redirecting to /login/{id}.',
        'css/company.php' => 'Public compatibility redirect to /css/company.css.',
        'login_themes/{theme}/index.php' => 'Public compatibility redirect to /login.',
    ];

    private array $retiredScriptEndpoints = [
        'scripts/affiliate_signup.php' => 'Use the Laravel signup routes.',
        'scripts/offer/request_offer.php' => 'Use /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Use POST /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Use GET|POST /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Use POST /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Use GET|POST /offer/rules/geo/{rule}.',
        'scripts/process_bonuses.php' => 'Use /bonuses/process.',
        'scripts/sale_log.php' => 'Use the Laravel chat-log routes.',
        'scripts/update_geoip.php' => 'Use the provisioning/ops workflow; the web updater is retired.',
    ];

    private array $frontControllerForbiddenPatterns = [
        '../legacy' => 'public/index.php must not include files from the legacy directory.',
        'legacy/index.php' => 'public/index.php must not execute legacy/index.php.',
        'is_file($file)' => 'public/index.php must not dynamically check request paths for executable files.',
        'include($file)' => 'public/index.php must not dynamically include request-matched files.',
    ];

    private array $legacyBootstrapRequiredPatterns = [
        'BIGPAYERS_LEGACY_LOADER_BOOTSTRAPPED' => 'bootstrap/legacy_loader.php is missing the idempotency guard.',
        'require_once __DIR__. "/../vendor/autoload.php";' => 'bootstrap/legacy_loader.php must load Composer with require_once.',
        'session_status() === PHP_SESSION_NONE' => 'bootstrap/legacy_loader.php must guard native session startup.',
    ];

    private array $legacyBootstrapForbiddenPatterns = [
        'include __DIR__. "/../vendor/autoload.php";' => 'bootstrap/legacy_loader.php must not include Composer repeatedly.',
    ];

    private array $retiredCompanySessionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Company' => 'Use App\\Company instead of the legacy company class.',
        'Company::loadFromSession()' => 'Use App\\Company current-company helpers instead of the legacy session company loader.',
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

    private array $legacyPermissionsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Permissions' => 'Use App\\Support\\LegacyPermissions instead of importing the legacy permissions class directly.',
        'Permissions::loadFromSession()' => 'Use App\\Support\\CurrentUserSession::permissions() instead of loading permissions from the legacy session directly.',
    ];

    private array $legacyPermissionsAllowedFiles = [
        'app/Support/LegacyPermissions.php' => 'The dedicated boundary around the legacy permissions class.',
    ];

    private array $legacyClickGeoForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickGeo' => 'Use App\\Support\\LegacyClickGeo instead of importing the legacy ClickGeo class directly.',
    ];

    private array $legacyClickGeoAllowedFiles = [
        'app/Support/LegacyClickGeo.php' => 'The dedicated boundary around the legacy ClickGeo class.',
    ];

    private array $legacyClickForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\Click;' => 'Use App\\Support\\LegacyClick instead of importing the legacy click class directly.',
        'LeadMax\\TrackYourStats\\Clicks\\Click as ' => 'Use App\\Support\\LegacyClick instead of importing the legacy click class directly.',
    ];

    private array $legacyClickAllowedFiles = [
        'app/Support/LegacyClick.php' => 'The dedicated boundary around the legacy click class.',
    ];

    private array $legacyClickVarsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickVars' => 'Use App\\Support\\LegacyClickVars instead of importing the legacy click vars class directly.',
    ];

    private array $legacyClickVarsAllowedFiles = [
        'app/Support/LegacyClickVars.php' => 'The dedicated boundary around the legacy click vars class.',
    ];

    private array $legacyClickSearcherForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickSearcher' => 'Use App\\Support\\LegacyClickSearcher instead of importing the legacy click searcher class directly.',
    ];

    private array $legacyClickSearcherAllowedFiles = [
        'app/Support/LegacyClickSearcher.php' => 'The dedicated boundary around the legacy click searcher class.',
    ];

    private array $legacyConversionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\Conversion' => 'Use App\\Support\\LegacyConversion instead of importing the legacy conversion class directly.',
    ];

    private array $legacyConversionAllowedFiles = [
        'app/Support/LegacyConversion.php' => 'The dedicated boundary around the legacy conversion class.',
    ];

    private array $legacyPendingConversionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\PendingConversion' => 'Use App\\Support\\LegacyPendingConversion instead of importing the legacy pending conversion class directly.',
    ];

    private array $legacyPendingConversionAllowedFiles = [
        'app/Support/LegacyPendingConversion.php' => 'The dedicated boundary around the legacy pending conversion class.',
    ];

    private array $legacyPostBackUrlEventHandlerForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler' => 'Use App\\Support\\LegacyPostBackURLEventHandler instead of importing the legacy postback URL event handler directly.',
    ];

    private array $legacyPostBackUrlEventHandlerAllowedFiles = [
        'app/Support/LegacyPostBackURLEventHandler.php' => 'The dedicated boundary around the legacy postback URL event handler.',
    ];

    private array $legacyClickRegistrationEventForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\URLEvents\\ClickRegistrationEvent' => 'Use App\\Support\\LegacyClickRegistrationEvent instead of importing the legacy click registration event directly.',
    ];

    private array $legacyClickRegistrationEventAllowedFiles = [
        'app/Support/LegacyClickRegistrationEvent.php' => 'The dedicated boundary around the legacy click registration event.',
    ];

    private array $legacyUidForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\UID' => 'Use App\\Support\\LegacyUid instead of importing the legacy UID class directly.',
    ];

    private array $legacyUidAllowedFiles = [
        'app/Support/LegacyUid.php' => 'The dedicated boundary around the legacy UID class.',
    ];

    private array $legacyTrackingParametersForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\TrackingParameters' => 'Use App\\Support\\LegacyTrackingParameters instead of importing the legacy tracking parameters class directly.',
    ];

    private array $legacyTrackingParametersAllowedFiles = [
        'app/Support/LegacyTrackingParameters.php' => 'The dedicated boundary around the legacy tracking parameters class.',
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
        'LeadMax\\TrackYourStats\\Offer\\Campaigns' => 'Use App\\Support\\LegacyCampaigns instead of importing the legacy campaigns class directly.',
        'LeadMax\\TrackYourStats\\Offer\\View' => 'Use App\\Support\\LegacyOfferView instead of referencing the legacy offer view class directly.',
    ];

    private array $legacyOfferSupportAllowedFiles = [
        'app/Support/LegacyCampaigns.php' => 'The dedicated boundary around the legacy campaigns class.',
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
        'LeadMax\\TrackYourStats\\Table\\Date' => 'Use App\\Support\\LegacyDate instead of importing the legacy date class directly.',
        '$_COOKIE["timezone"]' => 'Use Laravel request cookie helpers instead of reading the timezone cookie directly.',
    ];

    private array $legacyDateAllowedFiles = [
        'app/Support/LegacyDate.php' => 'The dedicated boundary around the legacy date class.',
    ];

    private array $legacyPaginateForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\Paginate' => 'Use App\\Support\\LegacyPaginate instead of importing the legacy paginate class directly.',
    ];

    private array $legacyPaginateAllowedFiles = [
        'app/Support/LegacyPaginate.php' => 'The dedicated boundary around the legacy paginate class.',
    ];

    private array $legacyAssignmentsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Table\\Assignments' => 'Use App\\Support\\LegacyAssignments instead of importing the legacy assignments class directly.',
    ];

    private array $legacyAssignmentsAllowedFiles = [
        'app/Support/LegacyAssignments.php' => 'The dedicated boundary around the legacy assignments class.',
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
        'LeadMax\\TrackYourStats\\User\\PostBackUrl' => 'Use App\\Support\\LegacyPostBackUrl instead of importing the legacy postback URL class directly.',
        'LeadMax\\TrackYourStats\\User\\Privileges' => 'Use App\\Support\\LegacyPrivileges instead of importing the legacy privileges class directly.',
        'LeadMax\\TrackYourStats\\User\\Referrals' => 'Use App\\Support\\LegacyReferrals instead of importing the legacy referrals class directly.',
        'LeadMax\\TrackYourStats\\User\\ReportPermissions' => 'Use App\\Support\\LegacyReportPermissions instead of importing the legacy report permissions class directly.',
    ];

    private array $legacyUserDomainAllowedFiles = [
        'app/Support/LegacyBonus.php' => 'The dedicated boundary around the legacy bonus class.',
        'app/Support/LegacySalary.php' => 'The dedicated boundary around the legacy salary class.',
        'app/Support/LegacyPostBackUrl.php' => 'The dedicated boundary around the legacy postback URL class.',
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
        'LeadMax\\TrackYourStats\\User\\AdminLogin' => 'Use App\\Support\\LegacyAdminLogin instead of importing the legacy admin-login class directly.',
    ];

    private array $legacyAdminLoginAllowedFiles = [
        'app/Support/LegacyAdminLogin.php' => 'The dedicated boundary around the legacy admin-login class.',
    ];

    private array $legacyNotifyForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Notify' => 'Use App\\Support\\LegacyNotify instead of importing the legacy notify class directly.',
    ];

    private array $legacyNotifyAllowedFiles = [
        'app/Support/LegacyNotify.php' => 'The dedicated boundary around the legacy notify class.',
    ];

    private array $legacyCompanyUpdaterForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Database\\CompanyUpdater' => 'Use App\\Support\\LegacyCompanyUpdater instead of importing the legacy company updater class directly.',
    ];

    private array $legacyCompanyUpdaterAllowedFiles = [
        'app/Support/LegacyCompanyUpdater.php' => 'The dedicated boundary around the legacy company updater class.',
    ];

    private array $legacyReportHtmlForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\Formats\\HTML' => 'Use App\\Support\\LegacyReportHtml instead of importing the legacy report HTML formatter directly.',
    ];

    private array $legacyReportHtmlAllowedFiles = [
        'app/Support/LegacyReportHtml.php' => 'The dedicated boundary around the legacy report HTML formatter.',
    ];

    private array $legacyReportIdOfferForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Report\\ID\\Offer' => 'Use App\\Support\\LegacyReportIdOffer instead of importing the legacy report ID offer class directly.',
    ];

    private array $legacyReportIdOfferAllowedFiles = [
        'app/Support/LegacyReportIdOffer.php' => 'The dedicated boundary around the legacy report ID offer class.',
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
        'LeadMax\\TrackYourStats\\Report\\Affiliate;' => 'Use App\\Support\\LegacyAffiliateReport instead of importing the legacy affiliate report directly.',
        'LeadMax\\TrackYourStats\\Report\\AffiliatePayout' => 'Use App\\Support\\LegacyAffiliatePayoutReport instead of importing the legacy affiliate payout report directly.',
        'LeadMax\\TrackYourStats\\Report\\BlackList' => 'Use App\\Support\\LegacyBlackListReport instead of referencing the legacy blacklist report directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\BlackListRepository' => 'Use App\\Support\\LegacyBlackListRepository instead of referencing the legacy blacklist repository directly.',
    ];

    private array $legacyReportObjectsAllowedFiles = [
        'app/Support/LegacyAffiliateReport.php' => 'The dedicated boundary around the legacy affiliate report.',
        'app/Support/LegacyAffiliatePayoutReport.php' => 'The dedicated boundary around the legacy affiliate payout report.',
        'app/Support/LegacyBlackListReport.php' => 'The dedicated boundary around the legacy blacklist report.',
        'app/Support/LegacyBlackListRepository.php' => 'The dedicated boundary around the legacy blacklist repository.',
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
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AdjustmentsLogRepository' => 'Use App\\Support\\LegacyAdjustmentsLogRepository instead of importing the legacy adjustments-log repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AdvertiserRepository' => 'Use App\\Support\\LegacyAdvertiserRepository instead of importing the legacy advertiser repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AffiliateChatLogRepository' => 'Use App\\Support\\LegacyAffiliateChatLogRepository instead of importing the legacy affiliate chat-log repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\AggregateReportRepository' => 'Use App\\Support\\LegacyAggregateReportRepository instead of importing the legacy aggregate report repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\PayoutLogRepository' => 'Use App\\Support\\LegacyPayoutLogRepository instead of importing the legacy payout-log repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\SaleLogRepository' => 'Use App\\Support\\LegacySaleLogRepository instead of importing the legacy sale-log repository directly.',
        'LeadMax\\TrackYourStats\\Report\\Repositories\\SubVarRepository' => 'Use App\\Support\\LegacySubVarRepository instead of importing the legacy sub-var repository directly.',
    ];

    private array $legacyMiscReportRepositoriesAllowedFiles = [
        'app/Support/LegacyAdjustmentsLogRepository.php' => 'The dedicated boundary around the legacy adjustments-log repository.',
        'app/Support/LegacyAdvertiserRepository.php' => 'The dedicated boundary around the legacy advertiser repository.',
        'app/Support/LegacyAffiliateChatLogRepository.php' => 'The dedicated boundary around the legacy affiliate chat-log repository.',
        'app/Support/LegacyAggregateReportRepository.php' => 'The dedicated boundary around the legacy aggregate report repository.',
        'app/Support/LegacyPayoutLogRepository.php' => 'The dedicated boundary around the legacy payout-log repository.',
        'app/Support/LegacySaleLogRepository.php' => 'The dedicated boundary around the legacy sale-log repository.',
        'app/Support/LegacySubVarRepository.php' => 'The dedicated boundary around the legacy sub-var repository.',
    ];

    private array $legacyMailForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Mail' => 'Use App\\Support\\LegacyMail instead of importing the legacy mail class directly.',
    ];

    private array $legacyMailAllowedFiles = [
        'app/Support/LegacyMail.php' => 'The dedicated boundary around the legacy mail class.',
    ];

    private array $legacyBoundaryAllowedDirectories = [
        'app/Support' => 'Dedicated wrappers around legacy classes.',
    ];

    private array $legacyBoundaryAllowedFiles = [
        'bootstrap/legacy_loader.php' => 'The explicit legacy runtime bootstrap boundary.',
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

        $registeredPhpRouteErrors = $this->registeredPhpRouteInventoryErrors($legacyFiles, $routeUris);

        if ($registeredPhpRouteErrors->isNotEmpty()) {
            $this->error('Registered PHP compatibility routes are stale or undocumented:');
            $registeredPhpRouteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $nonLegacyPhpRouteErrors = $this->allowedNonLegacyPhpRouteInventoryErrors($routeUris);

        if ($nonLegacyPhpRouteErrors->isNotEmpty()) {
            $this->error('Documented non-legacy PHP compatibility routes are stale or incomplete:');
            $nonLegacyPhpRouteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $intentionalInventoryErrors = $this->intentionallyUnroutedInventoryErrors($legacyFiles);

        if ($intentionalInventoryErrors->isNotEmpty()) {
            $this->error('Intentionally unrouted legacy file inventory is stale or incomplete:');
            $intentionalInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $missing = $this->legacyFilesWithoutRouteCoverage($legacyFiles, $routeUris);
        $unexpectedPublicPhp = $this->unexpectedPublicPhpEntrypoints();

        if ($missing->isNotEmpty()) {
            $this->error('Legacy PHP files without explicit route coverage or an intentional unrouted reason:');
            $missing->each(fn ($file) => $this->line(" - {$file}"));

            return self::FAILURE;
        }

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

        $legacyBootstrapErrors = $this->legacyBootstrapHardeningErrors();

        if ($legacyBootstrapErrors->isNotEmpty()) {
            $this->error('Legacy bootstrap hardening is incomplete:');
            $legacyBootstrapErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $modernScriptReferenceErrors = $this->modernRetiredScriptReferenceErrors();

        if ($modernScriptReferenceErrors->isNotEmpty()) {
            $this->error('Modern app code still references retired legacy script endpoints:');
            $modernScriptReferenceErrors->each(fn ($error) => $this->line(" - {$error}"));

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

        $legacyPermissionsDependencyErrors = $this->legacyPermissionsDependencyErrors();

        if ($legacyPermissionsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy permissions class directly:');
            $legacyPermissionsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickGeoDependencyErrors = $this->legacyClickGeoDependencyErrors();

        if ($legacyClickGeoDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy ClickGeo class directly:');
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
            $this->error('Modern Laravel code still imports the legacy click searcher class directly:');
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
            $this->error('Modern Laravel code still imports the legacy pending conversion class directly:');
            $legacyPendingConversionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPostBackUrlEventHandlerDependencyErrors = $this->legacyPostBackUrlEventHandlerDependencyErrors();

        if ($legacyPostBackUrlEventHandlerDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy postback URL event handler directly:');
            $legacyPostBackUrlEventHandlerDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickRegistrationEventDependencyErrors = $this->legacyClickRegistrationEventDependencyErrors();

        if ($legacyClickRegistrationEventDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy click registration event directly:');
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

        $legacyReportHtmlDependencyErrors = $this->legacyReportHtmlDependencyErrors();

        if ($legacyReportHtmlDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy report HTML formatter directly:');
            $legacyReportHtmlDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyReportIdOfferDependencyErrors = $this->legacyReportIdOfferDependencyErrors();

        if ($legacyReportIdOfferDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy report ID offer class directly:');
            $legacyReportIdOfferDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

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

        $legacyBoundaryDependencyErrors = $this->legacyBoundaryDependencyErrors();

        if ($legacyBoundaryDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still references legacy classes outside App\Support boundaries:');
            $legacyBoundaryDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $csrfExceptionErrors = $this->legacyPostCsrfExceptionErrors();

        if ($csrfExceptionErrors->isNotEmpty()) {
            $this->error('Legacy POST compatibility CSRF exceptions are missing or stale:');
            $csrfExceptionErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $this->info("Audited {$legacyFiles->count()} legacy PHP files.");
        $this->info(($legacyFiles->count() - count($this->intentionallyUnrouted)) . ' files have explicit Laravel route coverage.');
        $this->info(count($this->intentionallyUnrouted) . ' files are intentionally unrouted support or retired script files.');
        $publicEntrypointCount = count($this->allowedPublicPhp);
        $publicEntrypointSummary = $publicEntrypointCount === 1
            ? '1 public PHP entrypoint is an expected front controller or compatibility redirect.'
            : "{$publicEntrypointCount} public PHP entrypoints are expected front controllers or compatibility redirects.";
        $this->info($publicEntrypointSummary);
        $this->info('Public webserver rewrites route direct PHP file requests through Laravel.');
        $this->info('Front controller has no dynamic legacy file fallback.');
        $this->info('Legacy bootstrap is idempotent and guards native session startup.');
        $this->info('Intentionally unrouted legacy files are not registered as Laravel routes.');
        $this->info('Allowed public PHP entrypoints exist and have documented reasons.');
        $this->info('Modern views and assets do not reference retired legacy script endpoints.');
        $this->info('Legacy POST compatibility routes have CSRF exceptions.');
        $this->info('Registered PHP compatibility routes map to legacy files or documented public exceptions.');
        $this->info('Documented non-legacy PHP compatibility route exceptions remain registered.');
        $this->info('Runtime code does not reference the retired legacy company session loader.');
        $this->info('Runtime code reads current user/session state through CurrentUserSession.');
        $this->info('Modern Laravel code reads native PHP superglobals through NativeSession, NativeRequest, or request boundaries.');
        $this->info('Modern Laravel code reads legacy permission metadata through LegacyPermissions.');
        $this->info('Modern Laravel code resolves legacy ClickGeo through LegacyClickGeo.');
        $this->info('Modern Laravel code resolves legacy click writes through LegacyClick.');
        $this->info('Modern Laravel code resolves legacy click vars through LegacyClickVars.');
        $this->info('Modern Laravel code resolves legacy click search queries through LegacyClickSearcher.');
        $this->info('Modern Laravel code resolves legacy conversion helpers through LegacyConversion.');
        $this->info('Modern Laravel code resolves legacy pending conversion activation through LegacyPendingConversion.');
        $this->info('Modern Laravel code handles postback URL events through LegacyPostBackURLEventHandler.');
        $this->info('Modern Laravel code registers offer clicks through LegacyClickRegistrationEvent.');
        $this->info('Modern Laravel code encodes legacy click IDs through LegacyUid.');
        $this->info('Modern Laravel code normalizes tracking query parameters through LegacyTrackingParameters.');
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
        $this->info('Modern Laravel code resolves legacy date helpers through LegacyDate.');
        $this->info('Modern Laravel code resolves legacy pagination helpers through LegacyPaginate.');
        $this->info('Modern Laravel code resolves legacy assignment helpers through LegacyAssignments.');
        $this->info('Modern Laravel code rebuilds user trees through LegacyTree.');
        $this->info('Modern Laravel code resolves legacy users through LegacyUser.');
        $this->info('Modern login flows use LegacyLogin for legacy login constants.');
        $this->info('Modern signup flows use LegacyAffiliateSignUp.');
        $this->info('Modern Laravel code resolves legacy user-domain helpers through App\Support boundaries.');
        $this->info('Modern offer postback URL flows use App\Support boundaries.');
        $this->info('Modern layout assets append admin-login scripts through LegacyAdminLogin.');
        $this->info('Modern layouts render legacy notifications through LegacyNotify.');
        $this->info('Modern database update screens run through LegacyCompanyUpdater.');
        $this->info('Modern report views render through LegacyReportHtml.');
        $this->info('Modern click offer reports build through LegacyReportIdOffer.');
        $this->info('Modern report controllers coordinate reports through LegacyReporter.');
        $this->info('Modern report controllers format reports through legacy report filter wrappers.');
        $this->info('Modern report controllers build affiliate and blacklist reports through legacy report object wrappers.');
        $this->info('Modern report controllers resolve legacy database connections through LegacyDatabaseConnection.');
        $this->info('Modern offer report controllers resolve legacy offer repositories through App\Support boundaries.');
        $this->info('Modern employee report controllers and commands resolve legacy employee repositories through App\Support boundaries.');
        $this->info('Modern report controllers resolve remaining legacy report repositories through App\Support boundaries.');
        $this->info('Modern Laravel code sends legacy mail through LegacyMail.');
        $this->info('Modern Laravel source keeps direct legacy class references inside audited App\Support or bootstrap boundaries.');

        return self::SUCCESS;
    }

    private function legacyPhpFiles()
    {
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

    private function legacyFilesWithoutRouteCoverage($legacyFiles, array $routeUris)
    {
        return $legacyFiles
            ->reject(fn ($file) => in_array($file, $routeUris, true))
            ->reject(fn ($file) => array_key_exists($file, $this->intentionallyUnrouted))
            ->values();
    }

    private function intentionallyUnroutedInventoryErrors($legacyFiles)
    {
        $legacyFileLookup = $legacyFiles->flip();

        return collect($this->intentionallyUnrouted)
            ->flatMap(function ($reason, $file) use ($legacyFileLookup) {
                $errors = [];

                if (!$legacyFileLookup->has($file)) {
                    $errors[] = "{$file}: listed as intentionally unrouted but legacy/{$file} does not exist.";
                }

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

    private function registeredPhpRouteInventoryErrors($legacyFiles, array $routeUris)
    {
        $legacyFileLookup = $legacyFiles->flip();

        return collect($routeUris)
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->reject(fn ($uri) => $legacyFileLookup->has($uri))
            ->reject(fn ($uri) => array_key_exists($uri, $this->allowedNonLegacyPhpRoutes))
            ->map(fn ($uri) => "{$uri}: registered PHP route has no matching legacy file or documented public exception.")
            ->values();
    }

    private function allowedNonLegacyPhpRouteInventoryErrors(array $routeUris)
    {
        return collect($this->allowedNonLegacyPhpRoutes)
            ->flatMap(function ($reason, $uri) use ($routeUris) {
                $errors = [];

                if (!in_array($uri, $routeUris, true)) {
                    $errors[] = "{$uri}: documented non-legacy PHP route exception is not registered.";
                }

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "{$uri}: documented non-legacy PHP route exception reason is blank.";
                }

                return $errors;
            })
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

    private function legacyBootstrapHardeningErrors()
    {
        $errors = collect();
        $legacyBootstrap = base_path('bootstrap/legacy_loader.php');

        if (!File::exists($legacyBootstrap)) {
            return $errors->push('bootstrap/legacy_loader.php is missing.');
        }

        $contents = File::get($legacyBootstrap);

        $this->appendMissingPatternErrors($errors, $contents, $this->legacyBootstrapRequiredPatterns);
        $this->appendForbiddenPatternErrors($errors, $contents, $this->legacyBootstrapForbiddenPatterns);

        return $errors;
    }

    private function modernRetiredScriptReferenceErrors()
    {
        $errors = collect();
        $directories = [
            'resources/views',
            'resources/assets',
            'public/js',
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

    private function retiredCompanySessionDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->retiredCompanySessionDependencyErrorsFor($sourceFiles);
    }

    private function retiredCompanySessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacySessionDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'config',
            'database',
            'public',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->nativeSessionDependencyErrorsFor($sourceFiles);
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

    private function legacyMailDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'database',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyMailDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyBoundaryDependencyErrorsFor($sourceFiles);
    }

    private function legacyBoundaryDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(function (string $contents, string $relativePath) {
                if (array_key_exists($relativePath, $this->legacyBoundaryAllowedFiles)) {
                    return true;
                }

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

    private function legacyPermissionsDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPermissionsDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickGeoDependencyErrorsFor($sourceFiles);
    }

    private function legacyClickGeoDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickGeoAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickVarsDependencyErrorsFor($sourceFiles);
    }

    private function legacyClickVarsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickVarsAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickSearcherDependencyErrorsFor($sourceFiles);
    }

    private function legacyClickSearcherDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickSearcherAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyConversionDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPendingConversionDependencyErrorsFor($sourceFiles);
    }

    private function legacyPendingConversionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPendingConversionAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPostBackUrlEventHandlerDependencyErrorsFor($sourceFiles);
    }

    private function legacyPostBackUrlEventHandlerDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPostBackUrlEventHandlerAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickRegistrationEventDependencyErrorsFor($sourceFiles);
    }

    private function legacyClickRegistrationEventDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickRegistrationEventAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyUidDependencyErrorsFor($sourceFiles);
    }

    private function legacyUidDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyUidAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyTrackingParametersDependencyErrorsFor($sourceFiles);
    }

    private function legacyTrackingParametersDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyTrackingParametersAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyLanderDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyNavBarDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyIpBlackListDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyImagesUploaderDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyNotificationsDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPayoutsDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyOfferDomainDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyOfferSupportDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyOfferRulesDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyAdjustmentsLogDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacySaleLogDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyDateDependencyErrorsFor($sourceFiles);
    }

    private function legacyDateDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyDateAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPaginateDependencyErrorsFor($sourceFiles);
    }

    private function legacyPaginateDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPaginateAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyAssignmentsDependencyErrorsFor($sourceFiles);
    }

    private function legacyAssignmentsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyAssignmentsAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyTreeDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyUserDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyLoginDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyAffiliateSignUpDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyUserDomainDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyOfferPostBackUrlDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyAdminLoginDependencyErrorsFor($sourceFiles);
    }

    private function legacyAdminLoginDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyAdminLoginAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyNotifyDependencyErrorsFor($sourceFiles);
    }

    private function legacyNotifyDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyNotifyAllowedFiles))
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyCompanyUpdaterDependencyErrorsFor($sourceFiles);
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

    private function legacyReportHtmlDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyReportHtmlDependencyErrorsFor($sourceFiles);
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

    private function legacyReportIdOfferDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyReportIdOfferDependencyErrorsFor($sourceFiles);
    }

    private function legacyReportIdOfferDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyReportIdOfferAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyReportIdOfferForbiddenPatterns as $pattern => $message) {
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyReporterDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyReportFiltersDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyReportObjectsDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyDatabaseConnectionDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyOfferReportRepositoriesDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyEmployeeReportRepositoriesDependencyErrorsFor($sourceFiles);
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
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyMiscReportRepositoriesDependencyErrorsFor($sourceFiles);
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
            ->map(fn ($uri) => "{$uri}: POST compatibility route is missing from VerifyCsrfToken exceptions.")
            ->values();

        $staleExceptions = $normalizedCsrfExceptions
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->reject(fn ($uri) => $legacyPostRoutes->contains($uri))
            ->map(fn ($uri) => "{$uri}: VerifyCsrfToken exception does not match a registered POST compatibility route.")
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
