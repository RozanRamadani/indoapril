<footer class="bg-gray-800 text-white mt-auto">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Company Info -->
            <div>
                <h3 class="text-lg font-semibold mb-4">IndoApril</h3>
                <p class="text-gray-400 text-sm">
                    Sistem manajemen inventori dan penjualan terpadu untuk kebutuhan bisnis Anda.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Menu</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-white transition">Home</a></li>
                    <li><a href="{{ route('barang.index') }}" class="text-gray-400 hover:text-white transition">Barang</a></li>
                    <li><a href="{{ url('/vendor') }}" class="text-gray-400 hover:text-white transition">Vendor</a></li>
                    <li><a href="{{ url('/penjualan') }}" class="text-gray-400 hover:text-white transition">Penjualan</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Kontak</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>Email: info@indoapril.com</li>
                    <li>Phone: +62 852 2522 1311</li>
                    <li>Alamat: Surabaya, Indonesia</li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} IndoApril. All rights reserved.</p>
        </div>
    </div>
</footer>
