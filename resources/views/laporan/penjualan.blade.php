<x-layout>
    <x-slot:title>Laporan Penjualan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Laporan Penjualan" subtitle="Laporan transaksi penjualan berdasarkan periode, mingguan, bulanan, atau tahunan" />
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ url('/laporan/penjualan') }}" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Tipe Laporan -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipe Laporan
                                </label>
                                <select name="type" id="type"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onchange="togglePeriodeFields()">
                                    <option value="periode" {{ request('type') == 'periode' ? 'selected' : '' }}>Periode</option>
                                    <option value="mingguan" {{ request('type') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="bulanan" {{ request('type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="tahunan" {{ request('type', 'tahunan') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>

                            <!-- Periode Fields -->
                            <div id="periode-fields" class="col-span-2" style="display: {{ request('type', 'tahunan') == 'periode' ? 'block' : 'none' }}">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            Tanggal Mulai
                                        </label>
                                        <input type="date" name="start_date" id="start_date"
                                               value="{{ request('start_date', date('Y-m-01')) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                            Tanggal Akhir
                                        </label>
                                        <input type="date" name="end_date" id="end_date"
                                               value="{{ request('end_date', date('Y-m-d')) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Tahun Field -->
                            <div id="tahun-field" style="display: {{ in_array(request('type', 'tahunan'), ['mingguan', 'bulanan', 'tahunan']) ? 'block' : 'none' }}">
                                <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tahun
                                </label>
                                <input type="number" name="tahun" id="tahun"
                                       value="{{ request('tahun', date('Y')) }}" min="2020" max="2099"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Bulan Field (for mingguan dan bulanan) -->
                            <div id="bulan-field" style="display: {{ in_array(request('type', 'tahunan'), ['mingguan', 'bulanan']) ? 'block' : 'none' }}">
                                <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bulan
                                </label>
                                <select name="bulan" id="bulan"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
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
                        <!-- Export Buttons -->
                        <div class="flex justify-end mb-4 gap-2">
                            <a href="{{ route('laporan.penjualan.export', request()->query()) }}"
                               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </a>
                            <a href="{{ route('laporan.penjualan.pdf', request()->query()) }}"
                               target="_blank"
                               class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Export PDF
                            </a>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-blue-600">
                                    @if($type == 'periode')
                                        Total Transaksi
                                    @else
                                        Total Periode
                                    @endif
                                </div>
                                <div class="text-2xl font-bold text-blue-900">{{ number_format(count($laporan), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-green-600">Total Penjualan</div>
                                <div class="text-2xl font-bold text-green-900">Rp {{ number_format($laporan->sum('total_penjualan'), 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-purple-600">
                                    @if($type == 'periode')
                                        Rata-rata per Transaksi
                                    @else
                                        Rata-rata per Periode
                                    @endif
                                </div>
                                <div class="text-2xl font-bold text-purple-900">Rp {{ number_format($laporan->avg('total_penjualan'), 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Tabel Laporan -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @if($type == 'periode')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID Penjualan
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal
                                            </th>
                                        @elseif($type == 'mingguan')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Minggu Ke
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Periode
                                            </th>
                                        @elseif($type == 'bulanan')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bulan
                                            </th>
                                        @elseif($type == 'tahunan')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bulan
                                            </th>
                                        @endif
                                        @if($type == 'periode')
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Jumlah Item
                                            </th>
                                        @endif
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            PPN (Rp)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total (Rp)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($laporan as $item)
                                        <tr class="hover:bg-gray-50">
                                            @if($type == 'periode')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                    #{{ $item->idpenjualan }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ date('d/m/Y H:i', strtotime($item->tanggal_penjualan)) }}
                                                </td>
                                            @elseif($type == 'mingguan')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                    Minggu {{ $item->minggu ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $item->periode ?? '-' }}
                                                </td>
                                            @elseif($type == 'bulanan')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                    {{ isset($item->bulan) ? DateTime::createFromFormat('!m', $item->bulan)->format('F') : '-' }}
                                                </td>
                                            @elseif($type == 'tahunan')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                    {{ isset($item->bulan) ? DateTime::createFromFormat('!m', $item->bulan)->format('F') : '-' }}
                                                </td>
                                            @endif
                                            @if($type == 'periode')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                    {{ number_format($item->jumlah_item, 0, ',', '.') }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format(
                                                    $type == 'periode'
                                                        ? ($item->subtotal_penjualan ?? $item->subtotal ?? $item->total_subtotal ?? 0)
                                                        : ($item->total_subtotal ?? $item->subtotal ?? 0)
                                                    , 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                {{ number_format(
                                                    $type == 'periode'
                                                        ? ($item->ppn ?? $item->total_ppn ?? 0)
                                                        : ($item->total_ppn ?? $item->ppn ?? 0)
                                                    , 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                                {{ number_format($item->total_penjualan ?? ($item->total ?? 0), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data penjualan</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ada transaksi penjualan untuk periode yang dipilih.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePeriodeFields() {
            const type = document.getElementById('type').value;
            const periodeFields = document.getElementById('periode-fields');
            const tahunField = document.getElementById('tahun-field');
            const bulanField = document.getElementById('bulan-field');

            periodeFields.style.display = type === 'periode' ? 'block' : 'none';
            tahunField.style.display = ['mingguan', 'bulanan', 'tahunan'].includes(type) ? 'block' : 'none';
            bulanField.style.display = ['mingguan', 'bulanan'].includes(type) ? 'block' : 'none';
        }
    </script>
</x-layout>
