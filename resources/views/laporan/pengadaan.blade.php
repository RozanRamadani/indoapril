<x-layout>
    <x-slot:title>Laporan Pengadaan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Pengadaan" subtitle="Laporan transaksi pengadaan barang dari vendor berdasarkan periode" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ url('/laporan/pengadaan') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Mulai
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                       value="{{ $startDate ?? date('Y-m-01') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Akhir
                                </label>
                                <input type="date" name="end_date" id="end_date"
                                       value="{{ $endDate ?? date('Y-m-d') }}"
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

                    @if(isset($laporan) && count($laporan) > 0)
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-blue-600">Total Pengadaan</div>
                                <div class="text-2xl font-bold text-blue-900">{{ number_format(count($laporan), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-green-600">Total Item</div>
                                <div class="text-2xl font-bold text-green-900">{{ number_format(collect($laporan)->sum('jumlah_item'), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-purple-600">Subtotal</div>
                                <div class="text-2xl font-bold text-purple-900">Rp {{ number_format(collect($laporan)->sum('subtotal_nilai'), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-indigo-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-indigo-600">Total Nilai</div>
                                <div class="text-2xl font-bold text-indigo-900">Rp {{ number_format(collect($laporan)->sum('total_nilai'), 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Info Periode -->
                        <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
                            <p class="text-sm text-indigo-700">
                                <strong>Periode:</strong> {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
                            </p>
                        </div>

                        <!-- Tabel Laporan -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID Pengadaan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vendor
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jumlah Item
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            PPN (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($laporan as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                #{{ $item->idpengadaan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ date('d/m/Y H:i', strtotime($item->tanggal_pengadaan ?? $item->created_at ?? now())) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->nama_vendor ?? $item->nama ?? '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                                {{ number_format($item->jumlah_item, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($item->subtotal_nilai, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($item->ppn, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                                {{ number_format($item->total_nilai, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @php
                                                    // Prefer the stored-procedure alias `status_pengadaan`,
                                                    // fallback to legacy `status` column if present.
                                                    $rawStatus = $item->status_pengadaan ?? $item->status ?? '';
                                                    $s = is_string($rawStatus) ? strtolower($rawStatus) : '';
                                                @endphp

                                                @if($rawStatus === 'A' || $s === 'completed' || $s === 'approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Approved
                                                    </span>
                                                @elseif($rawStatus === 'P' || $s === 'draft' || $s === 'pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @elseif($rawStatus === 'R' || $s === 'rejected')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ $rawStatus }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-right font-semibold text-gray-700">
                                            Total:
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format(collect($laporan)->sum('subtotal_nilai'), 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format(collect($laporan)->sum('ppn'), 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                                            {{ number_format(collect($laporan)->sum('total_nilai'), 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data pengadaan</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada transaksi pengadaan untuk periode yang dipilih.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
