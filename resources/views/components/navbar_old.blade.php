<nav class="bg-indigo-600 shadow-lg" x-data="{ open: false, dropdownMaster: false, dropdownTransaksi: false, dropdownLaporan: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-white font-bold text-xl">
                        IndoApril
                    </a>
                </div>
                <!-- Desktop Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->is('/')">
                        Dashboard
                    </x-nav-link>

                    <!-- Dropdown: Data Master -->
                    <div class="relative" @mouseenter="dropdownMaster = true" @mouseleave="dropdownMaster = false">
                        <button class="inline-flex items-center px-1 pt-1 text-sm font-medium text-white hover:text-indigo-200 transition">
                            Data Master
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="dropdownMaster"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="{{ route('barang.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('barang*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    📦 Barang
                                </a>
                                <a href="{{ route('satuan.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('satuan*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    📏 Satuan
                                </a>
                                <a href="{{ url('/vendor') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('vendor*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    🏢 Vendor
                                </a>
                                <a href="{{ route('user.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('user*') || request()->is('role*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    👥 User & Role
                                </a>
                                <a href="{{ route('margin_penjualan.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('margin_penjualan*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    💰 Margin Penjualan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown: Transaksi -->
                    <div class="relative" @mouseenter="dropdownTransaksi = true" @mouseleave="dropdownTransaksi = false">
                        <button class="inline-flex items-center px-1 pt-1 text-sm font-medium text-white hover:text-indigo-200 transition">
                            Transaksi
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="dropdownTransaksi"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="{{ route('pengadaan.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('pengadaan*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    🛒 Pengadaan
                                </a>
                                <a href="{{ route('penerimaan.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('penerimaan*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    📥 Penerimaan
                                </a>
                                <a href="{{ route('penjualan.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 {{ request()->is('penjualan*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    🛍️ Penjualan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown: Laporan -->
                    <div class="relative" @mouseenter="dropdownLaporan = true" @mouseleave="dropdownLaporan = false">
                        <button class="inline-flex items-center px-1 pt-1 text-sm font-medium text-white hover:text-indigo-200 transition">
                            Laporan
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="dropdownLaporan"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="{{ url('/laporan/stock-opname') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    📊 Stock Opname
                                </a>
                                <a href="{{ url('/laporan/stock-rendah') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    ⚠️ Stock Rendah
                                </a>
                                <a href="{{ url('/laporan/kartu-stok') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    📋 Kartu Stok
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="{{ url('/laporan/penjualan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    📈 Laporan Penjualan
                                </a>
                                <a href="{{ url('/laporan/pengadaan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    📉 Laporan Pengadaan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                    <svg class="h-6 w-6" :class="{'hidden': open, 'block': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="h-6 w-6" :class="{'block': open, 'hidden': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden" x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform -translate-y-1"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-1"
         style="display: none;">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-md text-base font-medium transition {{ request()->is('/') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                Dashboard
            </a>

            <!-- Mobile: Data Master -->
            <div class="pt-3">
                <div class="px-3 py-1 text-xs font-semibold text-indigo-200 uppercase tracking-wider">Data Master</div>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('barang.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('barang*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Barang
                    </a>
                    <a href="{{ route('satuan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('satuan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Satuan
                    </a>
                    <a href="{{ url('/vendor') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('vendor*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Vendor
                    </a>
                    <a href="{{ route('user.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('user*') || request()->is('role*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        User & Role
                    </a>
                    <a href="{{ route('margin_penjualan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('margin_penjualan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Margin Penjualan
                    </a>
                </div>
            </div>

            <!-- Mobile: Transaksi -->
            <div class="pt-3">
                <div class="px-3 py-1 text-xs font-semibold text-indigo-200 uppercase tracking-wider">Transaksi</div>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('pengadaan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('pengadaan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Pengadaan
                    </a>
                    <a href="{{ route('penerimaan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('penerimaan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Penerimaan
                    </a>
                    <a href="{{ route('penjualan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition {{ request()->is('penjualan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-700' }}">
                        Penjualan
                    </a>
                </div>
            </div>

            <!-- Mobile: Laporan -->
            <div class="pt-3 pb-2">
                <div class="px-3 py-1 text-xs font-semibold text-indigo-200 uppercase tracking-wider">Laporan</div>
                <div class="mt-1 space-y-1">
                    <a href="{{ url('/laporan/stock-opname') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Stock Opname
                    </a>
                    <a href="{{ url('/laporan/stock-rendah') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Stock Rendah
                    </a>
                    <a href="{{ url('/laporan/kartu-stok') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Kartu Stok
                    </a>
                    <a href="{{ url('/laporan/penjualan') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Laporan Penjualan
                    </a>
                    <a href="{{ url('/laporan/pengadaan') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Laporan Pengadaan
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
