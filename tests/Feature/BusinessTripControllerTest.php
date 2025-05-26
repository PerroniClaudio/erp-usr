<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;

class BusinessTripControllerTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        Role::firstOrCreate(['name' => 'standard']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_user_can_create_and_view_business_trip_with_relations() {
        // Step 1: Crea un utente e assegnagli il ruolo corretto (es. 'standard')
        // Step 2: Esegui il login con actingAs
        // Step 3: Esegui una POST per creare una nuova BusinessTrip
        // Step 4: Verifica che la trasferta sia stata creata nel database
        // Step 5: Crea una spesa e un trasferimento associati alla trasferta
        // Step 6: Recupera la pagina di edit e verifica che le relazioni siano visibili nella response
    }

    public function test_user_can_update_business_trip() {
        // Step 1: Crea un utente e una BusinessTrip associata
        // Step 2: Esegui il login con actingAs
        // Step 3: Esegui una PUT per aggiornare la trasferta (es. cambia status o date)
        // Step 4: Verifica che i dati siano aggiornati nel database
    }

    public function test_user_can_delete_business_trip() {
        // Step 1: Crea un utente e una BusinessTrip associata
        // Step 2: Esegui il login con actingAs
        // Step 3: Esegui una DELETE sulla trasferta
        // Step 4: Verifica che la trasferta sia "eliminata" (soft delete o status=2)
    }

    public function test_user_can_add_and_remove_expense_and_transfer() {
        // Step 1: Crea un utente e una BusinessTrip associata
        // Step 2: Esegui il login con actingAs
        // Step 3: Esegui una POST per aggiungere una spesa (BusinessTripExpense) alla trasferta
        // Step 4: Verifica che la spesa sia presente nel database
        // Step 5: Esegui una DELETE per rimuovere la spesa e verifica che sia assente
        // Step 6: Ripeti la stessa logica per i trasferimenti (BusinessTripTransfer)
    }
}
