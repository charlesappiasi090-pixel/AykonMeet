@props([
    'type' => 'info',
    'title' => null,
    'message' => '',
    'dismissible' => true,
    'autoClose' => 5000,
    'action' => null,
    'actionText' => null,
])

@php
    $types = [
        'success' => ['icon' => '✓', 'iconClass' => 'bg-green-100 text-green-700', 'title' => $title ?? 'Success'],
        'error' => ['icon' => '✕', 'iconClass' => 'bg-red-100 text-red-700', 'title' => $title ?? 'Error'],
        'warning' => ['icon' => '⚠', 'iconClass' => 'bg-yellow-100 text-yellow-700', 'title' => $title ?? 'Warning'],
        'info' => ['icon' => 'ℹ', 'iconClass' => 'bg-blue-100 text-blue-700', 'title' => $title ?? 'Information'],
    ];
    $t = $types[$type] ?? $types['info'];
@endphp

<div
    class="ayk-toast ayk-toast-{{ $type }} flex items-start gap-3 p-4 bg-white border border-line rounded-xl shadow-lg min-w-[320px] max-w-[420px] animate-in"
    role="alert"
    aria-live="polite"
    aria-atomic="true"
    data-auto-close="{{ $autoClose }}"
    data-dismissible="{{ $dismissible ? 'true' : 'false' }}"
>
    <div class="ayk-toast-icon flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-lg {{ $t['iconClass'] }}">
        {{ $t['icon'] }}
    </div>

    <div class="flex-1 min-w-0">
        @if($t['title'])
            <div class="ayk-toast-title font-semibold text-navy text-sm mb-1">{{ $t['title'] }}</div>
        @endif
        <div class="ayk-toast-message text-muted text-sm leading-relaxed">{{ $message }}</div>
    </div>

    @if($dismissible)
        <button
            type="button"
            class="ayk-toast-close w-7 h-7 rounded-lg flex items-center justify-center text-muted hover:bg-paper hover:text-navy transition-colors"
            aria-label="Dismiss"
            onclick="this.closest('.ayk-toast').remove()"
        >
            ✕
        </button>
    @endif

    @if($action && $actionText)
        <button
            type="button"
            class="ayk-toast-action mt-2 text-sm font-semibold text-navy hover:underline"
            onclick="{{ $action }}"
        >
            {{ $actionText }}
        </button>
    @endif
</div>