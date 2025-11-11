<x-layout>
    <x-slot:title>Detail Penerimaan #{{ $penerimaan->idpenerimaan }} - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Detail Penerimaan #{{ $penerimaan->idpenerimaan }}"
                  subtitle="Vendor: {{ $penerimaan->nama_vendor }} | {{ date('d/m/Y H:i', strtotime($penerimaan->created_at)) }}" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8" x-data="{ editMode: {} }">
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

        <!-- Pengadaan Reference -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Barang dari Pengadaan #{{ $penerimaan->idpengadaan }}</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah Pengadaan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pengadaanDetails as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($item->jumlah) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">Rp {{ number_format($item->harga_satuan) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($item->sub_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Keranjang Penerimaan (Editable) -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Keranjang Penerimaan (Draft)</h3>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                    Draft
                </span>
            </div>

            <!-- Add Item Form -->
            <form action="{{ route('penerimaan.addItem', $penerimaan->idpenerimaan) }}" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                        <select name="idbarang" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 text-sm"
                                onchange="updateHargaSatuan(this)">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($pengadaanDetails as $item)
                                <option value="{{ $item->idbarang }}" data-harga="{{ $item->harga_satuan }}">
                                    {{ $item->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Diterima</label>
                        <input type="number" name="jumlah_terima" min="1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 text-sm"
                               placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan</label>
                        <input type="number" name="harga_satuan_terima" min="0" required id="harga_satuan_input"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 text-sm"
                               placeholder="0">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition text-sm font-medium">
                            Tambah
                        </button>
                    </div>
                </div>
            </form>

            <script>
                function updateHargaSatuan(select) {
                    const harga = select.options[select.selectedIndex].dataset.harga;
                    document.getElementById('harga_satuan_input').value = harga || '';
                }
            </script>

            <!-- Keranjang Items -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sub Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($keranjang as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->nama_satuan ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                                    <span x-show="!editMode[{{ $item->iddetail_penerimaan }}]">{{ number_format($item->jumlah_terima ?? $item->jumlah ?? 0) }}</span>
                                    <span x-show="editMode[{{ $item->iddetail_penerimaan }}]" class="block">
                                        <input form="edit-form-{{ $item->iddetail_penerimaan }}" type="number" name="jumlah_terima" min="1"
                                               value="{{ old('jumlah_terima', $item->jumlah_terima ?? $item->jumlah ?? 0) }}"
                                               class="mx-auto w-20 px-3 py-1 border border-gray-200 rounded-md text-sm text-center" />
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-right text-gray-900">
                                    <span x-show="!editMode[{{ $item->iddetail_penerimaan }}]">Rp {{ number_format($item->harga_satuan_terima ?? $item->harga_satuan ?? 0) }}</span>
                                    <span x-show="editMode[{{ $item->iddetail_penerimaan }}]" class="block">
                                        <input form="edit-form-{{ $item->iddetail_penerimaan }}" type="number" name="harga_satuan_terima" min="0"
                                               value="{{ $item->harga_satuan_terima ?? $item->harga_satuan ?? 0 }}"
                                               class="mx-auto w-28 px-3 py-1 border border-gray-200 rounded-md text-sm text-center" />
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">Rp {{ number_format($item->sub_total_terima ?? $item->sub_total ?? 0) }}</td>

                                <td class="px-4 py-3 text-center">
                                    <span x-show="!editMode[{{ $item->iddetail_penerimaan }}]">
                                        <button @click.prevent="editMode[{{ $item->iddetail_penerimaan }}] = true" class="text-blue-600 hover:text-blue-900 mr-2" title="Edit jumlah">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </span>

                                    {{-- Edit form (the inputs themselves are placed in other tds but belong to this form via the `form` attribute) --}}
                                                                        <form x-show="editMode[{{ $item->iddetail_penerimaan }}]" id="edit-form-{{ $item->iddetail_penerimaan }}"
                                                                                    action="{{ route('penerimaan.updateItem', [$penerimaan->idpenerimaan, $item->iddetail_penerimaan]) }}" method="POST" class="inline-flex items-center mr-2">
                                        @csrf
                                        @method('PUT')
                                                                                <input type="hidden" name="idbarang" value="{{ $item->idbarang }}">
                                        <button type="submit" class="ml-2 text-sky-600 hover:text-sky-800" title="Simpan perubahan">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button type="button" @click.prevent="editMode[{{ $item->iddetail_penerimaan }}] = false" class="ml-2 text-gray-500 hover:text-gray-700" title="Batal">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Delete form --}}
                                    <form action="{{ route('penerimaan.deleteItem', [$penerimaan->idpenerimaan, $item->iddetail_penerimaan]) }}"
                                          method="POST" class="inline" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 ml-1">
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
                                    Keranjang masih kosong. Tambahkan barang menggunakan form di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <a href="{{ route('penerimaan.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Kembali
            </a>

            @if(count($keranjang) > 0)
                <form action="{{ route('penerimaan.finalize', $penerimaan->idpenerimaan) }}" method="POST"
                      onsubmit="return confirm('Finalisasi penerimaan ini? Setelah finalisasi, data tidak dapat diubah dan kartu_stok akan diupdate.')">
                    @csrf
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        Finalisasi Penerimaan
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layout>
