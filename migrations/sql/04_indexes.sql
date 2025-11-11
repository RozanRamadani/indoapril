-- ========================================================================================================
-- INDEXES: Optimasi Query Performance
-- ========================================================================================================
-- Indexes untuk mempercepat query di Functions, Triggers, dan Stored Procedures
--
-- KENAPA PERLU INDEX?
-- - Query SELECT/JOIN/WHERE/ORDER BY jadi 10-100x lebih cepat
-- - Function fn_get_stock, fn_get_last_stock, fn_penjualan_total, dll jadi lebih efisien
-- - SP laporan dengan DATE filter dan pagination jadi lebih responsif
--
-- TRADE-OFF:
-- - INSERT/UPDATE/DELETE sedikit lebih lambat (MySQL harus update index)
-- - Storage bertambah (index memakan disk space)
-- - Tapi untuk READ-heavy application (seperti laporan), benefit jauh lebih besar
--
-- DEPLOYMENT:
-- - Jalankan setelah deploy 01_functions.sql, 02_triggers.sql, 03_stored_procedures.sql
-- - Untuk data besar, CREATE INDEX bisa memakan waktu (tunggu hingga selesai)
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- INDEX 1: kartu_stok - untuk fn_get_stock & fn_get_last_stock
-- ========================================================================================================
-- Digunakan oleh:
-- - fn_get_stock(idbarang) → SELECT SUM(masuk - keluar) WHERE idbarang = ?
-- - fn_get_last_stock(idbarang) → SELECT stock WHERE idbarang = ? ORDER BY created_at DESC LIMIT 1
-- - sp_report_stock_opname → LEFT JOIN kartu_stok GROUP BY idbarang
-- - sp_filter_barang_stock_rendah → LEFT JOIN kartu_stok GROUP BY idbarang
-- - Triggers → SET v_last_stock = fn_get_last_stock(idbarang)
--
-- Composite index (idbarang, created_at, idkartu_stok) untuk:
-- 1. Filter WHERE idbarang = ? (paling sering)
-- 2. Sort ORDER BY created_at DESC (untuk LIMIT 1)
-- 3. Sort ORDER BY idkartu_stok DESC (tie-breaker jika created_at sama)
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_kartu_stok_idbarang_created
ON kartu_stok(idbarang, created_at DESC, idkartu_stok DESC);

-- Note: Jika MySQL versi lama tidak support DESC di index, gunakan:
-- CREATE INDEX IF NOT EXISTS idx_kartu_stok_idbarang_created ON kartu_stok(idbarang, created_at, idkartu_stok);

-- ========================================================================================================
-- INDEX 2: detail_penjualan - untuk fn_penjualan_total
-- ========================================================================================================
-- Digunakan oleh:
-- - fn_penjualan_total(idpenjualan) → SELECT SUM(subtotal) WHERE idpenjualan = ?
-- - sp_filter_detail_penjualan(idpenjualan) → SELECT * WHERE idpenjualan = ?
-- - Triggers → INSERT INTO detail_penjualan (untuk foreign key check lebih cepat)
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_detail_penjualan_idpenjualan
ON detail_penjualan(idpenjualan);

-- ========================================================================================================
-- INDEX 3: detail_pengadaan - untuk fn_pengadaan_total
-- ========================================================================================================
-- Digunakan oleh:
-- - fn_pengadaan_total(idpengadaan) → SELECT SUM(sub_total) WHERE idpengadaan = ?
-- - sp_report_pengadaan_periode → LEFT JOIN detail_pengadaan untuk COUNT
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_detail_pengadaan_idpengadaan
ON detail_pengadaan(idpengadaan);

-- ========================================================================================================
-- INDEX 4: penjualan - untuk SP laporan dengan filter tanggal
-- ========================================================================================================
-- Digunakan oleh:
-- - sp_report_penjualan_periode → WHERE DATE(created_at) BETWEEN ? AND ?
-- - sp_report_penjualan_mingguan → WHERE YEAR(created_at) = ? AND WEEK(created_at) = ?
-- - sp_report_penjualan_bulanan → WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
-- - sp_report_penjualan_tahunan → WHERE YEAR(created_at) = ?
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_penjualan_created_at
ON penjualan(created_at);

