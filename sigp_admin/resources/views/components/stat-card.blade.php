@props([
    'title',
    'value',
    'icon',
    'color' => 'blue',
    'trend' => null,
    'trendDirection' => 'up'
])

@php
$colorClasses = [
    'blue' => 'bg-blue-500 text-blue-600 bg-blue-50',
    'green' => 'bg-green-500 text-green-600 bg-green-50',
    'yellow' => 'bg-yellow-500 text-yellow-600 bg-yellow-50',
    'red' => 'bg-red-500 text-red-600 bg-red-50',
    'purple' => 'bg-purple-500 text-purple-600 bg-purple-50',
    'indigo' => 'bg-indigo-500 text-indigo-600 bg-indigo-50',
    'gray' => 'bg-gray-500 text-gray-600 bg-gray-50',
];

$bgColor = explode(' ', $colorClasses[$color])[0];
$textColor = explode(' ', $colorClasses[$color])[1];
$lightBg = explode(' ', $colorClasses[$color])[2];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
            @if($trend)
            <div class="flex items-center mt-2">
                <i class="fas fa-arrow-{{ $trendDirection === 'up' ? 'up' : 'down' }} text-sm {{ $trendDirection === 'up' ? 'text-green-500' : 'text-red-500' }} mr-1"></i>
                <span class="text-sm {{ $trendDirection === 'up' ? 'text-green-600' : 'text-red-600' }}">{{ $trend }}</span>
                <span class="text-sm text-gray-500 ml-1">vs mês anterior</span>
            </div>
            @endif
        </div>
        <div class="w-12 h-12 {{ $lightBg }} rounded-lg flex items-center justify-center">
            <i class="{{ $icon }} text-xl {{ $textColor }}"></i>
        </div>
    </div>
</div>