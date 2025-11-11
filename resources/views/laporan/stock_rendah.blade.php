<x-layout>
    <x-slot:title>Laporan Stock Rendah - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Stock Rendah" subtitle="Daftar barang dengan stock di bawah batas threshold yang ditentukan" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ url('/laporan/stock-rendah') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label for="threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                    Batas Stock Rendah (Threshold)
                                </label>
                                <input type="number" name="threshold" id="threshold"
                                       value="{{ $threshold ?? 10 }}" min="1" max="100"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Tampilkan barang dengan stock ≤ threshold</p>
                            </div>
                            <div>
                                <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md transition duration-200">
                                    Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(isset($stockRendah) && count($stockRendah) > 0)
                        <!-- Alert Warning -->
                        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>{{ count($stockRendah) }} barang</strong> memiliki stock rendah (≤ {{ $threshold ?? 10 }})
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-yellow-600">Total Barang Stock Rendah</div>
                                <div class="text-2xl font-bold text-yellow-900">{{ count($stockRendah) }}</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-red-600">Total Stock Tersisa</div>
                                <div class="text-2xl font-bold text-red-900">{{ number_format(collect($stockRendah)->sum('current_stock'), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-blue-600">Estimasi Nilai</div>
                                <div class="text-2xl font-bold text-blue-900">Rp {{ number_format(collect($stockRendah)->sum(function($item) { return $item->current_stock * $item->harga; }), 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Barang Stock Rendah</h3>
                            <div class="flex gap-2">
                                <a href="{{ url('/laporan/stock-opname') }}"
                                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                    Lihat Semua Stock
                                </a>
                            </div>
                        </div>

                        <!-- Tabel Stock Rendah -->
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
                                            Harga (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock Saat Ini
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nilai Stock (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($stockRendah as $item)
                                        <tr class="hover:bg-gray-50 {{ $item->current_stock == 0 ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->idbarang }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->nama_barang }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->satuan }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($item->harga, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $item->current_stock == 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                                {{ number_format($item->current_stock, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format($item->current_stock * $item->harga, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($item->current_stock == 0)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Habis
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Rendah
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-right font-semibold text-gray-700">
                                            Total Nilai:
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                                            {{ number_format(collect($stockRendah)->sum(function($item) { return $item->current_stock * $item->harga; }), 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada barang stock rendah</h3>
                            <p class="mt-1 text-sm text-gray-500">Semua barang memiliki stock di atas threshold ({{ $threshold ?? 10 }}).</p>
                            <div class="mt-4">
                                <a href="{{ url('/laporan/stock-opname') }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Lihat Semua Stock
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
