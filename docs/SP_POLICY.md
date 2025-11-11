# Kebijakan Penggunaan SP, Function, dan Trigger

## Prinsip Dasar

Berdasarkan best practice dan arsitektur aplikasi Laravel + MySQL, berikut pembagian tanggung jawab:

### 1. **STORED PROCEDURES (SP)** - READ ONLY
SP **HANYA** digunakan untuk operasi READ (SELECT):
- ✅ Laporan (mingguan, bulanan, tahunan)
- ✅ Filtering data kompleks
- ✅ Query agregasi yang sering dipakai
- ❌ **TIDAK** untuk INSERT, UPDATE, DELETE

**Alasan Teknis (Kenapa SP Tidak untuk WRITE)**:

#### 🔴 Masalah #1: Debugging Sangat Sulit
```php
// ❌ MASALAH: Error di SP tidak tertangkap dengan jelas
try {
    DB::statement('CALL sp_create_penjualan(?, ?, @id)', [$user, $margin]);
} catch (\Exception $e) {
    // Error message: "SQLSTATE[HY000]: General error: 1366"
    // ❓ Error di line mana? Variable mana? Logic mana yang salah?
    // Stack trace tidak menunjuk ke kode SP, hanya ke statement CALL
}

// ✅ SOLUSI: Direct DB operation
try {
    DB::table('penjualan')->insert([...]);
    foreach ($items as $item) {
        DB::table('detail_penjualan')->insert([...]);
    }
} catch (\Exception $e) {
    // Error message jelas: "Column 'harga_satuan' cannot be null"
    // Stack trace menunjuk exact line di controller
    // Bisa breakpoint, inspect variable, easy debugging
}
```

#### 🔴 Masalah #2: Laravel Tidak Tahu Apa yang Terjadi di Dalam SP
```php
// ❌ MASALAH: Black box
DB::statement('CALL sp_finalize_pengadaan(?)', [$id]);
// Laravel tidak tahu:
// - Berapa banyak row yang di-INSERT?
// - Table mana saja yang terpengaruh?
// - Apakah ada error di tengah-tengah?
// - Apakah rollback sudah terjadi?
```

#### 🔴 Masalah #3: Testing Nightmare
```php
// ❌ SULIT: Test SP membutuhkan DB integration test selalu
public function test_create_penjualan() {
    // Harus setup DB, run migration, seed data
    // Tidak bisa mock, tidak bisa unit test
    DB::statement('CALL sp_create_penjualan(?, ?, @id)', [1, 1]);
    // Susah assert apa yang terjadi di dalam SP
}

// ✅ MUDAH: Test controller logic tanpa DB (dengan mock)
public function test_create_penjualan() {
    $mock = Mockery::mock(DB::class);
    $mock->shouldReceive('insert')->once();
    // Bisa test logic, bisa mock dependencies, fast
}
```

#### 🔴 Masalah #4: Transaction Conflict
```php
// ❌ MASALAH: SP manage transaction sendiri
DB::beginTransaction();
try {
    DB::statement('CALL sp_create_pengadaan(...)'); 
    // SP di dalam punya START TRANSACTION sendiri
    // Conflict! Nested transaction? Commit mana yang valid?
    DB::commit(); // Ini commit apa?
} catch (\Exception $e) {
    DB::rollBack(); // Ini rollback apa?
}
```

#### 🔴 Masalah #5: Code Review & Version Control
```sql
-- ❌ SULIT: Logic tersebar
-- File: sp_create_penjualan.sql (di database)
CREATE PROCEDURE sp_create_penjualan(...)
BEGIN
  -- 100 lines of logic here
  -- Siapa yang tau ini diubah kapan? Oleh siapa?
  -- Git history tidak tracking perubahan di DB
END;
```

```php
// ✅ MUDAH: Semua logic di Git
class PenjualanController {
    public function store(Request $request) {
        // Code review langsung di Pull Request
        // Git blame bisa track perubahan
        // CI/CD bisa test otomatis
    }
}
```

