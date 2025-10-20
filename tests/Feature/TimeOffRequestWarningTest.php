<?php

use Illuminate\Support\Facades\View;
use App\Models\User;

it('shows a warning when date_from is within 5 days', function () {
    // Calcola una data entro 5 giorni
    $date = now()->addDays(3)->format('Y-m-d');

    // Autentica un utente perché il layout si aspetta auth()->user()
    $user = User::factory()->create();
    $this->actingAs($user);

    // Renderizza la view passando la variabile $date_from
    $view = View::file(resource_path('views/standard/time_off_requests/create.blade.php'), ['date_from' => $date]);

    $rendered = $view->render();

    // Controllo che l'alert esista e non abbia la classe hidden
    expect(str_contains($rendered, 'id="date-from-warning"'))->toBeTrue();
    expect(str_contains($rendered, 'id="date-from-warning" class="alert alert-warning mt-2 hidden"'))->toBeFalse();
});
