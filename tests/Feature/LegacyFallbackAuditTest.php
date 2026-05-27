<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LegacyFallbackAuditTest extends TestCase
{
    public function test_legacy_fallback_audit_passes(): void
    {
        $this->assertSame(Command::SUCCESS, Artisan::call('legacy:audit-fallback-coverage'));
        $this->assertStringContainsString(
            'Modern views and assets do not reference retired legacy script endpoints.',
            Artisan::output()
        );
    }
}
