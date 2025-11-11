# 🔄 Sistem Pengadaan & Penerimaan - Flow Guide

## 📦 **FLOW 1: PENGADAAN (dengan Keranjang)**

### Step 1: Create Pengadaan (Pilih Vendor)
```
URL: /pengadaan/create
Method: GET

Form Input:
- Vendor (dropdown dari tabel vendor yang status='Y')

Action: Klik "Next" → Simpan pengadaan sebagai 'draft'
```

### Step 2: Detail Pengadaan (Keranjang Belanja)
```
URL: /pengadaan/{id}/detail
Method: GET

Tampilan:
- Info Vendor yang dipilih
- Form tambah barang:
  * Barang (dropdown dari tabel barang yang status=1)
  * Jumlah (input number)
  * Harga Satuan (auto-fill dari barang.harga, bisa edit)
  * Subtotal (auto calculate: jumlah × harga_satuan)
  
- Tabel Keranjang Detail Pengadaan
  * List barang yang sudah ditambahkan
  * Total Subtotal
  * PPN (11%)
  * Grand Total

Action: 
- "Tambah Barang" → Insert ke detail_pengadaan
- "Hapus" → Delete dari detail_pengadaan
- "Simpan & Finalisasi" → Update status_pengadaan = 'completed'
```

### Step 3: Trigger Otomatis
```sql
AFTER INSERT/UPDATE/DELETE detail_pengadaan:
→ Auto calculate:
  - subtotal_nilai (SUM jumlah × harga_satuan)
  - ppn (subtotal × 11%)
  - total_nilai (subtotal + ppn)
→ Update ke tabel pengadaan
```

---

## 📥 **FLOW 2: PENERIMAAN (dengan Keranjang & Preview)**

### Step 1: Create Penerimaan (Draft)
```
URL: /penerimaan/create
Method: POST

Action: Create penerimaan baru dengan status='draft'
Redirect ke: /penerimaan/{id}/keranjang
```

### Step 2: Keranjang Penerimaan (Loop Input)
```
URL: /penerimaan/{id}/keranjang
Method: GET

Form Input:
- ID Pengadaan (dropdown dari pengadaan yang status='completed')
  
Setelah pilih ID Pengadaan:
→ Tampil tabel barang dari view: v_sisa_pengadaan
  * Nama Barang
  * Jumlah Pengadaan
  * Jumlah Sudah Diterima
  * Sisa Belum Diterima ← PENTING!
  * Input: Jumlah Diterima Sekarang (max = sisa)

Action:
- "Simpan Sementara" → Insert ke detail_penerimaan (belum update kartu_stok)
- Loop: User bisa pilih ID pengadaan lagi
  → View v_sisa_pengadaan otomatis update (kurangi yang di keranjang)
```

### Step 3: Preview Penerimaan
```
URL: /penerimaan/{id}/preview
Method: GET

Tampilan: Tabel preview dari view: v_preview_penerimaan
- Vendor
- Barang yang akan diterima
- Jumlah
- Total Items

Action:
- "Hapus Item" → Delete dari detail_penerimaan
- "Edit Jumlah" → Update detail_penerimaan
- "Finalisasi" → CALL sp_finalisasi_penerimaan(id)
```

### Step 4: Finalisasi (Trigger Kartu Stok)
```sql
CALL sp_finalisasi_penerimaan(p_idpenerimaan):

Loop setiap detail_penerimaan:
1. Get last_stock dari kartu_stok
2. INSERT kartu_stok:
   - jenis_transaksi = 'P' (Penerimaan)
   - masuk = jumlah
   - keluar = 0
   - stock = last_stock + jumlah
3. UPDATE detail_pengadaan:
   - jumlah_diterima += jumlah
4. UPDATE penerimaan:
   - status_penerimaan = 'finalized'

Redirect ke: /penerimaan (list)
```

---

## 🎯 **KEY FEATURES**

### 1. **Tracking Sisa Pengadaan**
```sql
View: v_sisa_pengadaan
→ Menampilkan barang yang belum sepenuhnya diterima
→ Auto calculate: sisa = jumlah_pengadaan - jumlah_diterima
```

### 2. **Keranjang Penerimaan (Draft)**
- Status penerimaan = 'draft' → Bisa edit/hapus
- User bisa input bertahap dari berbagai pengadaan
- Preview sebelum finalisasi

### 3. **Finalisasi Irreversible**
- Status = 'finalized' → Tidak bisa edit
- Kartu stok langsung terupdate
- Jumlah_diterima di detail_pengadaan terupdate

