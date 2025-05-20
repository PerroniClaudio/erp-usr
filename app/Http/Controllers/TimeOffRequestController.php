<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Group;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use  Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $fields['company_id'] = 1;
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
                ->where('date_to', '<=', $endDate)
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $requests = TimeOffRequest::with(['type', 'user'])
                ->where('status', '<>', '4')
                ->orderBy('id', 'asc')
                ->get();
        }


        $events = [];

        foreach ($requests as $req) {
            $color = $req->getColorForRequest($user->id);

            $events[] = [
                'id' => $req->id,
                'title' => $req->user->name . " - " . $req->type->name,
                'start' => \Carbon\Carbon::parse($req->date_from)->format('Y-m-d'),
                'end' => \Carbon\Carbon::parse($req->date_to)->format('Y-m-d'),
                'color' => $color,
                'status' => $req->status,
                'display' => 'block',
                'groupId' => $req->batch_id
            ];
        }

        $groupedEventsWithMeta = $this->groupConsecutiveEventsWithMetadata($events);

        $event_result = [];

        foreach ($groupedEventsWithMeta as $group) {

            $events = collect($group['events']);
            $firstEvent = $events->first();

            $event_result[] = [
                'id' => $firstEvent['id'],
                'title' => $firstEvent['title'],
                'start' => Carbon::parse($group['metadata']['startDate'])->format('Y-m-d'),
                'end' => Carbon::parse($group['metadata']['endDate'])->addDay()->format('Y-m-d'),
                'color' => $firstEvent['color'],
                'groupId' => $group['metadata']['groupId'],
            ];
        }

        return response()->json([
            'events' => $event_result,
        ]);
    }

    /**
     * Raggruppa eventi con lo stesso groupId e date consecutive o dello stesso giorno
     * 
     * @param Collection|array $events La collezione di eventi da raggruppare
     * @return Collection Gli eventi raggruppati
     */
    public function groupConsecutiveEvents($events): Collection {
        // Converte l'input in una Collection se è un array
        $events = is_array($events) ? collect($events) : $events;

        // Risultato finale
        $groupedEvents = collect();

        // Raggruppa inizialmente gli eventi per groupId
        $eventsByGroupId = $events->groupBy('groupId');

        foreach ($eventsByGroupId as $groupId => $groupEvents) {
            // Ordina gli eventi per data di inizio
            $sortedEvents = $groupEvents->sortBy(function ($event) {
                return Carbon::parse($event['start']);
            });

            // Array temporaneo per il gruppo corrente di eventi consecutivi
            $currentGroup = [];
            $lastEvent = null;

            foreach ($sortedEvents as $event) {
                $currentDate = Carbon::parse($event['start']);

                if ($lastEvent === null) {
                    // Primo evento del gruppo
                    $currentGroup[] = $event;
                } else {
                    $lastDate = Carbon::parse($lastEvent['start']);

                    // Verifica se le date sono consecutive o dello stesso giorno
                    if ($lastDate->isSameDay($currentDate) || $lastDate->addDay()->isSameDay($currentDate)) {
                        // Evento consecutivo o stesso giorno, aggiungi al gruppo corrente
                        $currentGroup[] = $event;
                    } else {
                        // Non consecutivo, salva il gruppo corrente e inizia uno nuovo
                        $groupedEvents->push(collect($currentGroup));
                        $currentGroup = [$event];
                    }
                }

                $lastEvent = $event;
            }

            // Aggiungi l'ultimo gruppo se non è vuoto
            if (!empty($currentGroup)) {
                $groupedEvents->push(collect($currentGroup));
            }
        }

        return $groupedEvents;
    }

    /**
     * Versione avanzata che raggruppa eventi consecutivi e restituisce metadati utili
     * 
     * @param Collection|array $events La collezione di eventi da raggruppare
     * @return Collection Gli eventi raggruppati con metadati
     */
    public function groupConsecutiveEventsWithMetadata($events): Collection {
        // Converte l'input in una Collection se è un array
        $events = is_array($events) ? collect($events) : $events;

        // Risultato finale
        $groupedEvents = collect();

        // Raggruppa inizialmente gli eventi per groupId
        $eventsByGroupId = $events->groupBy('groupId');

        foreach ($eventsByGroupId as $groupId => $groupEvents) {
            // Ordina gli eventi per data di inizio
            $sortedEvents = $groupEvents->sortBy(function ($event) {
                return Carbon::parse($event['start']);
            });

            // Array temporaneo per il gruppo corrente di eventi consecutivi
            $currentGroup = [];
            $lastEvent = null;

            foreach ($sortedEvents as $event) {
                $currentDate = Carbon::parse($event['start']);

                if ($lastEvent === null) {
                    // Primo evento del gruppo
                    $currentGroup[] = $event;
                } else {
                    $lastDate = Carbon::parse($lastEvent['start']);

                    // Verifica se le date sono consecutive o dello stesso giorno
                    if ($lastDate->isSameDay($currentDate) || $lastDate->addDay()->isSameDay($currentDate)) {
                        // Evento consecutivo o stesso giorno, aggiungi al gruppo corrente
                        $currentGroup[] = $event;
                    } else {
                        // Non consecutivo, salva il gruppo corrente con metadati e inizia uno nuovo
                        $this->addGroupWithMetadata($groupedEvents, $currentGroup);
                        $currentGroup = [$event];
                    }
                }

                $lastEvent = $event;
            }

            // Aggiungi l'ultimo gruppo se non è vuoto
            if (!empty($currentGroup)) {
                $this->addGroupWithMetadata($groupedEvents, $currentGroup);
            }
        }

        return $groupedEvents;
    }

    /**
     * Helper per aggiungere un gruppo con metadati utili
     */
    private function addGroupWithMetadata(Collection &$groupedEvents, array $group): void {
        if (empty($group)) return;

        $firstEvent = $group[0];
        $lastEvent = end($group);

        $startDate = Carbon::parse($firstEvent['start']);
        $endDate = Carbon::parse($lastEvent['start']);

        $groupMetadata = [
            'events' => collect($group),
            'metadata' => [
                'groupId' => $firstEvent['groupId'],
                'totalEvents' => count($group),
                'startDate' => $startDate->toDateTimeString(),
                'endDate' => $endDate->toDateTimeString(),
                'durationInDays' => $startDate->diffInDays($endDate) + 1
            ]
        ];

        $groupedEvents->push(collect($groupMetadata));
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
    public function edit($batch_id) {
        $types = TimeOffType::all();

        $requests = TimeOffRequest::with(['type', 'user'])->where('batch_id', $batch_id)->orderBy('id', 'asc')->get();

        return view('standard.time_off_requests.edit', [
            'requests' => $requests,
            'types' => $types,
            'batch_id' => $batch_id,
        ]);
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

    public function updateBatch(Request $request, $batch_id) {
        $user = $request->user();
        $requests = json_decode($request->requests);

        DB::beginTransaction();

        foreach ($requests as $time_off_request) {
            $fields = [
                'date_from' => $time_off_request->date_from,
                'date_to' => $time_off_request->date_to,
                'time_off_type_id' => $time_off_request->time_off_type_id,
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
                return response()->json([
                    'error' => 'Hai già una richiesta di permesso in questo periodo',
                    'conflicting_request_id' => $existingRequest->id,
                ], 400);
            }

            TimeOffRequest::where('id', $time_off_request->id)->update($fields);
        }

        DB::commit();

        return response()->json(['success' => 'Richieste di permesso modificate con successo']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeOffRequest $timeOffRequest) {
        $timeOffRequest->update(['status' => '4']);

        return redirect()->route('time-off-requests.index')->with('success', 'Richiesta di permesso cancellata con successo');
    }

    public function estimateDays(Request $request) {
        $user = $request->user();
        $startDate = $request->input('date_from');
        $endDate = $request->input('date_to');

        $days = [];

        $currentDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);

        $festiveDays = $this->getFestiveDays($startDate, $endDate);

        while ($currentDate->lte($endDate)) {
            if (!in_array($currentDate->toDateString(), $festiveDays)) {
                $days[] = [
                    'day' => $currentDate->toDateString(),
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'total_hours' => "4.00",
                ];
                $days[] = [
                    'day' => $currentDate->toDateString(),
                    'start_time' => '14:00',
                    'end_time' => '18:00',
                    'total_hours' => "4.00",
                ];
            }
            $currentDate->addDay();
        }

        return response()->json([
            'days' => $days,
        ]);
    }

    private function getFestiveDays($startDate, $endDate) {
        $festiveDays = [];

        $currentDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);

        $easterDay = $this->calculateEasterDay($currentDate->year);
        $easterDay = $easterDay ? $easterDay->format('m-d') : null;
        $easterMonday = $this->calculateEasterMonday($currentDate->year)->format('m-d');;

        // List of known vacation days
        $knownHolidays = [
            '01-01', // New Year's Day
            '12-25', // Christmas
            '12-26', // Boxing Day
            '01-06', // Epiphany
            '04-25', // Liberation Day
            '05-01', // Labor Day
            '06-02', // Republic Day
            '08-15', // Assumption Day
            '11-01', // All Saints' Day
            '11-02', // All Souls' Day
            '12-08', // Immaculate Conception
            $easterDay, // Easter
            $easterMonday, // Easter Monday
        ];

        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->format('m-d');

            if ($currentDate->isWeekend() || in_array($formattedDate, $knownHolidays)) {
                $festiveDays[] = $currentDate->toDateString();
            }

            $currentDate->addDay();
        }

        return $festiveDays;
    }

    private function calculateEasterDay($year) {
        $a = $year % 19;
        $b = (int)($year / 100);
        $c = $year % 100;
        $d = (int)($b / 4);
        $e = $b % 4;
        $f = (int)(($b + 8) / 25);
        $g = (int)(($b - $f + 1) / 16);
        $h = (int)((19 * $a + $b - $d - $g + 15) % 30);
        $i = (int)($c / 16);
        $k = (int)($c % 16);
        $l = (int)((32 + 2 * $e + 2 * $i - $h - $k) % 7);
        $m = (int)(($a + 11 * $h + 22 * $l) / 451);

        return \Carbon\Carbon::createFromDate($year, (int)(($h + $l - 7 * $m + 114) / 31), ($h + $l - 7 * $m + 114) % 31 + 1);
    }

    private function calculateEasterMonday($year) {
        $easterDay = $this->calculateEasterDay($year);
        return $easterDay->modify('+1 day');
    }


    /** Funzioni per admin */

    public function adminIndex() {
        return view('admin.time_off_requests.index', [
            'groups' => Group::all(),
            'companies' => Company::all(),
            'users' => User::all(),
            'timeOffTypes' => TimeOffType::all(),
        ]);
    }

    public function listTimeOffRequests(Request $request) {

        // Filtri opzionali
        $companyId = $request->input('company_id') != null ? $request->input('company_id') : null;
        $groupId = $request->input('group_id') != null ? $request->input('group_id') : null;
        $time_off_type_id = $request->input('type_id') != null ? $request->input('type_id') : null;
        $startDate = $request->input('start');
        $endDate = date('Y-m-d', strtotime($request->input('end') . ' +3 days'));

        // Costruisci la query utenti solo se necessario

        $company_user_ids = [];
        if ($companyId) {
            $company  = Company::find($companyId);
            $company_users = $company->users()->get();

            $company_user_ids = $company_users->pluck('id');
        }

        $group_user_ids = [];
        if ($groupId) {
            $group = Group::find($groupId);
            $group_user_ids = $group->users()->get();

            $group_user_ids = $group_user_ids->pluck('id');
        }

        if ($companyId && $groupId) {
            // Intersezione tra utenti della company e del gruppo
            $userIds = collect($company_user_ids)->intersect($group_user_ids)->values();
        } elseif ($companyId) {
            $userIds = collect($company_user_ids)->values();
        } elseif ($groupId) {
            $userIds = collect($group_user_ids)->values();
        } else {
            $userIds = User::pluck('id'); // Prendi tutti gli utenti se non ci sono filtri
        }


        // Query richieste permesso
        $requests = TimeOffRequest::with(['type', 'user'])
            ->where('status', '<>', '4')
            ->where('date_from', '>=', $startDate)
            ->where('date_to', '<=', $endDate)
            ->when($time_off_type_id, function ($query, $time_off_type_id) {
                return $query->where('time_off_type_id', $time_off_type_id);
            })
            ->whereIn('user_id', $userIds)

            ->orderBy('id', 'asc')
            ->get();


        $events = [];

        foreach ($requests as $req) {

            $color = $req->getColorForRequest(0);

            $events[] = [
                'id' => $req->id,
                'user' => $req->user,
                'title' => $req->user->name . " - " . $req->type->name,
                'start' => \Carbon\Carbon::parse($req->date_from)->format('Y-m-d'),
                'end' => \Carbon\Carbon::parse($req->date_to)->format('Y-m-d'),
                'status' => $req->status,
                'color' => $color,
                'display' => 'block',
                'groupId' => $req->batch_id
            ];
        }

        $groupedEventsWithMeta = $this->groupConsecutiveEventsWithMetadata($events);

        $event_result = [];

        foreach ($groupedEventsWithMeta as $group) {

            $events = collect($group['events']);
            $firstEvent = $events->first();

            $event_result[] = collect([
                'id' => $firstEvent['id'],
                'title' => $firstEvent['title'],
                'start' => \Carbon\Carbon::parse($req->date_from)->format('Y-m-d'),
                'end' => \Carbon\Carbon::parse($req->date_to)->format('Y-m-d'),
                'user' => $firstEvent['user'],
                'status' => $firstEvent['status'],
                'start' => Carbon::parse($group['metadata']['startDate'])->format('Y-m-d'),
                'end' => Carbon::parse($group['metadata']['endDate'])->addDay()->format('Y-m-d'),
                'color' => $firstEvent['color'],

                'groupId' => $group['metadata']['groupId'],
            ]);
        }

        return response()->json([
            'events' => $event_result,
        ]);
    }

    public function viewTimeOffRequest(TimeOffRequest $timeOffRequest) {

        $types = TimeOffType::all();

        $requests = TimeOffRequest::with(['type', 'user'])->where('batch_id', $timeOffRequest->batch_id)->orderBy('id', 'asc')->get();

        return view('admin.time_off_requests.edit', [
            'requests' => $requests,
            'types' => $types,
            'batch_id' => $timeOffRequest->batch_id
        ]);
    }

    public function approveTimeOffRequest(TimeOffRequest $timeOffRequest) {


        // Approva tutte le richieste con lo stesso batch_id
        TimeOffRequest::where('batch_id', $timeOffRequest->batch_id)->update(['status' => '2']);

        return redirect()->route('admin.time-off.index')->with('success', 'Richiesta di permesso approvata con successo');
    }

    public function denyTimeOffRequest(TimeOffRequest $timeOffRequest) {
        // Rifiuta tutte le richieste con lo stesso batch_id
        TimeOffRequest::where('batch_id', $timeOffRequest->batch_id)->update(['status' => '3']);

        return redirect()->route('admin.time-off.index')->with('success', 'Richiesta di permesso rifiutata con successo');
    }

    public function deleteTimeOffRequest(TimeOffRequest $timeOffRequest) {
        // Elimina tutte le richieste con lo stesso batch_id
        TimeOffRequest::where('batch_id', $timeOffRequest->batch_id)->delete();

        return redirect()->route('admin.time-off.index')->with('success', 'Richiesta di permesso eliminata con successo');
    }
}
