<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
            'expense_type' => 'required|integer',
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

    public function storeExpense(BusinessTrip $businessTrip, Request $request) {

        $expense = BusinessTripExpense::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'payment_type' => $request->payment_type,
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'date' => $request->datetime,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return back()->with('success', 'Spesa aggiunta con successo');
    }

    public function getTransfers(BusinessTrip $businessTrip) {
        $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

        return response([
            'transfers' => $transfers,
        ], 200);
    }

    public function storeTransfer(BusinessTrip $businessTrip, Request $request) {

        $transfer = BusinessTripTransfer::create([
            'business_trip_id' => $businessTrip->id,
            'company_id' => $request->company_id,
            'date' => $request->datetime,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return back()->with('success', 'Trasferimento aggiunto con successo');
    }
}
