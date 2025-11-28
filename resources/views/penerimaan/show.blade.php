<x-layout>

    <x-slot:title>Penerimaan #{{ $penerimaan->idpenerimaan }} - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Detail Penerimaan #{{ $penerimaan->idpenerimaan }}" subtitle="Informasi lengkap penerimaan barang" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-end">
            <a href="{{ route('penerimaan.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                Kembali
            </a>
        </div>

        @if(isset($penerimaan->status) && $penerimaan->status === 'A')
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="font-semibold text-green-900">Penerimaan Sudah Completed</p>
                <p class="text-sm text-green-800">Data ini sudah tidak dapat diubah dan kartu_stok telah terupdate.</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penerimaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">ID Penerimaan</p>
                    <p class="font-semibold text-gray-900">#{{ $penerimaan->idpenerimaan }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Tanggal</p>
                    <p class="font-medium text-gray-900">{{ date('d F Y H:i', strtotime($penerimaan->created_at)) }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">ID Pengadaan</p>
                    <p class="font-medium text-gray-900"><a href="{{ route('pengadaan.show', $penerimaan->idpengadaan) }}" class="text-blue-600 hover:text-blue-900">#{{ $penerimaan->idpengadaan }}</a></p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Vendor</p>
                    <p class="font-semibold text-gray-900">{{ $penerimaan->nama_vendor }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">User</p>
                    <p class="font-medium text-gray-900">{{ $penerimaan->username ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p>
                        @if(isset($penerimaan->status) && $penerimaan->status === 'A')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Completed</span>
                        @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Draft</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 bg-sky-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Item Penerimaan</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah Terima</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $total = 0; @endphp
                        @foreach($details as $index => $detail)
                            @php $total += $detail->sub_total_terima ?? 0; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_barang }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->jenis ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_satuan ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($detail->jumlah_terima, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->harga_satuan_terima, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->sub_total_terima, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Total:</td>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <a href="{{ route('penerimaan.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</x-layout>
