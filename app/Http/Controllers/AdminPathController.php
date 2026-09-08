<?php

namespace App\Http\Controllers;

use App\Services\WhitelistFileService;
use App\Models\AuditLog;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminPathController extends Controller
{
    public function update(Request $request, WhitelistFileService $files): RedirectResponse
    {
        $validated = $request->validate(['output_directory' => ['required', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
            $path = trim((string) $value);
            if (str_contains(str_replace('\\', '/', $path), '../')) {
                $fail('The folder path cannot move above its selected directory.');
            }
            if (! preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]{2}|\\/)/', $path)) {
                $fail('Enter the full server path, for example C:\\laragon\\www\\EDL-lists.');
            }
        }]]);
        $directory = trim($validated['output_directory']);
        AppSetting::updateOrCreate(['key' => 'whitelist_output_directory'], ['value' => $directory]);
        $files->sync();
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'settings.output_directory.updated', 'description' => "Updated the shared whitelist output folder to {$directory}", 'ip_address' => $request->ip()]);

        return to_route('dashboard')->with('status', "Shared whitelist files are now saved to {$directory}.");
    }
}
