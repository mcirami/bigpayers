<?php

namespace Tests\Feature;

use App\Http\Controllers\LegacyCompatibilityController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AuthenticatedCompatibilityRoutesTest extends TestCase
{
    public function test_authenticated_legacy_routes_are_registered_to_compatibility_controller(): void
    {
        $routes = [
            ['/home.php', 'GET', 'redirectHomePhp'],
            ['/alogin.php', 'GET', 'redirectAdminLogin'],
            ['/aff_add.php', 'GET', 'redirectAffAdd'],
            ['/aff_details.php', 'GET', 'redirectAffDetails'],
            ['/aff_update.php', 'GET', 'redirectAffUpdate'],
            ['/clicksearch.php', 'GET', 'redirectClickSearch'],
            ['/ip_black_list.php', 'GET', 'redirectIpBlacklist'],
            ['/add_new_ip_blacklist.php', 'GET', 'redirectAddIpBlacklist'],
            ['/edit_blacklisted_ip.php', 'GET', 'redirectEditIpBlacklist'],
            ['/global_postback.php', 'GET', 'redirectGlobalPostback'],
            ['/offer_add.php', 'GET', 'redirectOfferAdd'],
            ['/offer_update.php', 'GET', 'redirectOfferUpdate'],
            ['/offer_edit_rules.php', 'GET', 'redirectOfferEditRules'],
            ['/create_none_unique.php', 'POST', 'redirectCreateNoneUniqueRule'],
            ['/edit_none_unique.php', 'POST', 'redirectEditNoneUniqueRule'],
            ['/offer_details.php', 'GET', 'redirectOfferDetails'],
            ['/offer_edit_pb.php', 'GET', 'redirectOfferPostback'],
            ['/offer_access.php', 'POST', 'redirectOfferAccess'],
            ['/offer_urls.php', 'GET', 'redirectOfferUrls'],
            ['/add_offer_url.php', 'GET', 'redirectAddOfferUrl'],
            ['/edit_offer_url.php', 'GET', 'redirectEditOfferUrl'],
            ['/view_pending_affiliates.php', 'GET', 'redirectViewPendingAffiliates'],
            ['/banned_users.php', 'GET', 'redirectBannedUsers'],
            ['/ban_user.php', 'GET', 'redirectBanUser'],
            ['/ban_user_edit.php', 'GET', 'redirectBanUserEdit'],
            ['/aff_add_ref.php', 'GET', 'redirectAffEditRef'],
            ['/aff_edit_ref.php', 'GET', 'redirectAffEditRef'],
            ['/add_referral.php', 'GET', 'redirectAddReferral'],
            ['/add_sale.php', 'GET', 'redirectAddSale'],
            ['/approve_offer_request.php', 'GET', 'redirectApproveOfferRequest'],
            ['/sale_log_view.php', 'POST', 'redirectSaleLogView'],
            ['/log_sale.php', 'POST', 'redirectLogSale'],
            ['/salaries.php', 'POST', 'redirectSalaries'],
            ['/edit_salaries.php', 'GET', 'redirectEditSalaries'],
            ['/edit_salary.php', 'POST', 'redirectEditSalary'],
            ['/bonus.php', 'GET', 'redirectBonuses'],
            ['/create_bonus.php', 'POST', 'redirectCreateBonus'],
            ['/bonus_edit.php', 'POST', 'redirectEditBonus'],
            ['/bonus_assign.php', 'POST', 'redirectAssignBonus'],
            ['/scripts/process_bonuses.php', 'GET', 'redirectProcessBonuses'],
            ['/scripts/update_geoip.php', 'GET', 'retiredGeoIpUpdater'],
            ['/create_notification.php', 'GET', 'redirectCreateNotification'],
            ['/campaign_manage.php', 'GET', 'redirectCampaignManage'],
            ['/campaign_create.php', 'GET', 'redirectCampaignCreate'],
            ['/campaign_edit.php', 'GET', 'redirectCampaignEdit'],
        ];

        foreach ($routes as [$uri, $method, $controllerMethod]) {
            $this->assertRouteAction($uri, $method, $controllerMethod);
        }
    }

    public function test_simple_legacy_redirects_preserve_extra_query_when_supported(): void
    {
        $controller = app(LegacyCompatibilityController::class);

        $this->assertRedirectPathAndQuery($controller->redirectHomePhp(), '/dashboard');
        $this->assertRedirectPathAndQuery(
            $controller->redirectAffAdd($this->getRequest('/aff_add.php?adminLogin=1')),
            '/user/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectClickSearch($this->getRequest('/clicksearch.php?term=abc')),
            '/click-search',
            ['term' => 'abc']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectIpBlacklist($this->getRequest('/ip_black_list.php?adminLogin=1')),
            '/ip-blacklist',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAddIpBlacklist($this->getRequest('/add_new_ip_blacklist.php?adminLogin=1')),
            '/ip-blacklist/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectGlobalPostback($this->getRequest('/global_postback.php?tab=conversion')),
            '/global-postback',
            ['tab' => 'conversion']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferAdd($this->getRequest('/offer_add.php?adminLogin=1')),
            '/offer/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferUrls($this->getRequest('/offer_urls.php?adminLogin=1')),
            '/offer/urls',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAddOfferUrl($this->getRequest('/add_offer_url.php?adminLogin=1')),
            '/offer/urls/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectSettings($this->getRequest('/settings.php?section=branding')),
            '/settings',
            ['section' => 'branding']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectViewPendingAffiliates($this->getRequest('/view_pending_affiliates.php?adminLogin=1')),
            '/user/pending',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectBannedUsers($this->getRequest('/banned_users.php?adminLogin=1')),
            '/user/banned',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAddSale($this->getRequest('/add_sale.php?adminLogin=1')),
            '/sales/add',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectSalaries($this->getRequest('/salaries.php?adminLogin=1')),
            '/salaries',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditSalaries($this->getRequest('/edit_salaries.php?adminLogin=1')),
            '/salaries/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectBonuses($this->getRequest('/bonus.php?adminLogin=1')),
            '/bonuses',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectCreateBonus($this->getRequest('/create_bonus.php?adminLogin=1')),
            '/bonuses/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery($controller->redirectProcessBonuses(), '/bonuses/process');
        $this->assertRedirectPathAndQuery(
            $controller->redirectCreateNotification($this->getRequest('/create_notification.php?adminLogin=1')),
            '/notifications/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectCampaignManage($this->getRequest('/campaign_manage.php?adminLogin=1')),
            '/advertisers',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectCampaignCreate($this->getRequest('/campaign_create.php?adminLogin=1')),
            '/advertisers/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->notificationsCompatibility($this->getRequest('/notifications.php')),
            '/notifications'
        );
    }

    public function test_id_based_legacy_redirects_translate_to_modern_paths_and_preserve_extra_query(): void
    {
        $controller = app(LegacyCompatibilityController::class);

        $this->assertRedirectPathAndQuery(
            $controller->redirectAffUpdate($this->getRequest('/aff_update.php?idrep=17&adminLogin=1')),
            '/user/17/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAdminLogin($this->getRequest('/alogin.php?affid=17')),
            '/login/17'
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAffDetails($this->getRequest('/aff_details.php?idrep=17&from=report')),
            '/user/17/edit',
            ['from' => 'report']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectActivateAffiliate($this->getRequest('/activate_affiliate.php?id=17&adminLogin=1')),
            '/user/pending/17/activate',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferUpdate($this->getRequest('/offer_update.php?idoffer=42&tab=details')),
            '/offer/edit/42',
            ['tab' => 'details']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferEditRules($this->getRequest('/offer_edit_rules.php?offid=42&section=device')),
            '/offer/rules/42',
            ['section' => 'device']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferDetails($this->getRequest('/offer_details.php?idoffer=42&adminLogin=1')),
            '/offer/view/42',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferPostback($this->getRequest('/offer_edit_pb.php?offid=42&type=free')),
            '/offer/42/postback',
            ['type' => 'free']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectOfferAccess($this->getRequest('/offer_access.php?id=42&adminLogin=1')),
            '/offer/42/access',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditNoneUniqueRule($this->getRequest('/edit_none_unique.php?id=66&adminLogin=1')),
            '/offer/rules/none-unique/66/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditIpBlacklist($this->getRequest('/edit_blacklisted_ip.php?id=88&adminLogin=1')),
            '/ip-blacklist/88/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditOfferUrl($this->getRequest('/edit_offer_url.php?id=55&adminLogin=1')),
            '/offer/urls/55/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectBanUser($this->getRequest('/ban_user.php?uid=17&adminLogin=1')),
            '/user/17/ban',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectBanUserEdit($this->getRequest('/ban_user_edit.php?uid=17&adminLogin=1')),
            '/user/17/ban/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAffEditRef($this->getRequest('/aff_edit_ref.php?affid=17&adminLogin=1')),
            '/user/17/referrals',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAddReferral($this->getRequest('/add_referral.php?id=17&adminLogin=1')),
            '/user/17/referrals/create',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectApproveOfferRequest($this->getRequest('/approve_offer_request.php?id=42&u=17&adminLogin=1')),
            '/offer/42/approve-request/17',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectSaleLogView($this->getRequest('/sale_log_view.php?id=99&adminLogin=1')),
            '/chat-log/view/99',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectLogSale($this->getRequest('/log_sale.php?pcid=123&adminLogin=1')),
            '/chat-log/add/123',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectLogSale($this->getRequest('/log_sale.php?cid=124&adminLogin=1')),
            '/chat-log/add/124',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectSaleLogEdit($this->getRequest('/edit_sale_log.php?sid=99&adminLogin=1')),
            '/chat-log/view/99',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditSalary($this->getRequest('/edit_salary.php?id=17&adminLogin=1')),
            '/user/17/salary/showUpdate',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectEditBonus($this->getRequest('/bonus_edit.php?id=5&adminLogin=1')),
            '/bonuses/5/edit',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectAssignBonus($this->getRequest('/bonus_assign.php?id=5&adminLogin=1')),
            '/bonuses/5/assign',
            ['adminLogin' => '1']
        );
        $this->assertRedirectPathAndQuery(
            $controller->redirectCampaignEdit($this->getRequest('/campaign_edit.php?id=8&adminLogin=1')),
            '/advertisers/8/edit',
            ['adminLogin' => '1']
        );
    }

    public function test_legacy_post_redirects_keep_method_with_temporary_redirects(): void
    {
        $controller = app(LegacyCompatibilityController::class);

        $createNoneUnique = $controller->redirectCreateNoneUniqueRule(
            $this->postRequest('/create_none_unique.php?id=42&source=rules')
        );
        $this->assertSame(307, $createNoneUnique->getStatusCode());
        $this->assertRedirectPathAndQuery($createNoneUnique, '/offer/rules/42/none-unique/create', ['source' => 'rules']);

        $offerAccess = $controller->redirectOfferAccess(
            $this->postRequest('/offer_access.php?id=42&source=access')
        );
        $this->assertSame(307, $offerAccess->getStatusCode());
        $this->assertRedirectPathAndQuery($offerAccess, '/offer/42/access', ['source' => 'access']);

        $saleLogView = $controller->redirectSaleLogView(
            $this->postRequest('/sale_log_view.php?id=99&source=upload')
        );
        $this->assertSame(307, $saleLogView->getStatusCode());
        $this->assertRedirectPathAndQuery($saleLogView, '/chat-log/view/99', ['source' => 'upload']);

        $salaries = $controller->redirectSalaries(
            $this->postRequest('/salaries.php?source=payroll')
        );
        $this->assertSame(307, $salaries->getStatusCode());
        $this->assertRedirectPathAndQuery($salaries, '/salaries', ['source' => 'payroll']);

        $editSalary = $controller->redirectEditSalary(
            $this->postRequest('/edit_salary.php', ['id' => 17])
        );
        $this->assertSame(307, $editSalary->getStatusCode());
        $this->assertRedirectPathAndQuery($editSalary, '/user/17/salary/update');

        $createBonus = $controller->redirectCreateBonus(
            $this->postRequest('/create_bonus.php?source=bonus')
        );
        $this->assertSame(307, $createBonus->getStatusCode());
        $this->assertRedirectPathAndQuery($createBonus, '/bonuses/create', ['source' => 'bonus']);

        $editBonus = $controller->redirectEditBonus(
            $this->postRequest('/bonus_edit.php?source=bonus', ['id' => 5])
        );
        $this->assertSame(307, $editBonus->getStatusCode());
        $this->assertRedirectPathAndQuery($editBonus, '/bonuses/5/edit', ['source' => 'bonus']);

        $assignBonus = $controller->redirectAssignBonus(
            $this->postRequest('/bonus_assign.php?source=bonus', ['id' => 5])
        );
        $this->assertSame(307, $assignBonus->getStatusCode());
        $this->assertRedirectPathAndQuery($assignBonus, '/bonuses/5/assign', ['source' => 'bonus']);

        $logSale = $controller->redirectLogSale(
            $this->postRequest('/log_sale.php', ['pendingConversionId' => 123])
        );
        $this->assertSame(307, $logSale->getStatusCode());
        $this->assertRedirectPathAndQuery($logSale, '/chat-log/upload', ['pendingConversionId' => '123']);
    }

    public function test_retired_geoip_script_returns_gone_response(): void
    {
        $response = app(LegacyCompatibilityController::class)->retiredGeoIpUpdater();

        $this->assertSame(410, $response->getStatusCode());
        $this->assertStringContainsString('GeoIP web updater is retired', $response->getContent());
    }

    public function test_missing_required_legacy_ids_return_not_found(): void
    {
        $controller = app(LegacyCompatibilityController::class);

        $this->assertNotFound(fn () => $controller->redirectAdminLogin($this->getRequest('/alogin.php')));
        $this->assertNotFound(fn () => $controller->redirectAffUpdate($this->getRequest('/aff_update.php')));
        $this->assertNotFound(fn () => $controller->redirectAffDetails($this->getRequest('/aff_details.php')));
        $this->assertNotFound(fn () => $controller->redirectActivateAffiliate($this->getRequest('/activate_affiliate.php')));
        $this->assertNotFound(fn () => $controller->redirectOfferUpdate($this->getRequest('/offer_update.php')));
        $this->assertNotFound(fn () => $controller->redirectOfferEditRules($this->getRequest('/offer_edit_rules.php')));
        $this->assertNotFound(fn () => $controller->redirectCreateNoneUniqueRule($this->getRequest('/create_none_unique.php')));
        $this->assertNotFound(fn () => $controller->redirectEditNoneUniqueRule($this->getRequest('/edit_none_unique.php')));
        $this->assertNotFound(fn () => $controller->redirectOfferDetails($this->getRequest('/offer_details.php')));
        $this->assertNotFound(fn () => $controller->redirectOfferPostback($this->getRequest('/offer_edit_pb.php')));
        $this->assertNotFound(fn () => $controller->redirectOfferAccess($this->getRequest('/offer_access.php')));
        $this->assertNotFound(fn () => $controller->redirectEditIpBlacklist($this->getRequest('/edit_blacklisted_ip.php')));
        $this->assertNotFound(fn () => $controller->redirectEditOfferUrl($this->getRequest('/edit_offer_url.php')));
        $this->assertNotFound(fn () => $controller->redirectBanUser($this->getRequest('/ban_user.php')));
        $this->assertNotFound(fn () => $controller->redirectBanUserEdit($this->getRequest('/ban_user_edit.php')));
        $this->assertNotFound(fn () => $controller->redirectAffEditRef($this->getRequest('/aff_edit_ref.php')));
        $this->assertNotFound(fn () => $controller->redirectAddReferral($this->getRequest('/add_referral.php')));
        $this->assertNotFound(fn () => $controller->redirectApproveOfferRequest($this->getRequest('/approve_offer_request.php?id=42')));
        $this->assertNotFound(fn () => $controller->redirectSaleLogView($this->getRequest('/sale_log_view.php')));
        $this->assertNotFound(fn () => $controller->redirectLogSale($this->getRequest('/log_sale.php')));
        $this->assertNotFound(fn () => $controller->redirectSaleLogEdit($this->getRequest('/edit_sale_log.php')));
        $this->assertNotFound(fn () => $controller->redirectEditSalary($this->getRequest('/edit_salary.php')));
        $this->assertNotFound(fn () => $controller->redirectEditBonus($this->getRequest('/bonus_edit.php')));
        $this->assertNotFound(fn () => $controller->redirectAssignBonus($this->getRequest('/bonus_assign.php')));
        $this->assertNotFound(fn () => $controller->redirectCampaignEdit($this->getRequest('/campaign_edit.php')));
    }

    private function assertRouteAction(string $uri, string $method, string $controllerMethod): void
    {
        $this->assertSame(
            LegacyCompatibilityController::class . '@' . $controllerMethod,
            Route::getRoutes()->match(Request::create($uri, $method))->getActionName()
        );
    }

    private function getRequest(string $uri): Request
    {
        return Request::create($uri);
    }

    private function postRequest(string $uri, array $payload = []): Request
    {
        return Request::create($uri, 'POST', $payload);
    }

    private function assertRedirectPathAndQuery(
        RedirectResponse $response,
        string $expectedPath,
        array $expectedQuery = []
    ): void {
        $target = parse_url($response->getTargetUrl());
        parse_str($target['query'] ?? '', $query);

        $this->assertStringEndsWith($expectedPath, $target['path'] ?? '');
        $this->assertSame($expectedQuery, $query);
    }

    private function assertNotFound(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected a not found exception.');
        } catch (NotFoundHttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }
}
