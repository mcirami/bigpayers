<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Mail;
use LeadMax\TrackYourStats\System\Session as LegacySession;
use LeadMax\TrackYourStats\User\User;

class LegacyCompatibilityController extends Controller
{
    public function redirectLoginPhp()
    {
        return redirect('/login');
    }

    public function redirectLogoutPhp()
    {
        return redirect('/logout');
    }

    public function redirectHomePhp()
    {
        return redirect('/dashboard');
    }

    public function redirectAdminLogin(Request $request)
    {
        $userId = (int) $request->query('affid');
        abort_if($userId <= 0, 404);

        return redirect("/login/{$userId}");
    }

    public function forgotPassword(Request $request)
    {
        $user = new User();
        $company = Company::loadFromSession();
        $company->reloadSettings();

        if ($user->is_loggedin() && $user->verify_login_session()) {
            return redirect('/dashboard');
        }

        $status = null;
        $statusType = 'info';
        $token = null;
        $tokenUserName = null;

        if ($request->isMethod('post')) {
            if ($request->filled('token')) {
                $payload = $request->validate([
                    'token' => 'required|string',
                    'password' => 'required|string|min:5|max:255|same:confirmpassword',
                    'confirmpassword' => 'required|string|min:5|max:255',
                ]);

                $reset = DB::table('password_resets')
                    ->where('verify', '=', $payload['token'])
                    ->where('active', '=', 1)
                    ->first();

                if (!$reset || ((int) date('U') - (int) $reset->time_stamp) >= 86400) {
                    $status = 'Token has expired, please request a new reset.';
                    $statusType = 'error';
                    $token = $payload['token'];
                } else {
                    DB::table('rep')
                        ->where('idrep', '=', $reset->repid)
                        ->update(['password' => password_hash($payload['password'], PASSWORD_DEFAULT)]);

                    DB::table('password_resets')
                        ->where('verify', '=', $payload['token'])
                        ->update(['active' => 0]);

                    $status = "Password successfully reset for {$reset->user_name}.";
                    $statusType = 'success';
                }
            } else {
                $payload = $request->validate([
                    'email' => 'required|email',
                ]);

                $recipient = DB::table('rep')
                    ->select(['first_name', 'email', 'idrep', 'user_name'])
                    ->where('email', '=', $payload['email'])
                    ->first();

                if ($recipient) {
                    $hash = hash('sha512', Str::random(80));
                    $timestamp = (string) date('U');

                    DB::table('password_resets')->insert([
                        'repid' => $recipient->idrep,
                        'user_name' => $recipient->user_name,
                        'email' => $recipient->email,
                        'verify' => $hash,
                        'time_stamp' => $timestamp,
                        'ip' => $request->ip(),
                        'active' => 1,
                    ]);

                    $resetUrl = url('/forgot-password?token=' . $hash);
                    $message = "<html><body>
                        <p>Greetings {$recipient->first_name},</p>
                        <p>A password reset has been requested today ({$timestamp}) from {$request->ip()}.</p>
                        <p>You can reset your password with this link:
                            <a href=\"{$resetUrl}\">Here</a>
                        </p>
                        <p>If you did not request this, ignore this email and it will expire in one day.</p>
                        <p>Thank you,<br/>Devs @ TrackYourStats.</p>
                    </body></html>";

                    (new Mail($recipient->email, 'Password Reset - TrackYourStats', $message))->send();
                }

                $status = 'If that email is associated with a user, password reset instructions have been sent.';
                $statusType = 'success';
            }
        }

        if ($request->filled('token')) {
            $reset = DB::table('password_resets')
                ->where('verify', '=', $request->query('token'))
                ->where('active', '=', 1)
                ->first();

            if ($reset && ((int) date('U') - (int) $reset->time_stamp) < 86400) {
                $token = $request->query('token');
                $tokenUserName = $reset->user_name;
            } elseif ($status === null) {
                $status = 'This password reset token is invalid or has expired.';
                $statusType = 'error';
            }
        }

        return view('auth.forgot-password', [
            'company' => $company,
            'webroot' => getWebRoot(),
            'loginTheme' => $loginTheme = $this->resolveLoginTheme($company),
            'themeCssUrl' => $this->themeCssUrl($loginTheme),
            'token' => $token,
            'tokenUserName' => $tokenUserName,
            'status' => $status,
            'statusType' => $statusType,
        ]);
    }

