<x-layouts.app>

    <div class="flex justify-between items-start flex-wrap gap-2">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">{{ __('files.files_search_title') }}</h1>
        </div>
        <a class="btn btn-outline" href="{{ route('admin.files.index') }}">
            <x-lucide-folder class="w-4 h-4" />
            {{ __('files.files_index_title') }}
        </a>
    </div>
    <hr>

    <div class="card bg-base-300">
        <div class="card-body space-y-4">
            <form method="GET" action="{{ route('admin.files.search') }}"
                class="flex flex-col gap-3 lg:flex-row lg:items-end">
                <label class="form-control w-full lg:flex-1">
                    <div class="label">
                        <span class="label-text">{{ __('files.files_search_title') }}</span>
                    </div>
                    <div class="relative">
                        <x-lucide-search class="absolute left-3 top-3 h-5 w-5 text-base-content/60" />
                        <input type="search" name="q" value="{{ $query }}" autocomplete="off"
                            placeholder="{{ __('files.files_search_placeholder') }}"
                            class="input input-bordered w-full pl-10" autofocus />
                    </div>
                </label>
                <div class="flex gap-2">
                    @if ($query !== '')
                        <a href="{{ route('admin.files.search') }}" class="btn btn-ghost">
                            <x-lucide-eraser class="w-4 h-4" />
                            {{ __('files.files_search_clear') }}
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <x-lucide-search class="w-4 h-4" />
                        {{ __('files.files_search_submit') }}
                    </button>
                </div>
            </form>

            @if ($results)
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div class="flex flex-col">
                        <span class="text-xl font-semibold">
                            {{ __('files.files_search_results_title', ['query' => $query]) }}
                        </span>
                        <span class="text-sm text-base-content/60">
                            {{ trans_choice('files.files_search_results_count', $results->total(), ['count' => $results->total()]) }}
                        </span>
                    </div>
                    <div class="badge badge-outline">Laravel Scout</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <th class="w-1/3">{{ __('files.files_table_header_name') }}</th>
                            <th>{{ __('files.files_table_header_sector') }}</th>
                            <th>{{ __('files.files_table_header_protocol') }}</th>
                            <th>{{ __('files.files_table_header_valid_at') }}</th>
                            <th>{{ __('files.files_table_header_type') }}</th>
                            <th>{{ __('files.files_table_header_size') }}</th>
                            <th>{{ __('files.files_table_header_uploaded_at') }}</th>
                            <th>{{ __('files.files_table_header_actions') }}</th>
                        </thead>
                        <tbody>
                            @forelse ($results as $file)
                                @php
                                    $baseRoot = app()->environment('local') ? 'dev/files/' : 'files/';
                                    $relativePath = \Illuminate\Support\Str::after($file->storage_path, $baseRoot);
                                    $currentUser = auth()->user();
                                    $canDownload =
                                        $currentUser->hasRole('admin') ||
                                        $file->is_public ||
                                        $file->user_id === $currentUser->id;
                                    $canDelete = $currentUser->hasRole('admin') || $file->user_id === $currentUser->id;
                                @endphp
                                <tr class="hover:bg-base-200">
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center">
                                                @switch($file->mime_type)
                                                    @case(str_starts_with($file->mime_type, 'image/'))
                                                        <x-lucide-image class="w-6 h-6 mr-2 text-blue-400" />
                                                    @break

                                                    @case(str_starts_with($file->mime_type, 'video/'))
                                                        <x-lucide-video class="w-6 h-6 mr-2 text-purple-400" />
                                                    @break

                                                    @case(str_starts_with($file->mime_type, 'audio/'))
                                                        <x-lucide-music class="w-6 h-6 mr-2 text-green-400" />
                                                    @break

                                                    @case($file->mime_type === 'application/pdf')
                                                        <x-lucide-file-text class="w-6 h-6 mr-2 text-red-400" />
                                                    @break

                                                    @case(str_starts_with($file->mime_type, 'application/zip'))
                                                        <x-lucide-archive class="w-6 h-6 mr-2 text-orange-400" />
                                                    @break

                                                    @default
                                                        <x-lucide-file class="w-6 h-6 mr-2 text-gray-400" />
                                                @endswitch
                                                <span>{{ $file->name }}</span>
                                            </div>
                                            <span class="text-xs text-base-content/60">
                                                {{ __('files.files_search_location', ['path' => $relativePath ?: '/']) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge text-white"
                                            style="background-color: {{ $file->sector ? $file->sector->color : 'gray' }}">
                                            {{ $file->sector ? $file->sector->acronym : __('files.files_table_no_sector') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($file->protocol_number)
                                            <div class="badge badge-outline">{{ $file->protocol_number }}</div>
                                        @else
                                            <span
                                                class="text-sm text-base-content/60">{{ __('files.files_table_no_protocol') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $file->valid_at ? $file->valid_at->locale('it')->isoFormat('DD/MM/YYYY') : __('files.files_table_no_valid_date') }}
                                    </td>
                                    <td>{{ $file->mime_type }}</td>
                                    <td>{{ $file->humanFileSize() }}</td>
                                    <td>{{ $file->created_at->locale('it')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                    <td>
                                        @if ($canDownload || $canDelete)
                                            <div class="dropdown dropdown-end">
                                                <button type="button" tabindex="0" class="btn btn-ghost btn-xs">
                                                    <x-lucide-ellipsis-vertical class="w-4 h-4" />
                                                </button>
                                                <ul tabindex="0"
                                                    class="dropdown-content menu menu-sm bg-base-200 rounded-box shadow">
                                                    @if ($canDownload)
                                                        <li>
                                                            <a href="{{ route('files.download', $file) }}"
                                                                class="flex items-center gap-2">
                                                                <x-lucide-download class="w-4 h-4" />
                                                                <span>{{ __('files.files_download_button') }}</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('files.versions', $file) }}"
                                                                class="flex items-center gap-2">
                                                                <x-lucide-history class="w-4 h-4" />
                                                                <span>{{ __('files.files_versioning_button') }}</span>
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($canDelete)
                                                        <li>
                                                            <form action="{{ route('files.destroy', $file) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('{{ __('files.files_delete_confirm') }}')"
                                                                class="w-full">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="flex items-center gap-2 text-error w-full text-left">
                                                                    <x-lucide-trash-2 class="w-4 h-4" />
                                                                    <span>{{ __('files.files_delete_button') }}</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-base-content/60">
                                            {{ __('files.files_search_no_results') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $results->withQueryString()->links() }}
                    </div>
                @else
                    <p class="text-base-content/60">{{ __('files.files_search_empty_state') }}</p>
                @endif
            </div>
        </div>

    </x-layouts.app>
