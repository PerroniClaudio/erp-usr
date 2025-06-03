<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.users') }}</h1>
    </div>

    <hr>

    <div class="overflow-x-auto">
        <table class="table">
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
                        <td>
                            <button class="btn btn-primary"
                                onclick="openModal('cedolino', {{ $user->id }}, '{{ $user->name }}')">
                                <x-lucide-file-text class="w-4 h-4" />
                                Cedolino paghe
                            </button>
                            <button class="btn btn-primary"
                                onclick="openModal('presenze', {{ $user->id }}, '{{ $user->name }}')">
                                <x-lucide-file-text class="w-4 h-4" />
                                Presenze
                            </button>
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

        <script>
            function openModal(type, userId, userName) {
                if (type === 'cedolino') {
                    const form = document.getElementById('form-cedolino');
                    form.action = "{{ route('users.export-cedolino', ':id') }}".replace(':id', userId);
                    document.getElementById('modal-cedolino').showModal();
                } else if (type === 'presenze') {
                    const form = document.getElementById('form-presenze');
                    form.action = "{{ route('users.export-presenze', ':id') }}".replace(':id', userId);
                    document.getElementById('modal-presenze').showModal();
                }
            }

            function closeModal(type) {
                if (type === 'cedolino') {
                    document.getElementById('modal-cedolino').close();
                } else if (type === 'presenze') {
                    document.getElementById('modal-presenze').close();
                }
            }
        </script>
</x-layouts.app>