#### 🔴 Masalah #6: Error Handling Tidak Konsisten
```sql
-- Di dalam SP, error handling berbeda
DELIMITER $$
CREATE PROCEDURE sp_add_detail_penjualan(...)
BEGIN
  -- Jika error, apa yang terjadi?
  -- SIGNAL? ROLLBACK? Silent fail?
  -- Laravel tidak bisa catch dengan try-catch biasa
END$$
```

#### 🔴 Masalah #7: Deployment & Migration Ribet
```bash
# ❌ SULIT: Deploy SP harus manual ke DB
# 1. SSH ke server
# 2. mysql -u root -p
# 3. SOURCE sp_create_penjualan.sql
# 4. Lupa drop procedure lama? Syntax error saat deploy?
```

```bash
# ✅ MUDAH: Deploy Laravel migration
php artisan migrate
# Semua terkelola, rollback mudah, tercatat di migrations table
```

---

**Kesimpulan**: SP untuk WRITE membuat debugging susah, testing ribet, dan maintenance nightmare. Laravel sudah punya DB transaction, Eloquent, dan Query Builder yang reliable, testable, dan maintainable.

**Exception**: SP untuk READ (laporan) aman karena:
- Tidak ada side-effect
- Tidak perlu rollback
- Tidak ada transaction conflict
- Error hanya gagal fetch data, tidak corrupt data
- Testing cukup integration test biasa

### 2. **FUNCTIONS** - Kalkulasi
Function digunakan untuk:
- ✅ Kalkulasi matematis (subtotal, ppn, margin)
- ✅ Pembacaan data agregat (fn_get_stock)
- ✅ Operasi pure/deterministic
- ❌ **TIDAK** untuk side-effects (INSERT/UPDATE)

**Alasan**:
- Function bisa dipanggil di SELECT/WHERE
- Reusable di berbagai query
- Centralized calculation logic

### 3. **TRIGGERS** - Operasi Otomatis
Trigger digunakan untuk:
- ✅ Side-effects otomatis (kartu_stok auto-update)
- ✅ Data integrity enforcement
- ✅ Audit trail otomatis
- ⚠️ Hati-hati dengan performa (trigger runs per-row)

**Alasan**:
- Menjamin konsistensi data di semua jalur (app, import, manual)
- Tidak ada logic yang terlupa
- Atomic dengan transaction parent

### 4. **APPLICATION (Laravel Controller)** - WRITE Operations
Semua INSERT/UPDATE/DELETE dilakukan di Laravel:
- ✅ DB::transaction() untuk atomicity
- ✅ Validation di FormRequest
- ✅ Business logic di Service/Controller
- ✅ Gunakan Eloquent atau Query Builder

**Alasan**:
- Testing lebih mudah
- Code review lebih transparan
- Version control dan deployment clear
- Rollback/debugging straightforward

---

## Contoh Implementasi

### ❌ SALAH (OLD WAY)
```php
// Controller memanggil SP untuk INSERT
DB::statement('CALL sp_create_penjualan(?, ?, @id)', [$user, $margin]);
```

### ✅ BENAR (NEW WAY)
```php
// Controller melakukan INSERT langsung
DB::beginTransaction();
try {
    $penjualan = DB::table('penjualan')->insertGetId([
        'created_at' => now(),
        'iduser' => $iduser,
        'idmargin_penjualan' => $idmargin,
        'subtotal_nilai' => 0,
        'ppn' => 0,
        'total_nilai' => 0,
    ]);
    
    foreach ($items as $item) {
        $subtotal = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as val', 
            [$item['jumlah'], $item['harga_satuan']])->val;
        
        // Insert detail - TRIGGER akan otomatis update kartu_stok
        DB::table('detail_penjualan')->insert([
            'idpenjualan' => $penjualan,
            'idbarang' => $item['idbarang'],
            'jumlah' => $item['jumlah'],
            'harga_satuan' => $item['harga_satuan'],
            'subtotal' => $subtotal,
        ]);
    }
    
    // Update totals
    $subtotal_total = DB::selectOne('SELECT fn_penjualan_total(?) as val', [$penjualan])->val;
    $ppn = DB::selectOne('SELECT fn_calc_ppn(?) as val', [$subtotal_total])->val;
    $total = DB::selectOne('SELECT fn_calc_total(?, ?) as val', [$subtotal_total, $ppn])->val;
    
    DB::table('penjualan')->where('idpenjualan', $penjualan)->update([
        'subtotal_nilai' => $subtotal_total,
        'ppn' => $ppn,
        'total_nilai' => $total,
    ]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

---

## File Structure

```
migrations/sql/
├── 01_functions.sql        # Semua FUNCTION (kalkulasi)
├── 02_triggers.sql         # Semua TRIGGER (kartu_stok auto)
├── 03_stored_procedures.sql # Semua SP (READ-ONLY: laporan, filter)
└── templates/
    └── report_sp_template.sql  # Template untuk SP laporan baru
