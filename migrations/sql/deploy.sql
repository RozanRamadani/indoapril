-- ========================================================================================================
-- DEPLOYMENT SCRIPT: Deploy All SP, Functions, Triggers, and Indexes
-- ========================================================================================================
-- Script ini akan deploy semua komponen database dalam urutan yang benar:
-- 1. Functions (8 functions) - Foundation untuk kalkulasi
-- 2. Triggers (6 triggers) - Auto-update kartu_stok
-- 3. Stored Procedures (9 SP) - Laporan dan filtering (READ-ONLY)
-- 4. Indexes (9 indexes) - Query optimization
--
-- CARA DEPLOY:
-- 1. Backup database dulu:
--    mysqldump -u root -p indoapril > backup_$(date +%Y%m%d_%H%M%S).sql
--
-- 2. Deploy via MySQL CLI:
--    mysql -u root -p indoapril < migrations/sql/deploy.sql
--
-- 3. Verify deployment:
--    - Cek functions: SELECT ROUTINE_NAME FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_TYPE='FUNCTION';
--    - Cek triggers: SHOW TRIGGERS;
--    - Cek SP: SHOW PROCEDURE STATUS WHERE Db='indoapril';
--    - Cek indexes: SHOW INDEX FROM kartu_stok;
--
-- CATATAN:
-- - Jangan jalankan di production sebelum test di development/staging
-- - Script ini DROP existing objects (IF EXISTS) sebelum CREATE
-- - Untuk rollback, restore dari backup
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- STEP 1: DEPLOY FUNCTIONS (8 functions)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'STEP 1: Deploying Functions...' AS '';
SELECT '========================================' AS '';

SOURCE migrations/sql/01_functions.sql;

SELECT 'Functions deployed successfully!' AS status;

-- ========================================================================================================
-- STEP 2: DEPLOY TRIGGERS (6 triggers)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'STEP 2: Deploying Triggers...' AS '';
SELECT '========================================' AS '';

SOURCE migrations/sql/02_triggers.sql;

SELECT 'Triggers deployed successfully!' AS status;

-- ========================================================================================================
-- STEP 3: DEPLOY STORED PROCEDURES (9 SP)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'STEP 3: Deploying Stored Procedures...' AS '';
SELECT '========================================' AS '';

SOURCE migrations/sql/03_stored_procedures.sql;

SELECT 'Stored Procedures deployed successfully!' AS status;

-- ========================================================================================================
-- STEP 4: DEPLOY INDEXES (9 indexes)
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'STEP 4: Creating Indexes for optimization...' AS '';
SELECT '========================================' AS '';

SOURCE migrations/sql/04_indexes.sql;

SELECT 'Indexes created successfully!' AS status;

-- ========================================================================================================
-- DEPLOYMENT SUMMARY
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'DEPLOYMENT COMPLETED!' AS '';
SELECT '========================================' AS '';

-- Count Functions
SELECT COUNT(*) AS total_functions
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'indoapril'
  AND ROUTINE_TYPE = 'FUNCTION'
  AND ROUTINE_NAME LIKE 'fn_%';

-- Count Triggers
SELECT COUNT(*) AS total_triggers
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = 'indoapril'
  AND TRIGGER_NAME LIKE 'trg_%';

-- Count Stored Procedures
SELECT COUNT(*) AS total_procedures
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'indoapril'
  AND ROUTINE_TYPE = 'PROCEDURE'
  AND ROUTINE_NAME LIKE 'sp_%';

-- Count Indexes
SELECT COUNT(DISTINCT INDEX_NAME) AS total_indexes
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'indoapril'
  AND INDEX_NAME LIKE 'idx_%';

-- ========================================================================================================
-- NEXT STEPS
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'NEXT STEPS:' AS '';
SELECT '1. Test functions: SELECT fn_get_stock(1);' AS step1;
SELECT '2. Test triggers: INSERT test data ke detail_penjualan' AS step2;
SELECT '3. Test SP: CALL sp_report_stock_opname();' AS step3;
SELECT '4. Verify indexes: SHOW INDEX FROM kartu_stok;' AS step4;
SELECT '5. Update controllers (remove SP WRITE operations)' AS step5;
SELECT '6. Drop old WRITE stored procedures' AS step6;
SELECT '========================================' AS '';
