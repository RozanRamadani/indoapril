<?php

/**
 * TAMBAHKAN KE routes/web.php
 * 
 * Routes untuk demonstrasi fitur SQL Advanced
 * Copy-paste code ini ke file routes/web.php Anda
 */

use App\Http\Controllers\Barang\BarangController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard (sudah ada, pastikan tidak duplikat)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ===== BARANG ROUTES =====

// Basic CRUD (resource route - sudah ada, pastikan tidak duplikat)
Route::resource('barang', BarangController::class);

// Advanced SQL Features Demo
Route::prefix('barang')->name('barang.')->group(function () {
    
    // SUBQUERY Demo: Barang di atas harga rata-rata
    Route::get('/above-average', [BarangController::class, 'aboveAverage'])
        ->name('above-average');
    
    // AGGREGATE Functions Demo: Statistik per jenis barang
    Route::get('/statistics', [BarangController::class, 'statistics'])
        ->name('statistics');
    
    // LIKE Operator Demo: Search barang
    Route::get('/search', [BarangController::class, 'search'])
        ->name('search');
    
    // BETWEEN Operator Demo: Filter by price range
    Route::get('/filter-price', [BarangController::class, 'filterByPrice'])
        ->name('filter-price');
    
    // SQL VIEW Demo: Menggunakan views
    Route::get('/view-demo', [BarangController::class, 'viewDemo'])
        ->name('view-demo');
    
    // STORED PROCEDURE Demo: Call procedures
    Route::get('/procedure-demo', [BarangController::class, 'procedureDemo'])
        ->name('procedure-demo');
    
    // CASE Statement Demo: Complex query dengan conditional logic
    Route::get('/complex-query', [BarangController::class, 'complexQuery'])
        ->name('complex-query');
});

// ===== VENDOR ROUTES =====

// Basic CRUD (resource route - sudah ada, pastikan tidak duplikat)
Route::resource('vendor', VendorController::class);
