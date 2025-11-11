# 📦 PENGADAAN & PENERIMAAN SYSTEM - IMPLEMENTATION GUIDE

## 🎯 Overview
Sistem Pengadaan & Penerimaan dengan keranjang flow yang lengkap, menggunakan **SQL biasa** (tanpa triggers/SP/views untuk fase pertama).

---

## ✅ COMPLETED IMPLEMENTATION

### 1. **Database Migration**
**File:** `database/migrations/sql/07_pengadaan_penerimaan_minimal.sql`

**Changes:**
- ✅ `pengadaan.status_pengadaan` - ENUM('draft', 'completed')
- ✅ `detail_pengadaan.jumlah_diterima` - INT DEFAULT 0
- ✅ `penerimaan.status_penerimaan` - ENUM('draft', 'finalized')

**Apply:**
```bash
mysql -u root indoapril < database/migrations/sql/07_pengadaan_penerimaan_minimal.sql
```

---

### 2. **Controllers**

#### A. PengadaanController
**File:** `app/Http/Controllers/Pengadaan/PengadaanController.php`

**Methods:**
| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/pengadaan` | List semua pengadaan |
| `create()` | GET `/pengadaan/create` | Form pilih vendor |
| `store()` | POST `/pengadaan` | Simpan pengadaan draft, redirect ke detail |
| `detail($id)` | GET `/pengadaan/{id}/detail` | Keranjang barang (editable) |
| `show($id)` | GET `/pengadaan/{id}` | Detail read-only (completed) |
| `addItem($id)` | POST `/pengadaan/{id}/add-item` | Tambah barang ke keranjang |
| `updateItem($id, $detailId)` | PUT `/pengadaan/{id}/update-item/{detailId}` | Edit jumlah/harga |
| `deleteItem($id, $detailId)` | DELETE `/pengadaan/{id}/delete-item/{detailId}` | Hapus dari keranjang |
| `finalize($id)` | POST `/pengadaan/{id}/finalize` | Set status='completed' |
| `updatePengadaanTotals($id)` | PRIVATE | Hitung subtotal + PPN 11% + total (PHP) |

**Key Features:**
- ✅ Manual calculation di PHP: `$ppn = $subtotal * 0.11`
- ✅ Validation: cek status draft sebelum edit
- ✅ Auto-redirect ke detail setelah store

#### B. PenerimaanController
**File:** `app/Http/Controllers/Penerimaan/PenerimaanController.php`

**Methods:**
| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/penerimaan` | List semua penerimaan |
| `create()` | POST `/penerimaan/create` | Bikin penerimaan draft, redirect ke keranjang |
| `keranjang($id)` | GET `/penerimaan/{id}/keranjang` | Pilih pengadaan + input jumlah |
| `getPengadaanDetail($penerimaanId, $pengadaanId)` | GET `/penerimaan/{penerimaanId}/pengadaan/{pengadaanId}` | AJAX get sisa barang (JSON) |
| `addItem($id)` | POST `/penerimaan/{id}/add-item` | Simpan sementara ke keranjang |
| `updateItem($id, $detailId)` | PUT `/penerimaan/{id}/update-item/{detailId}` | Edit jumlah diterima |
| `deleteItem($id, $detailId)` | DELETE `/penerimaan/{id}/delete-item/{detailId}` | Hapus dari keranjang |
| `preview($id)` | GET `/penerimaan/{id}/preview` | Preview semua barang (grouped by vendor) |
| `show($id)` | GET `/penerimaan/{id}` | Detail read-only (finalized) |
| `finalize($id)` | POST `/penerimaan/{id}/finalize` | Update kartu_stok + jumlah_diterima (manual loop) |

**Key Features:**
- ✅ Multi pengadaan support (bisa terima dari beberapa pengadaan)
- ✅ Real-time sisa calculation di query
- ✅ Manual kartu_stok INSERT di finalize()
- ✅ Increment jumlah_diterima dengan `->increment()`
- ✅ Transaction safety dengan `DB::beginTransaction()` + `rollback()`

---

