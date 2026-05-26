<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/',
        'email/incoming',
        'email/incoming/distribute',
        'login',
        'upload_logo.php',
        'upload_favicon.php',
        'add_new_ip_blacklist.php',
        'edit_blacklisted_ip.php',
        'mass_assign_pb.php',
        'scripts/sale_log.php',
    ];
}
