<x-layouts.app>


    <x-layouts.header :title="__('personnel.user_schedule_request_admin_title')">
        <x-slot:actions>
            <a href="{{ route('admin.sectors.index') }}" class="btn btn-secondary">
                <x-lucide-arrow-left class="w-4 h-4" />{{ __('files.sectors_back_to_sectors') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">{{ __('personnel.user_schedule_request_admin_pending') }}</h2>
                    <span class="badge badge-primary">{{ $pendingRequests->count() }}</span>
                </div>
                <div class="overflow-x-auto mt-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('personnel.users') }}</th>
                                <th>{{ __('personnel.user_schedule_request_admin_week_label') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->user->name }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($request->week_start)->format('d/m/Y') }}
                                        -
                                        {{ \Carbon\Carbon::parse($request->week_end ?? $request->week_start)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.user-schedule-requests.show', $request) }}"
                                            class="btn btn-primary btn-sm">
                                            {{ __('personnel.user_schedule_request_admin_view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/70 py-6">
                                        {{ __('personnel.user_schedule_request_admin_pending_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">{{ __('personnel.user_schedule_request_admin_processed') }}</h2>
                </div>
                <div class="overflow-x-auto mt-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('personnel.users') }}</th>
                                <th>{{ __('personnel.user_schedule_request_admin_week_label') }}</th>
                                <th>{{ __('personnel.user_schedule_request_admin_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($processedRequests as $request)
                                <tr>
                                    <td>{{ $request->user->name }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($request->week_start)->format('d/m/Y') }}
                                        -
                                        {{ \Carbon\Carbon::parse($request->week_end ?? $request->week_start)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @php
                                            $statusKey = match ($request->status) {
                                                \App\Models\UserScheduleChangeRequest::STATUS_APPROVED => 'approved',
                                                \App\Models\UserScheduleChangeRequest::STATUS_DENIED => 'denied',
                                                default => 'pending',
                                            };
                                        @endphp
                                        <span
                                            class="badge {{ $request->status === \App\Models\UserScheduleChangeRequest::STATUS_DENIED ? 'badge-error' : 'badge-success' }}">
                                            {{ __('personnel.user_schedule_request_admin_status_' . $statusKey) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.user-schedule-requests.show', $request) }}"
                                            class="btn btn-ghost btn-sm">
                                            {{ __('personnel.user_schedule_request_admin_view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-base-content/70 py-6">
                                        {{ __('personnel.user_schedule_request_admin_processed_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
