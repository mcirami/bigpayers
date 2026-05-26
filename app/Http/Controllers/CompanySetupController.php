<?php

namespace App\Http\Controllers;

use App\Services\CompanyProvisioningService;
use Illuminate\Http\Request;
use Throwable;

class CompanySetupController extends Controller
{
    public function create()
    {
        return view('admin.company-setup', [
            'pageTitle' => 'Company Setup',
            'result' => null,
        ]);
    }

    public function store(Request $request, CompanyProvisioningService $provisioning)
    {
        $validated = $request->validate([
            'shortHand' => 'required|string|max:255',
            'subDomain' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_]+$/'],
            'companyName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'skype' => 'nullable|string|max:255',
            'messenger_type' => 'nullable|string|max:255',
            'messenger_username' => 'nullable|string|max:255',
            'login_url' => 'nullable|string|max:255',
            'landing_page' => 'nullable|string|max:255',
            'login_theme' => 'nullable|string|max:255',
            'allow_register' => 'nullable|boolean',
            'adminEmail' => 'required|email|max:255',
            'userName' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_.@-]+$/'],
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|same:password',
        ]);

        try {
            $result = $provisioning->provision($validated);
        } catch (Throwable $exception) {
            return back()
                ->withErrors(['setup' => $exception->getMessage()])
                ->withInput($request->except(['password', 'confirmPassword']));
        }

        return view('admin.company-setup', [
            'pageTitle' => 'Company Setup',
            'result' => $result,
        ]);
    }
}