-- ========================================================================================================
-- INDEX 5: pengadaan - untuk SP laporan dengan filter tanggal
-- ========================================================================================================
-- Digunakan oleh:
-- - sp_report_pengadaan_periode → WHERE DATE(created_at) BETWEEN ? AND ?
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_pengadaan_created_at
ON pengadaan(created_at);

-- ========================================================================================================
-- INDEX 6: barang - untuk filter status aktif
-- ========================================================================================================
-- Digunakan oleh:
-- - sp_report_stock_opname → WHERE status = 1
-- - sp_filter_barang_stock_rendah → WHERE status = 1
-- - Banyak query lain yang filter barang aktif
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_barang_status
ON barang(status);

-- ========================================================================================================
-- INDEX 7: detail_penerimaan - untuk trigger performance
-- ========================================================================================================
-- Digunakan oleh:
-- - trg_after_insert_detail_penerimaan → INSERT kartu_stok dengan idpenerimaan
-- - trg_after_update_detail_penerimaan → UPDATE adjustment
-- - trg_after_delete_detail_penerimaan → DELETE reversal
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_detail_penerimaan_idpenerimaan
ON detail_penerimaan(idpenerimaan);

-- ========================================================================================================
-- INDEX 8: detail_penerimaan & detail_penjualan - untuk idbarang (trigger lookup)
-- ========================================================================================================
-- Digunakan oleh:
-- - Triggers untuk cek barang existence sebelum insert kartu_stok
-- - SP detail untuk JOIN ke barang
-- ========================================================================================================
CREATE INDEX IF NOT EXISTS idx_detail_penerimaan_idbarang
ON detail_penerimaan(idbarang);

CREATE INDEX IF NOT EXISTS idx_detail_penjualan_idbarang
ON detail_penjualan(idbarang);

-- ========================================================================================================
-- VERIFY INDEXES
-- ========================================================================================================
-- Cek apakah semua index sudah dibuat
SELECT
  TABLE_NAME,
  INDEX_NAME,
  COLUMN_NAME,
  SEQ_IN_INDEX,
  INDEX_TYPE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'indoapril'
  AND TABLE_NAME IN ('kartu_stok', 'detail_penjualan', 'detail_pengadaan',
                     'penjualan', 'pengadaan', 'barang', 'detail_penerimaan')
  AND INDEX_NAME LIKE 'idx_%'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- ========================================================================================================
-- STATUS CHECK
-- ========================================================================================================
SELECT 'Indexes created successfully! Total: 9 indexes for query optimization' AS status;

-- ========================================================================================================
-- PERFORMANCE TESTING (Optional)
-- ========================================================================================================
-- Test query performance sebelum dan sesudah index:
--
-- Test 1: fn_get_stock (should use idx_kartu_stok_idbarang_created)
-- EXPLAIN SELECT COALESCE(SUM(masuk) - SUM(keluar), 0) FROM kartu_stok WHERE idbarang = 1;
--
-- Test 2: fn_get_last_stock (should use idx_kartu_stok_idbarang_created)
-- EXPLAIN SELECT stock FROM kartu_stok WHERE idbarang = 1 ORDER BY created_at DESC, idkartu_stok DESC LIMIT 1;
--
-- Test 3: fn_penjualan_total (should use idx_detail_penjualan_idpenjualan)
-- EXPLAIN SELECT SUM(subtotal) FROM detail_penjualan WHERE idpenjualan = 1;
--
-- Test 4: SP laporan (should use idx_penjualan_created_at)
-- EXPLAIN SELECT * FROM penjualan WHERE created_at BETWEEN '2025-01-01' AND '2025-01-31';
--
-- Cek EXPLAIN output:
-- - "type" harus "ref" atau "range" (bukan "ALL" = full table scan)
-- - "key" harus menunjuk ke index yang dibuat (idx_*)
-- - "rows" harus kecil (bukan seluruh table)
-- ========================================================================================================
