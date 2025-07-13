<x-filament-panels::page>
    <div class="mb-6 p-4 border border-gray-300 rounded-md shadow-sm bg-white">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Master Part Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
            <p><strong>Part Number:</strong> {{ $masterPart->part_number }}</p>
            <p><strong>Part Name:</strong> {{ $masterPart->part_name }}</p>
        </div>
        <p class="mt-1"><strong>Total Calculated Price:</strong>
            <span class="font-bold text-primary-600">
                {{-- Assuming part_price is stored with 2 decimal places as per SQL decimal(10,2) --}}
                IDR {{ number_format($masterPart->part_price, 2, ',', '.') }}
            </span>
        </p>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 mb-3">Sub Parts for: {{ $masterPart->part_name }}</h3>

    {{-- This will render the table defined in your ViewSubParts PHP class --}}
    {{ $this->table }}

</x-filament-panels::page>