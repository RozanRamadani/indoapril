<x-layout>
    <x-slot:title>Detail Penjualan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Detail Penjualan #{{ $penjualan->idpenjualan }}" subtitle="Informasi lengkap transaksi penjualan" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-end">
            <a href="{{ route('penjualan.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                Kembali
            </a>
        </div>

        <!-- Header Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penjualan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Tanggal</p>
                <p class="font-medium text-gray-900">{{ date('d F Y H:i', strtotime($penjualan->created_at)) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">User</p>
                <p class="font-medium text-gray-900">{{ $penjualan->username }}</p>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 bg-orange-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Item Penjualan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Margin (%)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Nilai Margin</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sub Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($details as $index => $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_barang }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_satuan }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $penjualan->margin_persen ?? 0 }}%</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->nilai_margin, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="7" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                            Rp {{ number_format($penjualan->subtotal_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="px-6 py-3 text-right text-sm font-medium text-gray-700">PPN (10%):</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                            Rp {{ number_format($penjualan->ppn, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="bg-orange-50">
                        <td colspan="7" class="px-6 py-4 text-right text-base font-bold text-gray-800">Total Nilai:</td>
                        <td class="px-6 py-4 text-base font-bold text-gray-900 text-right">
                            Rp {{ number_format($penjualan->total_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</x-layout>
