<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\FailedAttendance;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class FailedAttendanceController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FailedAttendance $failedAttendance) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FailedAttendance $failedAttendance) {
        //
    }

    public function justify(FailedAttendance $failedAttendance) {
        return view('standard.failed_attendances.justify', [
            'failedAttendance' => $failedAttendance,
        ]);
    }

    public function sendJustification(Request $request, FailedAttendance $failedAttendance) {
        // Validazione base
        $rules = [
            'reason' => 'required|string',
            'request_type' => 'required|in:0,1', // 0 = permesso, 1 = presenza
        ];

        // Validazione condizionale in base al tipo di richiesta
        if ($request->request_type == 0) {
            // Richiesta permesso
            $rules['type'] = 'required|numeric';
        } else {
            // Richiesta presenza
            $rules['requested_time_in_morning'] = 'required|date_format:H:i';
            $rules['requested_time_out_morning'] = 'required|date_format:H:i|after:requested_time_in_morning';
            $rules['requested_time_in_afternoon'] = 'nullable|date_format:H:i|after:requested_time_out_morning';
            $rules['requested_time_out_afternoon'] = 'nullable|date_format:H:i|after:requested_time_in_afternoon';
        }

        $request->validate($rules);

        $updateData = [
            'status' => 1,
            'reason' => $request->reason,
            'request_type' => $request->request_type,
        ];

        if ($request->request_type == 0) {
            // Richiesta permesso
            $updateData['requested_type'] = $request->type;
        } else {
            // Richiesta presenza
            $updateData['requested_time_in_morning'] = $request->requested_time_in_morning;
            $updateData['requested_time_out_morning'] = $request->requested_time_out_morning;
            $updateData['requested_time_in_afternoon'] = $request->requested_time_in_afternoon;
            $updateData['requested_time_out_afternoon'] = $request->requested_time_out_afternoon;
        }

        $failedAttendance->update($updateData);

        if (config('app.env') === 'production') {
            Mail::to(config('mail.attendance_mail'))
                ->send(new \App\Mail\SentJustification($failedAttendance));
            return redirect()->route('home')->with('success', 'La richiesta verrà elaborata entro 48 ore.');
        } else {
            Mail::to(config('mail.dev_email'))
                ->send(new \App\Mail\SentJustification($failedAttendance));
            return redirect()->route('home')->with('success', 'La richiesta verrà elaborata entro 48 ore.');
        }
    }

    public function getPendingFailedAttendances() {
        return FailedAttendance::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_failed_attendances_page')
            ->withQueryString();
    }

    public function handleFailedAttendance(FailedAttendance $failedAttendance) {
        return view('admin.failed_attendances.edit', [
            'failedAttendance' => $failedAttendance,
        ]);
    }

    public function approveFailedAttendance(Request $request, FailedAttendance $failedAttendance) {
        if ($failedAttendance->request_type == 0) {
            // Richiesta permesso
            $request->validate([
                'type' => 'required|in:rol,time_off',
            ]);

            switch ($request->type) {
                case 'rol':
                    $this->approveAsRol($failedAttendance);
                    $mailType = 'Rol';
                    break;
                case 'time_off':
                    $this->approveAsTimeOff($failedAttendance);
                    $mailType = 'Ferie';
                    break;
            }
        } else {
            // Richiesta presenza - non serve validazione aggiuntiva
            $this->approveAsAttendance($failedAttendance);
            $mailType = 'Presenza';
        }

        // Aggiorna lo stato della FailedAttendance a giustificata
        $failedAttendance->update([
            'status' => 2,
        ]);

        // Invia una mail di conferma all'utente
        Mail::to($failedAttendance->user->email)
            ->send(new \App\Mail\FailedAttendanceTimeOffApproved($failedAttendance, $mailType));

        return redirect()->route('admin.home')->with('success', 'Presenza gestita con successo.');
    }

    private function approveAsRol(FailedAttendance $failedAttendance) {

        //Crea una TimeOffRequest con il tipo ROL 

        if ($failedAttendance->requested_hours == 8) {
            $date_from = Carbon::parse($failedAttendance->date . ' ' . '09:00:00');
            $date_to = Carbon::parse($failedAttendance->date . ' ' . '18:00:00');
        } else {
            $attendance = Attendance::where('date', $failedAttendance->date)->where('user_id', $failedAttendance->user_id)->latest()->get();
            $date_from = Carbon::parse($attendance->date . ' ' . $attendance->time_out);
            $date_to = $date_from->copy()->addHours($failedAttendance->requested_hours);
        }

        $attendanceType = TimeOffType::where('name', 'Rol')->first();
        $company = Company::where('name', 'iFortech')->first()->id;
        TimeOffRequest::create([
            'user_id' => $failedAttendance->user_id,
            'batch_id' => uniqid(),
            'status' => 2,
            'date_from' => $date_from->format('Y-m-d H:i:s'),
            'date_to' => $date_to->format('Y-m-d H:i:s'),
            'company_id' => $company,
            'time_off_type_id' => $attendanceType->id
        ]);
    }

    private function approveAsTimeOff(FailedAttendance $failedAttendance) {

        //Crea una TimeOffRequest con il tipo Ferie 

        if ($failedAttendance->requested_hours == 8) {
            $date_from = Carbon::parse($failedAttendance->date . ' ' . '09:00:00');
            $date_to = Carbon::parse($failedAttendance->date . ' ' . '18:00:00');
        } else {
            $attendance = Attendance::where('date', $failedAttendance->date)->where('user_id', $failedAttendance->user_id)->latest()->get();
            $date_from = Carbon::parse($attendance->date . ' ' . $attendance->time_out);
            $date_to = $date_from->copy()->addHours($failedAttendance->requested_hours);
        }

        $attendanceType = TimeOffType::where('name', 'Ferie')->first();
        $company = Company::where('name', 'iFortech')->first()->id;
        TimeOffRequest::create([
            'user_id' => $failedAttendance->user_id,
            'batch_id' => uniqid(),
            'status' => 2,
            'date_from' => $date_from->format('Y-m-d H:i:s'),
            'date_to' => $date_to->format('Y-m-d H:i:s'),
            'company_id' => $company,
            'time_off_type_id' => $attendanceType->id
        ]);
    }

    private function approveAsAttendance(FailedAttendance $failedAttendance) {
        // Crea una nuova Attendance con gli orari richiesti
        $company = Company::where('name', 'iFortech')->first();
        $attendanceType = \App\Models\AttendanceType::where('name', 'Presenza')->first();

        // Se non esiste il tipo "Presenza", usa il primo disponibile o un default
        if (!$attendanceType) {
            $attendanceType = \App\Models\AttendanceType::first();
        }

        // Calcola le ore totali
        $morningStart = \Carbon\Carbon::createFromFormat('H:i', $failedAttendance->requested_time_in_morning);
        $morningEnd = \Carbon\Carbon::createFromFormat('H:i', $failedAttendance->requested_time_out_morning);
        $morningHours = $morningEnd->diffInHours($morningStart, true);

        $afternoonHours = 0;
        if ($failedAttendance->requested_time_in_afternoon && $failedAttendance->requested_time_out_afternoon) {
            $afternoonStart = \Carbon\Carbon::createFromFormat('H:i', $failedAttendance->requested_time_in_afternoon);
            $afternoonEnd = \Carbon\Carbon::createFromFormat('H:i', $failedAttendance->requested_time_out_afternoon);
            $afternoonHours = $afternoonEnd->diffInHours($afternoonStart, true);
        }

        $totalHours = $morningHours + $afternoonHours;

        // Crea l'attendance del mattino
        Attendance::create([
            'user_id' => $failedAttendance->user_id,
            'company_id' => $company->id,
            'date' => $failedAttendance->date,
            'time_in' => $failedAttendance->requested_time_in_morning,
            'time_out' => $failedAttendance->requested_time_out_morning,
            'hours' => $morningHours,
            'status' => 1, // Approvata
            'attendance_type_id' => $attendanceType ? $attendanceType->id : null,
            'inserted_by' => Auth::id(),
        ]);

        // Se ci sono orari pomeridiani, crea una seconda attendance
        if ($failedAttendance->requested_time_in_afternoon && $failedAttendance->requested_time_out_afternoon) {
            Attendance::create([
                'user_id' => $failedAttendance->user_id,
                'company_id' => $company->id,
                'date' => $failedAttendance->date,
                'time_in' => $failedAttendance->requested_time_in_afternoon,
                'time_out' => $failedAttendance->requested_time_out_afternoon,
                'hours' => $afternoonHours,
                'status' => 1, // Approvata
                'attendance_type_id' => $attendanceType ? $attendanceType->id : null,
                'inserted_by' => Auth::id(),
            ]);
        }
    }

    public function denyFailedAttendance(Request $request, FailedAttendance $failedAttendance) {
        $failedAttendance->update([
            'status' => 3,
        ]);

        // Invia una mail di rifiuto all'utente
        Mail::to($failedAttendance->user->email)
            ->send(new \App\Mail\FailedAttendanceTimeOffDenied($failedAttendance, $request->reason));

        return redirect()->route('admin.home')->with('success', 'Presenza rifiutata con successo.');
    }
}
