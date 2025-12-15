@props(['title'])

<div {{ $attributes->class(['flex', 'items-center', 'justify-between', 'min-h-16', 'gap-4', 'hr-border']) }}>
    <h1 class="text-4xl">{{ $title }}</h1>

    @isset($actions)
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
