<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Company;
use App\Models\Group;
use App\Models\TimeOffRequest;
use App\Models\User;
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

    /** Admin */

    public function adminIndex() {

        $users = User::all();

        $today = date('Y-m-d');
        $usersStatus = [];

        foreach ($users as $user) {
            $attendanceToday = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->exists();

            if ($attendanceToday) {
                $usersStatus[] = [
                    'user' => $user,
                    'status' => 'registered',
                ];
                continue;
            }

            $timeOffToday = TimeOffRequest::where('user_id', $user->id)
                ->where('date_from', '<=', $today)
                ->where('date_to', '>=', $today)
                ->exists();

            if ($timeOffToday) {
                $usersStatus[] = [
                    'user' => $user,
                    'status' => 'time_off',
                ];
                continue;
            }

            $usersStatus[] = [
                'user' => $user,
                'status' => 'not_registered',
            ];
        }

        return view('admin.attendances.index', [
            'usersStatus' => $usersStatus,
            'groups' => Group::all(),
            'companies' => Company::all(),
        ]);
    }

    public function listAttendances(Request $request) {

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $companyId = $request->input('company_id') != null ? $request->input('company_id') : null;
        $groupId = $request->input('group_id') != null ? $request->input('group_id') : null;
        $userId = $request->input('user_id') != null ? $request->input('user_id') : null;

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

        $attendances = Attendance::where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->whereIn('user_id', $userIds)
            ->with(['user', 'attendanceType'])
            ->get();



        $attendances = $attendances->sortBy(function ($attendance) {
            return $attendance->user_id . '-' . $attendance->date . '-' . $attendance->time_in;
        })->values();

        $attendances = collect($this->mergeAttendances($attendances));

        $events = [];

        foreach ($attendances as $attendanceList) {
            foreach ($attendanceList as $attendance) {


                if ($attendance['user']->color === "") {
                    $attendance['user']->assignColorToUser();
                }

                $events[] = [
                    'id' => $attendance['id'],
                    'title' => $attendance['user']->name . " " . $attendance['attendanceType']->acronym . " (" . $attendance['time_in'] . " - " . $attendance['time_out'] . ")",
                    'date' => $attendance['date'],
                    'description' => $attendance['attendanceType']->description,
                    'color' => $attendance['user']->color,
                ];
            }
        };

        return response()->json([
            'events' => $events,
        ]);
    }

    public function mergeAttendances($attendances) {

        // Group attendances by user_id
        $grouped = [];
        foreach ($attendances as $attendance) {
            $userId = $attendance->user_id;
            if (!isset($grouped[$userId])) {
                $grouped[$userId] = [];
            }
            $grouped[$userId][] = $attendance;
        }

        // For each user, merge attendances on same day and same attendance_type_id
        $result = [];
        foreach ($grouped as $userId => $userAttendances) {
            // Group by date and attendance_type_id
            $merged = [];
            foreach ($userAttendances as $attendance) {
                $key = $attendance->date . '_' . $attendance->attendance_type_id;
                if (!isset($merged[$key])) {
                    $merged[$key] = clone $attendance;
                } else {
                    // Merge time_in and time_out
                    $merged[$key]->time_in = min($merged[$key]->time_in, $attendance->time_in);
                    $merged[$key]->time_out = max($merged[$key]->time_out, $attendance->time_out);
                    // Optionally, sum hours if needed
                    if (isset($merged[$key]->hours) && isset($attendance->hours)) {
                        $merged[$key]->hours += $attendance->hours;
                    }
                }
            }
            // Store merged attendances for this user
            $result[$userId] = array_values($merged);
        }



        return $result;
    }

    public function viewAttendance(Attendance $attendance) {
    }
}
