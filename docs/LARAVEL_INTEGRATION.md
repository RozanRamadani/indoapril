# Laravel Integration Guide: SP, Functions, Triggers

Panduan lengkap cara menggunakan Stored Procedures (SP), Functions (FN), dan Triggers yang sudah ada di database MySQL dari Laravel controller.

---

## 📋 Ringkasan Arsitektur

```
┌─────────────────────────────────────────────────┐
│           LARAVEL APPLICATION                    │
│                                                  │
│  ┌──────────────────────────────────────────┐  │
│  │   CONTROLLERS (WRITE Operations)         │  │
│  │   - PengadaanController::store()         │  │
│  │   - PenjualanController::store()         │  │
│  │   - PenerimaanController::create()       │  │
│  │   ↓                                       │  │
│  │   DB::transaction() {                    │  │
│  │     DB::table()->insert() ← WRITE        │  │
│  │     SELECT fn_calc_*() ← FUNCTION        │  │
│  │   }                                       │  │
│  └──────────────────────────────────────────┘  │
│                                                  │
│  ┌──────────────────────────────────────────┐  │
│  │   CONTROLLERS (READ Operations)          │  │
│  │   - LaporanController::penjualan()       │  │
│  │   - DashboardController::stock()         │  │
│  │   ↓                                       │  │
│  │   CALL sp_report_*() ← STORED PROCEDURE  │  │
│  │   CALL sp_filter_*() ← STORED PROCEDURE  │  │
│  └──────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│              MySQL DATABASE                      │
│                                                  │
│  ┌──────────────────────────────────────────┐  │
│  │   TRIGGERS (Automatic Operations)        │  │
│  │   - trg_after_insert_detail_penjualan    │  │
│  │   - trg_after_insert_detail_penerimaan   │  │
│  │   ↓                                       │  │
│  │   INSERT INTO kartu_stok                 │  │
│  └──────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

**Prinsip Utama:**
- ✅ **Controller = WRITE** (INSERT/UPDATE/DELETE dengan `DB::transaction()`)
- ✅ **Function = CALCULATION** (kalkulasi subtotal, ppn, margin, stock)
- ✅ **Trigger = AUTOMATIC** (kartu_stok auto-update)
- ✅ **SP = READ** (laporan, filtering, query kompleks)

---

## 🔧 1. Menggunakan FUNCTION (Kalkulasi)

### Available Functions

| Function | Purpose | Example |
|----------|---------|---------|
| `fn_calc_subtotal(qty, price)` | Hitung subtotal (qty × price) | `SELECT fn_calc_subtotal(10, 5000)` → 50000 |
| `fn_calc_ppn(subtotal)` | Hitung PPN 11% | `SELECT fn_calc_ppn(100000)` → 11000 |
| `fn_calc_total(subtotal, ppn)` | Hitung total | `SELECT fn_calc_total(100000, 11000)` → 111000 |
| `fn_calc_margin(subtotal, persen)` | Hitung nilai margin | `SELECT fn_calc_margin(100000, 10)` → 10000 |
| `fn_get_stock(idbarang)` | Get current stock (SUM) | `SELECT fn_get_stock(1)` → 50 |
| `fn_get_last_stock(idbarang)` | Get last stock (fast) | `SELECT fn_get_last_stock(1)` → 50 |
| `fn_penjualan_total(idpenjualan)` | SUM detail penjualan | `SELECT fn_penjualan_total(1)` → 150000 |
| `fn_pengadaan_total(idpengadaan)` | SUM detail pengadaan | `SELECT fn_pengadaan_total(1)` → 200000 |

### Contoh Penggunaan di Controller

```php
// ❌ JANGAN: Hitung di PHP
$subtotal = $item['jumlah'] * $item['harga_satuan'];

