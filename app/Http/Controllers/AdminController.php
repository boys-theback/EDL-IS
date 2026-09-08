<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function users(): View
    {
        return view('admin.users', ['users' => User::query()->latest()->paginate(15)->withQueryString(), 'outputDirectory' => AppSetting::where('key', 'whitelist_output_directory')->value('value') ?: 'default']);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:user,super_admin'],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        $this->audit($request, 'user.created', 'Created a new '.$validated['role'].' account for '.$validated['email']);

        return to_route('admin.users')->with('status', 'User account created.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:user,super_admin'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        if ($user->is($request->user()) && $validated['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'You cannot remove super-admin access from your own account.'])->withInput();
        }
        if (blank($validated['password'] ?? null)) unset($validated['password']); else $validated['password'] = Hash::make($validated['password']);
        $user->update($validated);
        $this->audit($request, 'user.updated', 'Updated account '.$user->email, $user);

        return to_route('admin.users')->with('status', 'User account updated.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'You cannot delete your own account.');
        abort_if($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1, 422, 'The last super-admin account cannot be deleted.');
        $email = $user->email;
        $user->delete();
        $this->audit($request, 'user.deleted', 'Deleted account '.$email);

        return to_route('admin.users')->with('status', 'User account deleted.');
    }

    public function auditLogs(): View
    {
        return view('admin.audit-logs', ['logs' => AuditLog::with('user')->latest()->paginate(25)->withQueryString()]);
    }

    private function audit(Request $request, string $action, string $description, ?User $subject = null): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'subject_type' => $subject ? User::class : null, 'subject_id' => $subject?->id, 'description' => $description, 'ip_address' => $request->ip()]);
    }
}
