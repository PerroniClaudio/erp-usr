<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Group;

class UsersControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_read_user() {
        $user = User::factory()->create();

        $response = $this->get('/admin/personnel/users/' . $user->id);

        $response->assertStatus(200);
        // La route 'users.edit' restituisce una view, non JSON
        $response->assertSee($user->name);
    }

    public function test_update_user() {
        $user = User::factory()->create();

        $response = $this->put(route('users.update', $user), [
            'title' => 'Sig.',
            'cfp' => 'RSSMRA80A01H501U',
            'birth_date' => '1980-01-01',
            'category' => 'Impiegato',
            'weekly_hours' => 40,
        ]);

        $response->assertRedirect(route('users.edit', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'title' => 'Sig.']);
    }

    public function test_associate_vehicle_to_user() {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->post(route('users.store-vehicles', $user), [
            'vehicle_id' => $vehicle->id,
            'vehicle_type' => 1,
            'ownership_type' => 1,
            'purchase_type' => 1,
            'contract_start_date' => now()->toDateString(),
            'contract_end_date' => now()->addYear()->toDateString(),
            'mileage' => 10000,
            'mileage_update_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('users.edit', $user));
        $this->assertDatabaseHas('user_vehicle', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_destroy_user_vehicle() {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user->vehicles()->attach($vehicle->id);

        $response = $this->delete(route('users.vehicles.destroy', [$user, $vehicle]));

        $response->assertRedirect(route('users.edit', $user));
        $this->assertDatabaseMissing('user_vehicle', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_destroy_user_group() {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group->id);

        $response = $this->delete(route('users.group.destroy', [$user, $group]));

        $response->assertRedirect(route('users.edit', $user));
        $this->assertDatabaseMissing('group_user', [
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
    }
}
