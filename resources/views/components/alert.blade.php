@props(['type' => 'info'])
@php
    $map = [
        'error' => 'bg-red-50 text-red-700 ring-red-200',
        'success' => 'bg-green-50 text-green-700 ring-green-200',
        'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-200',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-200',
    ];
    $iconPath = [
        'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M12 22a10 10 0 110-20 10 10 0 010 20z',
        'success' => 'M9 12l2 2 4-4M12 22a10 10 0 110-20 10 10 0 010 20z',
        'warning' => 'M12 9v4m0 4h.01M12 22a10 10 0 110-20 10 10 0 010 20z',
        'info' => 'M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z',
    ][$type] ?? 'M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z';
@endphp
<div {{ $attributes->merge(['class' => 'rounded-2xl ring-1 p-4 ' . $map[$type]]) }}>
    <div class="flex gap-3">
        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="{{ $iconPath }}" />
        </svg>
        <div class="flex-1">{{ $slot }}</div>
    </div>
</div>