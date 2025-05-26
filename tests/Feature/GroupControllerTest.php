<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Group;
use App\Models\User;

class GroupControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_index_view() {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/admin/personnel/groups');
        $response->assertStatus(200);
    }

    public function test_store_group() {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post('/admin/personnel/groups', [
            'name' => 'Nuovo Gruppo',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'Nuovo Gruppo',
        ]);
    }

    public function test_update_group() {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->actingAs($user);
        $response = $this->put('/admin/personnel/groups/' . $group->id, [
            'name' => 'Gruppo Modificato',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Gruppo Modificato',
        ]);
    }

    public function test_destroy_group() {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->actingAs($user);
        $response = $this->delete('/admin/personnel/groups/' . $group->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }
}
