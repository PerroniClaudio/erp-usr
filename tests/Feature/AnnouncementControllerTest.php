<?php

use App\Models\Announcement;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'standard']);
});

it('admin can view announcements index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('admin.announcements.index'));

    $response->assertOk()
        ->assertViewIs('admin.announcements.index');
});

it('standard user cannot access admin announcements', function () {
    $user = User::factory()->create();
    $user->assignRole('standard');

    $response = $this->actingAs($user)->get(route('admin.announcements.index'));

    $response->assertStatus(403);
});

it('admin can create announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
        'title' => 'Test Announcement',
        'content' => 'This is a test announcement content.',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.announcements.index'));

    $this->assertDatabaseHas('announcements', [
        'title' => 'Test Announcement',
        'content' => 'This is a test announcement content.',
        'is_active' => true,
        'created_by' => $admin->id,
    ]);
});

it('requires title and content when creating announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
        'title' => '',
        'content' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'content']);
});

it('admin can update announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.announcements.update', $announcement), [
        'title' => 'Updated Title',
        'content' => 'Updated content.',
        'is_active' => false,
    ]);

    $response->assertRedirect(route('admin.announcements.index'));

    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'title' => 'Updated Title',
        'content' => 'Updated content.',
        'is_active' => false,
    ]);
});

it('admin can delete announcement', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.announcements.destroy', $announcement));

    $response->assertRedirect(route('admin.announcements.index'));

    $this->assertDatabaseMissing('announcements', [
        'id' => $announcement->id,
    ]);
});

it('user can get unread announcements via api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('standard');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('announcements.unread'));

    $response->assertOk()
        ->assertJson([
            [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
            ],
        ]);
});

it('user can mark announcement as read', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('standard');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('announcements.mark-as-read', $announcement));

    $response->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('announcement_user', [
        'announcement_id' => $announcement->id,
        'user_id' => $user->id,
    ]);
});

it('cannot mark announcement as read twice', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('standard');

    $announcement = Announcement::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    // Mark as read first time
    $announcement->viewedByUsers()->attach($user->id);

    // Try to mark as read again
    $response = $this->actingAs($user)->post(route('announcements.mark-as-read', $announcement));

    $response->assertOk()
        ->assertJson(['success' => true]);

    // Should still have only one entry
    $this->assertDatabaseCount('announcement_user', 1);
});
