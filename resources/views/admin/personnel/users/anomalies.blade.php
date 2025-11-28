<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl flex items-center gap-2">
            <x-lucide-alert-triangle class="h-8 w-8 text-warning" />
            {{ __('anomalies.title', ['name' => $user->name]) }}
        </h1>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline">
            <x-lucide-arrow-left class="h-4 w-4" />
            {{ __('anomalies.back_to_user') }}
        </a>
    </div>

    <hr>

    <!-- Informazioni periodo -->
    <div class="alert alert-warning">
        <x-lucide-alert-triangle class="h-6 w-6" />
        <div>
            <h3 class="font-bold">{{ __('anomalies.period.title') }}</h3>
            <div class="text-xs">{{ $primoGiorno->format('d/m/Y') }} - {{ $ultimoGiorno->format('d/m/Y') }}
                ({{ $mese }} {{ $anno }})</div>
        </div>
    </div>

    <!-- Riepilogo generale -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card bg-primary text-primary-content">
            <div class="card-body">
                <h2 class="card-title text-sm">{{ __('anomalies.summary.expected_hours') }}</h2>
                <div class="text-3xl font-bold">{{ number_format($anomaliesData['totalExpectedHours'], 1) }}</div>
                <div class="text-xs opacity-75">
                    {{ trans_choice('anomalies.period.working_days', $anomaliesData['workingDaysInPeriod'], ['count' => $anomaliesData['workingDaysInPeriod']]) }}
                </div>
            </div>
        </div>

        <div class="card bg-secondary text-secondary-content">
            <div class="card-body">
                <h2 class="card-title text-sm">{{ __('anomalies.summary.actual_hours') }}</h2>
                <div class="text-3xl font-bold">{{ number_format($anomaliesData['totalActualHours'], 1) }}</div>
            </div>
        </div>

        <div
            class="card {{ $anomaliesData['totalDifference'] >= 0 ? 'bg-warning text-warning-content' : 'bg-error text-error-content' }}">
            <div class="card-body">
                <h2 class="card-title text-sm">{{ __('anomalies.summary.difference') }}</h2>
                <div class="text-3xl font-bold">
                    {{ $anomaliesData['totalDifference'] >= 0 ? '+' : '' }}{{ number_format($anomaliesData['totalDifference'], 1) }}
                </div>
                <div class="text-xs opacity-75">
                    {{ $anomaliesData['totalDifference'] >= 0 ? __('anomalies.summary.difference_positive') : __('anomalies.summary.difference_negative') }}
                </div>
            </div>
        </div>

        <div class="card bg-accent text-accent-content">
            <div class="card-body">
                <h2 class="card-title text-sm">{{ __('anomalies.summary.anomaly_type') }}</h2>
                <div class="text-lg font-bold">
                    @if ($anomaliesData['hasWeeklyAnomalies'] && $anomaliesData['hasMonthlyAnomalies'])
                        {{ __('anomalies.summary.weekly_monthly') }}
                    @elseif($anomaliesData['hasWeeklyAnomalies'])
                        {{ __('anomalies.summary.weekly') }}
                    @elseif($anomaliesData['hasMonthlyAnomalies'])
                        {{ __('anomalies.summary.monthly') }}
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
                {{ __('anomalies.weekly.title') }}
            </h2>

            <hr>

            <div class="join join-vertical w-full">
                @foreach ($anomaliesData['weeklyData'] as $week)
                    @php
                        $weekStart = \Carbon\Carbon::createFromFormat('d/m/Y', $week['week_start'])->startOfDay();
                        $weekEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $week['week_end'])->endOfDay();

                        $weekAttendances = $anomaliesData['attendances']
                            ->filter(function ($attendance) use ($weekStart, $weekEnd) {
                                $attendanceDate = \Carbon\Carbon::parse($attendance->date);

                                return $attendanceDate->between($weekStart, $weekEnd);
                            })
                            ->sortBy('date');

                        $weekTimeOffs = $anomaliesData['timeOffRequests']
                            ->filter(function ($timeOff) use ($weekStart, $weekEnd) {
                                $timeOffStart = \Carbon\Carbon::parse($timeOff->date_from);
                                $timeOffEnd = \Carbon\Carbon::parse($timeOff->date_to);

                                return $timeOffStart->between($weekStart, $weekEnd) ||
                                    $timeOffEnd->between($weekStart, $weekEnd) ||
                                    ($timeOffStart->lte($weekStart) && $timeOffEnd->gte($weekEnd));
                            })
                            ->sortBy('date_from');

                        $differenceClass = $week['difference'] >= 0 ? 'text-warning' : 'text-error';
                        $differenceBackground = $week['difference'] >= 0
                            ? 'bg-warning text-warning-content'
                            : 'bg-error text-error-content';

                        if ($week['limit_exceeded']) {
                            $statusLabel = __('anomalies.weekly.status.limit_exceeded');
                            $statusClasses = 'badge badge-error gap-1';
                            $statusIcon = 'lucide-ban';
                        } elseif ($week['has_excess']) {
                            $statusLabel = __('anomalies.weekly.status.excess');
                            $statusClasses = 'badge badge-warning gap-1';
                            $statusIcon = 'lucide-alert-triangle';
                        } elseif ($week['has_shortage']) {
                            $statusLabel = __('anomalies.weekly.status.shortage');
                            $statusClasses = 'badge badge-error gap-1';
                            $statusIcon = 'lucide-x-circle';
                        } else {
                            $statusLabel = __('anomalies.weekly.status.regular');
                            $statusClasses = 'badge badge-success gap-1';
                            $statusIcon = 'lucide-check-circle';
                        }
                    @endphp

                    <div class="collapse collapse-arrow join-item border border-base-300 bg-base-100">
                        <input type="checkbox" {{ $loop->first ? 'checked' : '' }} />
                        <div class="collapse-title flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="font-medium text-base">
                                    {{ $week['week_start'] }} - {{ $week['week_end'] }}
                                </div>
                                <div class="text-sm">
                                    {{ __('anomalies.weekly.actual_hours_prefix') }}
                                    {{ number_format($week['actual_hours'], 1) }}h
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-semibold {{ $differenceClass }}">
                                    {{ $week['difference'] >= 0 ? '+' : '' }}{{ number_format($week['difference'], 1) }}h
                                </span>
                                <div class="{{ $statusClasses }}">
                                    @switch($statusIcon)
                                        @case('lucide-ban')
                                            <x-lucide-ban class="h-3 w-3" />
                                        @break

                                        @case('lucide-alert-triangle')
                                            <x-lucide-alert-triangle class="h-3 w-3" />
                                        @break

                                        @case('lucide-x-circle')
                                            <x-lucide-x-circle class="h-3 w-3" />
                                        @break

                                        @default
                                            <x-lucide-check-circle class="h-3 w-3" />
                                    @endswitch
                                    {{ $statusLabel }}
                                </div>
                            </div>
                        </div>
                        <div class="collapse-content flex flex-col gap-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="rounded-lg bg-primary text-primary-content p-4 shadow-sm">
                                    <p class="text-xs uppercase opacity-90">{{ __('anomalies.weekly.expected_hours') }}</p>
                                    <p class="text-2xl font-semibold">{{ number_format($week['expected_hours'], 1) }}h
                                    </p>
                                </div>
                                <div class="rounded-lg bg-secondary text-secondary-content p-4 shadow-sm">
                                    <p class="text-xs uppercase opacity-90">{{ __('anomalies.weekly.actual_hours') }}</p>
                                    <p class="text-2xl font-semibold">{{ number_format($week['actual_hours'], 1) }}h</p>
                                </div>
                                <div class="rounded-lg p-4 shadow-sm {{ $differenceBackground }}">
                                    <p class="text-xs uppercase opacity-90">{{ __('anomalies.weekly.difference') }}</p>
                                    <p class="text-2xl font-semibold">
                                        {{ $week['difference'] >= 0 ? '+' : '' }}{{ number_format($week['difference'], 1) }}h
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center gap-2 text-sm font-semibold uppercase">
                                    <x-lucide-clock class="h-4 w-4" />
                                    {{ __('anomalies.weekly.attendance_title') }}
                                </div>
                                <div class="mt-2 overflow-x-auto">
                                    @if ($weekAttendances->isNotEmpty())
                                        <table class="table table-zebra">
                                            <thead>
                                                <tr class="text-xs uppercase">
                                                    <th>{{ __('anomalies.weekly.table.date') }}</th>
                                                    <th>{{ __('anomalies.weekly.table.type') }}</th>
                                                    <th>{{ __('anomalies.weekly.table.time_in') }}</th>
                                                    <th>{{ __('anomalies.weekly.table.time_out') }}</th>
                                                    <th>{{ __('anomalies.weekly.table.hours') }}</th>
                                                    <th class="text-right">{{ __('anomalies.weekly.table.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($weekAttendances as $attendance)
                                                    <tr>
                                                        <td>
                                                            <div class="font-medium">
                                                                {{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                                                            </div>
                                                            <div class="text-xs opacity-75">
                                                                {{ \Carbon\Carbon::parse($attendance->date)->locale('it')->isoFormat('dddd') }}
                                                            </div>
                                                        </td>
                                                        <td>{{ $attendance->attendanceType->name }}</td>
                                                        <td>
                                                            {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-' }}
                                                        </td>
                                                        <td>
                                                            {{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-' }}
                                                        </td>
                                                        <td>
                                                            @if ($attendance->time_in && $attendance->time_out)
                                                                {{ number_format(\Carbon\Carbon::parse($attendance->time_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->time_out)) / 60, 1) }}h
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="{{ route('admin.attendances.edit', $attendance) }}"
                                                                class="btn btn-secondary btn-xs btn-square"
                                                                title="{{ __('anomalies.actions.edit_attendance_title') }}"
                                                                aria-label="{{ __('anomalies.actions.edit_attendance_aria', ['date' => \Carbon\Carbon::parse($attendance->date)->format('d/m/Y')]) }}">
                                                                <x-lucide-pencil class="h-4 w-4" />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-sm opacity-75">{{ __('anomalies.weekly.attendance_empty') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center gap-2 text-sm font-semibold uppercase">
                                    <x-lucide-sun class="h-4 w-4" />
                                    {{ __('anomalies.weekly.time_off_title') }}
                                </div>
                                <div class="mt-2 overflow-x-auto">
                                    @if ($weekTimeOffs->isNotEmpty())
                                        <table class="table table-zebra">
                                            <thead>
                                                <tr class="text-xs uppercase">
                                                    <th>{{ __('anomalies.time_off.table.type') }}</th>
                                                    <th>{{ __('anomalies.time_off.table.start') }}</th>
                                                    <th>{{ __('anomalies.time_off.table.end') }}</th>
                                                    <th>{{ __('anomalies.time_off.table.hours') }}</th>
                                                    <th class="text-right">{{ __('anomalies.time_off.table.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($weekTimeOffs as $timeOff)
                                                    @php
                                                        $timeOffStart = \Carbon\Carbon::parse($timeOff->date_from);
                                                        $timeOffEnd = \Carbon\Carbon::parse($timeOff->date_to);
                                                        $effectiveStart = $timeOffStart->lt($weekStart)
                                                            ? $weekStart
                                                            : $timeOffStart;
                                                        $effectiveEnd = $timeOffEnd->gt($weekEnd)
                                                            ? $weekEnd
                                                            : $timeOffEnd;
                                                        $hours = $effectiveStart->diffInMinutes($effectiveEnd) / 60;
                                                    @endphp
                                                    <tr>
                                                        <td class="font-medium">{{ $timeOff->type->name }}</td>
                                                        <td>{{ $effectiveStart->format('d/m/Y H:i') }}</td>
                                                        <td>{{ $effectiveEnd->format('d/m/Y H:i') }}</td>
                                                        <td>{{ number_format($hours, 1) }}h</td>
                                                        <td class="text-right">
                                                            <a href="{{ route('admin.time-off.edit', ['time_off_request' => $timeOff->batch_id]) }}"
                                                                class="btn btn-secondary btn-xs btn-square"
                                                                title="{{ __('anomalies.actions.edit_time_off_title') }}"
                                                                aria-label="{{ __('anomalies.actions.edit_time_off_aria', ['date' => $effectiveStart->format('d/m/Y H:i')]) }}">
                                                                <x-lucide-pencil class="h-4 w-4" />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                    <p class="text-sm opacity-75">{{ __('anomalies.weekly.time_off_empty') }}</p>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <h3 class="font-semibold">Straordinari</h3>
                                @php
                                    $weekOvertimes = $anomaliesData['overtimeRequests']->filter(function ($ot) use ($weekStart, $weekEnd) {
                                        $otDate = \Carbon\Carbon::parse($ot->date);

                                        return $otDate->between($weekStart, $weekEnd);
                                    });
                                @endphp
                                @if ($weekOvertimes->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="table table-xs">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Tipo</th>
                                                    <th>Inizio</th>
                                                    <th>Fine</th>
                                                    <th class="text-right">Ore</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($weekOvertimes->sortBy('date') as $overtime)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }}</td>
                                                        <td>{{ $overtime->overtimeType->name ?? 'Straordinario' }}</td>
                                                        <td>{{ $overtime->time_in ? \Carbon\Carbon::parse($overtime->time_in)->format('H:i') : '-' }}</td>
                                                        <td>{{ $overtime->time_out ? \Carbon\Carbon::parse($overtime->time_out)->format('H:i') : '-' }}</td>
                                                        <td class="text-right">
                                                            {{ number_format($overtime->hours ?? (\Carbon\Carbon::parse($overtime->time_in)->diffInMinutes(\Carbon\Carbon::parse($overtime->time_out)) / 60), 1) }}h
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm opacity-75">Nessuno straordinario registrato.</p>
                                @endif
                            </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dettaglio permessi/ferie -->
    @if ($anomaliesData['timeOffRequests']->count() > 0)
        <div class="card bg-base-300">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <x-lucide-sun class="h-5 w-5" />
                    {{ __('anomalies.time_off.title') }}
                </h2>

                <hr>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>{{ __('anomalies.time_off.table.type') }}</th>
                                <th>{{ __('anomalies.time_off.table.start') }}</th>
                                <th>{{ __('anomalies.time_off.table.end') }}</th>
                                <th>{{ __('anomalies.time_off.table.hours') }}</th>
                                <th class="text-right">{{ __('anomalies.time_off.table.actions') }}</th>
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
                                    <td class="text-right">
                                        <a href="{{ route('admin.time-off.edit', ['time_off_request' => $timeOff->batch_id]) }}"
                                            class="btn btn-ghost btn-xs btn-square"
                                            title="{{ __('anomalies.actions.edit_time_off_title') }}"
                                            aria-label="{{ __('anomalies.actions.edit_time_off_aria', ['date' => \Carbon\Carbon::parse($timeOff->date_from)->format('d/m/Y H:i')]) }}">
                                            <x-lucide-pencil class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($anomaliesData['overtimeRequests']->count() > 0)
        <div class="card bg-base-300">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <x-lucide-clock-3 class="h-5 w-5" />
                    Straordinari
                </h2>

                <hr>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Data/Ora Inizio</th>
                                <th>Data/Ora Fine</th>
                                <th class="text-right">Ore</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($anomaliesData['overtimeRequests']->sortBy('date') as $overtime)
                                <tr>
                                    <td class="font-medium">{{ $overtime->overtimeType->name ?? 'Straordinario' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }} {{ $overtime->time_in ? \Carbon\Carbon::parse($overtime->time_in)->format('H:i') : '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }} {{ $overtime->time_out ? \Carbon\Carbon::parse($overtime->time_out)->format('H:i') : '-' }}</td>
                                    <td class="text-right">
                                        {{ number_format($overtime->hours ?? (\Carbon\Carbon::parse($overtime->time_in)->diffInMinutes(\Carbon\Carbon::parse($overtime->time_out)) / 60), 1) }}h
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
            {{ __('anomalies.back_to_user') }}
        </a>
        <a href="{{ route('users.export-anomalie', $user) }}?mese={{ $mese }}&anno={{ $anno }}"
            class="btn btn-primary">
            <x-lucide-download class="h-4 w-4" />
            {{ __('anomalies.actions.download_report') }}
        </a>
    </div>

</x-layouts.app>
