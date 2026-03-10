@props([
    'as' => 'button',
    'href' => '#',
    'type' => 'button',
    'tooltip' => '',
    'tooltipId' => '',
    'color' => 'blue',
])

@php
    $palette = [
        'blue' => 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50',
        'yellow' => 'border-yellow-200 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50',
        'red' => 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50',
        'gray' => 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
    ];

    $colorClasses = $palette[$color] ?? $palette['blue'];
    $triggerClasses = "inline-flex items-center rounded-lg border p-2 transition {$colorClasses}";
@endphp

@if ($as === 'a')
    <a href="{{ $href }}" data-tooltip-target="{{ $tooltipId }}" {{ $attributes->merge(['class' => $triggerClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" data-tooltip-target="{{ $tooltipId }}" {{ $attributes->merge(['class' => $triggerClasses]) }}>
        {{ $slot }}
    </button>
@endif

<div id="{{ $tooltipId }}" role="tooltip" class="tooltip invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-xs transition-opacity duration-300 dark:bg-gray-700">
    {{ $tooltip }}
    <div class="tooltip-arrow" data-popper-arrow></div>
</div>
