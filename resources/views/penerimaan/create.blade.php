<x-layout>
    <x-slot:title>Buat Penerimaan Baru - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Buat Penerimaan Baru" subtitle="Pilih pengadaan yang akan diterima" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Pengadaan</h2>

                <form action="{{ route('penerimaan.store') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="idpengadaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Pengadaan yang Sudah Selesai <span class="text-red-500">*</span>
                        </label>
                        <select name="idpengadaan" id="idpengadaan" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <option value="">-- Pilih Pengadaan --</option>
                            @foreach($pengadaans as $p)
                                <option value="{{ $p->idpengadaan }}">
                                    #{{ $p->idpengadaan }} - {{ $p->nama_vendor }} - {{ date('d/m/Y', strtotime($p->tanggal_pengadaan)) }}
                                    ({{ $p->total_item }} item, {{ number_format($p->total_pengadaan) }} pcs)
                                </option>
                            @endforeach
                        </select>
                        @error('idpengadaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(count($pengadaans) == 0)
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                            <p class="font-semibold">Tidak ada pengadaan yang tersedia</p>
                            <p class="text-sm mt-1">Silakan selesaikan pengadaan terlebih dahulu sebelum membuat penerimaan.</p>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3 mt-6">
                        <a href="{{ route('penerimaan.index') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-150">
                            Batal
                        </a>
                        <button type="submit"
                                @if(count($pengadaans) == 0) disabled @endif
                                class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                            Lanjut ke Penerimaan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2">💡 Informasi</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Hanya pengadaan dengan status <strong>Completed</strong> yang bisa diterima</li>
                    <li>• Setelah memilih pengadaan, Anda akan diarahkan ke halaman input jumlah penerimaan</li>
                    <li>• Anda bisa memilih barang mana saja yang akan diterima dari pengadaan tersebut</li>
                </ul>
            </div>
        </div>
    </div>
</x-layout>
