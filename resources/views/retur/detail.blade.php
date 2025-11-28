<x-layout>
    <x-slot:title>Input Retur - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Input Detail Retur" subtitle="Penerimaan #{{ $penerimaan->idpenerimaan }} - {{ $penerimaan->nama_vendor }}" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8" x-data="returForm()">
        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('retur.store') }}" method="POST">
            @csrf
            <input type="hidden" name="idpenerimaan" value="{{ $penerimaan->idpenerimaan }}">

            <!-- Info Penerimaan -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penerimaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">ID Penerimaan</p>
                        <p class="font-medium text-gray-900">#{{ $penerimaan->idpenerimaan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Vendor</p>
                        <p class="font-medium text-gray-900">{{ $penerimaan->nama_vendor }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Penerimaan</p>
                        <p class="font-medium text-gray-900">{{ date('d F Y', strtotime($penerimaan->created_at)) }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="px-6 py-4 bg-orange-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Item yang Akan Diretur</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Diterima</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Stock Tersedia</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Qty Retur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Alasan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">
                                    <input type="checkbox" @click="toggleSelectAll()" x-model="selectAll"
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $index => $item)
                            <tr x-data="{ selected: false }">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_satuan }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->jumlah_terima, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span :class="{{$item->current_stock}} > 10 ? 'text-green-600 font-semibold' : ({{$item->current_stock}} > 0 ? 'text-yellow-600 font-semibold' : 'text-red-600 font-semibold')">
                                        {{ number_format($item->current_stock, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           :name="selected ? 'items[{{ $index }}][jumlah]' : ''"
                                           min="1"
                                           max="{{ $item->jumlah_terima }}"
                                           value="1"
                                           x-bind:disabled="!selected"
                                           x-bind:required="selected"
                                           class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-100">
                                    <input type="hidden" :name="selected ? 'items[{{ $index }}][iddetail_penerimaan]' : ''" value="{{ $item->iddetail_penerimaan }}">
                                </td>
                                <td class="px-4 py-3">
                                    <select :name="selected ? 'items[{{ $index }}][alasan]' : ''"
                                            x-bind:disabled="!selected"
                                            x-bind:required="selected"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-100">
                                        <option value="">Pilih Alasan</option>
                                        <option value="Barang Rusak">Barang Rusak</option>
                                        <option value="Barang Salah">Barang Salah</option>
                                        <option value="Barang Expired">Barang Expired</option>
                                        <option value="Kualitas Buruk">Kualitas Buruk</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" x-model="selected"
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('retur.create') }}"
                   class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    Simpan Retur
                </button>
            </div>
        </form>
    </div>

    <script>
    function returForm() {
        return {
            selectAll: false,

            toggleSelectAll() {
                // Toggle all checkboxes
                document.querySelectorAll('input[type="checkbox"][x-model="selected"]').forEach(cb => {
                    Alpine.$data(cb.closest('tr')).selected = this.selectAll;
                });
            }
        }
    }
    </script>
</x-layout>
