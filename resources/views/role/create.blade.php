<x-layout>
    <x-slot:title>Tambah Role - IndoApril</x-slot:title>
    
    <x-slot:header>
        <x-header title="Tambah Role Baru" subtitle="Menambahkan role/hak akses baru ke sistem" />
    </x-slot:header>
    
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('role.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Nama Role -->
                        <div>
                            <label for="nama_role" class="block text-sm font-medium text-gray-700">Nama Role</label>
                            <input type="text" name="nama_role" id="nama_role" value="{{ old('nama_role') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   placeholder="Masukkan nama role (contoh: Admin, Staff, Manager)" required>
                            @error('nama_role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Nama role harus unik dan maksimal 45 karakter.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('role.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Batal
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>