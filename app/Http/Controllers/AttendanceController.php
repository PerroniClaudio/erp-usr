<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\TimeOffRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        return view('standard.attendances.index');
    }

    public function getUserAttendances(Request $request) {
        $user = $request->user();

        // Request will have a start and an end date

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $attendances = Attendance::where('user_id', $user->id)
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->with(['attendanceType'])
                ->get();
        } else {
            $attendances = Attendance::where('user_id', $user->id)->with(['attendanceType'])->get();
        }

        $events = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'title' => $attendance->attendanceType->acronym . " (" . $attendance->time_in . " - " . $attendance->time_out . ")",
                'date' => $attendance->date,
                'description' => $attendance->attendanceType->description,
                'color' => $attendance->attendanceType->color,
            ];
        });

        return response()->json([
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        $attendanceTypes = AttendanceType::all();
        $companies = Auth::user()->companies;


        return view('standard.attendances.create', [
            'attendanceTypes' => $attendanceTypes,
            'companies' => $companies,
        ])->with([
            'success' => session('success'),
            'error' => session('error'),
            'message' => session('message'),
            'errors' => session('errors'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = $request->user();

        $fields = $request->validate([
            'date' => 'required|string',
            'time_in' => 'required|string',
            'time_out' => 'required|string',
            'company_id' => 'required|int',
            'attendance_type_id' => 'required|int',
        ]);

        if (!$user->hasRole('admin')) {
            if (strtotime($fields['time_out']) < strtotime($fields['time_in'])) {
                return back()->withErrors(['message' => 'L\'orario di fine non può essere maggiore di quello di inizio']);
            }

            if (strtotime($fields['date']) > strtotime(date('Y-m-d'))) {
                return back()->withErrors(['message' => 'Non è possibile creare presenze nel futuro']);
            }

            if (strtotime($fields['date']) < strtotime(date('Y-m-d'))) {
                return back()->withErrors(['message' => 'Non è possibile creare presenze nel passato']);
            }
        }


        $difference = (strtotime($fields['time_out']) - strtotime($fields['time_in'])) / 3600;

        if ($difference > 4) {
            return back()->withErrors(['message' => 'Una presenza non può durare più di 4 ore']);
        }

        $timeOffRequests = TimeOffRequest::where('user_id', $user->id)
            ->where('company_id', $fields['company_id'])
            ->where('date_from', '<=', $fields['date'])
            ->where('date_to', '>=', $fields['date'])
            ->get();

        if (count($timeOffRequests) > 0) {
            return back()->withErrors(['message' => 'Non ci devono essere richieste di ferie o permesso nella presenza']);
        }

        Attendance::create([
            'user_id' => $user->id,
            'company_id' => $fields['company_id'],
            'date' => $fields['date'],
            'time_in' => $fields['time_in'],
            'time_out' => $fields['time_out'],
            'hours' => $difference,
            'attendance_type_id' => $fields['attendance_type_id'],
        ]);

        return redirect()->route('attendances.index')->with('success', 'Presenza registrata con successo');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance) {
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance) {

        if ($attendance->date !== date('Y-m-d')) {
            return redirect()->route('attendances.index')->withErrors(['message' => 'Non è possibile modificare una presenza che non è di oggi']);
        }

        $attendanceTypes = AttendanceType::all();
        return view('standard.attendances.edit', [
            'attendance' => $attendance,
            'attendanceTypes' => $attendanceTypes,
            'companies' => Auth::user()->companies,
        ])->with([
            'success' => session('success'),
            'error' => session('error'),
            'message' => session('message'),
            'errors' => session('errors'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance) {
        $user = $request->user();

        $fields = $request->validate([
            'date' => 'required|string',
            'time_in' => 'required|string',
            'time_out' => 'required|string',
            'company_id' => 'required|int',
            'attendance_type_id' => 'required|int',
        ]);

        if (strtotime($fields['time_out']) < strtotime($fields['time_in'])) {
            return back()->withErrors(['message' => 'L\'orario di fine non può essere maggiore di quello di inizio']);
        }

        if (strtotime($fields['date']) > strtotime(date('Y-m-d'))) {
            return back()->withErrors(['message' => 'Non è possibile creare presenze nel futuro']);
        }

        $difference = (strtotime($fields['time_out']) - strtotime($fields['time_in'])) / 3600;

        if ($difference > 4) {
            return back()->withErrors(['message' => 'Una presenza non può durare più di 4 ore']);
        }

        $attendance->update([
            'user_id' => $user->id,
            'company_id' => $fields['company_id'],
            'date' => $fields['date'],
            'time_in' => $fields['time_in'],
            'time_out' => $fields['time_out'],
            'attendance_type_id' => $fields['attendance_type_id'],
        ]);

        return redirect()->route('attendances.index')->with('success', 'Presenza modificata con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance) {
        $attendance->delete();
        return redirect()->route('attendances.index')->with('success', 'Presenza eliminata con successo');
    }

    public function types() {
        $types = AttendanceType::all();
        return view('attendances.types', compact('types'));
    }
}
