<x-layout>
    <x-slot:title>Tambah Margin Penjualan - IndoApril</x-slot:title>
    
    <x-slot:header>
        <x-header title="Tambah Margin Penjualan" subtitle="Menambahkan persentase margin keuntungan baru" />
    </x-slot:header>
    
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('margin_penjualan.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Persentase -->
                        <div>
                            <label for="persen" class="block text-sm font-medium text-gray-700">Persentase Margin (%)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="persen" id="persen" value="{{ old('persen') }}" 
                                       step="0.01" min="0" max="100"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" 
                                       placeholder="0.00" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            @error('persen')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Masukkan persentase margin antara 0% - 100%.</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" 
                                    required>
                                <option value="">Pilih Status</option>
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- User -->
                        <div>
                            <label for="iduser" class="block text-sm font-medium text-gray-700">User</label>
                            <select name="iduser" id="iduser" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" 
                                    required>
                                <option value="">Pilih User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->iduser }}" {{ old('iduser') == $user->iduser ? 'selected' : '' }}>
                                        {{ $user->username }} ({{ $user->nama_role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('iduser')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('margin_penjualan.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            Batal
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Margin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>