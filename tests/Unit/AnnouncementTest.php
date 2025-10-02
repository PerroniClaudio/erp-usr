<?php

use App\Models\Announcement;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'standard']);
});

it('can create an announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
    ]);

    expect($announcement)->toBeInstanceOf(Announcement::class)
        ->and($announcement->creator)->toBeInstanceOf(User::class)
        ->and($announcement->creator->id)->toBe($admin->id);
});

it('can scope active announcements', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Announcement::factory()->create(['is_active' => true, 'created_by' => $admin->id]);
    Announcement::factory()->create(['is_active' => false, 'created_by' => $admin->id]);

    $activeAnnouncements = Announcement::active()->get();

    expect($activeAnnouncements)->toHaveCount(1)
        ->and($activeAnnouncements->first()->is_active)->toBeTrue();
});

it('can check if user viewed announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('standard');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
    ]);

    expect($announcement->isViewedBy($user))->toBeFalse();

    $announcement->viewedByUsers()->attach($user->id);

    expect($announcement->isViewedBy($user))->toBeTrue();
});

it('can get unread announcements for user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('standard');

    $announcement1 = Announcement::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    $announcement2 = Announcement::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    // Mark one as read
    $announcement1->viewedByUsers()->attach($user->id);

    $unreadAnnouncements = $user->unreadAnnouncements()->get();

    expect($unreadAnnouncements)->toHaveCount(1)
        ->and($unreadAnnouncements->first()->id)->toBe($announcement2->id);
});
