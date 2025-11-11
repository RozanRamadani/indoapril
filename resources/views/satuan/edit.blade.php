<x-layout>
    <x-slot:title>Edit Satuan - IndoApril</x-slot:title>
    
    <x-slot:header>
        <x-header title="Edit Satuan" subtitle="Perbarui informasi satuan {{ $satuan->nama_satuan }}" />
    </x-slot:header>
    
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('satuan.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Daftar Satuan
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit Satuan #{{ str_pad($satuan->idsatuan, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <!-- Info Card -->
            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-purple-700">
                            Anda sedang mengedit satuan <strong>{{ $satuan->nama_satuan }}</strong>. Pastikan semua perubahan sudah benar sebelum menyimpan.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Form Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Informasi Satuan</h3>
                    <p class="mt-1 text-sm text-gray-500">Perbarui data satuan sesuai kebutuhan</p>
                </div>
                
                <form action="{{ route('satuan.update', $satuan->idsatuan) }}" method="POST" class="px-6 py-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- ID Satuan (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ID Satuan
                            </label>
                            <input type="text" value="#{{ str_pad($satuan->idsatuan, 4, '0', STR_PAD_LEFT) }}" readonly class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 cursor-not-allowed focus:outline-none">
                            <p class="mt-1 text-xs text-gray-500">ID tidak dapat diubah</p>
                        </div>

                        <!-- Nama Satuan -->
                        <div>
                            <label for="nama_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Satuan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_satuan" name="nama_satuan" value="{{ old('nama_satuan', $satuan->nama_satuan) }}" placeholder="Contoh: PCS, BOX, KG, LITER" maxlength="45" required autofocus class="block w-full px-4 py-3 border @error('nama_satuan') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            @error('nama_satuan')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Nama akan otomatis diubah menjadi huruf kapital</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required class="block w-full px-4 py-3 border @error('status') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                                <option value="">-- Pilih Status --</option>
                                <option value="1" {{ old('status', $satuan->status) == '1' ? 'selected' : '' }}>
                                    ✓ Aktif
                                </option>
                                <option value="0" {{ old('status', $satuan->status) == '0' ? 'selected' : '' }}>
                                    ✗ Nonaktif
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Satuan nonaktif tidak akan muncul di form barang baru</p>
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('satuan.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>

<script>
    // Auto uppercase untuk nama satuan
    document.getElementById('nama_satuan').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
</script>
