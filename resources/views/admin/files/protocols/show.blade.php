<x-layouts.app>
    <x-layouts.header :title="__('files.protocols_edit_protocol')">
        <x-slot:actions>
            <a href="{{ route('admin.protocols.index') }}" class="btn btn-secondary">
                <x-lucide-arrow-left class="w-4 h-4" />{{ __('files.protocols_back_to_protocols') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <form action="{{ route('admin.protocols.update', $protocol) }}" method="POST">
        @csrf
        @method('PUT')

        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('files.protocols_new_name_label') }}</legend>
            <input type="text" name="name" class="input w-full" value="{{ old('name', $protocol->name) }}"
                required placeholder="{{ __('files.protocols_new_name_label') }}">
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('files.protocols_new_acronym_label') }}</legend>
            <input type="text" name="acronym" class="input w-full" value="{{ old('acronym', $protocol->acronym) }}"
                placeholder="{{ __('files.protocols_new_acronym_label') }}" maxlength="10" required>
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('files.protocols_counter_label') }}</legend>
            <input type="number" name="counter" class="input w-full" value="{{ old('counter', $protocol->counter) }}"
                min="1">
            <p class="text-sm text-base-content/70 mt-1">{{ __('files.protocols_counter_helper') }}</p>
        </fieldset>

        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('files.protocols_counter_year_label') }}</legend>
            <input type="number" name="counter_year" class="input w-full"
                value="{{ old('counter_year', $protocol->counter_year ?? now()->year) }}" min="1900" max="3000">
        </fieldset>

        <div class="flex gap-2 justify-end mt-4">
            <button type="submit" class="btn btn-primary">
                <x-lucide-save class="w-4 h-4" />
                {{ __('files.protocols_new_save_button') }}
            </button>
        </div>
    </form>

</x-layouts.app>
