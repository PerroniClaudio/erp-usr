<?php

namespace App\Http\Controllers;

use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeOffRequestController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
        return view('standard.time_off_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        $types = TimeOffType::all();
        return view('standard.time_off_requests.create', ['types' => $types]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = $request->user();

        $fields = $request->validate([
            'date_from' => 'required|string',
            'date_to' => 'required|string',
            'company_id' => 'required|int',
            'time_off_type_id' => 'required|int',
            'description' => 'required|string',
        ]);

        if (strtotime($fields['date_to']) < strtotime($fields['date_from'])) {
            return redirect()->back()->withErrors(['message' => 'La data di fine non può essere maggiore di quella di inizio']);
        }

        $fields['user_id'] = $user->id;

        TimeOffRequest::create($fields);

        return redirect()->route('time-off-requests.index')->with('success', 'Richiesta di permesso creata con successo');
    }

    public function storeBatch(Request $request) {
        $user = $request->user();
        $requests = json_decode($request->requests);
        $batch_id = uniqid();

        DB::beginTransaction();

        foreach ($requests as $time_off_request) {
            $fields = [
                'date_from' => $time_off_request->date_from,
                'date_to' => $time_off_request->date_to,
                'time_off_type_id' => $time_off_request->time_off_type_id,
            ];

            $fields['user_id'] = $user->id;
            $fields['company_id'] = $user->company_id;
            $fields['batch_id'] = $batch_id;

            $existingRequest = TimeOffRequest::where('user_id', $user->id)
                ->where(function ($query) use ($fields) {
                    $query->whereBetween('date_from', [$fields['date_from'], $fields['date_to']])
                        ->orWhereBetween('date_to', [$fields['date_from'], $fields['date_to']]);
                })
                ->first();

            if ($existingRequest) {
                DB::rollBack();
                return redirect()->back()->withErrors(['message' => 'Hai già una richiesta di permesso in questo periodo']);
            }

            TimeOffRequest::create($fields);
        }

        DB::commit();

        return redirect()->route('time-off-requests.index')->with('success', 'Richieste di permesso create con successo');
    }

    public function getUserRequests(Request $request) {
        $user = $request->user();

        // Request will have a start and an end date

        $startDate = $request->input('start');
        $endDate = date('Y-m-d', strtotime($request->input('end') . ' +3 days'));

        if ($startDate && $endDate) {
            $requests = TimeOffRequest::with(['type', 'user'])
                ->where('status', '<>', '4')
                ->where('date_from', '>=', $startDate)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $requests = TimeOffRequest::with(['type', 'user'])
                ->where('status', '<>', '4')
                ->orderBy('id', 'asc')
                ->get();
        }


        $formattedRequests = [];
        $currentRequest = null;
        $events = [];

        foreach ($requests as $key => $req) {
            if ($req->time_off_type_id == 11) {
                if ($currentRequest) {
                    $currentRequest['date_to'] = $req->date_to;
                    if (isset($requests[$key + 1])) {
                        $nextBatchId = $requests[$key + 1]['batch_id'];
                        $currentBatchId = $req->batch_id;

                        if ($nextBatchId != $currentBatchId) {
                            $formattedRequests[] = $currentRequest;
                            $currentRequest = null;
                        }
                    } else {
                        $formattedRequests[] = $currentRequest;
                        $currentRequest = null;
                    }
                } else {
                    $currentRequest = $req;
                }
            } else {
                $formattedRequests[] = $req;
                $currentRequest = null;
            }
        }

        foreach ($formattedRequests as $req) {
            $color = $req->user_id != $user->id ? "#437f97" : "#e73028";

            $events[] = [
                'id' => $req->id,
                'title' => $req->user->name . " - " . $req->type->name,
                'start' => date('Y-m-d', strtotime($req->date_from)),
                'end' => date('Y-m-d', strtotime($req->date_to)),
                'description' => $req->description,
                'color' => $color
            ];
        }

        return response()->json([
            'events' => $events,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeOffRequest $timeOffRequest) {
        $requests = TimeOffRequest::with(['type', 'user'])->where('batch_id', $timeOffRequest->batch_id)->orderBy('id', 'asc')->get();

        return view('standard.time_off_requests.show', ['requests' => $requests]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeOffRequest $timeOffRequest) {
        $types = TimeOffType::all();
        return view('standard.time_off_requests.edit', ['timeOffRequest' => $timeOffRequest, 'types' => $types]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeOffRequest $timeOffRequest) {
        $fields = $request->validate([
            'date_from' => 'required|string',
            'date_to' => 'required|string',
            'company_id' => 'required|int',
            'time_off_type_id' => 'required|int',
        ]);

        $timeOffRequest->update($fields);

        return redirect()->route('time-off-requests.index')->with('success', 'Richiesta di permesso aggiornata con successo');
    }

    public function updateBatch(Request $request) {
        $user = $request->user();
        $requests = json_decode($request->requests);

        DB::beginTransaction();

        foreach ($requests as $time_off_request) {
            $fields = [
                'date_from' => $time_off_request->date_from,
                'date_to' => $time_off_request->date_to,
                'id' => $time_off_request->id,
            ];

            $existingRequest = TimeOffRequest::where('user_id', $user->id)->where('id', '<>', $time_off_request->id)
                ->where(function ($query) use ($fields) {
                    $query
                        ->whereBetween('date_from', [$fields['date_from'], $fields['date_to']])
                        ->orWhereBetween('date_to', [$fields['date_from'], $fields['date_to']]);
                })
                ->first();

            if ($existingRequest) {
                DB::rollBack();
                return redirect()->back()->withErrors(['message' => 'Hai già una richiesta di permesso in questo periodo']);
            }

            TimeOffRequest::where('id', $time_off_request->id)->update($fields);
        }

        DB::commit();

        return redirect()->route('time-off-requests.index')->with('success', 'Richieste di permesso modificate con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeOffRequest $timeOffRequest) {
        $timeOffRequest->update(['status' => '4']);

        return redirect()->route('time-off-requests.index')->with('success', 'Richiesta di permesso cancellata con successo');
    }
}
