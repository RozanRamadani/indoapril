<x-layout>
    <x-slot:title>Tambah Satuan - IndoApril</x-slot:title>
    
    <x-slot:header>
        <x-header title="Tambah Satuan Baru" subtitle="Tambahkan unit/ukuran barang baru ke sistem" />
    </x-slot:header>
    
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto"

>
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Tambah Satuan</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Form Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Form Tambah Satuan</h3>
                    <p class="mt-1 text-sm text-gray-500">Lengkapi formulir di bawah ini dengan data yang valid</p>
                </div>
                
                <form action="{{ route('satuan.store') }}" method="POST" class="px-6 py-6 space-y-6">
                    @csrf

                    <!-- Nama Satuan -->
                    <div>
                        <label for="nama_satuan" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Satuan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama_satuan" 
                               name="nama_satuan" 
                               value="{{ old('nama_satuan') }}"
                               placeholder="Contoh: PCS, BOX, KG, LITER, PACK"
                               maxlength="45"
                               required
                               autofocus
                               class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition @error('nama_satuan') border-red-500 @enderror">
                        @error('nama_satuan')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            💡 Maksimal 45 karakter. Contoh: PCS, BOX, KG, LITER, PACK, UNIT, KARTON
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status"
                                required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition @error('status') border-red-500 @enderror">
                            <option value="">-- Pilih Status --</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>✓ Aktif</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>✗ Nonaktif</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            💡 Satuan aktif akan muncul di dropdown form barang
                        </p>
                    </div>

                    <!-- Buttons -->
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
                            Simpan Satuan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-purple-800">Tips:</h3>
                        <ul class="mt-2 text-sm text-purple-700 list-disc list-inside space-y-1">
                            <li>Gunakan nama satuan yang jelas dan konsisten (huruf besar semua)</li>
                            <li>Contoh satuan umum: <strong>PCS</strong> (Pieces), <strong>BOX</strong> (Kotak), <strong>KG</strong> (Kilogram), <strong>LITER</strong>, <strong>PACK</strong> (Paket)</li>
                            <li>Nama satuan harus unik (tidak boleh duplikat)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

<script>
    // Auto uppercase input
    document.getElementById('nama_satuan').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
</script>
