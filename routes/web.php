<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Laporan\LaporanController;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('barang', BarangController::class);
Route::resource('vendor', VendorController::class);
Route::resource('satuan', SatuanController::class)->except(['show']);
Route::resource('margin_penjualan', MarginPenjualanController::class)->except(['show']);

// User & Role (MERGED)
Route::resource('user', UserController::class);
Route::patch('user/{user}/deactivate', [UserController::class, 'deactivate'])->name('user.deactivate');
Route::patch('user/{user}/activate', [UserController::class, 'activate'])->name('user.activate');

// Role Management (via UserController)
Route::get('role', [UserController::class, 'roleIndex'])->name('role.index');
Route::get('role/create', [UserController::class, 'roleCreate'])->name('role.create');
Route::post('role', [UserController::class, 'roleStore'])->name('role.store');
Route::get('role/{role}/edit', [UserController::class, 'roleEdit'])->name('role.edit');
Route::put('role/{role}', [UserController::class, 'roleUpdate'])->name('role.update');
Route::delete('role/{role}', [UserController::class, 'roleDestroy'])->name('role.destroy');

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
});

// Penjualan
Route::resource('penjualan', PenjualanController::class)->only(['index', 'create', 'store', 'show']);

// ==================== LAPORAN (READ-ONLY SP) ====================
Route::prefix('laporan')->group(function () {
    // Laporan Stock
    Route::get('stock-opname', [LaporanController::class, 'stockOpname'])->name('laporan.stock_opname');
    Route::get('stock-rendah', [LaporanController::class, 'stockRendah'])->name('laporan.stock_rendah');
    Route::get('kartu-stok', [LaporanController::class, 'kartuStok'])->name('laporan.kartu_stok');

    // Laporan Penjualan
    Route::get('penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');

    // Laporan Pengadaan
    Route::get('pengadaan', [LaporanController::class, 'pengadaan'])->name('laporan.pengadaan');
});
