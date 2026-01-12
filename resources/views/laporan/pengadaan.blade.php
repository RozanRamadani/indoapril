<x-layout>
    <x-slot:title>Laporan Pengadaan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Pengadaan" subtitle="Histori pengadaan barang dari vendor" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('laporan.pengadaan') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <x-date-range-filter
                            :startDate="$startDate"
                            :endDate="$endDate"
                        />
                    </form>

                    @if($laporan && count($laporan) > 0)
                        <!-- Export Buttons -->
                        <div class="flex justify-end mb-4 gap-2">
                            <a href="{{ route('laporan.pengadaan.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </a>
                            <a href="{{ route('laporan.pengadaan.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                               class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200"
                               target="_blank">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Export PDF
                            </a>
                        </div>

                        <!-- Info Periode -->
                        <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-lg text-indigo-900">Laporan Pengadaan</h3>
                            <p class="text-sm text-indigo-700">
                                Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
                            </p>
                            <p class="text-sm text-indigo-700 mt-1">
                                Total: {{ count($laporan) }} pengadaan
                            </p>
                        </div>

                        <!-- Tabel Laporan Pengadaan -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. Pengadaan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vendor
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            PPN (11%)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $grandSubtotal = 0;
                                        $grandPPN = 0;
                                        $grandTotal = 0;
                                    @endphp
                                    @foreach($laporan as $item)
                                        @php
                                            $grandSubtotal += $item->subtotal_nilai ?? 0;
                                            $grandPPN += $item->ppn ?? 0;
                                            $grandTotal += $item->total_nilai ?? 0;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('pengadaan.show', $item->idpengadaan) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    #{{ $item->idpengadaan }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ date('d/m/Y H:i', strtotime($item->tanggal_pengadaan)) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->nama_vendor }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $item->username }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @php
                                                    // Stored procedure returns `status_pengadaan` with values like
                                                    // 'draft', 'progress', 'completed'. Map them to readable labels.
                                                    $statusMap = [
                                                        'draft' => ['label' => 'Draft', 'color' => 'gray'],
                                                        'progress' => ['label' => 'Progress', 'color' => 'blue'],
                                                        'completed' => ['label' => 'Completed', 'color' => 'green'],
                                                    ];
                                                    $rawStatus = strtolower($item->status_pengadaan ?? '');
                                                    $status = $statusMap[$rawStatus] ?? ['label' => 'Unknown', 'color' => 'red'];
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800">
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                Rp {{ number_format($item->subtotal_nilai ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                Rp {{ number_format($item->ppn ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                                Rp {{ number_format($item->total_nilai ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-right font-semibold text-gray-700">
                                            Grand Total:
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            Rp {{ number_format($grandSubtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            Rp {{ number_format($grandPPN, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-indigo-600">
                                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada pengadaan untuk periode yang dipilih.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
