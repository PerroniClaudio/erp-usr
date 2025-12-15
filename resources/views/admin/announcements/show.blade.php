<x-layouts.app>
    <x-layouts.header title="Dettagli Annuncio">
        <x-slot:actions>
            <div class="flex gap-2">
                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-warning">
                    <x-lucide-edit class="h-4 w-4" />
                    Modifica
                </a>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    Torna alla lista
                </a>
            </div>
        </x-slot:actions>
    </x-layouts.header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="card bg-base-300">
                <div class="card-body">
                    <h2 class="card-title">{{ $announcement->title }}</h2>
                    <div class="divider"></div>
                    <div class="prose max-w-none">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    @if ($announcement->attachments->isNotEmpty())
                        <div class="divider"></div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-lg">Allegati</h3>
                            <span class="text-xs text-base-content/60">{{ $announcement->attachments->count() }} file</span>
                        </div>
                        <ul class="space-y-2">
                            @foreach ($announcement->attachments as $attachment)
                                <li class="flex items-center justify-between gap-3 p-2 bg-base-200 rounded">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $attachment->name }}</span>
                                        <span class="text-xs text-base-content/60">
                                            {{ $attachment->mime_type }}
                                            @if ($attachment->file_size)
                                                • {{ $attachment->humanFileSize() }}
                                            @endif
                                        </span>
                                    </div>
                                    <a href="{{ route('files.download', $attachment) }}" class="btn btn-outline btn-sm">
                                        <x-lucide-download class="w-4 h-4" />
                                        Scarica
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card bg-base-300">
                <div class="card-body">
                    <h3 class="card-title">Informazioni</h3>
                    <div class="divider"></div>

                    <div class="space-y-2">
                        <div>
                            <strong>Stato:</strong>
                            @if ($announcement->is_active)
                                <span class="badge badge-success">Attivo</span>
                            @else
                                <span class="badge badge-error">Disattivo</span>
                            @endif
                        </div>

                        <div>
                            <strong>Creato da:</strong>
                            {{ $announcement->creator->name }}
                        </div>

                        <div>
                            <strong>Data creazione:</strong>
                            {{ $announcement->created_at->format('d/m/Y H:i') }}
                        </div>

                        <div>
                            <strong>Ultima modifica:</strong>
                            {{ $announcement->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-300 mt-4">
                <div class="card-body">
                    <h3 class="card-title">Statistiche Visualizzazioni</h3>
                    <div class="divider"></div>

                    <div class="space-y-2">
                        <div>
                            <strong>Utenti totali:</strong>
                            {{ $totalUsers }}
                        </div>

                        <div>
                            <strong>Hanno visualizzato:</strong>
                            {{ $viewedCount }}
                        </div>

                        <div>
                            <strong>Non hanno visualizzato:</strong>
                            {{ $totalUsers - $viewedCount }}
                        </div>

                        @if ($totalUsers > 0)
                            <div class="mt-4">
                                <div class="text-sm">Percentuale visualizzazione:</div>
                                <div class="progress progress-primary">
                                    <div class="progress-value"
                                        style="width: {{ ($viewedCount / $totalUsers) * 100 }}%"></div>
                                </div>
                                <div class="text-center text-sm">{{ round(($viewedCount / $totalUsers) * 100, 1) }}%
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
