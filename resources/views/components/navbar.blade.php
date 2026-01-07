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
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-3">

                <!-- Stock Alert Notification Bell -->
                @php
                    $stockAlertCount = \Illuminate\Support\Facades\DB::selectOne('
                        SELECT COUNT(*) as total
                        FROM barang b
                        INNER JOIN kartu_stok ks ON b.idbarang = ks.idbarang
                        WHERE b.status = 1 AND ks.stock <= 10
                    ')->total ?? 0;
                @endphp

                @if($stockAlertCount > 0)
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen"
                            class="relative flex items-center justify-center w-10 h-10 text-white hover:bg-indigo-500 rounded-full transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                            {{ $stockAlertCount }}
                        </span>
                    </button>

                    <div x-show="notifOpen"
                         @click.away="notifOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50"
                         style="display: none;">
                        <div class="p-4 bg-gradient-to-r from-red-600 to-orange-600">
                            <h3 class="text-white font-bold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Stock Alert
                            </h3>
                            <p class="text-white text-sm mt-1">{{ $stockAlertCount }} barang perlu perhatian</p>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @php
                                $alerts = \Illuminate\Support\Facades\DB::select('
                                    SELECT b.nama, ks.stock,
                                           CASE
                                               WHEN ks.stock = 0 THEN "critical"
                                               WHEN ks.stock <= 5 THEN "danger"
                                               WHEN ks.stock <= 10 THEN "warning"
                                           END as level
                                    FROM barang b
                                    INNER JOIN kartu_stok ks ON b.idbarang = ks.idbarang
                                    WHERE b.status = 1 AND ks.stock <= 10
                                    ORDER BY ks.stock ASC
                                    LIMIT 10
                                ');
                            @endphp
                            <div class="divide-y divide-gray-100">
                                @foreach($alerts as $alert)
                                <div class="p-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $alert->nama }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                @if($alert->level == 'critical')
                                                    <span class="text-red-600 font-semibold">Stock Habis!</span>
                                                @elseif($alert->level == 'danger')
                                                    <span class="text-orange-600 font-semibold">Stock Kritis</span>
                                                @else
                                                    <span class="text-yellow-600 font-semibold">Stock Rendah</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-sm {{
                                                $alert->level == 'critical' ? 'bg-red-500' :
                                                ($alert->level == 'danger' ? 'bg-orange-500' : 'bg-yellow-500')
                                            }}">
                                                {{ $alert->stock }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 border-t">
                            <a href="{{ route('laporan.stock_rendah') }}"
                               class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                Lihat Semua Alert →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- User Menu -->
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
