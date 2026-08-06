<?php

final class ProductionDeployVerifier
{
    public function verify(string $root): array
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR);
        $errors = [];

        $requiredFiles = [
            'public/index.php',
            'composer.json',
            'composer.lock',
            'app/Support/GlobalFunctions.php',
            'app/Support/GlobalLogging.php',
            'vendor/autoload.php',
        ];

        foreach ($requiredFiles as $file) {
            if (! is_file($root.DIRECTORY_SEPARATOR.$file)) {
                $errors[] = "Missing required deployment file: {$file}";
            }
        }

        $index = $this->readFile($root, 'public/index.php', $errors);
        if ($index !== null) {
            foreach (['../legacy', 'legacy/index.php', 'is_file($file)', 'include($file)'] as $forbidden) {
                if (str_contains($index, $forbidden)) {
                    $errors[] = "public/index.php contains retired fallback code: {$forbidden}";
                }
            }
        }

        $composer = $this->readJson($root, 'composer.json', $errors);
        if ($composer !== null) {
            $autoloadFiles = $composer['autoload']['files'] ?? [];
            $expectedFiles = [
                'app/Support/GlobalFunctions.php',
                'app/Support/GlobalLogging.php',
            ];

            if ($autoloadFiles !== $expectedFiles) {
                $errors[] = 'composer.json autoload.files does not match the rebuilt application helpers.';
            }

            foreach (($composer['autoload']['psr-4'] ?? []) as $namespace => $path) {
                if (str_starts_with(trim((string) $path, '/'), 'src/')) {
                    $errors[] = "composer.json still autoloads retired src code for {$namespace}.";
                }
            }
        }

        $composerMetadata = glob($root.'/vendor/composer/autoload_*.php') ?: [];
        if ($composerMetadata === []) {
            $errors[] = 'Composer autoload metadata is missing; run composer install for this release.';
        }

        foreach ($composerMetadata as $file) {
            $contents = @file_get_contents($file);
            if ($contents === false) {
                $errors[] = 'Unable to read Composer metadata: '.substr($file, strlen($root) + 1);
                continue;
            }

            $referencesRetiredSource = preg_match(
                '~\\$baseDir\\s*\\.\\s*[\'\"]/src/|__DIR__[^\\r\\n;]*\\.\\s*[\'\"]/src/~',
                $contents
            );

            if ($referencesRetiredSource) {
                $errors[] = 'Generated Composer metadata references retired src code: '
                    .substr($file, strlen($root) + 1);
            }
        }

        return array_values(array_unique($errors));
    }

    private function readFile(string $root, string $relativePath, array &$errors): ?string
    {
        $path = $root.DIRECTORY_SEPARATOR.$relativePath;
        if (! is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            $errors[] = "Unable to read deployment file: {$relativePath}";

            return null;
        }

        return $contents;
    }

    private function readJson(string $root, string $relativePath, array &$errors): ?array
    {
        $contents = $this->readFile($root, $relativePath, $errors);
        if ($contents === null) {
            return null;
        }

        try {
            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $errors[] = "Invalid {$relativePath}: {$exception->getMessage()}";

            return null;
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__);
    $errors = (new ProductionDeployVerifier())->verify($root);

    if ($errors !== []) {
        fwrite(STDERR, "Production deployment verification failed:\n");
        foreach ($errors as $error) {
            fwrite(STDERR, " - {$error}\n");
        }
        fwrite(STDERR, "Upload the complete release and regenerate vendor with Composer.\n");
        exit(1);
    }

    fwrite(STDOUT, "Production deployment verification passed.\n");
}
