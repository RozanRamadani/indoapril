<x-layout>
    <x-slot:title>Preview Penerimaan #{{ $penerimaan->idpenerimaan }} - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Preview Penerimaan" subtitle="Review sebelum finalisasi" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        @if(session('info'))
            <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                {{ session('info') }}
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
                    <label class="text-sm font-medium text-gray-600">Tanggal</label>
                    <p class="text-lg font-semibold text-gray-900">{{ date('d/m/Y H:i', strtotime($penerimaan->created_at)) }}</p>
                </div>
            </div>
        </div>

        <!-- Detail per Vendor -->
        @foreach($grouped as $vendor => $items)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $vendor }}
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Pengadaan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Sudah Diterima</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Diterima Sekarang</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php $subtotal = 0; @endphp
                            @foreach($items as $item)
                                @php $subtotal += $item->nilai_penerimaan; @endphp
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $item->nama_barang }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $item->nama_satuan ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($item->jumlah_pengadaan) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-600">{{ number_format($item->jumlah_sudah_diterima) }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-rose-600">{{ number_format($item->jumlah) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">Rp {{ number_format($item->nilai_penerimaan, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Subtotal {{ $vendor }}:</td>
                                <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Grand Total -->
        <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Nilai Penerimaan</p>
                    <p class="text-3xl font-bold text-rose-600">
                        Rp {{ number_format(collect($details)->sum('nilai_penerimaan'), 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-1">Total Item</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ count($details) }} item</p>
                </div>
            </div>
        </div>

        <!-- Warning Box -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <svg class="w-6 h-6 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">Perhatian!</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Setelah finalisasi, kartu stok akan otomatis terupdate</li>
                            <li>Jumlah diterima di pengadaan akan otomatis bertambah</li>
                            <li>Data tidak bisa diubah lagi setelah finalisasi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('penerimaan.keranjang', $penerimaan->idpenerimaan) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Keranjang
            </a>
            <form action="{{ route('penerimaan.finalize', $penerimaan->idpenerimaan) }}" method="POST"
                  onsubmit="return confirm('⚠️ FINALISASI PENERIMAAN?\n\nSetelah finalisasi:\n✓ Kartu stok akan terupdate\n✓ Data tidak bisa diubah lagi\n\nLanjutkan?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-lg font-semibold rounded-lg shadow-lg transition duration-150">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    FINALISASI PENERIMAAN
                </button>
            </form>
        </div>
    </div>
</x-layout>
