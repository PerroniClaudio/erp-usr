@props(['pendingTimeOffRequests'])

<div class="card bg-base-200 col-span-3">
    <div class="card-body">
        <div class="card-title">{{ __('time_off_requests.pending_requests') }}</div>
        <hr>
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
                @unless ($pendingTimeOffRequests->count())
                    <tr>
                        <td colspan="3" class="text-center">
                            {{ __('time_off_requests.no_pending_requests') }}
                        </td>
                    </tr>
                @endunless

                @foreach ($pendingTimeOffRequests as $request)
                    <tr>
                        <td>{{ $request['title'] }}</td>
                        <td>{{ $request['type'] }}</td>
                        <td>{{ $request['start_end'] }}</td>
                        <td>
                            <a href="{{ route('admin.time-off.edit', $request['batch']) }}"
                                class="btn btn-primary btn-sm">{{ __('time_off_requests.handle') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
