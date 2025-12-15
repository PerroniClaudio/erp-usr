<x-layouts.app>
    <x-layouts.header :title="__('personnel.users')" />

    <div class="overflow-x-auto">
        <table class="table h-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('personnel.users_name') }}</th>
                    <th>{{ __('personnel.users_email') }}</th>
                    <th>{{ __('personnel.users_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="flex items-center gap-2">
                                <button
                                    class="btn btn-primary flex items-center gap-2"
                                    type="button"
                                    popovertarget="popover-{{ $user->id }}"
                                    style="anchor-name:--anchor-{{ $user->id }}"
                                >
                                    <x-lucide-download class="w-4 h-4" />
                                    {{ __('personnel.users_export') ?? 'Esporta' }}
                                    <x-lucide-chevron-down class="w-4 h-4" />
                                </button>
                                <ul
                                    class="dropdown menu rounded-box bg-base-300 shadow-sm"
                                    popover
                                    id="popover-{{ $user->id }}"
                                    style="position-anchor:--anchor-{{ $user->id }}"
                                >
                                    <li>
                                        <a href="#" onclick="openModal('cedolino', {{ $user->id }}, '{{ $user->name }}')">
                                            <x-lucide-file-text class="w-4 h-4 inline-block mr-2" />
                                            {{ __('personnel.users_export_cedolino') ?? 'Cedolino paghe' }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="openModal('presenze', {{ $user->id }}, '{{ $user->name }}')">
                                            <x-lucide-file-text class="w-4 h-4 inline-block mr-2" />
                                            {{ __('personnel.users_export_presenze') ?? 'Presenze' }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="openModal('nota_spese', {{ $user->id }}, '{{ $user->name }}')">
                                            <x-lucide-file-text class="w-4 h-4 inline-block mr-2" />
                                            {{ __('business_trips.export_nota_spese') }}
                                        </a>
                                    </li>
                                </ul>
                            <a href="{{ route('users.edit', ['user' => $user]) }}" class="btn btn-primary">
                                <x-lucide-pencil class="w-4 h-4" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Cedolino Modal (single instance) -->
        <dialog id="modal-cedolino" class="modal">
            <div class="modal-box">
                <h1 class="text-3xl mb-4">Esporta cedolino paghe</h1>
                <hr>
                <form id="form-cedolino" method="GET">
                    <fieldset class="fieldset mb-4">
                        <legend class="fieldset-legend">{{ __('personnel.users_cedolino_year') }}</legend>
                        <select id="cedolino-year" name="anno" class="select select-bordered">
                            @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $year)
                                <option value="{{ $year }}" @if ($year == \Carbon\Carbon::now()->year) selected @endif>
                                    {{ $year }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_cedolino_month') }}</legend>
                        <select id="cedolino-month" name="mese" class="select select-bordered">
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
                        <button type="button" class="btn btn-secondary" onclick="closeModal('cedolino')">
                            {{ __('personnel.users_cedolino_cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('personnel.users_cedolino_export') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

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

        <!-- Nota spese Modal (single instance) -->
        <dialog id="modal-nota-spese" class="modal">
            <div class="modal-box">
                <h1 class="text-3xl mb-4">Esporta nota spese</h1>
                <hr>
                <form id="form-nota-spese" method="GET">
                    <fieldset class="fieldset mb-4">
                        <legend class="fieldset-legend">{{ __('personnel.users_cedolino_year') }}</legend>
                        <select id="nota-spese-year" name="year" class="select select-bordered">
                            @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $year)
                                <option value="{{ $year }}" @if ($year == \Carbon\Carbon::now()->year) selected @endif>
                                    {{ $year }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.users_cedolino_month') }}</legend>
                        <select id="nota-spese-month" name="month" class="select select-bordered">
                            @foreach (range(1, 12) as $month)
                                <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                                    @if ($month == \Carbon\Carbon::now()->subMonth()->month) selected @endif>
                                    {{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                    <div class="modal-action">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('nota_spese')">
                            {{ __('personnel.users_cedolino_cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('personnel.users_cedolino_export') }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <script>
            function openModal(type, userId, userName) {
                if (type === 'cedolino') {
                    const form = document.getElementById('form-cedolino');
                    form.action = "{{ url('admin/personnel/users') }}" + '/' + userId + '/export-cedolino';
                    document.getElementById('modal-cedolino').showModal();
                } else if (type === 'presenze') {
                    const form = document.getElementById('form-presenze');
                    form.action = "{{ url('admin/personnel/users') }}" + '/' + userId + '/export-presenze';
                    document.getElementById('modal-presenze').showModal();
                } else if (type === 'nota_spese') {
                    const form = document.getElementById('form-nota-spese');
                    form.action = "{{ url('admin/personnel/users') }}" + '/' + userId + '/export-nota-spese';
                    document.getElementById('modal-nota-spese').showModal();
                }
            }

            function closeModal(type) {
                if (type === 'cedolino') {
                    document.getElementById('modal-cedolino').close();
                } else if (type === 'presenze') {
                    document.getElementById('modal-presenze').close();
                } else if (type === 'nota_spese') {
                    document.getElementById('modal-nota-spese').close();
                }
            }

            // Disabilita il bottone submit durante la richiesta GET
            document.addEventListener('DOMContentLoaded', function() {
                const forms = [
                    document.getElementById('form-cedolino'),
                    document.getElementById('form-presenze')
                    , document.getElementById('form-nota-spese')
                ];
                forms.forEach(function(form) {
                    if (!form) return;
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
                        }, 10000); // 3 secondi, regola se necessario
                    });
                });
            });
        </script>
</x-layouts.app>
