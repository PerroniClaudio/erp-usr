<x-layouts.app>
    <x-layouts.header :title="__('time_off_requests.time_off_requests')">
        <x-slot:actions>
            <a href="{{ route('time-off-requests.create') }}" class="btn btn-primary">
                {{ __('time_off_requests.new_request') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="card bg-base-300 lg:col-span-1" data-time-off-balance
            data-balance-url="{{ route('time-off-balance') }}">
            <div class="card-body">
                <h3 class="card-title">Saldo ferie e ROL</h3>
                <hr>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col items-center gap-2">
                        <div class="badge badge-primary">Ferie residue</div>
                        <p class="text-2xl font-bold" data-balance="ferie"
                            data-template=":hours ore">—</p>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="badge badge-secondary">ROL residui</div>
                        <p class="text-2xl font-bold" data-balance="rol"
                            data-template=":hours ore">—</p>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 hidden" data-balance-warning>
                    <span>
                        Dati del monte ore non disponibili per l'anno selezionato. Il saldo usa il residuo di
                        fine anno e potrebbe non essere accurato.
                    </span>
                </div>
            </div>
        </div>
        <div class="lg:col-span-3">
            <div id="calendar" class="max-w-full"></div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/time_off_requests.js')
        @vite('resources/js/time_off_balance.js')
    @endpush
</x-layouts.app>
