@props([
    'type' => 'default',
    'label' => '',
    'name' => null,
    'options' => [],
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'id' => null,
    'placeholder' => 'Select an option',
    'searchable' => false,
    'multiple' => false,
])

@php
    $fieldId = $id ?? $name ?? 'select-' . uniqid();
    $hasError = !empty($error);
    $selectClasses = 'w-full px-4 py-2.5 border rounded-lg bg-white text-ink text-sm transition-all duration-150 appearance-none';
    $selectClasses .= ' border-line focus:border-navy focus:ring-4 focus:ring-yellow/20 focus:outline-none';
    $selectClasses .= $hasError ? ' border-red-600 focus:border-red-600 focus:ring-red-200' : '';
    $selectClasses .= $disabled ? ' bg-paper cursor-not-allowed' : '';
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
        <select
            name="{{ $name }}"
            id="{{ $fieldId }}"
            class="{{ $selectClasses }} pr-10"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $multiple ? 'multiple' : '' }}
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            aria-describedby="{{ $hasError ? $fieldId . '-error' : ($hint ? $fieldId . '-hint' : '') }}"
        >
            @if(!$required && !$multiple)
                <option value="" disabled selected>{{ $placeholder }}</option>
            @endif
            @foreach($options as $option)
                @php
                    $optValue = is_array($option) ? ($option['value'] ?? $option[0] ?? '') : $option;
                    $optLabel = is_array($option) ? ($option['label'] ?? $option[1] ?? $option) : $option;
                    $isSelected = $multiple ? (is_array($value) && in_array($optValue, $value)) : ((string)$value === (string)$optValue);
                @endphp
                <option value="{{ $optValue }}" @if($isSelected) selected @endif>{{ $optLabel }}</option>
            @endforeach
        </select>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    @if($hasError)
        <p class="field-error visible text-red-600 text-xs mt-2 font-medium" role="alert">{{ $error }}</p>
    @elseif($hint)
        <p class="text-muted text-xs mt-1.5">{{ $hint }}</p>
    @endif
</div>