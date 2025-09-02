<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl flex items-center gap-2">
            <x-lucide-alert-triangle class="h-8 w-8 text-warning" />
            Anomalie Orari - {{ $user->name }}
        </h1>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline">
            <x-lucide-arrow-left class="h-4 w-4" />
            Torna ai Dati Utente
        </a>
    </div>

    <hr>

    <!-- Informazioni periodo -->
    <div class="alert alert-warning">
        <x-lucide-alert-triangle class="h-6 w-6" />
        <div>
            <h3 class="font-bold">Anomalie rilevate nel periodo</h3>
            <div class="text-xs">{{ $primoGiorno->format('d/m/Y') }} - {{ $ultimoGiorno->format('d/m/Y') }}
                ({{ $mese }} {{ $anno }})</div>
        </div>
    </div>

    <!-- Riepilogo generale -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-primary text-primary-content">
            <div class="card-body">
                <h2 class="card-title text-sm">Ore Previste</h2>
                <div class="text-3xl font-bold">{{ number_format($anomaliesData['totalExpectedHours'], 1) }}</div>
                <div class="text-xs opacity-75">{{ $anomaliesData['workingDaysInPeriod'] }} giorni lavorativi</div>
            </div>
        </div>

        <div class="card bg-secondary text-secondary-content">
            <div class="card-body">
                <h2 class="card-title text-sm">Ore Effettive</h2>
                <div class="text-3xl font-bold">{{ number_format($anomaliesData['totalActualHours'], 1) }}</div>
            </div>
        </div>

        <div
            class="card {{ $anomaliesData['totalDifference'] >= 0 ? 'bg-warning text-warning-content' : 'bg-error text-error-content' }}">
            <div class="card-body">
                <h2 class="card-title text-sm">Differenza</h2>
                <div class="text-3xl font-bold">
                    {{ $anomaliesData['totalDifference'] >= 0 ? '+' : '' }}{{ number_format($anomaliesData['totalDifference'], 1) }}
                </div>
                <div class="text-xs opacity-75">
                    {{ $anomaliesData['totalDifference'] >= 0 ? 'ore in eccesso' : 'ore mancanti' }}
                </div>
            </div>
        </div>

        <div class="card bg-accent text-accent-content">
            <div class="card-body">
                <h2 class="card-title text-sm">Tipo Anomalie</h2>
                <div class="text-lg font-bold">
                    @if ($anomaliesData['hasWeeklyAnomalies'] && $anomaliesData['hasMonthlyAnomalies'])
                        Settimanali e Mensili
                    @elseif($anomaliesData['hasWeeklyAnomalies'])
                        Settimanali
                    @elseif($anomaliesData['hasMonthlyAnomalies'])
                        Mensili
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analisi settimanale -->
    <div class="card bg-base-300">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
                <x-lucide-calendar class="h-5 w-5" />
                Analisi Settimanale
            </h2>

            <hr>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Settimana</th>
                            <th>Ore Previste</th>
                            <th>Ore Effettive</th>
                            <th>Differenza</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anomaliesData['weeklyData'] as $week)
                            <tr
                                class="{{ $week['limit_exceeded'] || $week['has_shortage'] || $week['has_excess'] ? 'bg-error/20' : '' }}">
                                <td class="font-medium">
                                    {{ $week['week_start'] }} - {{ $week['week_end'] }}
                                </td>
                                <td>{{ number_format($week['expected_hours'], 1) }}h</td>
                                <td>{{ number_format($week['actual_hours'], 1) }}h</td>
                                <td>
                                    <span
                                        class="font-medium {{ $week['difference'] >= 0 ? 'text-warning' : 'text-error' }}">
                                        {{ $week['difference'] >= 0 ? '+' : '' }}{{ number_format($week['difference'], 1) }}h
                                    </span>
                                </td>
                                <td>
                                    @if ($week['limit_exceeded'])
                                        <div class="badge badge-error gap-1">
                                            <x-lucide-ban class="h-3 w-3" />
                                            Limite superato (>40h)
                                        </div>
                                    @elseif($week['has_excess'])
                                        <div class="badge badge-warning gap-1">
                                            <x-lucide-alert-triangle class="h-3 w-3" />
                                            Ore in eccesso
                                        </div>
                                    @elseif($week['has_shortage'])
                                        <div class="badge badge-error gap-1">
                                            <x-lucide-x-circle class="h-3 w-3" />
                                            Ore mancanti
                                        </div>
                                    @else
                                        <div class="badge badge-success gap-1">
                                            <x-lucide-check-circle class="h-3 w-3" />
                                            Regolare
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dettaglio presenze -->
    <div class="card bg-base-300">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
                <x-lucide-clock class="h-5 w-5" />
                Dettaglio Presenze
            </h2>

            <hr>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Entrata</th>
                            <th>Uscita</th>
                            <th>Ore</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anomaliesData['attendances']->sortBy('date') as $attendance)
                            <tr>
                                <td>
                                    <div class="font-medium">
                                        {{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</div>
                                    <div class="text-xs opacity-75">
                                        {{ \Carbon\Carbon::parse($attendance->date)->locale('it')->isoFormat('dddd') }}
                                    </div>
                                </td>
                                <td>{{ $attendance->attendanceType->name }}</td>
                                <td>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-' }}
                                </td>
                                <td>{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-' }}
                                </td>
                                <td>
                                    @if ($attendance->time_in && $attendance->time_out)
                                        {{ number_format(\Carbon\Carbon::parse($attendance->time_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->time_out)) / 60, 1) }}h
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dettaglio permessi/ferie -->
    @if ($anomaliesData['timeOffRequests']->count() > 0)
        <div class="card bg-base-300">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <x-lucide-sun class="h-5 w-5" />
                    Permessi e Ferie
                </h2>

                <hr>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Inizio</th>
                                <th>Fine</th>
                                <th>Ore</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($anomaliesData['timeOffRequests']->sortBy('date_from') as $timeOff)
                                <tr>
                                    <td class="font-medium">{{ $timeOff->type->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($timeOff->date_from)->format('d/m/Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($timeOff->date_to)->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format(\Carbon\Carbon::parse($timeOff->date_from)->diffInMinutes(\Carbon\Carbon::parse($timeOff->date_to)) / 60, 1) }}h
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Azioni -->
    <div class="flex gap-4">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline">
            <x-lucide-arrow-left class="h-4 w-4" />
            Torna ai Dati Utente
        </a>
        <a href="{{ route('users.export-anomalie', $user) }}?mese={{ $mese }}&anno={{ $anno }}"
            class="btn btn-primary">
            <x-lucide-download class="h-4 w-4" />
            Scarica Report PDF
        </a>
    </div>

</x-layouts.app>