```

---

## Migration Guide (Lama → Baru)

### Step 1: Backup Database
```bash
mysqldump -u root indoapril > backup_before_migration.sql
```

### Step 2: Apply New Functions
```bash
mysql -u root indoapril < migrations/sql/01_functions.sql
```

### Step 3: Apply New Triggers
```bash
mysql -u root indoapril < migrations/sql/02_triggers.sql
```

### Step 4: Apply New SP (READ-ONLY)
```bash
mysql -u root indoapril < migrations/sql/03_stored_procedures.sql
```

### Step 5: Update Controllers
- Hapus semua `CALL sp_create_*`, `CALL sp_add_*`, `CALL sp_finalize_*`
- Ganti dengan direct DB operations seperti contoh di atas
- Gunakan `DB::transaction()` untuk atomicity

### Step 6: Drop Old SP (WRITE)
```sql
DROP PROCEDURE IF EXISTS sp_create_pengadaan;
DROP PROCEDURE IF EXISTS sp_add_detail_pengadaan;
DROP PROCEDURE IF EXISTS sp_finalize_pengadaan;
DROP PROCEDURE IF EXISTS sp_create_penjualan;
DROP PROCEDURE IF EXISTS sp_add_detail_penjualan;
DROP PROCEDURE IF EXISTS sp_finalize_penjualan;
DROP PROCEDURE IF EXISTS sp_create_penerimaan_from_pengadaan;
```

### Step 7: Test End-to-End
- Test create pengadaan → penerimaan → kartu_stok
- Test create penjualan → kartu_stok keluar
- Test fn_get_stock accuracy
- Test rollback scenario

---

## Checklist Pre-Commit

Sebelum commit SQL file baru, pastikan:

- [ ] SP hanya melakukan SELECT (tidak ada INSERT/UPDATE/DELETE)
- [ ] Function tidak mengubah data (DETERMINISTIC / READS SQL DATA)
- [ ] Trigger sudah handle INSERT/UPDATE/DELETE dengan benar
- [ ] Tidak ada START TRANSACTION / COMMIT / ROLLBACK di SP/Function
- [ ] Semua kalkulasi menggunakan function (fn_calc_*)
- [ ] Controller menggunakan DB::transaction() untuk WRITE

---

## FAQ

**Q: Kalau butuh SP baru untuk laporan, gimana formatnya?**  
A: Lihat `migrations/sql/templates/report_sp_template.sql` dan ikuti pattern yang sama.

**Q: Bagaimana kalau perlu update logic di trigger?**  
A: Edit file `02_triggers.sql`, lalu DROP dan CREATE ulang trigger di DB.

**Q: Apa yang terjadi jika lupa menulis kartu_stok?**  
A: Trigger otomatis akan menangani. Selama INSERT/UPDATE detail_penjualan atau detail_penerimaan dilakukan, trigger akan fire.

**Q: Bagaimana cara test trigger?**  
A: Buat test case di Laravel (PHPUnit) yang:
1. Insert detail_penjualan
2. Assert kartu_stok entry created
3. Assert fn_get_stock updated
4. Test rollback scenario

---

## Performance Notes

- **Trigger overhead**: Trigger berjalan per-row. Untuk bulk operations, pertimbangkan batch size.
- **Function dalam WHERE**: Avoid `WHERE fn_get_stock(idbarang) < 10` di query besar (use precomputed column atau index).
- **SP laporan**: Tambahkan INDEX pada kolom filter (created_at, status, dll).

---

## Contact & Review

Jika ada pertanyaan atau perlu review:
- Buka issue di GitHub
- Tag: `database`, `stored-procedure`, `architecture`
