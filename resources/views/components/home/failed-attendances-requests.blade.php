@props(['failedAttendancesRequests'])

<div class="card bg-base-200 col-span-3">
    <div class="card-body">
        <h3 class="card-title">{{ __('attendances.failed_attendances') }}</h3>
        <hr>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('attendances.name') }}</th>
                    <th>{{ __('attendances.date') }}</th>
                    <th>{{ __('attendances.requested_hours') }}</th>
                    <th>{{ __('attendances.requested_type') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @unless ($failedAttendancesRequests->count())
                    <tr>
                        <td colspan="5" class="text-center">
                            {{ __('attendances.no_failed_attendances') }}
                        </td>
                    </tr>
                @endunless

                @foreach ($failedAttendancesRequests as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                        <td>{{ $attendance->requested_hours }}</td>
                        <td>{{ $attendance->requested_type == 0 ? 'ROL' : 'Ferie' }}</td>
                        <td>
                            <a href="{{ route('admin.failed-attendances.edit', $attendance) }}"
                                class="btn btn-primary btn-sm">
                                {{ __('attendances.handle') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
