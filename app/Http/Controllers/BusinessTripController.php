<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusinessTripController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $user = $request->user();
        $businessTrips = BusinessTrip::where('user_id', $user->id)
            ->where('status', 0)
            ->with(['user'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('standard.business_trips.index', [
            'businessTrips' => $businessTrips,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $companies = Auth::user()->companies;

        return view('standard.business_trips.create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //

        $fields = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $code = date('Y') . 'TRA' . str_pad(BusinessTrip::count() + 1, 5, '0', STR_PAD_LEFT);

        $user = $request->user();

        $businessTrip = BusinessTrip::create([
            'user_id' => $user->id,
            'date_from' => $fields['date_from'],
            'date_to' => $fields['date_to'],
            'status' => $request->status,
            'company_id' => $request->company_id,
            'code' => $code,
            'expense_type' => 0,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferta creata con successo');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTrip $businessTrip) {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTrip $businessTrip) {
        //

        $companies = Auth::user()->companies;

        return view('standard.business_trips.edit', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
            'expenses' => BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
            'transfers' => BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTrip $businessTrip) {
        //

        $fields = $request->validate([
            'date_from' => 'required|string',
            'date_to' => 'required|string',
            'status' => 'required|integer',
        ]);

        $businessTrip->update($fields);

        return back()->with('success', 'Trasferta aggiornata con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTrip $businessTrip) {
        //

        $businessTrip->update([
            'status' => 2,
        ]);

        return back()->with('success', 'Trasferta eliminata con successo');
    }

    /**
     * * SPESE *
     */

    public function getExpenses(BusinessTrip $businessTrip) {
        $expenses = BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'expenses' => $expenses,
        ], 200);
    }

    public function createExpense(BusinessTrip $businessTrip) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
        ]);
    }

    public function editExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.expenses.edit', [
            'businessTrip' => $businessTrip,
            'expense' => $expense,
            'companies' => $companies,
        ]);
    }

    public function updateExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'payment_type' => 'required|string',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $expense->update($fields);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa aggiornata con successo');
    }

    public function storeExpense(BusinessTrip $businessTrip, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'payment_type' => 'required|string',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $expense = BusinessTripExpense::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'payment_type' => $request->payment_type,
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'date' => $request->date,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa aggiunta con successo');
    }

    public function destroyExpense(BusinessTrip $businessTrip, BusinessTripExpense $expense) {
        //

        $expense->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Spesa eliminata con successo');
    }

    /**
     * * TRASFERIMENTI *
     */

    public function createTransfer(BusinessTrip $businessTrip) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.transfers.create', [
            'businessTrip' => $businessTrip,
            'companies' => $companies,
        ]);
    }

    public function editTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer) {
        $companies = Auth::user()->companies;

        return view('standard.business_trips.transfers.edit', [
            'businessTrip' => $businessTrip,
            'transfer' => $transfer,
            'companies' => $companies,
        ]);
    }

    public function getTransfers(BusinessTrip $businessTrip) {
        $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'transfers' => $transfers,
        ], 200);
    }

    public function storeTransfer(BusinessTrip $businessTrip, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $transfer = BusinessTripTransfer::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'date' => $request->date,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento aggiunto con successo');
    }

    public function updateTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer, Request $request) {

        $fields = $request->validate([
            'company_id' => 'required|integer',
            'date' => 'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'zip_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $transfer->update($fields);

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento aggiornato con successo');
    }

    public function destroyTransfer(BusinessTrip $businessTrip, BusinessTripTransfer $transfer) {

        $transfer->delete();

        return redirect()->route('business-trips.edit', $businessTrip->id)->with('success', 'Trasferimento eliminato con successo');
    }

    /**
     * Valida un indirizzo utilizzando l'API di Nominatim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateAddress(Request $request) {
        // 1. Validazione dei dati di input
        $fields = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string', // O 'state' a seconda della convenzione usata
            'zip_code' => 'required|string',
        ]);

        // 2. Costruzione della query per Nominatim (utilizzando il parametro 'q')
        // Combiniamo i campi in una singola stringa indirizzo
        $fullAddress = $fields['address'] . ', ' . $fields['city'] . ', ' . $fields['province'] . ', ' . $fields['zip_code'];

        // Parametri per la richiesta a Nominatim
        $queryParams = [
            'q' => $fullAddress,
            'format' => 'jsonv2', // Usiamo jsonv2 per un formato più moderno
            'addressdetails' => 1, // Chiediamo i dettagli dell'indirizzo nella risposta
            'limit' => 1, // Chiediamo solo il risultato migliore
        ];

        // 3. Invio della richiesta a Nominatim usando il client HTTP di Laravel
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'IFT/1.0' // Sostituisci con un nome significativo e la tua email
            ])->get('https://nominatim.openstreetmap.org/search', $queryParams);

            // Verifica se la richiesta ha avuto successo
            if ($response->successful()) {
                $data = $response->json();

                // 4. Analisi della risposta
                if (!empty($data)) {
                    // Nominatim ha trovato almeno un risultato
                    $firstResult = $data[0];

                    // Esempi di come accedere ai dati:
                    $latitude = $firstResult['lat'];
                    $longitude = $firstResult['lon'];
                    $displayName = $firstResult['display_name'];
                    $addressDetails = $firstResult['address']; // Array con i dettagli dell'indirizzo

                    // Puoi fare ulteriori controlli qui per "validare" l'indirizzo
                    // ad esempio, confrontare i dettagli restituiti con quelli inseriti dall'utente.
                    // Ricorda che questa è una validazione basata su OSM, non postale ufficiale.

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Indirizzo trovato.',
                        'content' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'display_name' => $displayName,
                            'address_details' => $addressDetails,
                            // Puoi aggiungere altri dati dal risultato di Nominatim se necessario
                        ]
                    ]);
                } else {
                    // Nessun risultato trovato per l'indirizzo
                    return response()->json([
                        'status' => 'not_found',
                        'message' => 'Indirizzo non trovato.',
                        'data' => null
                    ], 404); // Codice di stato 404 Not Found
                }
            } else {
                // La richiesta HTTP a Nominatim non è andata a buon fine
                // Puoi loggare l'errore o restituire un messaggio generico
                Log::error('Nominatim API request failed: ' . $response->status());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Errore durante la comunicazione con il servizio di validazione indirizzi.',
                    'details' => $response->body() // Potrebbe contenere informazioni sull'errore da Nominatim
                ], $response->status()); // Usa il codice di stato della risposta di Nominatim

            }
        } catch (\Exception $e) {
            // Gestione di eventuali eccezioni durante la richiesta
            Log::error('Exception during Nominatim API call: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Si è verificato un errore imprevisto.',
                'details' => $e->getMessage()
            ], 500); // Codice di stato 500 Internal Server Error
        }
    }
}
