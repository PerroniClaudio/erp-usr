<?php

namespace App\Http\Controllers;

use App\Models\FailedAttendance;
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

        if (env('APP_ENV') === 'production') {
            Mail::to(env('ATTENDANCES_EMAIL'))
                ->send(new \App\Mail\SentJustification($failedAttendance));
            return redirect()->route('home')->with('success', 'La richiesta verrà elaborata entro 48 ore.');
        } else {
            Mail::to(env('DEV_EMAIL'))
                ->send(new \App\Mail\SentJustification($failedAttendance));
            return redirect()->route('home')->with('success', 'La richiesta verrà elaborata entro 48 ore.');
        }
    }
}
