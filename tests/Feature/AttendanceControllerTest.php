<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Company;
use App\Models\AttendanceType;
use Spatie\Permission\Models\Role;

class AttendanceControllerTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        Role::firstOrCreate(['name' => 'standard']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_index_view() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $user->assignRole('standard');
        $this->actingAs($user);
        $response = $this->get('/standard/attendances');
        $response->assertStatus(200);
    }

    public function test_store_attendance() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $user->assignRole('standard');
        $company = Company::factory()->createOne();
        $type = AttendanceType::factory()->createOne();
        $this->actingAs($user);
        $response = $this->post('/standard/attendances', [
            'date' => now()->toDateString(),
            'time_in' => '09:00',
            'time_out' => '13:00',
            'company_id' => $company->id,
            'attendance_type_id' => $type->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_update_attendance() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $user->assignRole('standard');
        $company = Company::factory()->createOne();
        $type = AttendanceType::factory()->createOne();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'attendance_type_id' => $type->id,
        ]);
        $this->actingAs($user);
        $response = $this->put('/standard/attendances/' . $attendance->id, [
            'date' => now()->toDateString(),
            'time_in' => '10:00',
            'time_out' => '14:00',
            'company_id' => $company->id,
            'attendance_type_id' => $type->id,
        ]);
        $response->assertRedirect();
            $this->assertDatabaseHas('attendances', [
                'id' => $attendance->id,
                'time_in' => '10:00',
                'hours' => 4.0,
            ]);
    }

    public function test_destroy_attendance() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $user->assignRole('standard');
        $company = Company::factory()->createOne();
        $type = AttendanceType::factory()->createOne();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'attendance_type_id' => $type->id,
        ]);
        $this->actingAs($user);
        $response = $this->delete('/standard/attendances/' . $attendance->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }
}
