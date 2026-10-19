@props([
    'type' => 'spinner',
    'size' => 'md',
    'label' => null,
    'overlay' => false,
    'message' => 'Loading...',
    'progress' => null,
])

@php
    $sizes = [
        'sm' => 'w-4 h-4 border-2',
        'md' => 'w-6 h-6 border-2',
        'lg' => 'w-8 h-8 border-3',
        'xl' => 'w-10 h-10 border-4',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($overlay)
<div class="fixed inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-50" role="status" aria-live="polite" aria-label="{{ $message }}">
    <div class="bg-white p-8 rounded-2xl shadow-xl flex flex-col items-center gap-4">
        <div class="relative" role="status" aria-label="{{ $message }}">
            <svg class="{{ $sizeClass }} text-yellow" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        @if($label)
            <p class="text-navy font-medium text-sm">{{ $label }}</p>
        @endif
    </div>
@else
<div class="inline-flex items-center gap-2" role="status" aria-live="polite" aria-label="{{ $message }}">
    <svg class="{{ $sizeClass }} text-yellow" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    @if($label)
        <span class="text-muted text-sm">{{ $label }}</span>
    @endif
</div>
@endif