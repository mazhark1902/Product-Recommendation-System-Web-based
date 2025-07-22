<x-filament::page>
    <h2 class="text-xl font-bold mb-4">Dealer Product Recommendation </h2>

    <div class="mb-6">
        <label for="dealer" class="block mb-2 font-medium text-gray-700">Choose the Dealer ID</label>
       <select wire:model.defer="selectedDealer" id="dealer"
    class="w-full border-gray-300 rounded shadow-sm focus:ring focus:ring-primary-500">
    <option value="" disabled {{ !$selectedDealer ? 'selected' : '' }}>🏢 Choose Your Dealer </option>

            @foreach (\App\Models\TopRecommendation::distinct()->pluck('dealer_id') as $dealer)
                <option value="{{ $dealer }}">{{ $dealer }}</option>
            @endforeach
        </select>

        <button wire:click="searchRecommendation"
            class="mt-3 px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
            Recommendation
        </button>
    </div>

    @if ($recommendation)
        <div class="p-4 border rounded bg-white shadow-md">
            <p><strong>Dealer ID:</strong> {{ $recommendation->dealer_id }}</p>
            <p><strong>Kategori Rekomendasi:</strong> {{ $recommendation->recommended_category }}</p>
            <p><strong>Skor:</strong> {{ number_format($recommendation->score, 4) }}</p>
        </div>
    @elseif ($selectedDealer)
        <div class="text-red-500">Data rekomendasi tidak ditemukan untuk dealer ini.</div>
    @endif
</x-filament::page>
