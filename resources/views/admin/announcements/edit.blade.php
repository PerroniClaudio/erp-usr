<x-layouts.app>
    <x-layouts.header title="Modifica Annuncio">
        <x-slot:actions>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                <x-lucide-arrow-left class="h-4 w-4" />
                Torna alla lista
            </a>
        </x-slot:actions>
    </x-layouts.header>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-300">
        <div class="card-body">
            <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Titolo</legend>
                        <input type="text" name="title" class="input w-full"
                            value="{{ old('title', $announcement->title) }}" required>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Contenuto</legend>
                        <textarea name="content" class="textarea w-full h-32" required>{{ old('content', $announcement->content) }}</textarea>
                    </fieldset>

                    @include('admin.announcements.partials.attachments')

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Stato</legend>
                        <label class="cursor-pointer label">
                            <span class="label-text">Annuncio attivo</span>
                            <input type="checkbox" name="is_active" value="1" class="checkbox"
                                {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                        </label>
                    </fieldset>

                    <div class="flex gap-2 justify-end">
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                            Annulla
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-save class="h-4 w-4" />
                            Aggiorna Annuncio
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
