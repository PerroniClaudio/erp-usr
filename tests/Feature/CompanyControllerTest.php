<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Company;
use App\Models\User;

class CompanyControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_index_view() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $this->actingAs($user);
        $response = $this->get('/admin/personnel/companies');
        $response->assertStatus(200);
    }

    public function test_store_company() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $this->actingAs($user);
        $response = $this->post('/admin/personnel/companies', [
            'name' => 'Nuova Azienda',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'name' => 'Nuova Azienda',
        ]);
    }

    public function test_update_company() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $company = Company::factory()->createOne();
        $this->actingAs($user);
        $response = $this->put('/admin/personnel/companies/' . $company->id, [
            'name' => 'Azienda Modificata',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Azienda Modificata',
        ]);
    }

    public function test_destroy_company() {
    /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->createOne();
        $company = Company::factory()->createOne();
        $this->actingAs($user);
        $response = $this->delete('/admin/personnel/companies/' . $company->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);
    }
}
