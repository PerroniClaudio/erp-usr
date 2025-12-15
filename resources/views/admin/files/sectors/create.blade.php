<x-layouts.app>

    @vite(['resources/js/color-picker.js', 'resources/js/acronym-generator.js'])

    <x-layouts.header :title="__('files.sectors_new_button')">
        <x-slot:actions>
            <a href="{{ route('admin.sectors.index') }}" class="btn btn-secondary">
                <x-lucide-arrow-left class="w-4 h-4" />{{ __('files.sectors_back_to_sectors') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>
    <div class="card bg-base-300 lg:w-1/2">
        <div class="card-body">
            <form action="{{ route('admin.sectors.store') }}" method="POST">
                @csrf

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.sectors_new_name_label') }}</legend>
                    <input type="text" name="name" class="input w-full" value="{{ old('name') }}" required
                        placeholder="{{ __('files.sectors_new_name_label') }}">
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.sectors_new_description_label') }}</legend>
                    <textarea name="description" class="textarea w-full" required
                        placeholder="{{ __('files.sectors_new_description_label') }}">{{ old('description') }}</textarea>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.sectors_new_acronym_label') }}</legend>
                    <input type="text" name="acronym" class="input w-full" value="{{ old('acronym') }}" required
                        placeholder="{{ __('files.sectors_new_acronym_label') }}" maxlength="4">
                </fieldset>


                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('files.sectors_new_color_label') }}</legend>
                    <div class="color-picker-wrapper flex items-center gap-3">
                        <div
                            class="color-preview w-10 h-10 rounded-full border border-base-content/20 cursor-pointer shadow-sm transition-transform hover:scale-105">
                        </div>
                        <span class="color-hex font-mono text-base cursor-pointer hover:opacity-80"></span>
                        <input type="color" name="color" class="sr-only" value="{{ old('color', '#E73028') }}"
                            required>
                    </div>
                </fieldset>

                <div class="flex gap-2 justify-end mt-4">

                    <button type="submit" class="btn btn-primary">
                        <x-lucide-save class="h-4 w-4" />
                        {{ __('files.sectors_new_save_button') }}
                    </button>
                </div>


            </form>
        </div>
    </div>

</x-layouts.app>
