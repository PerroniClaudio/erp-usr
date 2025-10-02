<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('attendances.attendances') }}</h1>
        <div class="flex items-center gap-2">
            <button type="button" class="btn btn-primary" onclick="openModal('presenze')">
                {{ __('personnel.users_export_presenze_user') }}
            </button>
            <a href="{{ route('attendances.create') }}" class="btn btn-primary">
                {{ __('attendances.new_attendance') }}
            </a>
        </div>
    </div>

    <hr>

    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    <!-- Presenze Modal (single instance) -->
    <dialog id="modal-presenze" class="modal">
        <div class="modal-box">
            <h1 class="text-3xl mb-4">Esporta cedolino presenze</h1>
            <hr>
            <form id="form-presenze" method="GET">
                <fieldset class="fieldset mb-4">
                    <legend class="fieldset-legend">{{ __('personnel.users_cedolino_year') }}</legend>
                    <select id="presenze-year" name="anno" class="select select-bordered">
                        @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $year)
                            <option value="{{ $year }}" @if ($year == \Carbon\Carbon::now()->year) selected @endif>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_cedolino_month') }}</legend>
                    <select id="presenze-month" name="mese" class="select select-bordered">
                        @foreach (range(1, 12) as $month)
                            <option
                                value="{{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}"
                                @if ($month == \Carbon\Carbon::now()->subMonth()->month) selected @endif>
                                {{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>
                <div class="modal-action">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('presenze')">
                        {{ __('personnel.users_cedolino_cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('personnel.users_cedolino_export') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/attedances.js')
        <script>
            function openModal(type) {
                if (type === 'presenze') {
                    const form = document.getElementById('form-presenze');
                    form.action = "{{ route('attendances.export-presenze') }}";
                    document.getElementById('modal-presenze').showModal();
                }
            }

            function closeModal(type) {
                if (type === 'presenze') {
                    document.getElementById('modal-presenze').close();
                }
            }

            // Disabilita il bottone submit durante la richiesta GET
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('form-presenze');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-50', 'pointer-events-none');
                        }
                        // Per GET, lasciamo il comportamento default (redirect/download)
                        // Ma riabilitiamo il bottone dopo un breve timeout in caso di download
                        setTimeout(function() {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'pointer-events-none');
                            }
                        }, 10000); // 10 secondi
                    });
                }
            });
        </script>
    @endpush

</x-layouts.app>
