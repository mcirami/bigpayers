<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class BaseInstallSql
{
    public function path(): string
    {
        foreach ($this->candidatePaths() as $path) {
            if (File::isFile($path)) {
                return $path;
            }
        }

        throw new RuntimeException('Unable to find base_install.sql.');
    }

    public function contents(?string $path = null): string
    {
        return File::get($path ?: $this->path());
    }

    private function candidatePaths(): array
    {
        $paths = [];
        $configuredPath = env('TYS_BASE_INSTALL');

        if ($configuredPath) {
            $paths[] = $this->absolutePath((string) $configuredPath);
        }

        $paths[] = base_path('base_install.sql');
        $paths[] = storage_path('base_install.sql');

        return array_values(array_unique($paths));
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return storage_path($path);
    }
}
