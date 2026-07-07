<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\LegacyMail as Mail;
use App\Support\LegacyUser as User;

class LegacyCompatibilityController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $user = new User();
        $company = $this->currentCompany();

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
            'loginTheme' => $company->loginTheme(),
            'themeCssUrl' => $company->themeCssUrl(),
            'token' => $token,
            'tokenUserName' => $tokenUserName,
            'status' => $status,
            'statusType' => $statusType,
        ]);
    }

    private function currentCompany(): Company
    {
        $company = Company::instance()->first();

        abort_unless($company, 404, 'Company install not found.');

        return $company;
    }

    public function retiredGeoIpUpdater()
    {
        return response(
            'The legacy GeoIP web updater is retired. Update GeoIP data through the controlled server provisioning workflow.',
            410
        );
    }
}