    public function redirectAffUpdate(Request $request)
    {
        $userId = (int) $request->query('idrep');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/{$userId}/edit", $request, ['idrep']));
    }

    public function redirectActivateAffiliate(Request $request)
    {
        $userId = (int) $request->query('id');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/pending/{$userId}/activate", $request, ['id']));
    }

    public function redirectOfferUpdate(Request $request)
    {
        $offerId = (int) $request->query('idoffer');
        abort_if($offerId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/edit/{$offerId}", $request, ['idoffer']));
    }

    public function redirectOfferEditRules(Request $request)
    {
        $offerId = (int) $request->query('offid');
        abort_if($offerId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/rules/{$offerId}", $request, ['offid']));
    }

    public function redirectCreateNoneUniqueRule(Request $request)
    {
        $offerId = (int) $request->query('id');
        abort_if($offerId <= 0, 404);

        return redirect(
            $this->buildRedirectUrl("/offer/rules/{$offerId}/none-unique/create", $request, ['id']),
            $request->isMethod('post') ? 307 : 302
        );
    }

    public function redirectEditNoneUniqueRule(Request $request)
    {
        $ruleId = (int) $request->query('id');
        abort_if($ruleId <= 0, 404);

        return redirect(
            $this->buildRedirectUrl("/offer/rules/none-unique/{$ruleId}/edit", $request, ['id']),
            $request->isMethod('post') ? 307 : 302
        );
    }

    public function redirectOfferDetails(Request $request)
    {
        $offerId = (int) $request->query('idoffer');
        abort_if($offerId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/view/{$offerId}", $request, ['idoffer']));
    }

    public function redirectOfferPostback(Request $request)
    {
        $offerId = (int) $request->query('offid');
        abort_if($offerId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/{$offerId}/postback", $request, ['offid']));
    }

    public function redirectOfferAccess(Request $request)
    {
        $offerId = (int) $request->query('id');
        abort_if($offerId <= 0, 404);

        return redirect(
            $this->buildRedirectUrl("/offer/{$offerId}/access", $request, ['id']),
            $request->isMethod('post') ? 307 : 302
        );
    }

    public function redirectClickSearch(Request $request)
    {
        return redirect($this->buildRedirectUrl('/click-search', $request));
    }

    public function redirectIpBlacklist(Request $request)
    {
        return redirect($this->buildRedirectUrl('/ip-blacklist', $request));
    }

    public function redirectGlobalPostback(Request $request)
    {
        return redirect($this->buildRedirectUrl('/global-postback', $request));
    }

    public function redirectOfferAdd(Request $request)
    {
        return redirect($this->buildRedirectUrl('/offer/create', $request));
    }

    public function redirectOfferUrls(Request $request)
    {
        return redirect($this->buildRedirectUrl('/offer/urls', $request));
    }

    public function redirectAddOfferUrl(Request $request)
    {
        return redirect($this->buildRedirectUrl('/offer/urls/create', $request));
    }

    public function redirectEditOfferUrl(Request $request)
    {
        $urlId = (int) $request->query('id');
        abort_if($urlId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/urls/{$urlId}/edit", $request, ['id']));
    }

    public function redirectSettings(Request $request)
    {
        return redirect($this->buildRedirectUrl('/settings', $request));
    }

    public function redirectViewPendingAffiliates(Request $request)
    {
        return redirect($this->buildRedirectUrl('/user/pending', $request));
    }

    public function redirectBannedUsers(Request $request)
    {
        return redirect($this->buildRedirectUrl('/user/banned', $request));
    }

    public function redirectBanUser(Request $request)
    {
        $userId = (int) $request->query('uid');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/{$userId}/ban", $request, ['uid']));
    }

    public function redirectBanUserEdit(Request $request)
    {
        $userId = (int) $request->query('uid');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/{$userId}/ban/edit", $request, ['uid']));
    }

    public function redirectAffEditRef(Request $request)
    {
        $userId = (int) $request->query('affid');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/{$userId}/referrals", $request, ['affid']));
    }

    public function redirectAddReferral(Request $request)
    {
        $userId = (int) $request->query('id');
        abort_if($userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/user/{$userId}/referrals/create", $request, ['id']));
    }

    public function redirectAddSale(Request $request)
    {
        return redirect($this->buildRedirectUrl('/sales/add', $request));
    }

    public function redirectApproveOfferRequest(Request $request)
    {
        $offerId = (int) $request->query('id');
        $userId = (int) $request->query('u');
        abort_if($offerId <= 0 || $userId <= 0, 404);

        return redirect($this->buildRedirectUrl("/offer/{$offerId}/approve-request/{$userId}", $request, ['id', 'u']));
    }

    public function redirectSaleLogView(Request $request)
    {
        $saleLogId = (int) $request->query('id');
        abort_if($saleLogId <= 0, 404);

        return redirect(
            $this->buildRedirectUrl("/chat-log/view/{$saleLogId}", $request, ['id']),
            $request->isMethod('post') ? 307 : 302
        );
    }

    public function redirectCreateNotification(Request $request)
    {
        return redirect($this->buildRedirectUrl('/notifications/create', $request));
    }

    public function redirectCampaignManage(Request $request)
    {
        return redirect($this->buildRedirectUrl('/advertisers', $request));
    }

    public function redirectCampaignCreate(Request $request)
    {
        return redirect($this->buildRedirectUrl('/advertisers/create', $request));
    }

    public function redirectCampaignEdit(Request $request)
    {
        $campaignId = (int) $request->query('id');
        abort_if($campaignId <= 0, 404);

        return redirect($this->buildRedirectUrl("/advertisers/{$campaignId}/edit", $request, ['id']));
    }

    public function notificationsCompatibility(Request $request)
    {
        $notificationId = (int) $request->query('id');
        $action = $request->query('action');

        if ($notificationId > 0 && $action === 'mark') {
            DB::table('user_has_notification')
                ->where('notification_id', '=', $notificationId)
                ->where('user_id', '=', LegacySession::userID())
                ->update(['seen' => 1]);

            return redirect("/notifications/{$notificationId}")->with('message', 'Notification marked as read.');
        }

        if ($notificationId > 0 && $action === 'delete') {
            DB::table('user_has_notification')
                ->where('notification_id', '=', $notificationId)
                ->where('user_id', '=', LegacySession::userID())
                ->update(['deleted' => 1]);

            return redirect('/notifications')->with('message', 'Notification deleted.');
        }

        return redirect('/notifications');
    }

    private function buildRedirectUrl(string $basePath, Request $request, array $ignoredKeys = []): string
    {
        $query = $request->except($ignoredKeys);

        return empty($query) ? $basePath : $basePath . '?' . http_build_query($query);
    }

    private function resolveLoginTheme(Company $company): ?string
    {
        $savedTheme = trim((string) ($company->login_theme ?? ''));

        if ($savedTheme !== '' && File::exists(public_path("login_themes/{$savedTheme}/theme.css"))) {
            return $savedTheme;
        }

        if (File::exists(public_path('login_themes/command-center/theme.css'))) {
            return 'command-center';
        }

        return null;
    }

    private function themeCssUrl(?string $loginTheme): ?string
    {
        if (!$loginTheme) {
            return null;
        }

        $themeCssPath = public_path("login_themes/{$loginTheme}/theme.css");

        return File::exists($themeCssPath)
            ? "/login_themes/{$loginTheme}/theme.css?v=" . filemtime($themeCssPath)
            : null;
    }
}
