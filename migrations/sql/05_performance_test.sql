-- ========================================================================================================
-- PERFORMANCE TESTING: Compare Query Performance Before/After Optimization
-- ========================================================================================================
-- Script ini untuk test performa query sebelum dan sesudah optimasi:
-- 1. N+1 Problem Fix (sp_report_stock_opname & sp_filter_barang_stock_rendah)
-- 2. Index Impact (functions dan SP laporan)
--
-- CARA TESTING:
-- 1. Jalankan test SEBELUM deploy optimasi (catat waktu)
-- 2. Deploy optimasi (03_stored_procedures.sql + 04_indexes.sql)
-- 3. Jalankan test SETELAH deploy optimasi (catat waktu)
-- 4. Compare hasilnya
--
-- EKSPEKTASI:
-- - sp_report_stock_opname: 10-100x lebih cepat (dari 1001 queries → 1 query)
-- - sp_filter_barang_stock_rendah: 10-100x lebih cepat (dari N+1 → 1 query)
-- - Functions dengan index: 2-10x lebih cepat (index scan vs full table scan)
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- TEST 1: sp_report_stock_opname (Stock Opname Semua Barang)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 1: sp_report_stock_opname' AS '';
SELECT '========================================' AS '';

-- Enable profiling
SET profiling = 1;

-- Run SP
CALL sp_report_stock_opname();

-- Show timing
SHOW PROFILES;

-- Reset profiling
SET profiling = 0;

-- Expected improvement:
-- BEFORE: 0.5-5 seconds (tergantung jumlah barang, N+1 problem)
-- AFTER:  0.05-0.5 seconds (1 query dengan JOIN)
-- Speedup: 10-100x

-- ========================================================================================================
-- TEST 2: sp_filter_barang_stock_rendah (Filter Stock Rendah)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 2: sp_filter_barang_stock_rendah' AS '';
SELECT '========================================' AS '';

SET profiling = 1;

CALL sp_filter_barang_stock_rendah(10);  -- Stock <= 10

SHOW PROFILES;

SET profiling = 0;

-- Expected improvement:
-- BEFORE: 0.3-3 seconds (N+1 problem)
-- AFTER:  0.03-0.3 seconds (1 query dengan JOIN)
-- Speedup: 10-100x

-- ========================================================================================================
-- TEST 3: fn_get_stock (Function dengan Index)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 3: fn_get_stock with INDEX' AS '';
SELECT '========================================' AS '';

-- Test query plan (should use idx_kartu_stok_idbarang_created)
EXPLAIN SELECT COALESCE(SUM(masuk) - SUM(keluar), 0) AS stock
FROM kartu_stok
WHERE idbarang = 1;

-- Expected EXPLAIN result:
-- BEFORE index: type=ALL, rows=ALL (full table scan)
-- AFTER index:  type=ref, key=idx_kartu_stok_idbarang_created, rows=10-100 (index scan)

SET profiling = 1;

-- Test function call
SELECT fn_get_stock(1) AS stock_barang_1;
SELECT fn_get_stock(2) AS stock_barang_2;
SELECT fn_get_stock(3) AS stock_barang_3;

SHOW PROFILES;

SET profiling = 0;

-- Expected improvement:
-- BEFORE: 0.01-0.1 seconds per call (full table scan)
-- AFTER:  0.001-0.01 seconds per call (index scan)
-- Speedup: 2-10x

-- ========================================================================================================
-- TEST 4: fn_get_last_stock (Function dengan Index)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 4: fn_get_last_stock with INDEX' AS '';
SELECT '========================================' AS '';

-- Test query plan
EXPLAIN SELECT stock
FROM kartu_stok
WHERE idbarang = 1
ORDER BY created_at DESC, idkartu_stok DESC
LIMIT 1;

-- Expected EXPLAIN result:
-- BEFORE: type=ALL, rows=ALL, filesort (very slow!)
-- AFTER:  type=ref, key=idx_kartu_stok_idbarang_created, rows=1 (very fast!)

SET profiling = 1;

SELECT fn_get_last_stock(1) AS last_stock_1;
SELECT fn_get_last_stock(2) AS last_stock_2;
SELECT fn_get_last_stock(3) AS last_stock_3;

SHOW PROFILES;

SET profiling = 0;

-- Expected improvement:
-- BEFORE: 0.01-0.1 seconds (full scan + sort)
-- AFTER:  0.001-0.005 seconds (index lookup + LIMIT 1)
-- Speedup: 5-20x (PENTING untuk trigger performance!)

-- ========================================================================================================
-- TEST 5: fn_penjualan_total (Function dengan Index)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 5: fn_penjualan_total with INDEX' AS '';
SELECT '========================================' AS '';

EXPLAIN SELECT SUM(subtotal)
FROM detail_penjualan
WHERE idpenjualan = 1;

