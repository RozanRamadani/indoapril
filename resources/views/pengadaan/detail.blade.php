<x-layout>
    <x-slot:title>Detail Pengadaan #{{ $pengadaan->idpengadaan }} - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Keranjang Pengadaan" subtitle="Tambah dan edit barang pengadaan" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8" x-data="{ showAddForm: false, editMode: {}, harga: {}, selectedBarang: '' }">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Info Pengadaan -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-600">ID Pengadaan</label>
                    <p class="text-lg font-semibold text-gray-900">#{{ $pengadaan->idpengadaan }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Vendor</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $pengadaan->nama_vendor }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">Status</label>
                    <p>
                        @php
                            $statusClass = 'bg-yellow-100 text-yellow-800'; // default: draft
                            $statusText = 'Draft';
                            if(isset($pengadaan->status_pengadaan)) {
                                if($pengadaan->status_pengadaan === 'progress') {
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    $statusText = 'Progress';
                                } elseif($pengadaan->status_pengadaan === 'completed') {
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusText = 'Completed';
                                }
                            }
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClass }}">{{ $statusText }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Tambah Barang -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Barang</h3>
            </div>

            <form action="{{ route('pengadaan.addItem', $pengadaan->idpengadaan) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Barang</label>
                        <select name="idbarang" required x-model="selectedBarang"
                            x-init="if($el.value) harga[$el.value] = Number($el.selectedOptions[0].dataset.harga || 0)"
                            @change="harga[$event.target.value] = Number($event.target.selectedOptions[0].dataset.harga || 0)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga_jual ?? $barang->harga ?? 0 }}">
                                    {{ $barang->nama }} ({{ $barang->nama_satuan ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="jumlah" required min="1" value="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga Satuan</label>
                        <input type="number" name="harga_satuan" required min="0"
                               x-model="harga[selectedBarang]"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Tambah ke Keranjang
                    </button>
                </div>
            </form>
        </div>

        <!-- Keranjang -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Keranjang Barang</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-rose-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($details as $detail)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $detail->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">
                                    <span x-show="!editMode[{{ $detail->iddetail_pengadaan }}]">
                                        {{ number_format($detail->jumlah) }}
                                    </span>
                                    <span x-show="editMode[{{ $detail->iddetail_pengadaan }}]" class="block">
                                        <input form="edit-form-{{ $detail->iddetail_pengadaan }}" type="number" name="jumlah" value="{{ $detail->jumlah }}" min="1" required
                                               class="mx-auto w-20 px-3 py-1 border border-gray-200 rounded-md text-sm text-center">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">
                                    <span x-show="!editMode[{{ $detail->iddetail_pengadaan }}]">
                                        Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </span>
                                    <span x-show="editMode[{{ $detail->iddetail_pengadaan }}]" class="block">
                                        <input form="edit-form-{{ $detail->iddetail_pengadaan }}" type="number" name="harga_satuan" value="{{ $detail->harga_satuan }}" min="0" required
                                               class="mx-auto w-32 px-3 py-1 border border-gray-200 rounded-md text-sm text-center">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                                    Rp {{ number_format($detail->sub_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span x-show="!editMode[{{ $detail->iddetail_pengadaan }}]">
                                        <button @click="editMode[{{ $detail->iddetail_pengadaan }}] = true" class="text-blue-600 hover:text-blue-900 mr-2">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </span>

                                    {{-- Edit form lives here; inputs in other tds use form="edit-form-..." to belong to this form --}}
                                    <form x-show="editMode[{{ $detail->iddetail_pengadaan }}]" id="edit-form-{{ $detail->iddetail_pengadaan }}"
                                          action="{{ route('pengadaan.updateItem', [$pengadaan->idpengadaan, $detail->iddetail_pengadaan]) }}" method="POST" class="inline-flex items-center mr-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="idbarang" value="{{ $detail->idbarang }}">
                                        <button type="submit" class="ml-2 text-sky-600 hover:text-sky-800" title="Simpan perubahan">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button type="button" @click.prevent="editMode[{{ $detail->iddetail_pengadaan }}] = false" class="ml-2 text-gray-500 hover:text-gray-700" title="Batal">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form action="{{ route('pengadaan.deleteItem', [$pengadaan->idpengadaan, $detail->iddetail_pengadaan]) }}"
                                          method="POST" class="inline" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Keranjang kosong. Silakan tambah barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-700">PPN (10%):</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-800">Grand Total:</td>
                            <td class="px-4 py-3 text-right text-lg font-bold text-rose-600">
                                Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <a href="{{ route('pengadaan.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>

                <div class="flex gap-3">
                    <!-- Tombol Hapus (hanya muncul jika status Draft) -->
                    <form action="{{ route('pengadaan.destroy', $pengadaan->idpengadaan) }}" method="POST"
                          onsubmit="return confirm('⚠️ HAPUS PENGADAAN?\n\nSemua data pengadaan dan detail barang akan dihapus permanen.\n\nLanjutkan?')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Batalkan
                        </button>
                    </form>

                    @if(count($details) > 0)
                        <form action="{{ route('pengadaan.finalize', $pengadaan->idpengadaan) }}" method="POST" onsubmit="return confirm('Finalisasi pengadaan? Setelah finalisasi tidak bisa diedit lagi.')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Simpan Permanen
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
