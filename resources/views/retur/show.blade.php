<x-layout>
    <x-slot:title>Detail Retur - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Detail Retur #{{ $retur->idretur }}" subtitle="Informasi lengkap retur barang ke vendor" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-6 flex justify-end">
            <a href="{{ route('retur.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                Kembali
            </a>
        </div>

        <!-- Header Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Retur</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Tanggal Retur</p>
                    <p class="font-medium text-gray-900">{{ date('d F Y H:i', strtotime($retur->created_at)) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Vendor</p>
                    <p class="font-medium text-gray-900">{{ $retur->nama_vendor }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">ID Penerimaan Asal</p>
                    <p class="font-medium text-gray-900">
                        <a href="{{ route('penerimaan.show', $retur->idpenerimaan) }}" class="text-blue-600 hover:text-blue-800">
                            #{{ $retur->idpenerimaan }}
                        </a>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">User</p>
                    <p class="font-medium text-gray-900">{{ $retur->username }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 bg-orange-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Item Retur</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah Diterima</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah Retur</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Alasan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($details as $index => $detail)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_barang }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_satuan }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($detail->jumlah_penerimaan_asal, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold text-orange-600">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->harga_satuan_terima, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ $detail->alasan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->jumlah * $detail->harga_satuan_terima, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Qty Retur:</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                                {{ number_format(collect($details)->sum('jumlah'), 0, ',', '.') }}
                            </td>
                            <td colspan="2" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total Nilai Retur:</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                                Rp {{ number_format(collect($details)->sum(function($d) { return $d->jumlah * $d->harga_satuan_terima; }), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Info:</strong> Barang yang diretur telah dikurangi dari stock.
                        Retur tercatat di kartu stok dengan jenis transaksi <strong>'R' (Retur)</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layout>
