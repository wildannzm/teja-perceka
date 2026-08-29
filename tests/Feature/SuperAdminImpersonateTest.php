<?php

use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'kepala_unit']);
});

test('super admin can start impersonating another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $targetUser = User::factory()->create(['name' => 'Target User']);
    $targetUser->assignRole('kepala_unit');

    $response = $this->actingAs($admin)
        ->post(route('impersonate.start', $targetUser));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($targetUser);
    expect(session('impersonator_id'))->toBe($admin->id);

    // Verify ActivityLog was created
    expect(ActivityLog::where('activity_type', 'impersonate_start')->count())->toBe(1);
});

test('super admin can stop impersonating and return to admin account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $targetUser = User::factory()->create(['name' => 'Target User']);

    // Start impersonating
    $this->actingAs($admin)->post(route('impersonate.start', $targetUser));

    // Stop impersonating
    $response = $this->actingAs($targetUser)
        ->withSession(['impersonator_id' => $admin->id])
        ->post(route('impersonate.stop'));

    $response->assertRedirect(route('super-admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
    expect(session()->has('impersonator_id'))->toBeFalse();

    // Verify ActivityLog stop entry
    expect(ActivityLog::where('activity_type', 'impersonate_stop')->count())->toBe(1);
});

test('non super admin cannot impersonate users', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('kepala_unit');

    $targetUser = User::factory()->create();

    $response = $this->actingAs($regularUser)
        ->post(route('impersonate.start', $targetUser));

    $response->assertForbidden();
});