// ✅ GUNAKAN: Function MySQL (consistent, reliable)
$subtotal = DB::selectOne(
    'SELECT fn_calc_subtotal(?, ?) as subtotal',
    [$item['jumlah'], $item['harga_satuan']]
)->subtotal;
```

**Kapan Gunakan Function?**
- ✅ Kalkulasi matematis (subtotal, ppn, margin)
- ✅ Query stock (fn_get_stock, fn_get_last_stock)
- ✅ Agregasi kompleks (fn_penjualan_total)

**Kenapa Pakai Function?**
- ✅ Konsisten (semua tempat pakai rumus yang sama)
- ✅ Reusable (bisa dipanggil dari SP, trigger, controller)
- ✅ Testable (bisa test langsung di MySQL Workbench)
- ✅ Performance (dicompile MySQL, lebih cepat)

---

## 🔄 2. TRIGGER (Automatic Operations)

**❌ JANGAN panggil trigger manual!** Trigger otomatis fire setelah INSERT/UPDATE/DELETE.

### Available Triggers

| Trigger | Event | Purpose |
|---------|-------|---------|
| `trg_after_insert_detail_penerimaan` | AFTER INSERT `detail_penerimaan` | Auto insert kartu_stok MASUK |
| `trg_after_insert_detail_penjualan` | AFTER INSERT `detail_penjualan` | Auto insert kartu_stok KELUAR + check stock |
| `trg_after_update_detail_penerimaan` | AFTER UPDATE `detail_penerimaan` | Auto insert adjustment MASUK/KELUAR |
| `trg_after_update_detail_penjualan` | AFTER UPDATE `detail_penjualan` | Auto insert adjustment MASUK/KELUAR + check stock |
| `trg_after_delete_detail_penerimaan` | AFTER DELETE `detail_penerimaan` | Auto insert reversal KELUAR |
| `trg_after_delete_detail_penjualan` | AFTER DELETE `detail_penjualan` | Auto insert reversal MASUK |

### Contoh Flow di Controller

```php
// PenjualanController.php
public function store(Request $request)
{
    DB::beginTransaction();
    try {
        // 1. INSERT penjualan header
        $idpenjualan = DB::table('penjualan')->insertGetId([...]);

        // 2. INSERT detail_penjualan
        DB::table('detail_penjualan')->insert([
            'idpenjualan' => $idpenjualan,
            'idbarang' => $idbarang,
            'jumlah' => $jumlah,
            ...
        ]);
        // ✅ TRIGGER trg_after_insert_detail_penjualan akan OTOMATIS:
        //    - Check stock: IF stock < jumlah THEN SIGNAL error
        //    - INSERT kartu_stok (jenis='K', keluar=$jumlah, stock=last_stock-$jumlah)

        DB::commit();
        // ✅ Jika commit sukses = kartu_stok sudah tersimpan otomatis!
        
    } catch (\Exception $e) {
        DB::rollBack();
        // ✅ Jika rollback = kartu_stok juga ikut rollback (tidak jadi insert)
    }
}
```

**Error Handling:**
```php
// ❌ Stock tidak cukup → Trigger akan throw error:
// SQLSTATE[45000]: Stok tidak cukup untuk barang ID: 5

try {
    DB::table('detail_penjualan')->insert([...]);
} catch (\Exception $e) {
    // Handle error dari trigger
    if (str_contains($e->getMessage(), 'Stok tidak cukup')) {
        return back()->with('error', 'Stock insufficient!');
    }
}
```

---

## 📊 3. Stored Procedure (READ-ONLY)

### Available SPs

| SP | Purpose | Parameters |
|----|---------|------------|
| `sp_report_penjualan_periode(start, end, offset, limit)` | Laporan penjualan by date range | Date, Date, Int, Int |
| `sp_report_penjualan_mingguan(tahun, minggu)` | Laporan penjualan mingguan | Year, Week |
| `sp_report_penjualan_bulanan(tahun, bulan)` | Laporan penjualan bulanan | Year, Month |
| `sp_report_penjualan_tahunan(tahun)` | Laporan penjualan tahunan | Year |
| `sp_report_pengadaan_periode(start, end, offset, limit)` | Laporan pengadaan by date range | Date, Date, Int, Int |
| `sp_report_stock_opname()` | Stock opname semua barang | - |
| `sp_filter_barang_stock_rendah(threshold)` | Barang dengan stock < threshold | Int |
| `sp_filter_detail_penjualan(idpenjualan)` | Detail penjualan dengan margin | Int |
| `sp_filter_kartu_stok(idbarang, start, end)` | Kartu stock by date range | Int, Date, Date |

### Contoh Penggunaan di Controller

```php
// ✅ READ-ONLY: Panggil SP untuk laporan
$laporan = DB::select('CALL sp_report_penjualan_bulanan(?, ?)', [2024, 10]);

