@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false,
    'ariaLabel' => null,
])

@php
    $variants = [
        'primary' => 'bg-navy text-yellow border-navy hover:bg-navy-2',
        'secondary' => 'bg-yellow text-navy border-yellow hover:bg-yellow-dark',
        'outline' => 'bg-transparent text-navy border-navy hover:bg-navy/5',
        'ghost' => 'bg-transparent text-navy hover:bg-navy/5',
        'danger' => 'bg-red-600 text-white border-red-600 hover:bg-red-700',
        'success' => 'bg-green-600 text-white border-green-600 hover:bg-green-700',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ];

    $variantClasses = $variants[$variant] ?? $variants['primary'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $widthClass = $fullWidth ? 'w-full' : '';

    $classes = "inline-flex items-center justify-center gap-2 font-semibold rounded-lg border transition-all duration-150 focus:outline-none focus-visible:ring-3 focus-visible:ring-yellow-400/50 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed {$variantClasses} {$sizeClasses} {$widthClass}";
@endphp

<button
    type="{{ $type }}"
    class="{{ $classes }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $loading ? 'aria-busy="true"' : '' }}
    {{ $ariaLabel ? "aria-label=\"{$ariaLabel}\"" : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{-- Loading Spinner --}}
    @if($loading)
        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif

    {{-- Icon (left) --}}
    @if($icon && $iconPosition === 'left' && !$loading)
        {{ $icon }}
    @endif

    {{-- Button Text --}}
    <span class="{{ $loading ? 'opacity-0' : '' }} transition-opacity duration-150">
        {{ $slot }}
    </span>

    {{-- Icon (right) --}}
    @if($icon && $iconPosition === 'right' && !$loading)
        {{ $icon }}
    @endif
</button>