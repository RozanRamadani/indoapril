<div class="space-y-6">
    <!-- Nama Vendor -->
    <div>
        <label for="nama_vendor" class="block text-sm font-medium text-gray-700 mb-2">
            Nama Vendor <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input type="text" 
                   id="nama_vendor" 
                   name="nama_vendor" 
                   value="{{ old('nama_vendor', $vendor->nama_vendor ?? '') }}" 
                   required
                   class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition @error('nama_vendor') border-red-500 @enderror"
                   placeholder="Contoh: PT Indo Makmur Jaya">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        @error('nama_vendor')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-gray-500">Nama lengkap vendor atau supplier</p>
    </div>

    <!-- Badan Hukum -->
    <div>
        <label for="badan_hukum" class="block text-sm font-medium text-gray-700 mb-2">
            Tipe Vendor <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select id="badan_hukum" 
                    name="badan_hukum" 
                    class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition @error('badan_hukum') border-red-500 @enderror">
                <option value="Y" {{ (old('badan_hukum', $vendor->badan_hukum ?? 'Y') == 'Y') ? 'selected' : '' }}>
                    🏢 Badan Hukum (PT, CV, Koperasi, dll)
                </option>
                <option value="N" {{ (old('badan_hukum', $vendor->badan_hukum ?? 'Y') == 'N') ? 'selected' : '' }}>
                    👤 Perorangan (Toko, Warung, UD)
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-10 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        @error('badan_hukum')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-gray-500">Pilih apakah vendor berbentuk badan hukum atau perorangan</p>
    </div>

    <!-- Status -->
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
            Status <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <select id="status" 
                    name="status" 
                    class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition @error('status') border-red-500 @enderror">
                <option value="Y" {{ (old('status', $vendor->status ?? 'Y') == 'Y') ? 'selected' : '' }}>
                    ✓ Aktif (Dapat melakukan transaksi)
                </option>
                <option value="N" {{ (old('status', $vendor->status ?? 'Y') == 'N') ? 'selected' : '' }}>
                    ✗ Nonaktif (Tidak dapat melakukan transaksi)
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
        <p class="mt-2 text-sm text-gray-500">Status kerjasama dengan vendor</p>
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
