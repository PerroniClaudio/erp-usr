<x-layouts.app>

    @vite('resources/js/file_explorer.js')

    <div class="flex justify-between items-center flex-wrap gap-2">
        <h1 class="text-4xl">{{ __('files.files_index_title') }}</h1>
        <div class="flex gap-2 flex-wrap justify-end">
            <a class="btn btn-ghost" href="{{ route('admin.files.search') }}">
                <x-lucide-search class="w-4 h-4" />
                {{ __('files.files_search_button') }}
            </a>
            <button class="btn btn-outline" onclick="create_folder_modal.showModal()">
                <x-lucide-folder-plus class="w-4 h-4" />
                {{ __('files.files_create_folder_button') }}
            </button>
            <button class="btn btn-primary" id="upload-file-button" onclick="upload_file_modal.showModal()">
                <x-lucide-upload class="w-4 h-4" />
                {{ __('files.files_upload_button') }}
            </button>
        </div>
    </div>
    <hr>

    <div class="card bg-base-300">
        <div class="card-body">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li>
                        <a href="{{ route('admin.files.index') }}">
                            Pagina principale
                        </a>
                    </li>
                    @foreach ($folder_steps as $index => $step)
                        @if ($step == '/' || $step == '')
                            @continue
                        @endif

                        @php
                            $displayName = $folder_aliases[$step] ?? $step;
                            if ($index > 0 && $folder_steps[$index - 1] === 'personnel') {
                                $displayName = $personnel_names[$step] ?? $displayName;
                            }
                        @endphp

                        @if ($index + 1 === count($folder_steps))
                            <li>
                                <span>{{ $displayName }}</span>
                            </li>
                        @else
                            <li>
                                <a
                                    href="{{ route('admin.files.folder', ['hash' => base64_encode(implode('/', array_slice($folder_steps, 0, $index + 1))) ?: 'root']) }}">
                                    {{ $displayName }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <table class="table">
                <thead>
                    <th class="w-1/2">{{ __('files.files_table_header_name') }}</th>
                    <th>{{ __('files.files_table_header_sector') }}</th>
                    <th>{{ __('files.files_table_header_protocol') }}</th>
                    <th>{{ __('files.files_table_header_valid_at') }}</th>
                    <th>{{ __('files.files_table_header_type') }}</th>
                    <th>{{ __('files.files_table_header_size') }}</th>
                    <th>{{ __('files.files_table_header_uploaded_at') }}</th>
                    <th>{{ __('files.files_table_header_actions') }}</th>
                </thead>
                <tbody>
                    @unless ($folders->isNotEmpty() || $files->isNotEmpty())
                        <tr>
                            <td colspan="8" class="text-center text-gray-500">
                                {{ __('files.files_table_no_files_or_folders') }}
                            </td>
                        </tr>
                    @else
                        @foreach ($folders as $folder)
                            <tr class="hover:bg-base-200 cursor-pointer"
                                data-folder-hash="{{ base64_encode($folder->relative_path) }}">
                                <td colspan="8">
                                    <div class="flex items-center">
                                        <x-lucide-folder class="w-6 h-6 mr-2 text-yellow-400" />
                                        <span>{{ $folder->name ?? basename($folder->storage_path) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($files as $file)
                            <tr class="hover:bg-base-200">
                                <td>
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
                                </td>
                                @php
                                    $currentUser = auth()->user();
                                    $canDownload = $currentUser->hasRole('admin') || $file->is_public || $file->user_id === $currentUser->id;
                                    $canDelete = $currentUser->hasRole('admin') || $file->user_id === $currentUser->id;
                                @endphp
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
                                        <span class="text-sm text-base-content/60">{{ __('files.files_table_no_protocol') }}</span>
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
                                            <ul tabindex="0" class="dropdown-content menu menu-sm bg-base-200 rounded-box shadow">
                                                @if ($canDownload)
                                                    <li>
                                                        <a href="{{ route('files.download', $file) }}" class="flex items-center gap-2">
                                                            <x-lucide-download class="w-4 h-4" />
                                                            <span>{{ __('files.files_download_button') }}</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('files.versions', $file) }}" class="flex items-center gap-2">
                                                            <x-lucide-history class="w-4 h-4" />
                                                            <span>{{ __('files.files_versioning_button') }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                @if ($canDelete)
                                                    <li>
                                                        <form action="{{ route('files.destroy', $file) }}" method="POST"
                                                            onsubmit="return confirm('{{ __('files.files_delete_confirm') }}')" class="w-full">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="flex items-center gap-2 text-error w-full text-left">
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
                        @endforeach
                    @endunless

                </tbody>
            </table>
        </div>
    </div>

    <dialog class="modal" id="upload_file_modal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('files.files_upload_button') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>


            <form action="{{ route('admin.files.upload') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col gap-2 w-full">
                @csrf
                <input type="hidden" name="current_folder_path" value="{{ implode('/', $folder_steps) }}">

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.files_upload_modal_select_sector') }}</legend>
                    <select class="select w-full" name="file_object_sector_id"
                        value="{{ old('file_object_sector_id') }}" required>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}"
                                {{ old('file_object_sector_id') == $sector->id ? 'selected' : '' }}>
                                {{ $sector->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.files_upload_modal_select_protocol') }}</legend>
                    <select class="select w-full" name="protocol_id" value="{{ old('protocol_id') }}" required>
                        @foreach ($protocols as $protocol)
                            <option value="{{ $protocol->id }}" {{ old('protocol_id') == $protocol->id ? 'selected' : '' }}>
                                {{ $protocol->name }} ({{ strtoupper($protocol->acronym) }})
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.files_upload_modal_select_file') }}</legend>
                    <input type="file" name="file" class="file-input w-full" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.files_upload_valid_at_label') }}</legend>
                    <input type="date" name="valid_at" class="input w-full"
                        value="{{ old('valid_at', now()->format('Y-m-d')) }}">
                </fieldset>

                <fieldset class="fieldset">
                    <label class="cursor-pointer label">
                        <input type="checkbox" name="is_public" value="1" class="toggle toggle-primary"
                            {{ old('is_public', true) ? 'checked' : '' }}>
                        <span class="label-text">{{ __('files.files_upload_is_public_label') }}</span>
                    </label>
                </fieldset>

                <div class="flex gap-2 justify-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <x-lucide-upload class="h-4 w-4" />
                        {{ __('files.files_upload_button') }}
                    </button>
                </div>
            </form>

        </div>
    </dialog>

    <dialog class="modal" id="create_folder_modal">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">{{ __('files.files_create_folder_title') }}</h3>
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <form action="{{ route('admin.files.create-folder') }}" method="POST" class="flex flex-col gap-3 w-full">
                @csrf
                <input type="hidden" name="current_folder_path" value="{{ implode('/', $folder_steps) }}">

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.files_create_folder_name_label') }}</legend>
                    <input type="text" name="folder_name" class="input w-full" required
                        placeholder="{{ __('files.files_create_folder_name_label') }}">
                </fieldset>

                <div class="flex gap-2 justify-end mt-2">
                    <button type="submit" class="btn btn-primary">
                        <x-lucide-folder-plus class="h-4 w-4" />
                        {{ __('files.files_create_folder_button') }}
                    </button>
                </div>
            </form>

        </div>
    </dialog>

</x-layouts.app>
