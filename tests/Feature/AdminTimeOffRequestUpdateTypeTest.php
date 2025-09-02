<?php

use App\Models\Company;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'standard']);
});

it('can update time off request type for admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $company = Company::factory()->create();

    $ferieType = TimeOffType::factory()->create(['id' => 1, 'name' => 'Ferie']);
    $rolType = TimeOffType::factory()->create(['id' => 2, 'name' => 'Rol']);

    $batchId = uniqid();

    // Create time off requests with Ferie type
    $requests = TimeOffRequest::factory()->count(2)->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'time_off_type_id' => $ferieType->id,
        'batch_id' => $batchId,
        'status' => 0, // Pending
    ]);

    $this->actingAs($admin);

    $response = $this->put("/admin/time-off-requests/{$batchId}/update-type", [
        'time_off_type_id' => $rolType->id,
    ]);

    $response->assertRedirect();

    // Verify all requests with the same batch_id were updated
    $updatedRequests = TimeOffRequest::where('batch_id', $batchId)->get();

    expect($updatedRequests)->toHaveCount(2);

    foreach ($updatedRequests as $request) {
        expect($request->time_off_type_id)->toBe($rolType->id);
    }
});

it('only allows Ferie and Rol types for update', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $company = Company::factory()->create();

    $ferieType = TimeOffType::factory()->create(['id' => 1, 'name' => 'Ferie']);
    $invalidType = TimeOffType::factory()->create(['id' => 3, 'name' => 'Invalid']);

    $batchId = uniqid();

    $request = TimeOffRequest::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'time_off_type_id' => $ferieType->id,
        'batch_id' => $batchId,
        'status' => 0,
    ]);

    $this->actingAs($admin);

    $response = $this->put("/admin/time-off-requests/{$batchId}/update-type", [
        'time_off_type_id' => $invalidType->id,
    ]);

    $response->assertSessionHasErrors('time_off_type_id');
});

it('displays requester name and editable type in admin view', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create(['name' => 'Mario Rossi']);
    $company = Company::factory()->create();
    $ferieType = TimeOffType::factory()->create(['id' => 1, 'name' => 'Ferie']);
    $rolType = TimeOffType::factory()->create(['id' => 2, 'name' => 'Rol']);

    $batchId = uniqid();

    $request = TimeOffRequest::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'time_off_type_id' => $ferieType->id,
        'batch_id' => $batchId,
        'status' => 0,
    ]);

    $this->actingAs($admin);

    $response = $this->get("/admin/time-off-requests/{$batchId}");

    $response->assertOk()
        ->assertSee('Mario Rossi') // Requester name
        ->assertSee('Ferie') // Current type
        ->assertSee('Rol') // Available type option
        ->assertSee('Aggiorna Tipo'); // Update button
});
