<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller {
    /**
     * Display a listing of the resource (admin view).
     */
    public function adminIndex() {
        $requests = OvertimeRequest::with(['user', 'company'])->orderBy('date', 'desc')->get();
        return view('admin.overtime_requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource (user view).
     */
    public function create() {
        $companies = Company::all();
        $overtimeTypes = \App\Models\OvertimeType::all();
        return view('standard.overtime_requests.create', compact('companies', 'overtimeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = $request->user();
        $fields = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
            'company_id' => 'required|int',
            'overtime_type_id' => 'required|int',
        ]);
        $fields['user_id'] = $user->id;
        $fields['hours'] = (strtotime($fields['time_out']) - strtotime($fields['time_in'])) / 3600;
        $fields['status'] = 0;

        // Recupera il tipo di straordinario
        $overtimeType = \App\Models\OvertimeType::find($fields['overtime_type_id']);
        $date = $fields['date'];
        $timeIn = $fields['time_in'];
        $isHoliday = $this->isHoliday($date);
        $isNight = strtotime($timeIn) >= strtotime('22:00');

        if ($overtimeType) {
            if ($overtimeType->acronym === 'STN' && !$isNight) {
                return back()->withErrors(['time_in' => 'Lo straordinario notturno deve iniziare dopo le 22:00.'])->withInput();
            }
            if ($overtimeType->acronym === 'STF' && !$isHoliday) {
                return back()->withErrors(['date' => 'Lo straordinario festivo deve essere in un giorno festivo.'])->withInput();
            }
            if ($overtimeType->acronym === 'STNF' && (!$isNight || !$isHoliday)) {
                return back()->withErrors(['date' => 'Lo straordinario notturno e festivo deve essere sia dopo le 22:00 che in un giorno festivo.'])->withInput();
            }
        }

        OvertimeRequest::create($fields);
        return redirect()->route('overtime-requests.index')->with('success', 'Richiesta di straordinario inviata con successo');
    }

    /**
     * Ritorna true se la data è festiva (sabato, domenica o festivo nazionale)
     */
    private function isHoliday($date) {
        $carbon = \Carbon\Carbon::parse($date);
        // Sabato o domenica
        if ($carbon->isWeekend()) {
            return true;
        }
        // Festivi nazionali (aggiungi qui le tue date)
        $holidays = [
            '01-01', // Capodanno
            '06-01', // Epifania
            '25-04', // Liberazione
            '01-05', // Lavoro
            '02-06', // Repubblica
            '15-08', // Ferragosto
            '01-11', // Ognissanti
            '08-12', // Immacolata
            '25-12', // Natale
            '26-12', // Santo Stefano
        ];
        $monthDay = $carbon->format('d-m');
        if (in_array($monthDay, $holidays)) {
            return true;
        }
        // Pasqua e Pasquetta
        $year = $carbon->year;
        $easter = \Carbon\Carbon::createFromTimestamp(easter_date($year));
        $easterMonday = $easter->copy()->addDay();
        if ($carbon->isSameDay($easter) || $carbon->isSameDay($easterMonday)) {
            return true;
        }
        return false;
    }

    /**
     * Approve an overtime request (admin).
     */
    public function approve(OvertimeRequest $overtimeRequest) {
        $overtimeRequest->update(['status' => 2]);
        return redirect()->route('admin.overtime-requests.index')->with('success', 'Richiesta di straordinario approvata');
    }

    /**
     * Deny an overtime request (admin).
     */
    public function deny(OvertimeRequest $overtimeRequest) {
        $overtimeRequest->update(['status' => 3]);
        return redirect()->route('admin.overtime-requests.index')->with('success', 'Richiesta di straordinario rifiutata');
    }

    /**
     * Lista richieste utente (standard view)
     */
    public function index(Request $request) {
        $user = $request->user();
        $requests = OvertimeRequest::where('user_id', $user->id)->orderBy('date', 'desc')->get();
        return view('standard.overtime_requests.index', compact('requests'));
    }

    /**
     * Restituisce le richieste di straordinario in formato JSON per il calendario admin
     */
    public function listOvertimeRequests(Request $request) {
        $requests = OvertimeRequest::with(['user', 'company', 'overtimeType'])->get();
        $events = $requests->map(function ($req) {
            return [
                'id' => $req->id,
                'title' => $req->user->name . ' - ' . ($req->overtimeType->acronym ?? ''),
                'start' => $req->date . 'T' . $req->time_in,
                'end' => $req->date . 'T' . $req->time_out,
                'backgroundColor' => $req->status == 2 ? '#22c55e' : ($req->status == 3 ? '#f87171' : '#fbbf24'),
                'extendedProps' => [
                    'user' => $req->user->name,
                    'company' => $req->company->name,
                    'type' => $req->overtimeType->name ?? '',
                    'status' => $req->status,
                ],
            ];
        });
        return response()->json($events);
    }

    /**
     * Restituisce le richieste di straordinario dell'utente in formato JSON per il calendario utente
     */
    public function listUserOvertimeRequests(Request $request) {
        $user = $request->user();
        $requests = OvertimeRequest::with(['overtimeType'])
            ->where('user_id', $user->id)
            ->get();
        $events = $requests->map(function ($req) {
            return [
                'id' => $req->id,
                'title' => ($req->overtimeType->acronym ?? '') . ' (' . $req->hours . 'h)',
                'start' => $req->date . 'T' . $req->time_in,
                'end' => $req->date . 'T' . $req->time_out,
                'backgroundColor' => $req->status == 2 ? '#22c55e' : ($req->status == 3 ? '#f87171' : '#fbbf24'),
                'extendedProps' => [
                    'type' => $req->overtimeType->name ?? '',
                    'status' => $req->status,
                ],
            ];
        });
        return response()->json($events);
    }

    /**
     * Mostra la singola richiesta di straordinario lato utente
     */
    public function show(OvertimeRequest $overtimeRequest) {
        return view('standard.overtime_requests.show', compact('overtimeRequest'));
    }

    /**
     * Mostra la singola richiesta di straordinario lato admin
     */
    public function adminShow(OvertimeRequest $overtimeRequest) {
        return view('admin.overtime_requests.show', compact('overtimeRequest'));
    }

    /**
     * Recupera le richieste di straordinario in sospeso per la home admin
     */
    public static function getPendingOvertimeRequests() {
        return OvertimeRequest::with(['user', 'company', 'overtimeType'])
            ->where('status', 0)
            ->orderBy('date', 'asc')
            ->get();
    }
}
