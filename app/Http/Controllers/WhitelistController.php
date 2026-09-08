<?php

namespace App\Http\Controllers;

use App\Models\WhitelistEntry;
use App\Models\AuditLog;
use App\Models\AppSetting;
use App\Services\WhitelistFileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class WhitelistController extends Controller
{
    public function __construct(private readonly WhitelistFileService $files) {}

    public function index(Request $request): View
    {
        $entries = WhitelistEntry::query()
            ->forApplication($request->string('application')->toString())
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('ip_address', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard', [
            'entries' => $entries,
            'total' => WhitelistEntry::count(),
            'ytCount' => WhitelistEntry::whereIn('application', ['YT', 'BOTH'])->count(),
            'snsCount' => WhitelistEntry::whereIn('application', ['FB', 'BOTH'])->count(),
            'generalCount' => WhitelistEntry::where('application', 'GENERAL')->count(),
            'outputDirectory' => AppSetting::where('key', 'whitelist_output_directory')->value('value') ?: '',
            'resolvedOutputDirectory' => $this->files->resolveDirectory(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = WhitelistEntry::create($this->validated($request) + ['created_by' => $request->user()->id, 'added_ip_address' => $request->ip()]);
        $this->files->sync();
        $this->audit($request, 'whitelist.created', "Added {$entry->name} / {$entry->ip_address}", $entry);

        return to_route('dashboard')->with('status', "{$entry->ip_address} was added to the whitelist.");
    }

    public function edit(WhitelistEntry $entry): View
    {
        return view('entries.edit', compact('entry'));
    }

    public function update(Request $request, WhitelistEntry $entry): RedirectResponse
    {
        $entry->update($this->validated($request));
        $this->files->sync();
        $this->audit($request, 'whitelist.updated', "Updated {$entry->name} / {$entry->ip_address}", $entry);

        return to_route('dashboard')->with('status', "{$entry->ip_address} was updated.");
    }

    public function destroy(Request $request, WhitelistEntry $entry): RedirectResponse
    {
        $ipAddress = $entry->ip_address;
        $entry->delete();
        $this->files->sync();
        $this->audit($request, 'whitelist.deleted', "Removed {$ipAddress} from the whitelist", $entry);

        return to_route('dashboard')->with('status', "{$ipAddress} was removed.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate(['entry_ids' => ['required', 'array'], 'entry_ids.*' => ['integer', 'exists:whitelist_entries,id']]);
        $deleted = WhitelistEntry::whereIn('id', $validated['entry_ids'])->delete();
        $this->files->sync();
        $this->audit($request, 'whitelist.bulk_deleted', "Removed {$deleted} whitelist entries");

        return to_route('dashboard')->with('status', "{$deleted} entr" . ($deleted === 1 ? 'y was' : 'ies were') . ' removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['bail', 'required', 'string', 'min:2', 'max:120'],
            'ip_address' => ['required', 'ip', Rule::unique('whitelist_entries', 'ip_address')->ignore($request->route('entry'))],
            'application' => ['required', Rule::in(['YT', 'FB', 'BOTH', 'GENERAL'])],
        ], ['ip_address.unique' => 'This IP address is already on the whitelist.']);
    }

    private function audit(Request $request, string $action, string $description, ?WhitelistEntry $entry = null): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'subject_type' => $entry ? WhitelistEntry::class : null, 'subject_id' => $entry?->id, 'description' => $description, 'ip_address' => $request->ip()]);
    }
}
