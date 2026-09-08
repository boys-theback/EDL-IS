<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WhitelistWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_an_entry_and_sync_allow_lists(): void
    {
        $directory = storage_path('framework/testing/allow-lists');
        AppSetting::updateOrCreate(['key' => 'whitelist_output_directory'], ['value' => $directory]);
        $user = User::factory()->create(['email' => 'operator@example.com']);

        $this->withoutMiddleware()
            ->actingAs($user)
            ->post('/whitelist', [
                'name' => 'Training room',
                'ip_address' => '192.168.10.25',
                'application' => 'BOTH',
            ])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('whitelist_entries', ['ip_address' => '192.168.10.25', 'application' => 'BOTH']);
        $this->assertStringContainsString('192.168.10.25', File::get($directory . '/YT-Allow-list.txt'));
        $this->assertStringContainsString('192.168.10.25', File::get($directory . '/SNS-Allow-list.txt'));
        $this->assertSame('', File::get($directory . '/ICT-GENERALS-list.txt'));
    }
}
