{{-- resources/views/components/invertible-toggle.blade.php --}}

@props([
    'includeIcon',
    'excludeIcon',
    'includeToggleLabel' => null,
    'excludeToggleLabel' => null,
    'includeColor' => 'success',
    'excludeColor' => 'danger',
    'excludePath' => null,
])

@php
    $includeToggleLabel = $includeToggleLabel ?? __('filament-invertible-fields::messages.toggle_to_only');
    $excludeToggleLabel = $excludeToggleLabel ?? __('filament-invertible-fields::messages.toggle_to_except');
    $statePath = $excludePath instanceof \Closure ? $excludePath() : $excludePath;
    $includeClass = 'text-' . $includeColor . '-500';
    $excludeClass = 'text-' . $excludeColor . '-500';
@endphp

<span
    x-data="{ exclude: $wire.entangle(@js($statePath)) }"
    x-on:click.stop="exclude = !exclude"
    x-tooltip="{
        content: () => exclude ? '{{ addslashes($includeToggleLabel) }}' : '{{ addslashes($excludeToggleLabel) }}',
        placement: 'top',
    }"
    class="inline-flex items-center transition"
    :class="exclude ? '{{ $excludeClass }}' : '{{ $includeClass }}'">
    <span x-cloak x-show="!exclude" aria-hidden="true">
        <x-filament::icon :icon="$includeIcon" class="h-5 w-5" />
    </span>
    <span x-cloak x-show="exclude" aria-hidden="true">
        <x-filament::icon :icon="$excludeIcon" class="h-5 w-5" />
    </span>
</span>