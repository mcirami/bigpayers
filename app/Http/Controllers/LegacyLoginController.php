<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\User\Login;
use LeadMax\TrackYourStats\User\User;

class LegacyLoginController extends Controller
{

	public function showLoginForm(Request $request)
	{
		$user = new User();
		$company = $this->currentCompany();

		if ($user->is_loggedin() && $user->verify_login_session()) {
			return redirect('dashboard');
		}

		$user->checkLoginAttempts();

		return $this->loginView($user, $company);
	}

	public function login(Request $request)
	{
		$user = new User();
		$company = $this->currentCompany();

		$user->checkLoginAttempts();

		$error = null;
		if ($user->count < 5) {
			$username = $request->input('txt_uname_email');
			$password = $request->input('txt_password');

			$result = $user->login($username, $username, $password);

			if ($result == Login::RESULT_SUCCESS) {
				if ($request->has('redirectUri')) {
					return redirect(urldecode($request->get('redirectUri')));
				}

				return redirect('dashboard');
			} elseif ($result == Login::RESULT_PENDING) {
				return redirect('/signup-success?pending=1');
			} else {
				$user->badLoginAttempt();

				if ($result == Login::RESULT_INVALID_CRED) {
					$error = "Wrong Details ! <p>You have {$user->count} / 5 login attempts remaining. </p>";
				} elseif ($result == Login::RESULT_BANNED) {
					$error = "This account has been banned. Login attempt has been logged and an administrator will be notified. ";
				}
			}
		} else {
			if ($request->has('button')) {
				$error = "You have {$user->count} / 5 login attempts remaining. Please wait until tomorrow or contact an admin to reset your login attempt and/or password.";
			}
		}

		return $this->loginView($user, $company, $error);
	}

	private function loginView(User $user, Company $company, $error = null)
	{
		$loginTheme = $company->loginTheme();

		return view('auth.login', [
			'webroot' => getWebRoot(),
			'user' => $user,
			'error' => $error,
			'company' => $company,
			'loginTheme' => $loginTheme,
			'themeCssUrl' => $company->themeCssUrl(),
		]);
	}

	private function currentCompany(): Company
	{
		$company = Company::instance()->first();

		abort_unless($company, 404, 'Company install not found.');

		return $company;
	}
    public function logout()
    {
        if (isset($_GET["adminLogin"])) {
            unset($_SESSION["adminLogin"]);

            return '<script type="text/javascript">window.close();</script>';
        }


        $user_logout = new \LeadMax\TrackYourStats\User\User();

        $user_logout->logout();

        return redirect('/login');
    }


    public function adminLogin($userId)
    {
        $user = new User();
        if ($user->hasRep($userId)) {
            $user->adminLogin($userId);

            return redirect('/dashboard?adminLogin');
        } else {
            return redirect('/dashboard');
        }
    }
}
