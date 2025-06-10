@props(['pendingTimeOffRequests'])

<div class="card bg-base-200 col-span-3">
    <div class="card-body">
        <h3 class="card-title text-lg mb-2">
            <x-lucide-sun class="h-5 w-5 text-primary inline-block" />
            {{ __('time_off_requests.pending_requests') }}
        </h3>
        <hr>
        @if ($pendingTimeOffRequests->isEmpty())
            <div class="text-sm text-gray-500">{{ __('time_off_requests.no_pending_requests') }}</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingTimeOffRequests as $request)
                        <tr>
                            <td>{{ $request['title'] }}</td>
                            <td>{{ $request['type'] }}</td>
                            <td>{{ $request['start_end'] }}</td>
                            <td>
                                <a href="{{ route('admin.time-off.edit', $request['batch']) }}"
                                    class="btn btn-xs btn-primary">{{ __('time_off_requests.handle') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
