<nav class="bg-indigo-600 shadow-lg" x-data="{
    open: false,
    activeDropdown: null,
    showDropdown(dropdown) {
        this.activeDropdown = dropdown;
    },
    hideDropdown() {
        this.activeDropdown = null;
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-white font-bold text-xl hover:text-indigo-100 transition-colors duration-200">
                        IndoApril
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden sm:ml-8 sm:flex sm:space-x-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('/') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-500' }}">
                        Dashboard
                    </a>

                    <!-- Dropdown: Data Master -->
                    <div class="relative"
                         @mouseenter="showDropdown('master')"
                         @mouseleave="hideDropdown()">
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('barang*') || request()->is('satuan*') || request()->is('vendor*') || request()->is('user*') || request()->is('role*') || request()->is('margin_penjualan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-500' }}">
                            Data Master
                            <svg class="ml-1.5 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': activeDropdown === 'master' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'master'"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-56 rounded-md shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50"
                             style="display: none;">
                            <div class="py-1">
                                <a href="{{ route('barang.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('barang*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Barang
                                </a>
                                <a href="{{ route('satuan.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('satuan*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Satuan
                                </a>
                                <a href="{{ url('/vendor') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('vendor*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Vendor
                                </a>
                                <a href="{{ route('user.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('user*') || request()->is('role*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    User & Role
                                </a>
                                <a href="{{ route('margin_penjualan.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('margin_penjualan*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Margin Penjualan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown: Transaksi -->
                    <div class="relative"
                         @mouseenter="showDropdown('transaksi')"
                         @mouseleave="hideDropdown()">
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('pengadaan*') || request()->is('penerimaan*') || request()->is('penjualan*') || request()->is('retur*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-500' }}">
                            Transaksi
                            <svg class="ml-1.5 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': activeDropdown === 'transaksi' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'transaksi'"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-48 rounded-md shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50"
                             style="display: none;">
                            <div class="py-1">
                                <a href="{{ route('pengadaan.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('pengadaan*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Pengadaan
                                </a>
                                <a href="{{ route('penerimaan.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('penerimaan*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Penerimaan
                                </a>
                                <a href="{{ route('penjualan.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('penjualan*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Penjualan
                                </a>
                                <a href="{{ route('retur.index') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150 {{ request()->is('retur*') ? 'bg-indigo-50 text-indigo-600 font-medium' : '' }}">
                                    Retur
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown: Laporan -->
                    <div class="relative"
                         @mouseenter="showDropdown('laporan')"
                         @mouseleave="hideDropdown()">
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('laporan*') ? 'bg-indigo-700 text-white' : 'text-white hover:bg-indigo-500' }}">
                            Laporan
                            <svg class="ml-1.5 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': activeDropdown === 'laporan' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'laporan'"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-56 rounded-md shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50"
                             style="display: none;">
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Stock</div>
                                <a href="{{ url('/laporan/stock-opname') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Stock Opname
                                </a>
                                <a href="{{ url('/laporan/stock-rendah') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Stock Rendah
                                </a>
                                <a href="{{ url('/laporan/kartu-stok') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Kartu Stok
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Transaksi</div>
                                <a href="{{ url('/laporan/penjualan') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Laporan Penjualan
                                </a>
                                <a href="{{ url('/laporan/pengadaan') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Laporan Pengadaan
                                </a>
                                <a href="{{ url('/laporan/penerimaan') }}"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-150">
                                    Laporan Penerimaan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center text-white hover:text-indigo-100 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ session('username') }}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-indigo-500">{{ session('idrole') == 1 ? 'Super Admin' : 'Admin' }}</span>
                        <svg class="ml-1.5 h-4 w-4" :class="{'rotate-180': userMenuOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="userMenuOpen"
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-md shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50"
                         style="display: none;">
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-150">
                                    <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white transition-colors duration-200">
                    <span class="sr-only">Open main menu</span>
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
    <div class="sm:hidden"
         x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         style="display: none;">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-indigo-700">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200 {{ request()->is('/') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                Dashboard
            </a>

            <!-- Mobile: Data Master Section -->
            <div class="pt-2 pb-1">
                <div class="px-3 py-2 text-xs font-semibold text-indigo-200 uppercase tracking-wider">
                    Data Master
                </div>
                <div class="space-y-1">
                    <a href="{{ route('barang.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('barang*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Barang
                    </a>
                    <a href="{{ route('satuan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('satuan*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Satuan
                    </a>
                    <a href="{{ url('/vendor') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('vendor*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Vendor
                    </a>
                    <a href="{{ route('user.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('user*') || request()->is('role*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        User & Role
                    </a>
                    <a href="{{ route('margin_penjualan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('margin_penjualan*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Margin Penjualan
                    </a>
                </div>
            </div>

            <!-- Mobile: Transaksi Section -->
            <div class="pt-2 pb-1">
                <div class="px-3 py-2 text-xs font-semibold text-indigo-200 uppercase tracking-wider">
                    Transaksi
                </div>
                <div class="space-y-1">
                    <a href="{{ route('pengadaan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('pengadaan*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Pengadaan
                    </a>
                    <a href="{{ route('penerimaan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('penerimaan*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Penerimaan
                    </a>
                    <a href="{{ route('penjualan.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('penjualan*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Penjualan
                    </a>
                    <a href="{{ route('retur.index') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('retur*') ? 'bg-indigo-800 text-white' : 'text-white hover:bg-indigo-800' }}">
                        Retur
                    </a>
                </div>
            </div>

            <!-- Mobile: Laporan Section -->
            <div class="pt-2 pb-3">
                <div class="px-3 py-2 text-xs font-semibold text-indigo-200 uppercase tracking-wider">
                    Laporan
                </div>
                <div class="space-y-1">
                    <a href="{{ url('/laporan/stock-opname') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-800 transition-colors duration-200">
                        Stock Opname
                    </a>
                    <a href="{{ url('/laporan/stock-rendah') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-800 transition-colors duration-200">
                        Stock Rendah
                    </a>
                    <a href="{{ url('/laporan/kartu-stok') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-800 transition-colors duration-200">
                        Kartu Stok
                    </a>
                    <a href="{{ url('/laporan/penjualan') }}"
                       class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-indigo-800 transition-colors duration-200">
                        Laporan Penjualan
                    </a>
                    <a href="{{ url('/laporan/pengadaan') }}"
                       class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-indigo-500 transition-colors duration-200">
                        Laporan Pengadaan
                    </a>
                    <a href="{{ url('/laporan/penerimaan') }}"
                       class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-indigo-500 transition-colors duration-200">
                        Laporan Penerimaan
                    </a>
                </div>
            </div>

            <!-- Mobile: User Section -->
            <div class="pt-2 pb-3 border-t border-indigo-800">
                <div class="px-3 py-2">
                    <div class="text-sm font-medium text-white">{{ session('username') }}</div>
                    <div class="text-xs text-indigo-200 mt-1">{{ session('idrole') == 1 ? 'Super Admin' : 'Admin' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-red-600 transition-colors duration-200">
                        <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
