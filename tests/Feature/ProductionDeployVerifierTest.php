<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/scripts/verify-production-deploy.php';

class ProductionDeployVerifierTest extends TestCase
{
    public function test_current_checkout_is_a_consistent_deployment(): void
    {
        $errors = (new \ProductionDeployVerifier())->verify(dirname(__DIR__, 2));

        $this->assertSame([], $errors);
    }

    public function test_it_reports_old_front_controller_and_composer_metadata(): void
    {
        $root = sys_get_temp_dir().'/bigpayers-deploy-verifier-'.bin2hex(random_bytes(6));

        try {
            $this->writeFixture($root);

            $errors = (new \ProductionDeployVerifier())->verify($root);

            $this->assertContains(
                'public/index.php contains retired fallback code: ../legacy',
                $errors
            );
            $this->assertContains(
                'public/index.php contains retired fallback code: legacy/index.php',
                $errors
            );
            $this->assertContains(
                'Generated Composer metadata references retired src code: vendor/composer/autoload_files.php',
                $errors
            );
        } finally {
            $this->removeDirectory($root);
        }
    }

    private function writeFixture(string $root): void
    {
        foreach ([
            'public',
            'app/Support',
            'vendor/composer',
        ] as $directory) {
            mkdir($root.'/'.$directory, 0777, true);
        }

        file_put_contents($root.'/public/index.php', "<?php\n\$file = '../legacy/index.php';\n");
        file_put_contents($root.'/composer.lock', '{}');
        file_put_contents($root.'/app/Support/GlobalFunctions.php', '<?php');
        file_put_contents($root.'/app/Support/GlobalLogging.php', '<?php');
        file_put_contents($root.'/vendor/autoload.php', '<?php');
        file_put_contents(
            $root.'/vendor/composer/autoload_files.php',
            "<?php\nreturn ['old' => \$baseDir . '/src/System/Functions.php'];\n"
        );
        file_put_contents($root.'/composer.json', json_encode([
            'autoload' => [
                'psr-4' => ['App\\' => 'app/'],
                'files' => [
                    'app/Support/GlobalFunctions.php',
                    'app/Support/GlobalLogging.php',
                ],
            ],
        ], JSON_PRETTY_PRINT));
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory), ['.', '..']) as $item) {
            $path = $directory.'/'.$item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}