### 4. **Auto Calculate**
- Pengadaan: Subtotal + PPN + Total (via trigger)
- Penerimaan: Sisa barang (via view)

---

## 📊 **DATABASE CHANGES**

### Kolom Baru:
```sql
pengadaan.status_pengadaan → ENUM('draft', 'completed')
detail_pengadaan.jumlah_diterima → INT (tracking)
penerimaan.status_penerimaan → ENUM('draft', 'finalized')
```

### Triggers:
- `trg_after_insert_detail_pengadaan_update_total`
- `trg_after_update_detail_pengadaan_update_total`
- `trg_after_delete_detail_pengadaan_update_total`

### Views:
- `v_sisa_pengadaan` → Sisa barang belum diterima
- `v_preview_penerimaan` → Preview keranjang penerimaan

### Stored Procedures:
- `sp_finalisasi_penerimaan(id)` → Finalize + update kartu_stok

---

## 🔧 **IMPLEMENTASI**

### Urutan Apply SQL:
```bash
# 1. Apply enhanced system
mysql -u root indoapril < database/migrations/sql/07_pengadaan_penerimaan_enhanced.sql

# 2. Verify
mysql -u root indoapril -e "SHOW TRIGGERS LIKE 'trg_%'"
mysql -u root indoapril -e "SHOW PROCEDURE STATUS WHERE Db='indoapril'"
mysql -u root indoapril -e "SELECT * FROM v_sisa_pengadaan LIMIT 5"
```

### Controller Routes:
```php
// Pengadaan
Route::resource('pengadaan', PengadaanController::class);
Route::get('pengadaan/{id}/detail', [PengadaanController::class, 'detail']);
Route::post('pengadaan/{id}/add-item', [PengadaanController::class, 'addItem']);
Route::delete('pengadaan/item/{id}', [PengadaanController::class, 'deleteItem']);
Route::post('pengadaan/{id}/finalize', [PengadaanController::class, 'finalize']);

// Penerimaan
Route::resource('penerimaan', PenerimaanController::class);
Route::get('penerimaan/{id}/keranjang', [PenerimaanController::class, 'keranjang']);
Route::post('penerimaan/{id}/add-item', [PenerimaanController::class, 'addItem']);
Route::get('penerimaan/{id}/preview', [PenerimaanController::class, 'preview']);
Route::delete('penerimaan/item/{id}', [PenerimaanController::class, 'deleteItem']);
Route::post('penerimaan/{id}/finalize', [PenerimaanController::class, 'finalize']);
```

---

## ✅ **VALIDASI**

### Pengadaan:
- ✅ Vendor wajib dipilih
- ✅ Minimal 1 barang di detail
- ✅ Harga satuan > 0
- ✅ Jumlah > 0
- ✅ Total otomatis terhitung

### Penerimaan:
- ✅ Hanya pengadaan status='completed' yang bisa dipilih
- ✅ Jumlah diterima ≤ sisa belum diterima
- ✅ Tidak bisa finalisasi keranjang kosong
- ✅ Setelah finalisasi tidak bisa edit

---

## 📝 **CONTOH USAGE**

### Scenario: Pengadaan 100 Buku, Penerimaan Bertahap

```
1. PENGADAAN:
   - Vendor: Toko Buku Maju
   - Barang: Buku Tulis (100 pcs @ Rp 5,000)
   - Subtotal: Rp 500,000
   - PPN 11%: Rp 55,000
   - Total: Rp 555,000
   - Status: 'completed'

2. PENERIMAAN #1 (30 pcs):
   - Pilih ID Pengadaan #1
   - Sisa: 100 pcs
   - Input: 30 pcs diterima
   - Simpan sementara (draft)
   
3. PENERIMAAN #1 (tambahan 20 pcs):
   - Pilih ID Pengadaan #1 lagi
   - Sisa: 100 - 30 = 70 pcs ← auto update!
   - Input: 20 pcs diterima
   - Preview: Total 50 pcs (30 + 20)
   - Finalisasi → kartu_stok +50

4. PENERIMAAN #2 (sisa 50 pcs):
   - Create penerimaan baru
   - Pilih ID Pengadaan #1
   - Sisa: 100 - 50 = 50 pcs ← dari penerimaan sebelumnya
   - Input: 50 pcs
   - Finalisasi → kartu_stok +50
   
5. RESULT:
   - detail_pengadaan.jumlah_diterima = 100 (FULL)
   - v_sisa_pengadaan tidak tampil pengadaan #1 (sudah complete)
```

---

**File SQL sudah dibuat di:**
`database/migrations/sql/07_pengadaan_penerimaan_enhanced.sql`

**Next: Buat Controllers & Views?**
