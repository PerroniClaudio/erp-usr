<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\TimeOffType;

class TimeOffRequestControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_index_view() {
        $user = User::factory()->create();
            /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */

        $this->actingAs($user);
        $response = $this->get('/standard/time-off-requests');
        $response->assertStatus(200);
    }

    public function test_store_time_off_request() {
        $user = User::factory()->create();
        $type = TimeOffType::factory()->create();
        $company = Company::factory()->create();
            /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */

        $this->actingAs($user);
        $response = $this->post('/standard/time-off-requests', [
            'requests' => json_encode([
                [
                    'date_from' => now()->toDateString() . ' 09:00:00',
                    'date_to' => now()->toDateString() . ' 13:00:00',
                    'time_off_type_id' => $type->id,
                ]
            ]),
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('time_off_requests', [
            'user_id' => $user->id,
            'time_off_type_id' => $type->id,
        ]);
    }

    public function test_update_time_off_request() {
        $user = User::factory()->create();
        $type = TimeOffType::factory()->create();
        $company = Company::factory()->create();
        $request = TimeOffRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'time_off_type_id' => $type->id,
        ]);
            /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */

        $this->actingAs($user);
        $response = $this->put('/standard/time-off-requests/' . $request->id, [
            'date_from' => now()->toDateString() . ' 10:00:00',
            'date_to' => now()->toDateString() . ' 14:00:00',
            'time_off_type_id' => $type->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('time_off_requests', [
            'id' => $request->id,
            'date_from' => now()->toDateString() . ' 10:00:00',
        ]);
    }

    public function test_destroy_time_off_request() {
        $user = User::factory()->create();
        $type = TimeOffType::factory()->create();
        $company = Company::factory()->create();
        $request = TimeOffRequest::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'time_off_type_id' => $type->id,
        ]);
            /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */

        $this->actingAs($user);
        $response = $this->delete('/standard/time-off-requests/' . $request->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('time_off_requests', [
            'id' => $request->id,
        ]);
    }
}
