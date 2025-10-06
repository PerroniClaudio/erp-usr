<?php

use App\Models\Attendance;
use App\Models\FailedAttendance;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use App\Models\User;
use App\Models\Company;
use App\Services\AttendanceCheckService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new AttendanceCheckService();
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create(['name' => 'iFortech']);
    $this->user->companies()->attach($this->company);
});

it('rileva giorni con ore lavorative mancanti', function () {
    // Crea un giorno lavorativo con solo 4 ore di presenza
    $yesterday = Carbon::yesterday();
    
    // Salta se ieri era weekend
    if ($yesterday->isWeekend()) {
        $yesterday = $yesterday->subDays($yesterday->dayOfWeek === Carbon::SUNDAY ? 2 : 1);
    }
    
    Attendance::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'date' => $yesterday->format('Y-m-d'),
        'hours' => 4,
        'status' => 1, // Approvata
    ]);
    
    $missingDays = $this->service->checkMissingWorkHours($this->user);
    
    expect($missingDays)->toHaveCount(1);
    expect($missingDays[0]['date'])->toBe($yesterday->format('Y-m-d'));
    expect($missingDays[0]['total_hours'])->toBe(4.0);
    expect($missingDays[0]['missing_hours'])->toBe(4.0);
});

it('non rileva giorni con 8 ore complete di presenza', function () {
    $yesterday = Carbon::yesterday();
    
    // Salta se ieri era weekend
    if ($yesterday->isWeekend()) {
        $yesterday = $yesterday->subDays($yesterday->dayOfWeek === Carbon::SUNDAY ? 2 : 1);
    }
    
    Attendance::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'date' => $yesterday->format('Y-m-d'),
        'hours' => 8,
        'status' => 1, // Approvata
    ]);
    
    $missingDays = $this->service->checkMissingWorkHours($this->user);
    
    expect($missingDays)->toBeEmpty();
});

it('considera sia presenze che permessi nel calcolo delle ore', function () {
    $yesterday = Carbon::yesterday();
    
    // Salta se ieri era weekend
    if ($yesterday->isWeekend()) {
        $yesterday = $yesterday->subDays($yesterday->dayOfWeek === Carbon::SUNDAY ? 2 : 1);
    }
    
    // 4 ore di presenza
    Attendance::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'date' => $yesterday->format('Y-m-d'),
        'hours' => 4,
        'status' => 1,
    ]);
    
    // 4 ore di permesso/ferie
    $timeOffType = TimeOffType::factory()->create(['name' => 'Ferie']);
    TimeOffRequest::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'time_off_type_id' => $timeOffType->id,
        'date_from' => $yesterday->setTime(14, 0)->format('Y-m-d H:i:s'),
        'date_to' => $yesterday->setTime(18, 0)->format('Y-m-d H:i:s'),
        'status' => 2, // Approvato
    ]);
    
    $missingDays = $this->service->checkMissingWorkHours($this->user);
    
    expect($missingDays)->toBeEmpty();
});

it('crea automaticamente record FailedAttendance per giorni con ore mancanti', function () {
    $yesterday = Carbon::yesterday();
    
    // Salta se ieri era weekend
    if ($yesterday->isWeekend()) {
        $yesterday = $yesterday->subDays($yesterday->dayOfWeek === Carbon::SUNDAY ? 2 : 1);
    }
    
    // Solo 3 ore di presenza
    Attendance::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'date' => $yesterday->format('Y-m-d'),
        'hours' => 3,
        'status' => 1,
    ]);
    
    // Esegui il controllo completo
    $createdRecords = $this->service->performLoginAttendanceCheck($this->user);
    
    expect($createdRecords)->toHaveCount(1);
    
    // Verifica che il record sia stato creato nel database
    $failedAttendance = FailedAttendance::where('user_id', $this->user->id)
        ->where('date', $yesterday->format('Y-m-d'))
        ->first();
        
    expect($failedAttendance)->not->toBeNull();
    expect($failedAttendance->status)->toBe(0); // Da giustificare
    expect($failedAttendance->requested_hours)->toBe(5.0); // 8 - 3 = 5 ore mancanti
});

it('non crea record duplicati per giorni già processati', function () {
    $yesterday = Carbon::yesterday();
    
    // Salta se ieri era weekend
    if ($yesterday->isWeekend()) {
        $yesterday = $yesterday->subDays($yesterday->dayOfWeek === Carbon::SUNDAY ? 2 : 1);
    }
    
    // Crea già un FailedAttendance per ieri
    FailedAttendance::factory()->create([
        'user_id' => $this->user->id,
        'date' => $yesterday->format('Y-m-d'),
        'status' => 0, // Da giustificare
    ]);
    
    // Solo 3 ore di presenza
    Attendance::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'date' => $yesterday->format('Y-m-d'),
        'hours' => 3,
        'status' => 1,
    ]);
    
    // Esegui il controllo
    $createdRecords = $this->service->performLoginAttendanceCheck($this->user);
    
    expect($createdRecords)->toBeEmpty(); // Non dovrebbe creare nuovi record
    
    // Verifica che esista ancora solo un record
    $count = FailedAttendance::where('user_id', $this->user->id)
        ->where('date', $yesterday->format('Y-m-d'))
        ->count();
        
    expect($count)->toBe(1);
});

it('salta i weekend nel controllo delle ore lavorative', function () {
    $saturday = Carbon::now()->previous(Carbon::SATURDAY);
    $sunday = Carbon::now()->previous(Carbon::SUNDAY);
    
    // Non crea presenze per il weekend
    // Il servizio dovrebbe saltare questi giorni
    
    $missingDays = $this->service->checkMissingWorkHours($this->user);
    
    // Verifica che nessun giorno del weekend sia incluso nei giorni mancanti
    foreach ($missingDays as $day) {
        $date = Carbon::parse($day['date']);
        expect($date->isWeekend())->toBeFalse();
    }
});