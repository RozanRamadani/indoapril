<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Barang\BarangController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Satuan\SatuanController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\MarginPenjualan\MarginPenjualanController;
use App\Http\Controllers\Transaksi\TransaksiController;
use App\Http\Controllers\Pengadaan\PengadaanController;
use App\Http\Controllers\Penerimaan\PenerimaanController;
use App\Http\Controllers\Penjualan\PenjualanController;
use App\Http\Controllers\Retur\ReturController;
use App\Http\Controllers\Laporan\LaporanController;
use App\Http\Controllers\DashboardController;

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// PROTECTED ROUTES (Authentication Required)
// ============================================
Route::middleware(['check.auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // DATA MASTER (Super Admin & Admin)
    // ============================================
    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::resource('barang', BarangController::class);
        Route::resource('vendor', VendorController::class);
        Route::resource('satuan', SatuanController::class)->except(['show']);
        Route::resource('margin_penjualan', MarginPenjualanController::class)->except(['show']);
    });

    // ============================================
    // USER MANAGEMENT (Super Admin Only)
    // ============================================
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('user', UserController::class);
        Route::patch('user/{user}/deactivate', [UserController::class, 'deactivate'])->name('user.deactivate');
        Route::patch('user/{user}/activate', [UserController::class, 'activate'])->name('user.activate');

        // Role Management
        Route::get('role', [UserController::class, 'roleIndex'])->name('role.index');
        Route::get('role/create', [UserController::class, 'roleCreate'])->name('role.create');
        Route::post('role', [UserController::class, 'roleStore'])->name('role.store');
        Route::get('role/{role}/edit', [UserController::class, 'roleEdit'])->name('role.edit');
        Route::put('role/{role}', [UserController::class, 'roleUpdate'])->name('role.update');
        // Route::delete('role/{role}', [UserController::class, 'roleDestroy'])->name('role.destroy');
        // Deletion of roles via HTTP route is disabled by request to prevent accidental removals.
    });

    // ============================================
    // TRANSAKSI (Super Admin & Admin)
    // ============================================
    Route::middleware(['role:superadmin,admin'])->group(function () {

        // routes tambahan untuk Satuan
        Route::patch('satuan/{satuan}/toggle-status', [SatuanController::class, 'toggleStatus'])->name('satuan.toggleStatus');
        Route::get('api/satuan/active', [SatuanController::class, 'getActive'])->name('satuan.getActive');

        // routes tambahan untuk Margin Penjualan
        Route::patch('margin_penjualan/{margin_penjualan}/toggle-status', [MarginPenjualanController::class, 'toggleStatus'])->name('margin_penjualan.toggleStatus');

        // Transaksi: daftar penerimaan / pengadaan / penjualan
        Route::get('transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');

        // ==================== PENGADAAN (KERANJANG FLOW) ====================
        Route::prefix('pengadaan')->name('pengadaan.')->group(function () {
            Route::get('/', [PengadaanController::class, 'index'])->name('index');
            Route::get('/create', [PengadaanController::class, 'create'])->name('create');
            Route::post('/', [PengadaanController::class, 'store'])->name('store');
            Route::get('/{id}', [PengadaanController::class, 'show'])->name('show');
            Route::get('/{id}/detail', [PengadaanController::class, 'detail'])->name('detail');
            Route::post('/{id}/add-item', [PengadaanController::class, 'addItem'])->name('addItem');
            Route::put('/{id}/update-item/{detailId}', [PengadaanController::class, 'updateItem'])->name('updateItem');
            Route::delete('/{id}/delete-item/{detailId}', [PengadaanController::class, 'deleteItem'])->name('deleteItem');
            Route::post('/{id}/finalize', [PengadaanController::class, 'finalize'])->name('finalize');
            Route::delete('/{id}', [PengadaanController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/purchase-order', [PengadaanController::class, 'printPO'])->name('printPO');
        });

        // ==================== PENERIMAAN (SIMPLIFIED - 1 Pengadaan per Penerimaan) ====================
        Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
            Route::get('/', [PenerimaanController::class, 'index'])->name('index');
            Route::get('/create', [PenerimaanController::class, 'create'])->name('create');
            Route::post('/store', [PenerimaanController::class, 'store'])->name('store');
            Route::get('/{id}', [PenerimaanController::class, 'show'])->name('show');
            Route::get('/{id}/detail', [PenerimaanController::class, 'detail'])->name('detail');
            Route::post('/{id}/add-item', [PenerimaanController::class, 'addItem'])->name('addItem');
            Route::put('/{id}/update-item/{detailId}', [PenerimaanController::class, 'updateItem'])->name('updateItem');
            Route::delete('/{id}/delete-item/{detailId}', [PenerimaanController::class, 'deleteItem'])->name('deleteItem');
            Route::post('/{id}/finalize', [PenerimaanController::class, 'finalize'])->name('finalize');
            Route::delete('/{id}', [PenerimaanController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/goods-receipt', [PenerimaanController::class, 'printGRN'])->name('printGRN');
        });

        // Penjualan
        Route::resource('penjualan', PenjualanController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('penjualan/{id}/invoice', [PenjualanController::class, 'printInvoice'])->name('penjualan.printInvoice');

        // ==================== RETUR ====================
        Route::prefix('retur')->name('retur.')->group(function () {
            Route::get('/', [ReturController::class, 'index'])->name('index');
            Route::get('/create', [ReturController::class, 'create'])->name('create');
            Route::get('/{idpenerimaan}/detail', [ReturController::class, 'detail'])->name('detail');
            Route::post('/store', [ReturController::class, 'store'])->name('store');
            Route::get('/show/{id}', [ReturController::class, 'show'])->name('show');
        });
    });

    // ==================== LAPORAN (READ-ONLY SP - All Authenticated Users) ====================
    Route::prefix('laporan')->group(function () {
        // Laporan Stock
        Route::get('stock-opname', [LaporanController::class, 'stockOpname'])->name('laporan.stock_opname');
        Route::get('stock-rendah', [LaporanController::class, 'stockRendah'])->name('laporan.stock_rendah');
        Route::get('kartu-stok', [LaporanController::class, 'kartuStok'])->name('laporan.kartu_stok');

        // Laporan Penjualan
        Route::get('penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');

        // Laporan Pengadaan
        Route::get('pengadaan', [LaporanController::class, 'pengadaan'])->name('laporan.pengadaan');

        // Laporan Penerimaan
        Route::get('penerimaan', [LaporanController::class, 'penerimaan'])->name('laporan.penerimaan');

        // Export Routes
        Route::get('stock-opname/export', [LaporanController::class, 'exportStockOpname'])->name('laporan.stock_opname.export');
        Route::get('penjualan/export', [LaporanController::class, 'exportPenjualan'])->name('laporan.penjualan.export');
        Route::get('pengadaan/export', [LaporanController::class, 'exportPengadaan'])->name('laporan.pengadaan.export');
        Route::get('penerimaan/export', [LaporanController::class, 'exportPenerimaan'])->name('laporan.penerimaan.export');
    });
});
