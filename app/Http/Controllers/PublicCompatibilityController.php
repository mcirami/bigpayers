<?php

namespace App\Http\Controllers;

class PublicCompatibilityController extends Controller
{
    public function redirectCompanyCss()
    {
        return redirect('/css/company.css');
    }

    public function redirectLoginTheme()
    {
        return redirect('/login');
    }
}
