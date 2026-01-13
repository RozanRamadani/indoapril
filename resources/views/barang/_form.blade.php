<div class="space-y-6">
    <!-- Jenis Barang -->
    <div>
        <label for="jenis" class="block text-sm font-medium text-gray-700 mb-2">
            Jenis Barang <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select id="jenis"
                    name="jenis"
                    required
                    class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('jenis') border-red-500 @enderror">
                <option value="">-- Pilih Jenis --</option>
                <option value="M" {{ (old('jenis', $barang->jenis ?? '') == 'M') ? 'selected' : '' }}>M - Makanan</option>
                <option value="N" {{ (old('jenis', $barang->jenis ?? '') == 'N') ? 'selected' : '' }}>N - Minuman</option>
                <option value="A" {{ (old('jenis', $barang->jenis ?? '') == 'A') ? 'selected' : '' }}>A - Alat Tulis</option>
                <option value="K" {{ (old('jenis', $barang->jenis ?? '') == 'K') ? 'selected' : '' }}>K - Kertas</option>
                <option value="B" {{ (old('jenis', $barang->jenis ?? '') == 'B') ? 'selected' : '' }}>B - Buku</option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-10 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">Pilih kategori jenis barang (1 huruf kode)</p>
        @error('jenis')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Nama Barang -->
    <div>
        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
            Nama Barang <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input type="text"
                   id="nama"
                   name="nama"
                   value="{{ old('nama', $barang->nama ?? '') }}"
                   required
                   class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('nama') border-red-500 @enderror"
                   placeholder="Masukkan nama barang">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        @error('nama')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Satuan -->
    <div>
        <label for="idsatuan" class="block text-sm font-medium text-gray-700 mb-2">
            Satuan <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select id="idsatuan"
                    name="idsatuan"
                    required
                    class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('idsatuan') border-red-500 @enderror">
                <option value="">-- Pilih Satuan --</option>
                @foreach($satuans ?? [] as $satuan)
                    <option value="{{ $satuan->idsatuan }}" {{ (old('idsatuan', $barang->idsatuan ?? '') == $satuan->idsatuan) ? 'selected' : '' }}>
                        {{ $satuan->nama_satuan }} (ID: {{ $satuan->idsatuan }})
                    </option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 right-0 pr-10 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">Pilih satuan untuk barang ini (PCS, BOX, KG, dll)</p>
        @error('idsatuan')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
            Status <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select id="status"
                    name="status"
                    class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('status') border-red-500 @enderror">
                <option value="1" {{ (old('status', $barang->status ?? 1) == 1) ? 'selected' : '' }}>
                    ✓ Aktif (Dapat dijual)
                </option>
                <option value="0" {{ (old('status', $barang->status ?? 1) == 0) ? 'selected' : '' }}>
                    ✗ Nonaktif (Tidak dapat dijual)
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-10 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @error('status')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Harga -->
    <div>
        <label for="harga" class="block text-sm font-medium text-gray-700 mb-2">
            Harga Satuan (Rp) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">Rp</span>
            </div>
            <input type="number"
                   id="harga"
                   name="harga"
                   value="{{ old('harga', $barang->harga ?? '') }}"
                   required
                   min="0"
                   step="1"
                   class="block w-full pl-12 pr-12 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('harga') border-red-500 @enderror"
                   placeholder="0">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">Harga dalam Rupiah (tanpa desimal)</p>
        @error('harga')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Image Upload -->
    <div>
        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
            Foto Produk
        </label>
        <div class="mt-1 flex flex-col items-center">
            <!-- Preview Container -->
            <div class="mb-4 w-full">
                <div id="imagePreview" class="mx-auto w-48 h-48 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                    @if(isset($barang) && $barang->image)
                        <img src="{{ asset('storage/' . $barang->image) }}" alt="Product Image" class="w-full h-full object-cover" id="previewImg">
                    @else
                        <div class="text-center" id="placeholderContent">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada gambar</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upload Button -->
            <div class="w-full">
                <label for="image" class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 transition">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Pilih Gambar</span>
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/png,image/jpg,image/gif"
                           class="sr-only"
                           onchange="previewImage(event)">
                </label>
                <p class="mt-2 text-xs text-gray-500">PNG, JPG, GIF hingga 2MB</p>
            </div>
        </div>
        @error('image')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Required Fields Info -->
    <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-700">
                    <span class="text-red-500">*</span> Semua field wajib diisi dengan benar
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-full h-full object-cover" id="previewImg">';
            }
            reader.readAsDataURL(file);
        }
    }
</script>
