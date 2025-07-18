<x-filament-panels::page>
    {{-- Filament akan secara otomatis menampilkan form dari getFormSchema() --}}
    {{ $this->form }}

    {{-- Bagian pratinjau ini akan muncul secara otomatis setelah file diunggah --}}
    @if (!empty($previewData))
        <div class="mt-6 p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Hasil Preview</h2>
            
            {{-- (Konten tabel pratinjau tetap sama seperti sebelumnya) --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="p-4 bg-green-100 border border-green-300 rounded">
                    <dt class="text-sm font-medium text-gray-500">Baris Valid</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-700">{{ count($previewData['valid_rows']) }}</dd>
                </div>
                <div class="p-4 bg-red-100 border border-red-300 rounded">
                    <dt class="text-sm font-medium text-gray-500">Baris Gagal</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-700">{{ count($previewData['invalid_rows']) }}</dd>
                </div>
            </div>

            @if (!empty($previewData['valid_rows']))
                <div class="mt-4">
                    {{-- (Isi tabel untuk baris valid di sini) --}}
                </div>
            @endif

            @if (!empty($previewData['invalid_rows']))
                 <div class="mt-4">
                    {{-- (Isi tabel untuk baris gagal di sini) --}}
                </div>
            @endif

            <div class="mt-6">
                <x-filament::button wire:click="confirmImport" color="primary" :disabled="empty($previewData['valid_rows'])">
                    Konfirmasi dan Impor Stok
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>