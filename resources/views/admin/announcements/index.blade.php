<x-layouts.app>
    <x-layouts.header title="Gestione Annunci">
        <x-slot:actions>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <x-lucide-plus class="h-4 w-4" />
                Nuovo Annuncio
            </a>
        </x-slot:actions>
    </x-layouts.header>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="table h-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titolo</th>
                    <th>Creato da</th>
                    <th>Stato</th>
                    <th>Data Creazione</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr>
                        <td>{{ $announcement->id }}</td>
                        <td>{{ $announcement->title }}</td>
                        <td>{{ $announcement->creator->name }}</td>
                        <td>
                            @if ($announcement->is_active)
                                <span class="badge badge-success">Attivo</span>
                            @else
                                <span class="badge badge-error">Disattivo</span>
                            @endif
                        </td>
                        <td>{{ $announcement->created_at->format('d/m/Y H:i') }}</td>
                        <td class="flex items-center gap-2">
                            <a href="{{ route('admin.announcements.show', $announcement) }}"
                                class="btn btn-info btn-sm">
                                <x-lucide-eye class="h-4 w-4" />
                                Visualizza
                            </a>
                            <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                class="btn btn-warning btn-sm">
                                <x-lucide-edit class="h-4 w-4" />
                                Modifica
                            </a>
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-error btn-sm"
                                    onclick="return confirm('Sei sicuro di voler eliminare questo annuncio?')">
                                    <x-lucide-trash class="h-4 w-4" />
                                    Elimina
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            Nessun annuncio presente
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($announcements->hasPages())
        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    @endif
</x-layouts.app>
