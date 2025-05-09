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
                            <button class="btn btn-primary" onclick="openModal({{ $user->id }})">
                                <x-lucide-file-text class="w-4 h-4" />
                            </button>

                            <!-- Modal -->
                            <div id="modal-{{ $user->id }}" class="modal">
                                <div class="modal-box">
                                    <h2 class="text-xl mb-4">Scegli mese ed anno</h2>
                                    <form method="GET" action="{{ route('users.export-cedolino', $user->id) }}">

                                        <fieldset class="fieldset">
                                            <legend class="fieldset-legend">Mese</legend>
                                            <select id="month-{{ $user->id }}" name="mese"
                                                class="select select-bordered">
                                                @foreach (range(1, 12) as $month)
                                                    <option
                                                        value="{{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}">
                                                        {{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                        <fieldset class="fieldset mb-4">
                                            <legend class="fieldset-legend">Anno</legend>
                                            <select id="year-{{ $user->id }}" name="anno"
                                                class="select select-bordered">
                                                @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $year)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                        <div class="modal-action">
                                            <button type="button" class="btn btn-secondary"
                                                onclick="closeModal({{ $user->id }})">Annulla</button>
                                            <button type="submit" class="btn btn-primary">Esporta</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <script>
                                function openModal(userId) {
                                    document.getElementById(`modal-${userId}`).classList.add('modal-open');
                                }

                                function closeModal(userId) {
                                    document.getElementById(`modal-${userId}`).classList.remove('modal-open');
                                }
                            </script>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

</x-layouts.app>
