@props([
    'label' => null,
    'type' => 'text',
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'success' => false,
    'hint' => null,
    'id' => null,
    'autocomplete' => null,
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'step' => null,
])

@php
    $fieldId = $id ?? $name ?? 'field-' . uniqid();
    $hasError = !empty($error);
    $inputClasses = 'w-full px-4 py-2.5 border rounded-lg bg-white text-ink text-sm transition-all duration-150';
    $inputClasses .= ' border-line focus:border-navy focus:ring-4 focus:ring-yellow/20 focus:outline-none';
    $inputClasses .= $hasError ? ' border-red-600 focus:border-red-600 focus:ring-red-200' : '';
    $inputClasses .= $success && !$hasError ? ' border-green-600 focus:border-green-600 focus:ring-green-200' : '';
    $inputClasses .= $disabled ? ' bg-paper cursor-not-allowed' : '';
    $inputClasses .= $readonly ? ' bg-paper' : '';
@endphp

<div class="form-field">
    @if($label)
        <label for="{{ $fieldId }}" class="block text-navy font-semibold text-sm mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-600 ml-1" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $fieldId }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            class="{{ $inputClasses }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $maxlength ? 'maxlength="' . $maxlength . '"' : '' }}
            {{ $min !== null ? 'min="' . $min . '"' : '' }}
            {{ $max !== null ? 'max="' . $max . '"' : '' }}
            {{ $step !== null ? 'step="' . $step . '"' : '' }}
            {{ $autocomplete ? 'autocomplete="' . $autocomplete . '"' : '' }}
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            aria-describedby="{{ $hasError ? $fieldId . '-error' : ($hint ? $fieldId . '-hint' : '') }}"
            autocomplete="{{ $autocomplete ?? 'off' }}"
        >

        @if($hasError || $success)
            <div class="validation-icon absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center" aria-hidden="true">
                @if($hasError)
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                @else
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @endif
            </div>
        @endif
    </div>

    @if($hasError)
        <p id="{{ $fieldId }}-error" class="field-error visible text-red-600 text-xs mt-2 font-medium" role="alert">{{ $error }}</p>
    @elseif($hint)
        <p id="{{ $fieldId }}-hint" class="text-muted text-xs mt-1.5">{{ $hint }}</p>
    @endif
</div>