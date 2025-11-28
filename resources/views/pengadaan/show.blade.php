<x-layout>
    <x-slot:title>Detail Pengadaan - IndoApril</x-slot:title>

    <x-slot:header>
        <x-header title="Detail Pengadaan #{{ $pengadaan->idpengadaan }}" subtitle="Informasi lengkap pengadaan" />
    </x-slot:header>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-end">
            <a href="{{ route('pengadaan.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                Kembali
            </a>
        </div>

        <!-- Header Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengadaan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Tanggal</p>
                <p class="font-medium text-gray-900">{{ date('d F Y H:i', strtotime($pengadaan->created_at)) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p>
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                               {{ $pengadaan->status === 'A' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $pengadaan->status === 'A' ? 'Aktif' : 'Pending' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Vendor</p>
                <p class="font-medium text-gray-900">{{ $pengadaan->nama_vendor }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">User</p>
                <p class="font-medium text-gray-900">{{ $pengadaan->username }}</p>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 bg-rose-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Item Pengadaan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga Satuan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Sub Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($details as $index => $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_barang }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $detail->nama_satuan }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                            Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-700">PPN (10%):</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">
                            Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="bg-rose-50">
                        <td colspan="5" class="px-6 py-4 text-right text-base font-bold text-gray-800">Total Nilai:</td>
                        <td class="px-6 py-4 text-base font-bold text-gray-900 text-right">
                            Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Action Button -->
    @php
        // Check if PO is in 'progress' status (ready to receive goods)
        $isProgress = (isset($pengadaan->status_pengadaan) && strtolower($pengadaan->status_pengadaan) === 'progress') || (isset($pengadaan->status) && strtoupper($pengadaan->status) === 'A');
    @endphp
    @if($isProgress)
    <div class="flex justify-end">
        <form action="{{ route('penerimaan.store') }}" method="POST"
              onsubmit="return confirm('Apakah Anda yakin ingin membuat penerimaan untuk pengadaan ini?')">
            @csrf
            <input type="hidden" name="idpengadaan" value="{{ $pengadaan->idpengadaan }}">
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Buat Penerimaan
            </button>
        </form>
    </div>
    @endif
</div>
</x-layout>
