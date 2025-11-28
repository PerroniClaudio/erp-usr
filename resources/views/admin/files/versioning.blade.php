<x-layouts.app>

    <div class="flex justify-between items-center flex-wrap gap-2 mb-4">
        <div class="flex flex-col">
            <h1 class="text-3xl">{{ __('files.files_versioning_title', ['name' => $file->name]) }}</h1>
            <p class="text-base text-base-content/70">{{ __('files.files_versioning_subtitle') }}</p>
        </div>
        <a class="btn btn-ghost" href="{{ url()->previous() }}">
            <x-lucide-arrow-left class="w-4 h-4" />
            {{ __('files.files_back_button') }}
        </a>
    </div>

    <div class="card bg-base-300 mb-6">
        <div class="card-body space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="badge badge-outline">{{ __('files.files_versioning_total_versions', ['count' => $versions->count()]) }}</span>
                </div>
                @if ($canUpload)
                    <form action="{{ route('files.versions.upload', $file) }}" method="POST" enctype="multipart/form-data"
                        class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="file" class="file-input file-input-bordered file-input-sm" required>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <x-lucide-upload class="w-4 h-4" />
                            {{ __('files.files_versioning_upload_button') }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <th>{{ __('files.files_table_header_actions') }}</th>
                        <th>{{ __('files.files_table_header_name') }}</th>
                        <th>{{ __('files.files_versioning_version') }}</th>
                        <th>{{ __('files.files_table_header_size') }}</th>
                        <th>{{ __('files.files_table_header_uploaded_at') }}</th>
                        <th>{{ __('files.files_versioning_uploaded_by') }}</th>
                    </thead>
                    <tbody>
                        @forelse ($versions as $version)
                            @php
                                $currentUser = auth()->user();
                                $canDownload = $currentUser->hasRole('admin') || $version->is_public || $version->user_id === $currentUser->id;
                            @endphp
                            <tr>
                                <td>
                                    @if ($canDownload)
                                        <a href="{{ route('files.download', $version) }}" class="btn btn-ghost btn-xs">
                                            <x-lucide-download class="w-4 h-4" />
                                            {{ __('files.files_download_button') }}
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $version->name }}</td>
                                <td>
                                    <span class="badge badge-outline">v{{ $version->version }}</span>
                                </td>
                                <td>{{ $version->humanFileSize() }}</td>
                                <td>{{ $version->created_at->locale('it')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                <td>{{ $version->user?->name ?? __('files.files_versioning_unknown_user') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/60">
                                    {{ __('files.files_versioning_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>