-- Expected EXPLAIN result:
-- BEFORE: type=ALL (full table scan)
-- AFTER:  type=ref, key=idx_detail_penjualan_idpenjualan (index scan)

SET profiling = 1;

SELECT fn_penjualan_total(1) AS total;

SHOW PROFILES;

SET profiling = 0;

-- ========================================================================================================
-- TEST 6: SP Laporan Penjualan dengan Date Filter
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 6: sp_report_penjualan_periode with INDEX' AS '';
SELECT '========================================' AS '';

EXPLAIN SELECT *
FROM penjualan
WHERE created_at BETWEEN '2025-01-01' AND '2025-01-31';

-- Expected EXPLAIN result:
-- BEFORE: type=ALL (full table scan)
-- AFTER:  type=range, key=idx_penjualan_created_at (index range scan)

SET profiling = 1;

CALL sp_report_penjualan_periode('2025-01-01', '2025-12-31', 100, 0);

SHOW PROFILES;

SET profiling = 0;

-- Expected improvement:
-- BEFORE: 0.1-1 seconds (full scan)
-- AFTER:  0.01-0.1 seconds (index range scan)
-- Speedup: 5-10x

-- ========================================================================================================
-- TEST 7: Trigger Performance (Insert Detail Penjualan)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'TEST 7: Trigger performance with INDEX' AS '';
SELECT '========================================' AS '';

-- NOTE: Test ini perlu data sample di penjualan dan barang
-- Uncomment jika sudah ada data

/*
SET profiling = 1;

-- Test insert (trigger akan call fn_get_last_stock)
START TRANSACTION;

INSERT INTO detail_penjualan (idpenjualan, idbarang, jumlah, harga_satuan, subtotal)
VALUES (1, 1, 5, 10000, 50000);

ROLLBACK;  -- Rollback untuk tidak ubah data

SHOW PROFILES;

SET profiling = 0;
*/

-- Expected improvement:
-- BEFORE: 0.05-0.5 seconds (fn_get_last_stock slow tanpa index)
-- AFTER:  0.005-0.05 seconds (fn_get_last_stock fast dengan index)
-- Speedup: 10x (SANGAT PENTING untuk trigger!)

-- ========================================================================================================
-- SUMMARY: INDEX VERIFICATION
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'INDEX VERIFICATION' AS '';
SELECT '========================================' AS '';

-- List all indexes
SELECT
  TABLE_NAME,
  INDEX_NAME,
  GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns,
  INDEX_TYPE,
  CASE WHEN NON_UNIQUE = 0 THEN 'UNIQUE' ELSE 'NON-UNIQUE' END AS uniqueness
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'indoapril'
  AND INDEX_NAME LIKE 'idx_%'
GROUP BY TABLE_NAME, INDEX_NAME, INDEX_TYPE, NON_UNIQUE
ORDER BY TABLE_NAME, INDEX_NAME;

-- ========================================================================================================
-- PERFORMANCE TIPS
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'PERFORMANCE TIPS:' AS '';
SELECT '1. Monitor slow queries: SET GLOBAL slow_query_log = ON;' AS tip1;
SELECT '2. Analyze tables regularly: ANALYZE TABLE kartu_stok;' AS tip2;
SELECT '3. Check index usage: SHOW INDEX FROM kartu_stok;' AS tip3;
SELECT '4. For large tables, consider partitioning' AS tip4;
SELECT '5. Use EXPLAIN for all new queries' AS tip5;
SELECT '========================================' AS '';

-- ========================================================================================================
-- EXPECTED OVERALL IMPROVEMENT
-- ========================================================================================================
-- Metric                          | Before Optimization | After Optimization | Improvement
-- --------------------------------|---------------------|--------------------|--------------
-- sp_report_stock_opname          | 1-5 seconds         | 0.05-0.5 seconds   | 10-100x faster
-- sp_filter_barang_stock_rendah   | 0.5-3 seconds       | 0.03-0.3 seconds   | 10-100x faster
-- fn_get_stock                    | 0.01-0.1 seconds    | 0.001-0.01 seconds | 2-10x faster
-- fn_get_last_stock (TRIGGER!)    | 0.01-0.1 seconds    | 0.001-0.005 seconds| 5-20x faster
-- fn_penjualan_total              | 0.005-0.05 seconds  | 0.001-0.01 seconds | 2-5x faster
-- SP laporan dengan date filter   | 0.1-1 seconds       | 0.01-0.1 seconds   | 5-10x faster
-- Trigger insert performance      | 0.05-0.5 seconds    | 0.005-0.05 seconds | 10x faster
--
-- TOTAL QUERIES REDUCED:
-- - sp_report_stock_opname: 1001 queries → 1 query (99.9% reduction!)
-- - sp_filter_barang_stock_rendah: N+1 queries → 1 query (99.9% reduction!)
-- ========================================================================================================
