<x-layout>
    <x-slot:title>Laporan Kartu Stok - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Kartu Stok" subtitle="Histori pergerakan stok barang (penerimaan dan penjualan)" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ url('/laporan/kartu-stok') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Barang Dropdown -->
                            <div>
                                <label for="idbarang" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pilih Barang
                                </label>
                                <select name="idbarang" id="idbarang"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih Barang --</option>
                                    <option value="all" {{ request('idbarang') == 'all' ? 'selected' : '' }}>
                                        🔍 Semua Barang
                                    </option>
                                    @foreach($barangList as $b)
                                        <option value="{{ $b->idbarang }}" {{ request('idbarang') == $b->idbarang ? 'selected' : '' }}>
                                            {{ $b->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Mulai
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                       value="{{ request('start_date', date('Y-m-01')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Akhir
                                </label>
                                <input type="date" name="end_date" id="end_date"
                                       value="{{ request('end_date', date('Y-m-d')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-end">
                                <button type="submit"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(isset($kartuStok) && count($kartuStok) > 0)
                        <!-- Info Barang -->
                        <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-lg text-indigo-900">{{ $namaBarang }}</h3>
                            <p class="text-sm text-indigo-700">
                                Periode: {{ date('d/m/Y', strtotime(request('start_date'))) }} - {{ date('d/m/Y', strtotime(request('end_date'))) }}
                            </p>
                        </div>

                        <!-- Tabel Kartu Stok -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @if(request('idbarang') === 'all')
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Barang
                                        </th>
                                        @endif
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Keterangan
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. Transaksi
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Barang Masuk
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Barang Keluar
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nilai (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sisa Stok
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($kartuStok as $item)
                                        <tr class="hover:bg-gray-50">
                                            @if(request('idbarang') === 'all')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->nama_barang ?? '' }}
                                            </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ date('d/m/Y H:i', strtotime($item->tanggal)) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->keterangan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <span class="px-2 py-1 rounded-md font-semibold
                                                    {{ $item->tipe_mutasi == 'Masuk' ? 'bg-green-100 text-green-800' :
                                                       ($item->tipe_mutasi == 'Retur' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $item->nomor_transaksi }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $item->tipe_mutasi == 'Masuk' ? 'bg-green-100 text-green-800' :
                                                       ($item->tipe_mutasi == 'Retur' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $item->tipe_mutasi }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $item->qty_masuk > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                                {{ $item->qty_masuk > 0 ? number_format($item->qty_masuk, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $item->qty_keluar > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                                {{ $item->qty_keluar > 0 ? number_format($item->qty_keluar, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($item->nilai_transaksi, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-indigo-600">
                                                {{ number_format($item->sisa_stok, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="{{ request('idbarang') === 'all' ? '5' : '4' }}" class="px-6 py-4 text-right font-semibold text-gray-700">
                                            Total:
                                        </td>
                                        @php
                                            $totalMasuk = $kartuStok->sum('qty_masuk');
                                            $totalKeluar = $kartuStok->sum('qty_keluar');
                                            $totalNilai = $kartuStok->sum('nilai_transaksi');
                                        @endphp
                                        <td class="px-6 py-4 text-right font-semibold text-green-600">
                                            {{ number_format($totalMasuk, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-red-600">
                                            {{ number_format($totalKeluar, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format($totalNilai, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-500">
                                            -
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @elseif(request('idbarang'))
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada transaksi</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada transaksi untuk periode yang dipilih.</p>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih barang</h3>
                            <p class="mt-1 text-sm text-gray-500">Pilih barang dan periode untuk menampilkan kartu stok.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
