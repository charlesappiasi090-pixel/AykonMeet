@props([
    'id' => null,
    'title' => '',
    'size' => 'md',
    'closeable' => true,
    'persistent' => false,
])

@php
    $modalId = $id ?? 'modal-' . uniqid();
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $modalId }}-title"
    aria-hidden="true"
    style="display: none;"
>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div
            class="relative w-full {{ $sizeClass }} bg-white rounded-2xl shadow-xl transform transition-all"
            role="document"
        >
            @if($title)
            <div class="flex items-center justify-between p-4 border-b border-line">
                <h2 id="{{ $modalId }}-title" class="text-navy font-semibold text-lg">{{ $title }}</h2>
                @if($closeable)
                    <button
                        type="button"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-muted hover:bg-paper hover:text-navy transition-colors"
                        aria-label="Close"
                        onclick="closeModal('{{ $modalId }}')"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 p-4 border-t border-line bg-paper/50 rounded-b-2xl">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function openModal('{{ $modalId }}') {
    const modal = document.getElementById('{{ $modalId }}');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        modal.querySelector('[role="document"]').focus();
        document.dispatchEvent(new CustomEvent('modal-open', { detail: { modalId: '{{ $modalId }}' }}));
    }
}

function closeModal('{{ $modalId }}') {
    const modal = document.getElementById('{{ $modalId }}');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        document.dispatchEvent(new CustomEvent('modal-close', { detail: { modalId: '{{ $modalId }}' }}));
    }
}
</script>