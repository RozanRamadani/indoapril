<x-layout>
    <x-slot:title>Penerimaan #{{ $penerimaan->idpenerimaan }} - Keranjang - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Keranjang Penerimaan" subtitle="Pilih pengadaan dan input jumlah yang diterima" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8" x-data="{ selectedPengadaan: '', pengadaanDetails: [], loading: false }">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Info Penerimaan -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">ID Penerimaan</label>
                    <p class="text-lg font-semibold text-gray-900">#{{ $penerimaan->idpenerimaan }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">User</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $penerimaan->username ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p><span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span></p>
                </div>
            </div>
        </div>

        <!-- Pilih Pengadaan -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pilih Pengadaan</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pengadaan (yang masih ada sisa)</label>
                <select x-model="selectedPengadaan"
                        @change="loadPengadaanDetails()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                    <option value="">-- Pilih Pengadaan --</option>
                    @foreach($pengadaans as $pengadaan)
                        <option value="{{ $pengadaan->idpengadaan }}">
                            #{{ $pengadaan->idpengadaan }} - {{ $pengadaan->nama_vendor }}
                            ({{ date('d/m/Y', strtotime($pengadaan->tanggal_pengadaan)) }}) -
                            Sisa: {{ number_format($pengadaan->total_sisa) }} pcs
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Detail Barang dari Pengadaan yang Dipilih -->
            <div x-show="pengadaanDetails.length > 0" class="mt-4">
                <h4 class="text-md font-semibold text-gray-700 mb-3">Barang yang Tersedia:</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Pengadaan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Sudah Diterima</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Sisa</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in pengadaanDetails" :key="item.iddetail_pengadaan">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <span x-text="item.nama_barang"></span>
                                        <span class="text-gray-500 text-xs" x-text="'(' + (item.nama_satuan || '-') + ')'"></span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900" x-text="item.jumlah_pengadaan"></td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-600" x-text="item.jumlah_sudah_diterima"></td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-gray-900" x-text="item.sisa_belum_diterima"></td>
                                    <td class="px-4 py-2 text-center">
                                        <form :action="`{{ route('penerimaan.addItem', $penerimaan->idpenerimaan) }}`" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="iddetail_pengadaan" :value="item.iddetail_pengadaan">
                                            <input type="number" name="jumlah" min="1" :max="item.sisa_belum_diterima" required
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-sm">
                                            <button type="submit"
                                                    class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white text-sm rounded transition duration-150">
                                                Tambah
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="loading" class="text-center py-4 text-gray-500">
                <svg class="inline w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
            </div>
        </div>

        <!-- Keranjang Penerimaan -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Keranjang Penerimaan (Draft)</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-rose-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Pengadaan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sisa</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Diterima</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($keranjang as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_vendor }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($item->jumlah_pengadaan) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($item->sisa_belum_diterima) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                                    {{ number_format($item->jumlah) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('penerimaan.deleteItem', [$penerimaan->idpenerimaan, $item->iddetail_penerimaan]) }}"
                                          method="POST" class="inline" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    Keranjang kosong. Silakan pilih pengadaan dan tambah barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('penerimaan.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                @if(count($keranjang) > 0)
                    <a href="{{ route('penerimaan.preview', $penerimaan->idpenerimaan) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview & Finalisasi
                    </a>
                @endif
            </div>
        </div>
    </div>

    <script>
        function loadPengadaanDetails() {
            const idpengadaan = Alpine.store('selectedPengadaan') || document.querySelector('[x-model="selectedPengadaan"]').value;
            if (!idpengadaan) {
                Alpine.store('pengadaanDetails', []);
                return;
            }

            Alpine.store('loading', true);

            fetch(`/penerimaan/{{ $penerimaan->idpenerimaan }}/pengadaan/${idpengadaan}`)
                .then(res => res.json())
                .then(data => {
                    Alpine.store('pengadaanDetails', data);
                    Alpine.store('loading', false);
                })
                .catch(err => {
                    console.error('Error loading pengadaan details:', err);
                    Alpine.store('loading', false);
                });
        }
    </script>
</x-layout>