// ✅ READ-ONLY: Panggil SP untuk filtering
$stockRendah = DB::select('CALL sp_filter_barang_stock_rendah(?)', [10]);

// ✅ READ-ONLY: Stock opname
$stockOpname = DB::select('CALL sp_report_stock_opname()');
```

**❌ JANGAN untuk WRITE:**
```php
// ❌ JANGAN: Panggil SP untuk INSERT (ini akan DIHAPUS dari database!)
DB::statement('CALL sp_create_penjualan(?, ?, @new_id)', [$iduser, $margin]);
```

**Kapan Gunakan SP?**
- ✅ Laporan kompleks (GROUP BY, JOIN multiple tables)
- ✅ Filtering dengan kalkulasi (stock rendah, penjualan by periode)
- ✅ Query yang sering digunakan (reusable report)

**Kenapa SP READ-ONLY?**
- ✅ Debugging: Laravel bisa track semua WRITE operations
- ✅ Transaction control: DB::transaction() di Laravel, bukan di SP
- ✅ Error handling: Catch exception di Laravel, bukan di SP
- ✅ Testing: Unit test di Laravel untuk WRITE, SP hanya untuk query

---

## 📝 4. Contoh Lengkap: PengadaanController

```php
<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    /**
     * CREATE: WRITE operation di Laravel (bukan SP)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'idvendor' => 'required|integer|exists:vendor,idvendor',
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|integer|exists:barang,idbarang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|integer|min:0',
        ]);

        try {
            $iduser = auth()->id() ?? 1;

            // ✅ TRANSACTION di Laravel (bukan SP)
            DB::beginTransaction();

            // ✅ INSERT pengadaan header (WRITE di Laravel)
            $idpengadaan = DB::table('pengadaan')->insertGetId([
                'user_iduser' => $iduser,
                'vendor_idvendor' => $data['idvendor'],
                'subtotal_nilai' => 0,
                'ppn' => 0,
                'total_nilai' => 0,
                'status' => 'PENDING',
                'created_at' => now(),
            ]);

            // ✅ INSERT detail + FUNCTION untuk kalkulasi
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                // ✅ FUNCTION untuk hitung subtotal
                $itemSubtotal = DB::selectOne(
                    'SELECT fn_calc_subtotal(?, ?) as subtotal',
                    [$item['jumlah'], $item['harga_satuan']]
                )->subtotal;

                // ✅ INSERT detail (WRITE di Laravel)
                DB::table('detail_pengadaan')->insert([
                    'idpengadaan' => $idpengadaan,
                    'idbarang' => $item['idbarang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'sub_total' => $itemSubtotal,
                    'created_at' => now(),
                ]);

                $subtotal += $itemSubtotal;
            }

            // ✅ FUNCTION untuk hitung PPN dan Total
            $ppn = DB::selectOne('SELECT fn_calc_ppn(?) as ppn', [$subtotal])->ppn;
            $total = DB::selectOne('SELECT fn_calc_total(?, ?) as total', [$subtotal, $ppn])->total;

            // ✅ UPDATE header (WRITE di Laravel)
            DB::table('pengadaan')
                ->where('idpengadaan', $idpengadaan)
                ->update([
                    'subtotal_nilai' => $subtotal,
                    'ppn' => $ppn,
                    'total_nilai' => $total,
                    'status' => 'COMPLETED',
                ]);

            // ✅ COMMIT di Laravel
            DB::commit();

            return redirect()->route('pengadaan.show', $idpengadaan)
                ->with('success', 'Pengadaan berhasil dibuat');

        } catch (\Exception $e) {
            // ✅ ROLLBACK di Laravel
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat pengadaan: ' . $e->getMessage());
        }
    }

    /**
     * READ: Gunakan SP untuk laporan
     */
    public function laporan(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);

        // ✅ SP READ-ONLY untuk laporan kompleks
        $laporan = DB::select(
            'CALL sp_report_pengadaan_periode(?, ?, ?, ?)',
            [$startDate, $endDate, $offset, $limit]
        );

        return view('pengadaan.laporan', compact('laporan', 'startDate', 'endDate'));
    }
}
```

---

## 📝 5. Contoh Lengkap: PenjualanController

```php
<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * CREATE: WRITE operation di Laravel
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|integer|exists:barang,idbarang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|integer|min:0',
            'items.*.idmargin_penjualan' => 'required|integer|exists:margin_penjualan,idmargin_penjualan',
        ]);

        try {
            $iduser = auth()->id() ?? 1;

            DB::beginTransaction();

            // ✅ VALIDASI stock dengan FUNCTION (sebelum INSERT)
            foreach ($data['items'] as $item) {
                $stock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$item['idbarang']]);
                if ($stock->stock < $item['jumlah']) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$item['idbarang']]);
                    throw new \Exception("Stok tidak cukup untuk {$barang->nama}. Stok tersedia: {$stock->stock}");
                }
            }

            $headerMarginId = $data['items'][0]['idmargin_penjualan'] ?? null;

            // ✅ INSERT penjualan header (WRITE di Laravel)
            $idpenjualan = DB::table('penjualan')->insertGetId([
                'iduser' => $iduser,
                'idmargin_penjualan' => $headerMarginId,
                'subtotal_nilai' => 0,
                'ppn' => 0,
                'total_nilai' => 0,
                'created_at' => now(),
            ]);

            // ✅ INSERT detail + FUNCTION untuk kalkulasi
            // ✅ TRIGGER akan otomatis handle kartu_stok (KELUAR)
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                // ✅ FUNCTION untuk hitung subtotal
                $itemSubtotal = DB::selectOne(
                    'SELECT fn_calc_subtotal(?, ?) as subtotal',
                    [$item['jumlah'], $item['harga_satuan']]
                )->subtotal;

                // ✅ INSERT detail (WRITE di Laravel)
                // ✅ TRIGGER trg_after_insert_detail_penjualan akan OTOMATIS:
                //    - Check stock (SIGNAL jika tidak cukup)
                //    - INSERT kartu_stok (jenis='K', keluar=$jumlah)
                DB::table('detail_penjualan')->insert([
                    'idpenjualan' => $idpenjualan,
                    'idbarang' => $item['idbarang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                ]);

                $subtotal += $itemSubtotal;
            }

            // ✅ FUNCTION untuk hitung PPN dan Total
            $ppn = DB::selectOne('SELECT fn_calc_ppn(?) as ppn', [$subtotal])->ppn;
            $total = DB::selectOne('SELECT fn_calc_total(?, ?) as total', [$subtotal, $ppn])->total;

            // ✅ UPDATE header (WRITE di Laravel)
            DB::table('penjualan')
                ->where('idpenjualan', $idpenjualan)
                ->update([
                    'subtotal_nilai' => $subtotal,
                    'ppn' => $ppn,
                    'total_nilai' => $total,
                ]);

            // ✅ COMMIT di Laravel
            DB::commit();

            return redirect()->route('penjualan.show', $idpenjualan)
                ->with('success', 'Penjualan berhasil dibuat');

        } catch (\Exception $e) {
            // ✅ ROLLBACK di Laravel
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat penjualan: ' . $e->getMessage());
        }
    }

    /**
     * READ: Detail dengan SP (query kompleks dengan margin)
     */
    public function show($id)
    {
        $penjualan = DB::selectOne("
            SELECT pj.*, u.username
            FROM penjualan pj
            LEFT JOIN user u ON pj.iduser = u.iduser
            WHERE pj.idpenjualan = ?
        ", [$id]);

        if (!$penjualan) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Penjualan tidak ditemukan');
        }

        // ✅ SP READ-ONLY untuk detail dengan kalkulasi margin
        $details = DB::select('CALL sp_filter_detail_penjualan(?)', [$id]);

        return view('penjualan.show', compact('penjualan', 'details'));
    }

    /**
     * READ: Laporan dengan SP
     */
    public function laporanBulanan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));

        // ✅ SP READ-ONLY untuk laporan
        $laporan = DB::select('CALL sp_report_penjualan_bulanan(?, ?)', [$tahun, $bulan]);

        return view('penjualan.laporan_bulanan', compact('laporan', 'tahun', 'bulan'));
    }
}
```

---

## 📝 6. Contoh Lengkap: PenerimaanController

```php
<?php

