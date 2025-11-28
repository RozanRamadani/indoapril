<x-layout>
    <x-slot:title>Buat Penjualan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Buat Penjualan Baru" subtitle="Tambah transaksi penjualan barang" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8" x-data="penjualanForm()">
        <form action="{{ route('penjualan.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Margin selector + Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Item Penjualan</h2>
                <div class="flex items-center space-x-3">
                    <label class="text-sm text-gray-600">Margin</label>
                    <select name="idmargin_penjualan" x-model="marginSelected" @change="updateGlobalMargin()" required
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Pilih Margin</option>
                        @foreach($margins as $margin)
                            <option value="{{ $margin->idmargin_penjualan }}" data-persen="{{ $margin->persen }}">{{ $margin->persen }}%</option>
                        @endforeach
                    </select>

                    <button type="button" @click="addItem()"
                            class="inline-flex items-center px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg shadow-md transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Tambah Item
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-orange-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nilai Margin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-3">
                                    <select x-model="item.idbarang" :name="'items['+index+'][idbarang]'" required
                                            @change="onBarangChange(index)"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="">Pilih Barang</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->idbarang }}"
                                                    data-harga="{{ $barang->harga }}"
                                                    data-stock="{{ $barang->current_stock }}"
                                                    data-nama="{{ $barang->nama }}">
                                                {{ $barang->nama }} ({{ $barang->jenis }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        <span x-show="item.current_stock !== null"
                                              :class="item.current_stock > 10 ? 'text-green-600 font-semibold' : (item.current_stock > 0 ? 'text-yellow-600 font-semibold' : 'text-red-600 font-semibold')"
                                              x-text="item.current_stock !== null ? item.current_stock : '-'"></span>
                                        <span x-show="item.current_stock === null || item.current_stock === ''" class="text-gray-400">-</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" x-model="item.jumlah" :name="'items['+index+'][jumlah]'" required min="1"
                                           :max="item.current_stock || 999999"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <p x-show="item.jumlah > item.current_stock" class="text-xs text-red-600 mt-1">
                                        Melebihi stock tersedia!
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" x-model="item.harga_satuan" :name="'items['+index+'][harga_satuan]'" required min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-900">
                                    Rp <span x-text="formatNumber(calculateMargin(item))"></span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    Rp <span x-text="formatNumber(calculateItemTotal(item))"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                Belum ada item. Klik "Tambah Item" untuk menambah
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">Total:</td>
                            <td colspan="2" class="px-4 py-3 font-bold text-gray-900">
                                Rp <span x-text="formatNumber(calculateTotal())"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('penjualan.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                Simpan Penjualan
            </button>
        </div>
    </form>
</div>

<script>
function penjualanForm() {
    return {
        items: [],
        margins: @json($margins),
        barangs: @json($barangs),
        marginSelected: '',
        marginPersen: 0,

        addItem() {
            this.items.push({
                idbarang: '',
                jumlah: 1,
                harga_satuan: 0,
                current_stock: null,
                nama_barang: '',
                margin_persen: this.marginPersen || 0
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onBarangChange(index) {
            const idbarang = this.items[index].idbarang;
            if (!idbarang) {
                this.items[index].harga_satuan = 0;
                this.items[index].current_stock = null;
                this.items[index].nama_barang = '';
                return;
            }

            // Find barang data
            const barang = this.barangs.find(b => b.idbarang == idbarang);
            if (barang) {
                // Auto-fill harga dari data barang
                this.items[index].harga_satuan = parseInt(barang.harga || 0);
                this.items[index].current_stock = parseInt(barang.current_stock || 0);
                this.items[index].nama_barang = barang.nama;

                // Reset jumlah to 1
                this.items[index].jumlah = 1;
            }
        },

        updateGlobalMargin() {
            // read selected option's data-persen
            const sel = document.querySelector('select[name="idmargin_penjualan"]');
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            const persen = parseFloat(opt?.getAttribute('data-persen') || 0);
            this.marginPersen = persen;
            // propagate to existing items
            this.items.forEach(it => it.margin_persen = persen);
        },

        calculateMargin(item) {
            const harga = parseInt(item.harga_satuan || 0);
            const persen = parseFloat(item.margin_persen || this.marginPersen || 0);
            return Math.floor(harga * persen / 100);
        },

        calculateItemTotal(item) {
            const jumlah = parseInt(item.jumlah || 0);
            const harga = parseInt(item.harga_satuan || 0);
            const margin = this.calculateMargin(item);
            return jumlah * (harga + margin);
        },

        calculateTotal() {
            return this.items.reduce((sum, item) => {
                return sum + this.calculateItemTotal(item);
            }, 0);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        },

        init() {
            // set default margin if available
            if (this.margins && this.margins.length) {
                this.marginSelected = this.margins[0].idmargin_penjualan;
                this.marginPersen = parseFloat(this.margins[0].persen || 0);
            }
            // Add first item by default
            this.addItem();
        }
    }
}
</script>
</x-layout>
