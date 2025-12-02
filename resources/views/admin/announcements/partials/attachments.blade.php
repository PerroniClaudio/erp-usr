@php
    $selectedAttachments = $selectedAttachments ?? collect([]);
@endphp

@vite('resources/js/announcement_attachments.js')

<div class="card bg-base-200">
    <div class="card-body">
        <fieldset class="fieldset">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="card-title">Allegati selezionati</h4>
                    <div class="flex items-center gap-2">
                        <span id="attachment-counter" class="text-xs text-base-content/60"></span>
                        <button type="button" id="open-attachment-modal" class="btn btn-secondary btn-sm">
                            <x-lucide-paperclip class="w-4 h-4" />
                            Allega un file
                        </button>
                    </div>
                </div>

                <hr>

                <p class="text-sm text-base-content/70">Aggiungi file già caricati oppure carica un nuovo documento
                    protocollato.</p>

                <div id="selected-attachments" class="flex flex-col gap-2">
                    @forelse ($selectedAttachments as $attachment)
                        <div class="flex items-center justify-between gap-3 p-2 bg-base-300 rounded"
                            data-attachment-id="{{ $attachment->id }}">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $attachment->name }}</span>
                                <span class="text-xs text-base-content/60">
                                    {{ $attachment->mime_type }}
                                    @if ($attachment->file_size)
                                        • {{ $attachment->humanFileSize() }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('files.download', $attachment) }}" class="btn btn-ghost btn-xs"
                                    title="Scarica allegato">
                                    <x-lucide-download class="w-4 h-4" />
                                </a>
                                <button type="button" class="btn btn-ghost btn-xs remove-attachment"
                                    data-attachment-id="{{ $attachment->id }}" title="Rimuovi">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </div>
                            <input type="hidden" name="attachment_file_ids[]" value="{{ $attachment->id }}">
                        </div>
                    @empty
                        <p class="text-sm text-base-content/60" data-empty-attachments>
                            Nessun allegato selezionato.
                        </p>
                    @endforelse
                </div>
            </div>
        </fieldset>
    </div>
</div>