namespace App\Http\Controllers\Penerimaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    /**
     * CREATE: WRITE operation di Laravel
     */
    public function createFromPengadaan($idpengadaan)
    {
        try {
            $iduser = auth()->id() ?? 1;

            DB::beginTransaction();

            // ✅ Validasi pengadaan exists
            $pengadaan = DB::table('pengadaan')
                ->where('idpengadaan', $idpengadaan)
                ->first();

            if (!$pengadaan) {
                throw new \Exception('Pengadaan tidak ditemukan');
            }

            // ✅ Cek duplicate
            $existingPenerimaan = DB::table('penerimaan')
                ->where('idpengadaan', $idpengadaan)
                ->first();

            if ($existingPenerimaan) {
                throw new \Exception('Penerimaan sudah dibuat untuk pengadaan ini');
            }

            // ✅ INSERT penerimaan header (WRITE di Laravel)
            $idpenerimaan = DB::table('penerimaan')->insertGetId([
                'idpengadaan' => $idpengadaan,
                'iduser' => $iduser,
                'status' => 'RECEIVED',
                'created_at' => now(),
            ]);

            // ✅ Copy detail dari pengadaan
            $detailPengadaan = DB::table('detail_pengadaan')
                ->where('idpengadaan', $idpengadaan)
                ->get();

            // ✅ INSERT detail penerimaan
            // ✅ TRIGGER akan otomatis handle kartu_stok (MASUK)
            foreach ($detailPengadaan as $detail) {
                // ✅ TRIGGER trg_after_insert_detail_penerimaan akan OTOMATIS:
                //    - INSERT kartu_stok (jenis='M', masuk=$jumlah)
                DB::table('detail_penerimaan')->insert([
                    'idpenerimaan' => $idpenerimaan,
                    'idbarang' => $detail->idbarang,
                    'jumlah_terima' => $detail->jumlah,
                    'harga_satuan_terima' => $detail->harga_satuan,
                    'sub_total_terima' => $detail->sub_total,
                    'created_at' => now(),
                ]);
            }

            // ✅ COMMIT di Laravel
            DB::commit();

            return redirect()->route('penerimaan.show', $idpenerimaan)
                ->with('success', 'Penerimaan berhasil dibuat');

        } catch (\Exception $e) {
            // ✅ ROLLBACK di Laravel
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat penerimaan: ' . $e->getMessage());
        }
    }
}
```

---

## ✅ Checklist Integrasi

### 1. Controller sudah UPDATE:
- ✅ `PengadaanController.php` - hapus CALL sp_create_*, sp_add_*, sp_finalize_*, ganti dengan INSERT + function
- ✅ `PenjualanController.php` - hapus CALL sp_create_*, sp_add_*, sp_finalize_*, ganti dengan INSERT + function
- ✅ `PenerimaanController.php` - hapus CALL sp_create_penerimaan_from_pengadaan, ganti dengan INSERT + function
- ✅ `PenjualanControllerNew.php` - sudah benar (pakai architecture baru)

### 2. Database sudah DEPLOY:
- ✅ `01_functions.sql` - 8 functions untuk kalkulasi
- ✅ `02_triggers.sql` - 6 triggers untuk kartu_stok auto-update
- ✅ `03_stored_procedures.sql` - 9 SP READ-only untuk laporan
- ✅ `04_indexes.sql` - 9 indexes untuk optimasi query

### 3. Testing:
- [ ] Test create pengadaan → cek subtotal/ppn/total calculated correctly
- [ ] Test create penjualan → cek kartu_stok KELUAR created automatically
- [ ] Test create penerimaan → cek kartu_stok MASUK created automatically
- [ ] Test stock validation → cek error jika stock tidak cukup
- [ ] Test transaction rollback → cek kartu_stok ikut rollback
- [ ] Test laporan → CALL sp_report_penjualan_bulanan(2024, 10)
- [ ] Test stock opname → CALL sp_report_stock_opname()
- [ ] Test query performance → EXPLAIN SELECT with indexes

---

## 📚 Best Practices

### ✅ DO:
- ✅ Gunakan `DB::transaction()` untuk semua WRITE operations
- ✅ Gunakan FUNCTION untuk kalkulasi (consistent, reusable)
- ✅ Gunakan SP READ-ONLY untuk laporan/filtering kompleks
- ✅ Biarkan TRIGGER handle kartu_stok (automatic, consistent)
- ✅ Validasi stock dengan `fn_get_stock()` sebelum INSERT
- ✅ Catch exception dari trigger (stock validation error)

### ❌ DON'T:
- ❌ Jangan panggil SP untuk WRITE operations (sp_create_*, sp_add_*)
- ❌ Jangan hitung di PHP (gunakan FUNCTION untuk konsistensi)
- ❌ Jangan INSERT kartu_stok manual (biar trigger yang handle)
- ❌ Jangan lupa DB::transaction() (trigger butuh transaction context)
- ❌ Jangan skip stock validation (cek dulu sebelum INSERT)

---

## 🐛 Troubleshooting

### Error: "Stok tidak cukup untuk barang ID: X"
**Cause:** Trigger `trg_after_insert_detail_penjualan` detect stock < jumlah  
**Solution:** Validasi stock sebelum INSERT atau handle exception:
```php
try {
    DB::table('detail_penjualan')->insert([...]);
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'Stok tidak cukup')) {
        return back()->with('error', 'Stock insufficient!');
    }
}
```

### Error: "FUNCTION indoapril.fn_calc_subtotal does not exist"
**Cause:** Function belum deploy ke database  
**Solution:** Deploy `01_functions.sql` di MySQL Workbench

### Error: "PROCEDURE indoapril.sp_report_penjualan_bulanan does not exist"
**Cause:** SP belum deploy ke database  
**Solution:** Deploy `03_stored_procedures.sql` di MySQL Workbench

### Kartu_stok tidak ter-create
**Cause:** Trigger tidak fire karena tidak ada transaction  
**Solution:** Pastikan ada `DB::beginTransaction()` dan `DB::commit()`

### Query lambat
**Cause:** Index belum deploy atau tidak digunakan  
**Solution:** 
1. Deploy `04_indexes.sql` di MySQL Workbench
2. Run `EXPLAIN SELECT ...` untuk verify index usage
3. Run `ANALYZE TABLE barang, kartu_stok, ...` untuk update statistics

---

## 📞 Support

Jika ada error atau pertanyaan, cek:
1. `docs/SP_POLICY.md` - Policy kenapa SP READ-only
2. `migrations/sql/01_functions.sql` - Dokumentasi function
3. `migrations/sql/02_triggers.sql` - Dokumentasi trigger
4. `migrations/sql/03_stored_procedures.sql` - Dokumentasi SP

---

**Happy Coding! 🚀**
