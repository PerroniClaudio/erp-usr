<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\FailedAttendance;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $request->validate([
            'reason' => 'required|string',
            'type' => 'required|numeric',
        ]);

        $failedAttendance->update([
            'status' => 1,
            'reason' => $request->reason,
            'requested_type' => $request->type,
        ]);

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
            ->get();
    }

    public function handleFailedAttendance(FailedAttendance $failedAttendance) {
        return view('admin.failed_attendances.edit', [
            'failedAttendance' => $failedAttendance,
        ]);
    }

    public function approveFailedAttendance(Request $request, FailedAttendance $failedAttendance) {
        $request->validate([
            'type' => 'required|in:rol,time_off',
        ]);

        switch ($request->type) {
            case 'rol':
                $this->approveAsRol($failedAttendance);
                break;
            case 'time_off':
                $this->approveAsTimeOff($failedAttendance);
                break;
        }

        // Aggiorna lo stato della FailedAttendance a giustificata
        $failedAttendance->update([
            'status' => 2,
        ]);
        // Invia una mail di conferma all'utente

        Mail::to($failedAttendance->user->email)
            ->send(new \App\Mail\FailedAttendanceTimeOffApproved($failedAttendance, ($request->type === 'rol' ? 'Rol' : 'Ferie')));


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
