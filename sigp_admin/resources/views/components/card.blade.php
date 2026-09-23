@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'actions' => null,
    'padding' => 'p-6'
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200']) }}>
    @if($title || $subtitle || $icon || $actions)
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center">
            @if($icon)
            <div class="mr-3 text-blue-600">
                <i class="{{ $icon }} text-xl"></i>
            </div>
            @endif
            <div>
                @if($title)
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                <p class="text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if($actions)
        <div class="flex items-center space-x-2">
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>