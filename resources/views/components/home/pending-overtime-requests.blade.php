<div class="card bg-base-200">
    <div class="card-body">
        <h2 class="card-title text-lg mb-2">
            <x-lucide-clock class="h-5 w-5 text-primary inline-block" />
            Richieste straordinario in sospeso
        </h2>
        <hr>
        @if ($pendingOvertimeRequests->isEmpty())
            <div class="text-sm text-gray-500">Nessuna richiesta di straordinario in sospeso.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Utente</th>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Tipo</th>
                            <th class="text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingOvertimeRequests as $req)
                            <tr>
                                <td class="font-semibold">{{ $req->user->name }}</td>
                                <td>{{ $req->date }}</td>
                                <td>{{ $req->time_in }} - {{ $req->time_out }}</td>
                                <td>{{ $req->overtimeType->name ?? '' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.overtime-requests.show', $req) }}"
                                        class="btn btn-xs btn-primary">Gestisci</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pendingOvertimeRequests->links() }}
            </div>
        @endif
    </div>
</div>
