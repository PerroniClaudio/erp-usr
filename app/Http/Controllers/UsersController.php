<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class UsersController extends Controller {
    //

    public function index() {
        $users = User::all();
        return view('admin.personnel.users.index', compact('users'));
    }

    public function exportPdf(User $user, Request $request) {
        $request->validate([
            'mese' => 'required|string',
            'anno' => 'required|integer',
        ]);

        $mese = $request->mese;
        $anno = $request->anno;

        // Otteniamo i dati per il PDF
        $mesiMap = [
            'Gennaio' => 1,
            'Febbraio' => 2,
            'Marzo' => 3,
            'Aprile' => 4,
            'Maggio' => 5,
            'Giugno' => 6,
            'Luglio' => 7,
            'Agosto' => 8,
            'Settembre' => 9,
            'Ottobre' => 10,
            'Novembre' => 11,
            'Dicembre' => 12
        ];

        $meseNumero = $mesiMap[$mese];
        $primoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->startOfDay();
        $ultimoGiorno = Carbon::createFromDate($anno, $meseNumero, 1)->endOfMonth()->endOfDay();

        // Generiamo il PDF dalla vista
        $pdf = PDF::loadView('cedolini.pdf', [
            'user' => $user,
            'mese' => $mese,
            'anno' => $anno,
            'meseNumero' => $meseNumero,
            'primoGiorno' => $primoGiorno,
            'ultimoGiorno' => $ultimoGiorno
        ]);

        // Impostiamo le opzioni del PDF
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download('cedolino_' . $user->name . '_' . $mese . '_' . $anno . '.pdf');
    }
}
