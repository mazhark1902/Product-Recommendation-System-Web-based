<x-filament-panels::page>
    {{-- Bagian Form untuk Upload File --}}
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}
    </form>

    {{-- Bagian Pratinjau Data & Tombol Konfirmasi --}}
    @if (!empty($previewData['valid_rows']) || !empty($previewData['invalid_rows']))
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Preview Data
            </h2>

            {{-- Tombol Konfirmasi hanya muncul jika ada data valid --}}
            @if (!empty($previewData['valid_rows']))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <span class="font-medium">{{ count($previewData['valid_rows']) }} baris data valid</span> dan siap untuk diimpor. Silakan periksa kembali sebelum melanjutkan.
                </div>
                <x-filament::button wire:click="confirmImport" color="primary">
                    Confirm & Import Stock
                </x-filament::button>
            @endif

            {{-- Tabel untuk Data Valid --}}
            @if (!empty($previewData['valid_rows']))
                <div class="mt-6 overflow-x-auto relative shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">Product ID</th>
                                <th scope="col" class="py-3 px-6">Product Name</th>
                                <th scope="col" class="py-3 px-6">Quantity to Add</th>
                                <th scope="col" class="py-3 px-6">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previewData['valid_rows'] as $row)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $row['product_id'] }}</td>
                                    <td class="py-4 px-6">{{ $row['product_name'] }}</td>
                                    <td class="py-4 px-6">{{ $row['quantity'] }}</td>
                                    <td class="py-4 px-6">{{ $row['location'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Tabel untuk Data Invalid --}}
            @if (!empty($previewData['invalid_rows']))
                 <div class="p-4 mt-6 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                   <span class="font-medium">{{ count($previewData['invalid_rows']) }} baris data tidak valid</span> dan tidak akan diimpor.
               </div>
               <div class="mt-6 overflow-x-auto relative shadow-md sm:rounded-lg">
                   <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                       <thead class="text-xs text-gray-700 uppercase bg-red-100 dark:bg-red-900 dark:text-gray-400">
                           <tr>
                               <th scope="col" class="py-3 px-6">Product ID</th>
                               <th scope="col" class="py-3 px-6">Quantity</th>
                               <th scope="col" class="py-3 px-6">Error</th>
                           </tr>
                       </thead>
                       <tbody>
                           @foreach ($previewData['invalid_rows'] as $row)
                               <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                   <td class="py-4 px-6">{{ $row['product_id'] }}</td>
                                   <td class="py-4 px-6">{{ $row['quantity'] }}</td>
                                   <td class="py-4 px-6 font-medium text-red-600 dark:text-red-400">{{ $row['error'] }}</td>
                               </tr>
                           @endforeach
                       </tbody>
                   </table>
               </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
