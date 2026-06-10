<?php

namespace App\Http\Controllers;

use App\Company;
use App\Support\LegacyAffiliateSignUp as AffiliateSignUp;
use App\Support\LegacyUser as User;
use App\Support\NativeRequest;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function show(Request $request)
    {
        $user = new User();
        $company = $this->currentCompany();

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
        $company = $this->currentCompany();

        if (!$company->allowsRegister()) {
            return redirect('/login');
        }

        NativeRequest::mergePost($request->all());

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
        $company = $this->currentCompany();

        return view('auth.signup-success', [
            'company' => $company,
            'webroot' => getWebRoot(),
            'mid' => (bool) $request->query('mid'),
            'pending' => (bool) $request->query('pending'),
            'messengerType' => $company->getMessengerType(),
            'messengerUsername' => $company->getMessengerUsername(),
            'themeCssUrl' => $company->themeCssUrl(),
        ]);
    }

    private function currentCompany(): Company
    {
        $company = Company::instance()->first();

        abort_unless($company, 404, 'Company install not found.');

        return $company;
    }

    private function signupView(Company $company, array $data = [], int $status = 200)
    {
        return response()->view('auth.signup', array_merge([
            'company' => $company,
            'webroot' => getWebRoot(),
            'themeCssUrl' => $company->themeCssUrl(),
            'errorCode' => null,
            'formValues' => [],
            'mid' => '',
        ], $data), $status);
    }
}
