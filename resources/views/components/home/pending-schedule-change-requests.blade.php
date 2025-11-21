@props(['pendingScheduleRequests'])

<div class="card bg-base-200 col-span-3">
    <div class="card-body">
        <h3 class="card-title text-lg mb-2 flex items-center gap-2">
            <x-lucide-calendar-clock class="h-5 w-5 text-primary" />
            {{ __('personnel.user_schedule_request_admin_quicklook_title') }}
        </h3>
        <hr>
        @if ($pendingScheduleRequests->isEmpty())
            <div class="text-sm text-base-content/60">
                {{ __('personnel.user_schedule_request_admin_quicklook_empty') }}
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('personnel.users') }}</th>
                        <th>{{ __('personnel.user_schedule_request_admin_week_label') }}</th>
                        <th class="text-right">{{ __('personnel.user_schedule_request_admin_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingScheduleRequests as $request)
                        <tr>
                            <td>{{ $request->user->name }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($request->week_start)->format('d/m/Y') }}
                                -
                                {{ \Carbon\Carbon::parse($request->week_end ?? $request->week_start)->format('d/m/Y') }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.user-schedule-requests.show', $request) }}"
                                    class="btn btn-primary btn-xs">
                                    {{ __('personnel.user_schedule_request_admin_handle') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
