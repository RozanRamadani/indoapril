<x-layout>
    <x-slot:title>Laporan Stock Opname - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Stock Opname" subtitle="Laporan stok barang seluruh item yang tersedia di sistem" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(isset($stockOpname) && count($stockOpname) > 0)
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-blue-600">Total Barang</div>
                                <div class="text-2xl font-bold text-blue-900">{{ number_format(count($stockOpname), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-green-600">Total Stock</div>
                                <div class="text-2xl font-bold text-green-900">{{ number_format(collect($stockOpname)->sum('current_stock'), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-yellow-600">Stock Rendah (≤10)</div>
                                <div class="text-2xl font-bold text-yellow-900">{{ collect($stockOpname)->where('current_stock', '<=', 10)->count() }}</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-red-600">Stock Habis (0)</div>
                                <div class="text-2xl font-bold text-red-900">{{ collect($stockOpname)->where('current_stock', '=', 0)->count() }}</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Stock Barang</h3>
                            <div class="flex gap-2">
                                <a href="{{ route('laporan.stock_opname.export') }}"
                                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export Excel
                                </a>
                                <button onclick="window.print()"
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print
                                </button>
                                <a href="{{ url('/laporan/stock-rendah') }}"
                                   class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Lihat Stock Rendah
                                </a>
                            </div>
                        </div>

                        <!-- Tabel Stock Opname -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Barang
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Satuan
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Saat Ini
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($stockOpname as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->idbarang }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->nama_barang }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->nama_satuan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                                {{ number_format($item->current_stock, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($item->current_stock == 0)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Habis
                                                    </span>
                                                @elseif($item->current_stock <= 10)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Rendah
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Aman
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada barang yang terdaftar di sistem.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</x-layout>
