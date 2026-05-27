<?php

namespace Tests\Feature;

use App\Http\Controllers\LegacyCompatibilityController;
use App\Http\Controllers\PublicCompatibilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicCompatibilityRoutesTest extends TestCase
{
    public function test_public_php_compatibility_routes_are_registered(): void
    {
        $this->assertSame(
            LegacyCompatibilityController::class . '@redirectLoginPhp',
            Route::getRoutes()->match(Request::create('/login.php'))->getActionName()
        );
        $this->assertSame(
            LegacyCompatibilityController::class . '@redirectLogoutPhp',
            Route::getRoutes()->match(Request::create('/logout.php'))->getActionName()
        );
        $this->assertSame(
            PublicCompatibilityController::class . '@redirectCompanyCss',
            Route::getRoutes()->match(Request::create('/css/company.php'))->getActionName()
        );
        $this->assertSame(
            PublicCompatibilityController::class . '@redirectLoginTheme',
            Route::getRoutes()->match(Request::create('/login_themes/default/index.php'))->getActionName()
        );
    }

    public function test_public_php_compatibility_controllers_redirect_to_laravel_routes(): void
    {
        $legacyController = app(LegacyCompatibilityController::class);
        $publicController = app(PublicCompatibilityController::class);

        $this->assertStringEndsWith('/login', $legacyController->redirectLoginPhp()->getTargetUrl());
        $this->assertStringEndsWith('/logout', $legacyController->redirectLogoutPhp()->getTargetUrl());
        $this->assertStringEndsWith('/css/company.css', $publicController->redirectCompanyCss()->getTargetUrl());
        $this->assertStringEndsWith('/login', $publicController->redirectLoginTheme()->getTargetUrl());
    }
}
