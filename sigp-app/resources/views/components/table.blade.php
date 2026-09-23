@props([
    'headers' => [],
    'searchable' => false,
    'exportable' => false
])

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    @if($searchable || $exportable)
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            @if($searchable)
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" 
                           placeholder="Buscar..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            @endif
            
            @if($exportable)
            <div class="flex items-center space-x-2">
                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            @if(!empty($headers))
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody class="bg-white divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>