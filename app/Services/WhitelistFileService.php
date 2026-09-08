<?php

namespace App\Services;

use App\Models\WhitelistEntry;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class WhitelistFileService
{
    private const FILES = [
        'YT' => 'YT-Allow-list.txt',
        'FB' => 'SNS-Allow-list.txt',
        'GENERAL' => 'ICT-GENERALS-list.txt',
    ];

    public function files(): array
    {
        return array_values(self::FILES);
    }

    public function webDirectoryName(): string
    {
        return basename(rtrim($this->resolveDirectory(), "\\/"));
    }

    public function pathForWebFile(string $directory, string $filename): string
    {
        abort_unless($directory === $this->webDirectoryName() && in_array($filename, self::FILES, true), 404);

        $path = $this->resolveDirectory() . DIRECTORY_SEPARATOR . $filename;
        File::ensureDirectoryExists($this->resolveDirectory());
        if (! File::exists($path)) {
            File::put($path, '');
        }

        return $path;
    }

    public function sync(): void
    {
        $directory = $this->resolveDirectory();
        File::ensureDirectoryExists($directory);
        if (! File::isDirectory($directory) || ! is_writable($directory)) {
            throw new \RuntimeException("The whitelist output directory is not writable: {$directory}");
        }
        $entries = WhitelistEntry::query()->orderBy('ip_address')->get();

        foreach (self::FILES as $application => $filename) {
            $ips = $entries
                ->filter(fn (WhitelistEntry $entry) => $entry->application === $application || ($application !== 'GENERAL' && $entry->application === 'BOTH'))
                ->pluck('ip_address')
                ->unique()
                ->values()
                ->implode(PHP_EOL);

            File::put($directory . DIRECTORY_SEPARATOR . $filename, $ips === '' ? '' : $ips . PHP_EOL);
        }
    }
    
    public function resolveDirectory(): string
    {
        $configuredDirectory = trim((string) AppSetting::where('key', 'whitelist_output_directory')->value('value'));
        $isAbsolute = $configuredDirectory && (preg_match('/^[A-Za-z]:[\\\\\/]/', $configuredDirectory) || str_starts_with($configuredDirectory, '/') || str_starts_with($configuredDirectory, '\\\\'));

        return $configuredDirectory && ! $isAbsolute
            ? storage_path('app/whitelists/' . trim($configuredDirectory, "\\/"))
            : ($configuredDirectory ?: storage_path('app/whitelists'));
    }
}
