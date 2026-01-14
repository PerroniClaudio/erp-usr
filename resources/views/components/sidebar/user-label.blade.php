@props(['user'])

<li class="border-t-2 border-base-100 pt-2 mt-auto menu-user-label h-16">
    <div class="flex items-center gap-2">
        <div
            class="bg-primary w-10 aspect-square rounded-full flex flex-col justify-center items-center text-primary-content font-bold">
            @php
                $initials = collect(explode(' ', $user->name))
                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                    ->take(2)
                    ->implode('');

                echo $initials[0] . ($initials[1] ?? '');
            @endphp
        </div>
        <span class="leading-tight">
            {{ $user->name }}
        </span>
    </div>
</li>
