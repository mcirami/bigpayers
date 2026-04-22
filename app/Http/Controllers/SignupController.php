<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\User\AffiliateSignUp;
use LeadMax\TrackYourStats\User\User;

class SignupController extends Controller
{
    public function show(Request $request)
    {
        $user = new User();
        $company = Company::loadFromSession();
        $company->reloadSettings();

        if (!$company->allowsRegister()) {
            return redirect('/login');
        }

        if ($user->is_loggedin() && $user->verify_login_session()) {
            return redirect('dashboard');
        }

        return $this->signupView($company, [
            'mid' => (string) $request->query('mid', ''),
        ]);
    }

    public function submit(Request $request)
    {
        $company = Company::loadFromSession();
        $company->reloadSettings();

        if (!$company->allowsRegister()) {
            return redirect('/login');
        }

        $_POST = array_merge($_POST, $request->all());

        $signup = new AffiliateSignUp();
        $result = trim((string) $signup->getResult());

        if ($result === AffiliateSignUp::SUCCESS) {
            if ((string) $request->input('mid', '') !== '') {
                return redirect('/signup-success?mid=true');
            }

            return redirect('/signup-success');
        }

        return $this->signupView($company, [
            'mid' => (string) $request->input('mid', ''),
            'errorCode' => $result,
            'formValues' => $request->only([
                'tys_first_name',
                'tys_last_name',
                'tys_email',
                'tys_username',
                'tys_company_name',
                'tys_telegram',
            ]),
        ], 422);
    }

    public function success(Request $request)
    {
        $company = Company::loadFromSession();
        $company->reloadSettings();

        return view('auth.signup-success', [
            'company' => $company,
            'webroot' => getWebRoot(),
            'mid' => (bool) $request->query('mid'),
            'pending' => (bool) $request->query('pending'),
            'messengerType' => $company->getMessengerType(),
            'messengerUsername' => $company->getMessengerUsername(),
            'themeCssUrl' => $this->themeCssUrl($company),
        ]);
    }

    private function signupView(Company $company, array $data = [], int $status = 200)
    {
        return response()->view('auth.signup', array_merge([
            'company' => $company,
            'webroot' => getWebRoot(),
            'themeCssUrl' => $this->themeCssUrl($company),
            'errorCode' => null,
            'formValues' => [],
            'mid' => '',
        ], $data), $status);
    }

    private function themeCssUrl(Company $company): ?string
    {
        $savedTheme = trim((string) ($company->login_theme ?? ''));
        $theme = $savedTheme !== '' && File::exists(public_path("login_themes/{$savedTheme}/theme.css"))
            ? $savedTheme
            : (File::exists(public_path('login_themes/command-center/theme.css')) ? 'command-center' : null);

        if (!$theme) {
            return null;
        }

        $themeCssPath = public_path("login_themes/{$theme}/theme.css");

        return "/login_themes/{$theme}/theme.css?v=" . filemtime($themeCssPath);
    }
}
