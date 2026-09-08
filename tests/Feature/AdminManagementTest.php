<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_users_and_audit_events(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Whitelist Operator',
                'email' => 'operator@example.com',
                'password' => 'password123',
                'role' => 'user',
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['email' => 'operator@example.com', 'role' => 'user']);
        $this->assertTrue(AuditLog::where('action', 'user.created')->exists());
    }

    public function test_regular_users_cannot_open_admin_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }
}
