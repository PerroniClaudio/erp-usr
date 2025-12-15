@props(['title'])

<div
    {{ $attributes->class([
        'flex',
        'flex-col',
        'items-start',
        'gap-3',
        'hr-border',
        'sm:flex-row',
        'sm:items-center',
        'sm:justify-between',
        'sm:min-h-16',
        'py-4',
        'md:p-0',
    ]) }}>
    <h1 class="text-3xl sm:text-4xl">{{ $title }}</h1>

    @isset($actions)
        <div
            class="flex w-full flex-col gap-2 [&>*]:w-full sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:[&>*]:w-auto">
            {{ $actions }}
        </div>
    @endisset
</div>