<dialog class="modal" id="attachments-modal">
    <div class="modal-box w-11/12 max-w-4xl">
        <div class="flex items-start justify-between mb-2">
            <div>
                <h3 class="text-lg font-bold">Gestisci allegati</h3>
                <p class="text-sm text-base-content/70">Scegli se selezionare un file esistente o caricarne uno nuovo.
                </p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" id="close-attachment-modal">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            <button type="button" class="btn btn-primary btn-sm attachment-mode-button"
                data-target-panel="search-panel">
                Seleziona file dai documenti
            </button>
            <button type="button" class="btn btn-ghost btn-sm attachment-mode-button" data-target-panel="upload-panel">
                Carica nuovo file
            </button>
        </div>

        <div class="space-y-4">
            <div id="search-panel" class="p-4 bg-base-200 rounded">
                <h4 class="font-semibold">Cerca tra i documenti</h4>
                <p class="text-xs text-base-content/60 mt-1">Digita il nome o il numero di protocollo del file e
                    aggiungilo all'annuncio.</p>
                <div class="flex gap-2 mt-3">
                    <input type="search" id="attachment-search-input" class="input input-bordered w-full"
                        placeholder="Cerca file">
                    <button type="button" id="attachment-search-button" class="btn btn-secondary"
                        data-search-url="{{ route('admin.files.search') }}">
                        Cerca
                    </button>
                </div>
                <div id="attachment-search-results" class="mt-3 space-y-2 text-sm text-base-content/80"></div>
            </div>

            <div id="upload-panel" class="p-4 bg-base-200 rounded hidden">
                <h4 class="font-semibold">Carica nuovo allegato</h4>
                <p class="text-xs text-base-content/60 mt-1">Il file verrà salvato in <code>attachments</code> e
                    protocollato automaticamente.</p>
                <div class="grid grid-cols-1 gap-2 mt-3">
                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text">Settore</span>
                        </div>
                        <select name="new_attachment_file_object_sector_id" class="select select-bordered w-full">
                            <option value="">Seleziona settore</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}" @selected(old('new_attachment_file_object_sector_id') == $sector->id)>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text">Protocollo</span>
                        </div>
                        <select name="new_attachment_protocol_id" class="select select-bordered w-full">
                            <option value="">Seleziona protocollo</option>
                            @foreach ($protocols as $protocol)
                                <option value="{{ $protocol->id }}" @selected(old('new_attachment_protocol_id') == $protocol->id)>
                                    {{ $protocol->name }} ({{ strtoupper($protocol->acronym) }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text">File allegato</span>
                        </div>
                        <input type="file" name="new_attachment" class="file-input file-input-bordered w-full" />
                    </label>

                    <label class="form-control w-full">
                        <div class="label">
                            <span class="label-text">Data validità</span>
                        </div>
                        <input type="date" name="new_attachment_valid_at" class="input input-bordered w-full"
                            value="{{ old('new_attachment_valid_at', now()->format('Y-m-d')) }}">
                    </label>

                    <label class="cursor-pointer label">
                        <input type="checkbox" name="new_attachment_is_public" value="1"
                            class="toggle toggle-primary"
                            {{ old('new_attachment_is_public', true) ? 'checked' : '' }}>
                        <span class="label-text">Rendi allegato scaricabile da tutti gli utenti</span>
                    </label>
                </div>
                <p class="text-xs text-base-content/60 mt-2">Lascia vuoti questi campi se non vuoi caricare un nuovo
                    file.</p>
            </div>
        </div>
        <div id="pending-existing-feedback" class="mt-4 p-3 bg-base-200 rounded">
            <div class="flex items-center gap-2 mb-2">
                <x-lucide-info class="w-4 h-4" />
                <span class="text-sm font-semibold">File in coda per l'aggiunta</span>
            </div>
            <div class="text-sm text-base-content/70" data-pending-placeholder>Nessun file selezionato dal catalogo.
            </div>
            <div class="flex flex-wrap gap-2" data-pending-list></div>
        </div>
        <div class="modal-action">
            <button type="button" class="btn btn-ghost" id="cancel-attachments">Annulla</button>
            <button type="button" class="btn btn-primary" id="confirm-attachments">
                Conferma allegati
            </button>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<template id="tpl-selected-attachment">
    <div class="flex items-center justify-between gap-3 p-2 bg-base-300 rounded" data-attachment-id="">
        <div class="flex items-center gap-2">
            <span data-role="icon" class="inline-flex items-center justify-center h-5 w-5">
                <x-lucide-file class="w-4 h-4" data-icon-type="default" />
                <x-lucide-image class="w-4 h-4 hidden" data-icon-type="image" />
                <x-lucide-clapperboard class="w-4 h-4 hidden" data-icon-type="video" />
                <x-lucide-music class="w-4 h-4 hidden" data-icon-type="audio" />
                <x-lucide-file-text class="w-4 h-4 hidden" data-icon-type="document" />
                <x-lucide-archive class="w-4 h-4 hidden" data-icon-type="archive" />
            </span>
            <div class="flex flex-col">
                <span class="font-medium" data-role="name"></span>
                <span class="text-xs text-base-content/60" data-role="meta"></span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="#" class="btn btn-ghost btn-xs" data-role="download" title="Scarica allegato">
                <x-lucide-download class="w-4 h-4" />
            </a>
            <button type="button" class="btn btn-ghost btn-xs remove-attachment" data-role="remove-btn"
                data-attachment-id="" title="Rimuovi">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
        <input type="hidden" name="attachment_file_ids[]" value="" data-role="hidden-id">
    </div>
</template>

<template id="tpl-search-result">
    <div class="flex items-center justify-between p-2 bg-base-300 rounded">
        <div class="flex items-center gap-2">
            <span data-role="icon" class="inline-flex items-center justify-center h-5 w-5">
                <x-lucide-file class="w-4 h-4" data-icon-type="default" />
                <x-lucide-image class="w-4 h-4 hidden" data-icon-type="image" />
                <x-lucide-clapperboard class="w-4 h-4 hidden" data-icon-type="video" />
                <x-lucide-music class="w-4 h-4 hidden" data-icon-type="audio" />
                <x-lucide-file-text class="w-4 h-4 hidden" data-icon-type="document" />
                <x-lucide-archive class="w-4 h-4 hidden" data-icon-type="archive" />
            </span>
            <div class="flex flex-col">
                <span class="font-medium" data-role="name"></span>
                <span class="text-xs text-base-content/60" data-role="meta"></span>
            </div>
        </div>
        <button type="button" class="btn btn-primary btn-xs" data-role="action">Aggiungi</button>
    </div>
</template>

<template id="tpl-upload-preview">
    <div class="flex items-center justify-between gap-3 p-2 bg-base-300 rounded" data-upload-preview="true">
        <div class="flex items-center gap-2">
            <span data-role="icon" class="inline-flex items-center justify-center h-5 w-5">
                <x-lucide-file class="w-4 h-4" data-icon-type="default" />
                <x-lucide-image class="w-4 h-4 hidden" data-icon-type="image" />
                <x-lucide-clapperboard class="w-4 h-4 hidden" data-icon-type="video" />
                <x-lucide-music class="w-4 h-4 hidden" data-icon-type="audio" />
                <x-lucide-file-text class="w-4 h-4 hidden" data-icon-type="document" />
                <x-lucide-archive class="w-4 h-4 hidden" data-icon-type="archive" />
            </span>
            <div class="flex flex-col">
                <span class="font-medium" data-role="name"></span>
                <span class="text-xs text-base-content/60" data-role="meta"></span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="btn btn-ghost btn-xs remove-upload-preview" data-role="remove-btn">
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>

<template id="tpl-pending-badge">
    <div class="border border-primary text-primary-content rounded bg-primary/30 flex items-center gap-2 p-2"
        data-pending-id="">
        <span data-role="icon" class="inline-flex items-center justify-center h-4 w-4">
            <x-lucide-file class="w-4 h-4" data-icon-type="default" />
            <x-lucide-image class="w-4 h-4 hidden" data-icon-type="image" />
            <x-lucide-clapperboard class="w-4 h-4 hidden" data-icon-type="video" />
            <x-lucide-music class="w-4 h-4 hidden" data-icon-type="audio" />
            <x-lucide-file-text class="w-4 h-4 hidden" data-icon-type="document" />
            <x-lucide-archive class="w-4 h-4 hidden" data-icon-type="archive" />
        </span>
        <span data-role="name"></span>
        <button type="button" class="btn btn-primary btn-xs" data-role="remove">&times;</button>
    </div>
</template>
