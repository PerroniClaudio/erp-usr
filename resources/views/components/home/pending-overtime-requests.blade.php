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
            <ul class="divide-y divide-base-300">
                @foreach ($pendingOvertimeRequests as $req)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <span class="font-semibold">{{ $req->user->name }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $req->date }}
                                {{ $req->time_in }}-{{ $req->time_out }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $req->overtimeType->name ?? '' }}</span>
                        </div>
                        <a href="{{ route('admin.overtime-requests.show', $req) }}"
                            class="btn btn-xs btn-primary">Gestisci</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