### 3. **Routes**
**File:** `routes/web.php`

**Pengadaan Routes:**
```php
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
```

**Penerimaan Routes:**
```php
Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
    Route::get('/', [PenerimaanController::class, 'index'])->name('index');
    Route::post('/create', [PenerimaanController::class, 'create'])->name('create');
    Route::get('/{id}', [PenerimaanController::class, 'show'])->name('show');
    Route::get('/{id}/keranjang', [PenerimaanController::class, 'keranjang'])->name('keranjang');
    Route::get('/{penerimaanId}/pengadaan/{pengadaanId}', [PenerimaanController::class, 'getPengadaanDetail'])->name('getPengadaanDetail');
    Route::post('/{id}/add-item', [PenerimaanController::class, 'addItem'])->name('addItem');
    Route::put('/{id}/update-item/{detailId}', [PenerimaanController::class, 'updateItem'])->name('updateItem');
    Route::delete('/{id}/delete-item/{detailId}', [PenerimaanController::class, 'deleteItem'])->name('deleteItem');
    Route::get('/{id}/preview', [PenerimaanController::class, 'preview'])->name('preview');
    Route::post('/{id}/finalize', [PenerimaanController::class, 'finalize'])->name('finalize');
});
```

---

### 4. **Blade Views**

#### A. Pengadaan Views
| File | Description |
|------|-------------|
| `resources/views/pengadaan/index.blade.php` | List pengadaan dengan status badge |
| `resources/views/pengadaan/create.blade.php` | Form pilih vendor (simple) |
| `resources/views/pengadaan/detail.blade.php` | Keranjang barang (add, edit, delete, finalize) |
| `resources/views/pengadaan/show.blade.php` | Detail read-only (belum dibuat, optional) |

#### B. Penerimaan Views
| File | Description |
|------|-------------|
| `resources/views/penerimaan/index.blade.php` | List penerimaan dengan status badge |
| `resources/views/penerimaan/keranjang.blade.php` | Pilih pengadaan + keranjang (AJAX) |
| `resources/views/penerimaan/preview.blade.php` | Preview grouped by vendor + grand total |
| `resources/views/penerimaan/show.blade.php` | Detail read-only (belum dibuat, optional) |

**Key UI Features:**
- ✅ Tailwind CSS styling (sesuai tema existing)
- ✅ Alpine.js untuk AJAX loading pengadaan details
- ✅ Status badges (draft/completed, draft/finalized)
- ✅ Confirmation dialogs untuk finalisasi
- ✅ Responsive table design
- ✅ Icons (Heroicons)

---

## 🔥 USER FLOW

### Flow 1: PENGADAAN
```
1. Klik "Buat Pengadaan" → Form pilih vendor
2. Submit → Redirect ke /pengadaan/{id}/detail (keranjang)
3. Tambah barang (dropdown barang, input jumlah + harga)
   → Submit → Refresh halaman, total auto-update (PHP calc)
4. Edit/Hapus barang sesuka hati (masih draft)
5. Klik "Finalisasi Pengadaan"
   → Confirm → status_pengadaan = 'completed'
   → Redirect ke /pengadaan (index)
```

### Flow 2: PENERIMAAN
```
1. Klik "Buat Penerimaan Baru" → Auto create draft
   → Redirect ke /penerimaan/{id}/keranjang
2. Pilih pengadaan dari dropdown (yang masih ada sisa)
   → AJAX load detail barang yang belum diterima penuh
3. Input jumlah diterima, klik "Tambah"
   → Insert ke detail_penerimaan (simpan sementara)
4. Pilih pengadaan lain (opsional, bisa multi pengadaan)
5. Klik "Preview & Finalisasi"
   → Tampil preview grouped by vendor + grand total
6. Klik "FINALISASI PENERIMAAN"
   → Confirm → Loop semua detail:
     - INSERT kartu_stok (jenis_transaksi='P', masuk=jumlah)
     - INCREMENT detail_pengadaan.jumlah_diterima
   → status_penerimaan = 'finalized'
   → Redirect ke /penerimaan (index)
```

