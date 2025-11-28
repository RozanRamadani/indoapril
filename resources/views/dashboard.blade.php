<x-layout>
    <x-slot name="header">
        <x-header title="Dashboard" subtitle="Selamat datang di Sistem Manajemen Inventori IndoApril" />
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h2 class="text-3xl md:text-4xl font-bold mb-2">Selamat Datang! 👋</h2>
                    <p class="text-white/90 text-lg">Kelola inventori dan transaksi bisnis Anda dengan mudah</p>
                    <p class="text-white/70 text-sm mt-2">{{ now()->isoFormat('dddd, D MMMM YYYY') }} • {{ now()->format('H:i') }} WIB</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs mb-1">Total Transaksi</div>
                        <div class="text-2xl font-bold">{{ number_format($totalPenjualan) }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20">
                        <div class="text-white/70 text-xs mb-1">Total User</div>
                        <div class="text-2xl font-bold">{{ number_format($totalUser) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Statistics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Card: Total Barang --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Inventory</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($totalBarang) }}</h3>
                    <p class="text-sm text-gray-500 mb-3">Total Barang</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Stock: <span class="font-semibold text-blue-600">{{ number_format($totalStock) }}</span></span>
                        @if($stockRendah > 0)
                        <span class="text-orange-600 font-semibold">⚠️ {{ $stockRendah }} rendah</span>
                        @endif
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3">
                    <a href="{{ route('barang.index') }}" class="text-white text-sm font-medium hover:underline flex items-center justify-between">
                        Lihat Detail
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card: Nilai Inventori --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Value</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">Rp {{ number_format($nilaiInventori / 1000000, 1) }}M</h3>
                    <p class="text-sm text-gray-500 mb-3">Nilai Inventori</p>
                    <div class="text-xs text-gray-600">
                        {{ $barangAktif }} aktif • {{ $barangNonaktif }} nonaktif
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-3">
                    <a href="{{ route('barang.index') }}" class="text-white text-sm font-medium hover:underline flex items-center justify-between">
                        Kelola Barang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card: Pengadaan --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Procurement</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($totalPengadaan) }}</h3>
                    <p class="text-sm text-gray-500 mb-3">Total Pengadaan</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Penerimaan: <span class="font-semibold">{{ $totalPenerimaan }}</span></span>
                        <span class="text-purple-600 font-semibold">Rp {{ number_format($nilaiPengadaanBulanIni / 1000, 0) }}K</span>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-3">
                    <a href="{{ route('pengadaan.index') }}" class="text-white text-sm font-medium hover:underline flex items-center justify-between">
                        Kelola Pengadaan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Card: Penjualan --}}
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-3 py-1 rounded-full">Sales</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($totalPenjualan) }}</h3>
                    <p class="text-sm text-gray-500 mb-3">Total Penjualan</p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Bulan ini</span>
                        <span class="text-orange-600 font-semibold">Rp {{ number_format($nilaiPenjualanBulanIni / 1000, 0) }}K</span>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3">
                    <a href="{{ route('penjualan.index') }}" class="text-white text-sm font-medium hover:underline flex items-center justify-between">
                        Kelola Penjualan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kategori Barang (2 columns) --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Kategori Barang
                    </h3>
                </div>
                <div class="p-6">
                    @if(count($statistikJenis) > 0)
                        <div class="space-y-4">
                            @foreach($statistikJenis as $index => $stat)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-lg flex items-center justify-center font-bold text-lg shadow-md">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-gray-800 text-base">
                                            @if($stat->jenis == 'M')
                                                Makanan
                                            @elseif($stat->jenis == 'A')
                                                Alat Tulis
                                            @elseif($stat->jenis == 'N')
                                                Non-Makanan
                                            @elseif($stat->jenis == 'K')
                                                Kebersihan
                                            @else
                                                {{ $stat->jenis }}
                                            @endif
                                        </span>
                                        <span class="font-bold text-indigo-600">{{ number_format($stat->total) }} items</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($stat->total / $totalBarang) * 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ number_format(($stat->total / $totalBarang) * 100, 1) }}%
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-gray-500">Belum ada data kategori</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-500 to-red-500">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('barang.create') }}" class="block p-4 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-lg transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-blue-700">Tambah Barang</span>
                            </div>
                            <svg class="w-5 h-5 text-blue-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('pengadaan.create') }}" class="block p-4 bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-purple-700">Buat Pengadaan</span>
                            </div>
                            <svg class="w-5 h-5 text-purple-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('penjualan.create') }}" class="block p-4 bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-lg transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-orange-700">Transaksi Penjualan</span>
                            </div>
                            <svg class="w-5 h-5 text-orange-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('vendor.index') }}" class="block p-4 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-lg transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-green-700">Kelola Vendor</span>
                            </div>
                            <svg class="w-5 h-5 text-green-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </div>

                {{-- Vendor Stats --}}
                <div class="px-6 pb-6">
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Total Vendor:</span>
                            <span class="font-bold text-gray-900">{{ number_format($totalVendor) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-gray-600">Vendor Aktif:</span>
                            <span class="font-bold text-green-600">{{ number_format($vendorAktif) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Recent Activities --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Transaksi Terbaru --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-cyan-500 to-blue-500">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Transaksi Terbaru
                    </h3>
                </div>
                <div class="p-6">
                    @if(count($transaksiTerbaru) > 0)
                        <div class="space-y-3">
                            @foreach($transaksiTerbaru as $transaksi)
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10
                                    @if($transaksi->color == 'blue') bg-blue-100
                                    @elseif($transaksi->color == 'green') bg-green-100
                                    @else bg-orange-100
                                    @endif
                                    rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5
                                        @if($transaksi->color == 'blue') text-blue-600
                                        @elseif($transaksi->color == 'green') text-green-600
                                        @else text-orange-600
                                        @endif
                                    " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($transaksi->tipe == 'Pengadaan')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        @elseif($transaksi->tipe == 'Penerimaan')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        @endif
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800">{{ $transaksi->tipe }} #{{ $transaksi->id }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaksi->tanggal)->diffForHumans() }}</div>
                                </div>
                                @if($transaksi->nilai > 0)
                                <div class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($transaksi->nilai, 0) }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Barang Terbaru --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-pink-500 to-rose-500">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Barang Terbaru
                    </h3>
                </div>
                <div class="p-6">
                    @if(count($barangTerbaru) > 0)
                        <div class="space-y-3">
                            @foreach($barangTerbaru as $barang)
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-lg flex items-center justify-center font-bold">
                                    {{ substr($barang->nama, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800">{{ $barang->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $barang->jenis }} • {{ $barang->nama_satuan }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($barang->stock ?? 0) }}</div>
                                    <div class="text-xs text-gray-500">stock</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-gray-500">Belum ada barang</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Top Products --}}
        @if(count($topBarang) > 0)
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-yellow-500 to-orange-500">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Top 5 Barang Terlaris
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @foreach($topBarang as $index => $barang)
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-4 border-2 border-yellow-200 hover:border-yellow-400 transition-all">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-400 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-md">
                                {{ $index + 1 }}
                            </div>
                            <div class="text-xs font-semibold text-yellow-700">Rank {{ $index + 1 }}</div>
                        </div>
                        <div class="font-bold text-gray-800 mb-2 line-clamp-2">{{ $barang->nama }}</div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">Terjual:</span>
                            <span class="text-lg font-bold text-orange-600">{{ number_format($barang->total_terjual) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

</x-layout>