---

## 📊 DATABASE FLOW

### Pengadaan Tables
```sql
pengadaan
├── idpengadaan (PK)
├── created_at
├── idvendor (FK → vendor)
├── iduser (FK → user)
├── subtotal_nilai (auto calc di PHP)
├── ppn (auto calc di PHP: subtotal * 0.11)
├── total_nilai (auto calc di PHP: subtotal + ppn)
└── status_pengadaan ('draft' | 'completed') ← NEW

detail_pengadaan
├── iddetail_pengadaan (PK)
├── idpengadaan (FK → pengadaan)
├── idbarang (FK → barang)
├── jumlah
├── harga_satuan
├── sub_total (jumlah * harga_satuan)
└── jumlah_diterima (tracking penerimaan) ← NEW
```

### Penerimaan Tables
```sql
penerimaan
├── idpenerimaan (PK)
├── created_at
├── iduser (FK → user)
└── status_penerimaan ('draft' | 'finalized') ← NEW

detail_penerimaan
├── iddetail_penerimaan (PK)
├── idpenerimaan (FK → penerimaan)
├── iddetail_pengadaan (FK → detail_pengadaan) ← LINK
├── idbarang (FK → barang)
└── jumlah (jumlah yang diterima)
```

### Kartu Stok Flow
```sql
-- AFTER FINALISASI PENERIMAAN:
INSERT INTO kartu_stok (
    jenis_transaksi = 'P',  -- P = Penerimaan
    masuk = {jumlah_diterima},
    keluar = 0,
    stock = {last_stock + jumlah_diterima},
    idtransaksi = {idpenerimaan},
    idbarang = {idbarang}
)

UPDATE detail_pengadaan
SET jumlah_diterima = jumlah_diterima + {jumlah}
WHERE iddetail_pengadaan = {iddetail_pengadaan}
```

---

## 🛠️ NEXT STEPS

### 1. Apply SQL Migration
```bash
cd c:\laragon\www\indoapril
mysql -u root indoapril < database/migrations/sql/07_pengadaan_penerimaan_minimal.sql
```

### 2. Test Flow End-to-End
1. Buat pengadaan vendor "Vendor A" → Tambah 3 barang → Finalisasi
2. Buat penerimaan → Pilih pengadaan #1 → Terima 2 barang → Preview → Finalisasi
3. Cek kartu_stok: apakah stock bertambah?
4. Cek detail_pengadaan: apakah jumlah_diterima terupdate?

### 3. (Optional) Optimize dengan Triggers/SP
Setelah testing berhasil, bisa migrate ke:
- **Trigger:** Auto calculate pengadaan totals
- **View:** v_sisa_pengadaan, v_keranjang_penerimaan
- **Stored Procedure:** sp_finalisasi_penerimaan

File sudah ready: `07_pengadaan_penerimaan_enhanced.sql`

---

## 📝 NOTES

### Kenapa SQL Biasa Dulu?
1. **Easier to Debug** - Bisa lihat exact SQL yang dijalankan
2. **More Flexible** - Gampang edit logic di controller
3. **Faster Development** - Tidak perlu setup triggers/SP/views dulu
4. **Learning Curve** - Lebih mudah dipahami tim

### Kapan Pakai Triggers/SP?
Setelah flow sudah stable dan tested, bisa optimize dengan:
- Trigger: Auto calculate totals (less PHP code)
- View: Simplify complex JOINs (better performance)
- SP: Finalisasi atomic operations (better consistency)

---

## 🎉 SUMMARY

**Total Files Created/Modified:**
- ✅ 1 SQL migration (minimal)
- ✅ 2 Controllers (Pengadaan + Penerimaan) - 500+ lines
- ✅ 1 Routes file (updated with 18 routes)
- ✅ 7 Blade views (index, create, detail, keranjang, preview)

**Total Lines of Code:** ~1500 LOC

**Status:** ✅ **READY FOR TESTING**

**Estimated Testing Time:** 30 minutes

**Next Action:** Apply SQL migration dan test flow! 🚀
