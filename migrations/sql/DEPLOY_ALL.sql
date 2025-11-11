-- ========================================================================================================
-- COMPLETE SQL DEPLOYMENT SCRIPT
-- ========================================================================================================
-- File ini menggabungkan Functions, Triggers, Stored Procedures, dan Indexes
-- untuk deployment lengkap ke database indoapril
--
-- CARA PENGGUNAAN:
-- 1. Backup database dulu: mysqldump -u root -p indoapril > backup_$(date +%Y%m%d).sql
-- 2. Jalankan file ini: mysql -u root -p indoapril < DEPLOY_ALL.sql
-- 3. Atau di MySQL client: SOURCE /path/to/DEPLOY_ALL.sql;
--
-- CREATED: November 10, 2025
-- VERSION: 1.0 - Updated for Laravel Controller Flow (No Triggers for Pengadaan/Penerimaan)
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- PART 1: CLEANUP - Drop old objects
-- ========================================================================================================
SELECT '========================================' AS '';
SELECT 'PART 1: CLEANUP - Dropping old objects...' AS '';
SELECT '========================================' AS '';

-- Drop old triggers
DROP TRIGGER IF EXISTS trg_after_insert_detail_penerimaan;
DROP TRIGGER IF EXISTS trg_after_insert_detail_penjualan;
DROP TRIGGER IF EXISTS trg_after_update_detail_penerimaan;
DROP TRIGGER IF EXISTS trg_after_update_detail_penjualan;
DROP TRIGGER IF EXISTS trg_after_delete_detail_penerimaan;
DROP TRIGGER IF EXISTS trg_after_delete_detail_penjualan;
DROP TRIGGER IF EXISTS trg_after_insert_detail_pengadaan;
DROP TRIGGER IF EXISTS trg_after_update_detail_pengadaan;
DROP TRIGGER IF EXISTS trg_after_delete_detail_pengadaan;

-- Drop old stored procedures
DROP PROCEDURE IF EXISTS sp_report_penjualan_periode;
DROP PROCEDURE IF EXISTS sp_report_penjualan_mingguan;
DROP PROCEDURE IF EXISTS sp_report_penjualan_bulanan;
DROP PROCEDURE IF EXISTS sp_report_penjualan_tahunan;
DROP PROCEDURE IF EXISTS sp_report_pengadaan_periode;
DROP PROCEDURE IF EXISTS sp_report_penerimaan_periode;
DROP PROCEDURE IF EXISTS sp_report_stock_opname;
DROP PROCEDURE IF EXISTS sp_filter_barang_stock_rendah;
DROP PROCEDURE IF EXISTS sp_filter_detail_pengadaan;
DROP PROCEDURE IF EXISTS sp_filter_detail_penerimaan;
DROP PROCEDURE IF EXISTS sp_filter_detail_penjualan;
DROP PROCEDURE IF EXISTS sp_filter_kartu_stok;

-- Drop old functions
DROP FUNCTION IF EXISTS fn_get_stock;
DROP FUNCTION IF EXISTS fn_get_last_stock;
DROP FUNCTION IF EXISTS fn_calc_subtotal;
DROP FUNCTION IF EXISTS fn_calc_ppn;
DROP FUNCTION IF EXISTS fn_calc_total;
DROP FUNCTION IF EXISTS fn_calc_margin;
DROP FUNCTION IF EXISTS fn_penjualan_total;
DROP FUNCTION IF EXISTS fn_pengadaan_total;
DROP FUNCTION IF EXISTS fn_penerimaan_total;

SELECT 'Cleanup completed!' AS status;

-- ========================================================================================================
-- PART 2: FUNCTIONS
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'PART 2: Creating Functions...' AS '';
SELECT '========================================' AS '';

-- Function 1: fn_get_stock
DELIMITER $$
CREATE FUNCTION fn_get_stock(p_idbarang INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_stock INT DEFAULT 0;
  SELECT COALESCE(stock, 0)
  INTO v_stock
  FROM kartu_stok
  WHERE idbarang = p_idbarang
  ORDER BY idkartu_stok DESC
  LIMIT 1;
  RETURN IFNULL(v_stock, 0);
END$$
DELIMITER ;

-- Function 2: fn_get_last_stock (alias)
DELIMITER $$
CREATE FUNCTION fn_get_last_stock(p_idbarang INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
  RETURN fn_get_stock(p_idbarang);
END$$
DELIMITER ;

-- Function 3: fn_calc_subtotal
DELIMITER $$
CREATE FUNCTION fn_calc_subtotal(p_jumlah INT, p_harga_satuan DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  RETURN p_jumlah * p_harga_satuan;
END$$
DELIMITER ;

-- Function 4: fn_calc_ppn
DELIMITER $$
CREATE FUNCTION fn_calc_ppn(p_subtotal DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  RETURN FLOOR(p_subtotal * 0.11);
END$$
DELIMITER ;

-- Function 5: fn_calc_total
DELIMITER $$
CREATE FUNCTION fn_calc_total(p_subtotal DECIMAL(15,2), p_ppn DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  RETURN p_subtotal + p_ppn;
END$$
DELIMITER ;

-- Function 6: fn_calc_margin
DELIMITER $$
CREATE FUNCTION fn_calc_margin(p_subtotal DECIMAL(15,2), p_persen DECIMAL(5,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  RETURN FLOOR(p_subtotal * p_persen / 100);
END$$
DELIMITER ;

-- Function 7: fn_penjualan_total
DELIMITER $$
CREATE FUNCTION fn_penjualan_total(p_idpenjualan INT)
RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_total DECIMAL(15,2) DEFAULT 0;
  SELECT COALESCE(SUM(subtotal), 0)
  INTO v_total
  FROM detail_penjualan
  WHERE idpenjualan = p_idpenjualan;
  RETURN v_total;
END$$
DELIMITER ;

-- Function 8: fn_pengadaan_total
DELIMITER $$
CREATE FUNCTION fn_pengadaan_total(p_idpengadaan INT)
RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_total DECIMAL(15,2) DEFAULT 0;
  SELECT COALESCE(SUM(sub_total), 0)
  INTO v_total
  FROM detail_pengadaan
  WHERE idpengadaan = p_idpengadaan;
  RETURN v_total;
END$$
DELIMITER ;

-- Function 9: fn_penerimaan_total
DELIMITER $$
CREATE FUNCTION fn_penerimaan_total(p_idpenerimaan INT)
RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_total DECIMAL(15,2) DEFAULT 0;
  SELECT COALESCE(SUM(sub_total_terima), 0)
  INTO v_total
  FROM detail_penerimaan
  WHERE idpenerimaan = p_idpenerimaan;
  RETURN v_total;
END$$
DELIMITER ;

SELECT '9 Functions created successfully!' AS status;

-- ========================================================================================================
-- PART 2B: TRIGGERS (Auto-update Pengadaan Totals)
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'PART 2B: Creating Triggers...' AS '';
SELECT '========================================' AS '';

-- Trigger 1: trg_after_insert_detail_pengadaan
-- Auto-update pengadaan subtotal, ppn, dan total setelah INSERT detail
DELIMITER $$
CREATE TRIGGER trg_after_insert_detail_pengadaan
AFTER INSERT ON detail_pengadaan
FOR EACH ROW
BEGIN
  DECLARE v_subtotal DECIMAL(15,2);
  DECLARE v_ppn DECIMAL(15,2);
  DECLARE v_total DECIMAL(15,2);

  -- Hitung subtotal dari semua detail
  SET v_subtotal = fn_pengadaan_total(NEW.idpengadaan);

  -- Hitung PPN 11%
  SET v_ppn = fn_calc_ppn(v_subtotal);

  -- Hitung total
  SET v_total = fn_calc_total(v_subtotal, v_ppn);

  -- Update pengadaan
  UPDATE pengadaan
  SET subtotal_nilai = v_subtotal,
      ppn = v_ppn,
      total_nilai = v_total
  WHERE idpengadaan = NEW.idpengadaan;
END$$
DELIMITER ;

-- Trigger 2: trg_after_update_detail_pengadaan
-- Auto-update pengadaan subtotal, ppn, dan total setelah UPDATE detail
DELIMITER $$
CREATE TRIGGER trg_after_update_detail_pengadaan
AFTER UPDATE ON detail_pengadaan
FOR EACH ROW
BEGIN
  DECLARE v_subtotal DECIMAL(15,2);
  DECLARE v_ppn DECIMAL(15,2);
  DECLARE v_total DECIMAL(15,2);

  -- Hitung subtotal dari semua detail
  SET v_subtotal = fn_pengadaan_total(NEW.idpengadaan);

  -- Hitung PPN 11%
  SET v_ppn = fn_calc_ppn(v_subtotal);

  -- Hitung total
  SET v_total = fn_calc_total(v_subtotal, v_ppn);

  -- Update pengadaan
  UPDATE pengadaan
  SET subtotal_nilai = v_subtotal,
      ppn = v_ppn,
      total_nilai = v_total
  WHERE idpengadaan = NEW.idpengadaan;
END$$
DELIMITER ;

-- Trigger 3: trg_after_delete_detail_pengadaan
-- Auto-update pengadaan subtotal, ppn, dan total setelah DELETE detail
DELIMITER $$
CREATE TRIGGER trg_after_delete_detail_pengadaan
AFTER DELETE ON detail_pengadaan
FOR EACH ROW
BEGIN
  DECLARE v_subtotal DECIMAL(15,2);
  DECLARE v_ppn DECIMAL(15,2);
  DECLARE v_total DECIMAL(15,2);

  -- Hitung subtotal dari semua detail yang tersisa
  SET v_subtotal = fn_pengadaan_total(OLD.idpengadaan);

  -- Hitung PPN 11%
  SET v_ppn = fn_calc_ppn(v_subtotal);

  -- Hitung total
  SET v_total = fn_calc_total(v_subtotal, v_ppn);

  -- Update pengadaan
  UPDATE pengadaan
  SET subtotal_nilai = v_subtotal,
      ppn = v_ppn,
      total_nilai = v_total
  WHERE idpengadaan = OLD.idpengadaan;
END$$
DELIMITER ;

SELECT '3 Triggers created successfully!' AS status;

-- ========================================================================================================
-- PART 3: STORED PROCEDURES
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'PART 3: Creating Stored Procedures...' AS '';
SELECT '========================================' AS '';

-- SP 1: sp_report_penjualan_periode
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_periode(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE,
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  SELECT
    pj.idpenjualan,
    pj.created_at,
    u.username,
    m.persen AS margin_persen,
    pj.subtotal_nilai,
    pj.ppn,
    pj.total_nilai,
    COUNT(dp.iddetail_penjualan) AS jumlah_item
  FROM penjualan pj
  LEFT JOIN user u ON pj.iduser = u.iduser
  LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
  LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
  WHERE DATE(pj.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pj.idpenjualan, pj.created_at, u.username, m.persen,
           pj.subtotal_nilai, pj.ppn, pj.total_nilai
  ORDER BY pj.created_at DESC
  LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- SP 2: sp_report_penjualan_mingguan
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_mingguan(
  IN p_tahun INT,
  IN p_minggu INT
)
BEGIN
  SELECT
    DATE(pj.created_at) AS tanggal,
    COUNT(pj.idpenjualan) AS jumlah_transaksi,
    SUM(pj.subtotal_nilai) AS total_subtotal,
    SUM(pj.ppn) AS total_ppn,
    SUM(pj.total_nilai) AS total_penjualan,
    SUM(dp.jumlah) AS total_qty_terjual
  FROM penjualan pj
  LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
  WHERE YEAR(pj.created_at) = p_tahun
    AND WEEK(pj.created_at, 1) = p_minggu
  GROUP BY DATE(pj.created_at)
  ORDER BY tanggal;
END$$
DELIMITER ;

-- SP 3: sp_report_penjualan_bulanan
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_bulanan(
  IN p_tahun INT,
  IN p_bulan INT
)
BEGIN
  SELECT
    DATE(pj.created_at) AS tanggal,
    COUNT(pj.idpenjualan) AS jumlah_transaksi,
    SUM(pj.subtotal_nilai) AS total_subtotal,
    SUM(pj.ppn) AS total_ppn,
    SUM(pj.total_nilai) AS total_penjualan,
    SUM(dp.jumlah) AS total_qty_terjual
  FROM penjualan pj
  LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
  WHERE YEAR(pj.created_at) = p_tahun
    AND MONTH(pj.created_at) = p_bulan
  GROUP BY DATE(pj.created_at)
  ORDER BY tanggal;
END$$
DELIMITER ;

-- SP 4: sp_report_penjualan_tahunan
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_tahunan(
  IN p_tahun INT
)
BEGIN
  SELECT
    MONTH(pj.created_at) AS bulan,
    MONTHNAME(pj.created_at) AS nama_bulan,
    COUNT(pj.idpenjualan) AS jumlah_transaksi,
    SUM(pj.subtotal_nilai) AS total_subtotal,
    SUM(pj.ppn) AS total_ppn,
    SUM(pj.total_nilai) AS total_penjualan,
    SUM(dp.jumlah) AS total_qty_terjual
  FROM penjualan pj
  LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
  WHERE YEAR(pj.created_at) = p_tahun
  GROUP BY MONTH(pj.created_at), MONTHNAME(pj.created_at)
  ORDER BY bulan;
END$$
DELIMITER ;

-- SP 5: sp_report_pengadaan_periode
DELIMITER $$
CREATE PROCEDURE sp_report_pengadaan_periode(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE,
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  SELECT
    pg.idpengadaan,
    pg.created_at AS tanggal_pengadaan,
    u.username,
    v.nama_vendor AS nama_vendor,
    -- Normalize status: map legacy single-letter codes to friendly labels
    CASE
      WHEN pg.status = 'A' THEN 'completed'
      WHEN pg.status = 'P' THEN 'draft'
      ELSE pg.status
    END AS status_pengadaan,
    pg.subtotal_nilai,
    pg.ppn,
    pg.total_nilai,
    COUNT(dp.iddetail_pengadaan) AS jumlah_item
  FROM pengadaan pg
  LEFT JOIN user u ON pg.user_iduser = u.iduser
  LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
  LEFT JOIN detail_pengadaan dp ON pg.idpengadaan = dp.idpengadaan
  WHERE DATE(pg.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pg.idpengadaan, pg.created_at, u.username, v.nama_vendor,
           pg.status, pg.subtotal_nilai, pg.ppn, pg.total_nilai
  ORDER BY pg.created_at DESC
  LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- SP 6: sp_report_penerimaan_periode
DELIMITER $$
CREATE PROCEDURE sp_report_penerimaan_periode(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE,
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  SELECT
    pen.idpenerimaan,
    pen.created_at,
    pen.status,
    pen.idpengadaan,
    u.username,
    v.nama_vendor,
    COUNT(dpen.iddetail_penerimaan) AS jumlah_item,
    SUM(dpen.jumlah_terima) AS total_jumlah_terima,
    SUM(dpen.sub_total_terima) AS total_nilai
  FROM penerimaan pen
  LEFT JOIN user u ON pen.iduser = u.iduser
  LEFT JOIN pengadaan p ON pen.idpengadaan = p.idpengadaan
  LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
  LEFT JOIN detail_penerimaan dpen ON pen.idpenerimaan = dpen.idpenerimaan
  WHERE DATE(pen.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pen.idpenerimaan, pen.created_at, pen.status, pen.idpengadaan,
           u.username, v.nama_vendor
  ORDER BY pen.created_at DESC
  LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

-- SP 7: sp_report_stock_opname
DELIMITER $$
CREATE PROCEDURE sp_report_stock_opname()
BEGIN
  SELECT
    b.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan AS satuan,
    COALESCE(
      (SELECT COALESCE(SUM(dp.jumlah_terima),0) FROM detail_penerimaan dp WHERE dp.idbarang = b.idbarang), 0
    ) - COALESCE(
      (SELECT COALESCE(SUM(dpj.jumlah),0) FROM detail_penjualan dpj WHERE dpj.idbarang = b.idbarang), 0
    ) AS current_stock,
    b.status
  FROM barang b
  LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
  WHERE b.status = 1
  ORDER BY b.nama;
END$$
DELIMITER ;

-- SP 8: sp_filter_barang_stock_rendah
DELIMITER $$
CREATE PROCEDURE sp_filter_barang_stock_rendah(
  IN p_min_stock INT
)
BEGIN
  -- Robust calculation that returns the fields expected by the Blade view
  SELECT
    t.idbarang,
    t.nama_barang,
    t.satuan,
    t.harga,
    t.current_stock,
    t.status
  FROM (
    SELECT
      b.idbarang,
      b.nama AS nama_barang,
      s.nama_satuan AS satuan,
      b.harga,
      (
        COALESCE((SELECT SUM(dp.jumlah_terima) FROM detail_penerimaan dp WHERE dp.idbarang = b.idbarang), 0)
        - COALESCE((SELECT SUM(dpj.jumlah) FROM detail_penjualan dpj WHERE dpj.idbarang = b.idbarang), 0)
      ) AS current_stock,
      b.status
    FROM barang b
    LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE b.status = 1
  ) AS t
  WHERE t.current_stock <= p_min_stock
  ORDER BY t.current_stock ASC, t.nama_barang;
END$$
DELIMITER ;

-- SP 9: sp_filter_detail_pengadaan
DELIMITER $$
CREATE PROCEDURE sp_filter_detail_pengadaan(
  IN p_idpengadaan INT
)
BEGIN
  SELECT
  dp.iddetail_pengadaan,
  dp.idpengadaan,
  dp.idbarang,
  b.nama AS nama_barang,
  b.idbarang AS kode_barang,
  s.nama_satuan,
  dp.jumlah,
  dp.harga_satuan,
  dp.sub_total
  FROM detail_pengadaan dp
  INNER JOIN barang b ON dp.idbarang = b.idbarang
  LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
  WHERE dp.idpengadaan = p_idpengadaan
  ORDER BY dp.iddetail_pengadaan;
END$$
DELIMITER ;

-- SP 10: sp_filter_detail_penerimaan
DELIMITER $$
CREATE PROCEDURE sp_filter_detail_penerimaan(
  IN p_idpenerimaan INT
)
BEGIN
  SELECT
  dpen.iddetail_penerimaan,
  dpen.idpenerimaan,
  dpen.idbarang,
  b.nama AS nama_barang,
  b.idbarang AS kode_barang,
  s.nama_satuan,
  dpen.jumlah_terima,
  dpen.harga_satuan_terima,
  dpen.sub_total_terima
  FROM detail_penerimaan dpen
  INNER JOIN barang b ON dpen.idbarang = b.idbarang
  LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
  WHERE dpen.idpenerimaan = p_idpenerimaan
  ORDER BY dpen.iddetail_penerimaan;
END$$
DELIMITER ;

-- SP 11: sp_filter_detail_penjualan
DELIMITER $$
CREATE PROCEDURE sp_filter_detail_penjualan(
  IN p_idpenjualan INT
)
BEGIN
  SELECT
  dp.iddetail_penjualan,
  dp.idpenjualan,
  dp.idbarang,
  b.nama AS nama_barang,
  b.idbarang AS kode_barang,
  s.nama_satuan,
  dp.jumlah,
  dp.harga_satuan,
  dp.subtotal,
    (SELECT persen FROM margin_penjualan m
     INNER JOIN penjualan pj ON pj.idmargin_penjualan = m.idmargin_penjualan
     WHERE pj.idpenjualan = dp.idpenjualan) AS margin_persen,
    FLOOR(dp.subtotal * COALESCE((
      SELECT persen FROM margin_penjualan m
      INNER JOIN penjualan pj ON pj.idmargin_penjualan = m.idmargin_penjualan
      WHERE pj.idpenjualan = dp.idpenjualan
    ), 0) / 100) AS nilai_margin
  FROM detail_penjualan dp
  INNER JOIN barang b ON dp.idbarang = b.idbarang
  LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
  WHERE dp.idpenjualan = p_idpenjualan
  ORDER BY dp.iddetail_penjualan;
END$$
DELIMITER ;

-- SP 12: sp_filter_kartu_stok
DELIMITER $$
CREATE PROCEDURE sp_filter_kartu_stok(
  IN p_idbarang INT,
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE
)
BEGIN
  SELECT
    ks.idkartu_stok,
    ks.jenis_transaksi,
    CASE
      WHEN ks.jenis_transaksi = 'P' THEN 'Penerimaan'
      WHEN ks.jenis_transaksi = 'M' THEN 'Masuk (Legacy)'
      WHEN ks.jenis_transaksi = 'K' THEN 'Keluar (Penjualan)'
      WHEN ks.jenis_transaksi = 'R' THEN 'Retur'
      ELSE 'Unknown'
    END AS keterangan,
    ks.masuk,
    ks.keluar,
    ks.stock,
    ks.created_at,
    ks.idtransaksi
  FROM kartu_stok ks
  WHERE ks.idbarang = p_idbarang
    AND DATE(ks.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  ORDER BY ks.created_at DESC, ks.idkartu_stok DESC;
END$$
DELIMITER ;

SELECT '12 Stored Procedures created successfully!' AS status;

-- ========================================================================================================
-- PART 4: INDEXES
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'PART 4: Creating Indexes...' AS '';
SELECT '========================================' AS '';

CREATE INDEX IF NOT EXISTS idx_kartu_stok_idbarang_id ON kartu_stok(idbarang, idkartu_stok);
CREATE INDEX IF NOT EXISTS idx_kartu_stok_idbarang_created ON kartu_stok(idbarang, created_at);
CREATE INDEX IF NOT EXISTS idx_detail_penjualan_idpenjualan ON detail_penjualan(idpenjualan);
CREATE INDEX IF NOT EXISTS idx_detail_pengadaan_idpengadaan ON detail_pengadaan(idpengadaan);
CREATE INDEX IF NOT EXISTS idx_detail_penerimaan_idpenerimaan ON detail_penerimaan(idpenerimaan);
CREATE INDEX IF NOT EXISTS idx_penjualan_created_at ON penjualan(created_at);
CREATE INDEX IF NOT EXISTS idx_pengadaan_created_at ON pengadaan(created_at);
CREATE INDEX IF NOT EXISTS idx_penerimaan_created_at ON penerimaan(created_at);
CREATE INDEX IF NOT EXISTS idx_pengadaan_status ON pengadaan(status);
CREATE INDEX IF NOT EXISTS idx_penerimaan_status ON penerimaan(status);
CREATE INDEX IF NOT EXISTS idx_barang_status ON barang(status);
CREATE INDEX IF NOT EXISTS idx_detail_penerimaan_idbarang ON detail_penerimaan(idbarang);
CREATE INDEX IF NOT EXISTS idx_detail_pengadaan_idbarang ON detail_pengadaan(idbarang);
CREATE INDEX IF NOT EXISTS idx_detail_penjualan_idbarang ON detail_penjualan(idbarang);
CREATE INDEX IF NOT EXISTS idx_penerimaan_idpengadaan ON penerimaan(idpengadaan);

SELECT '15 Indexes created successfully!' AS status;

-- ========================================================================================================
-- PART 5: VERIFICATION
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'PART 5: Verification...' AS '';
SELECT '========================================' AS '';

-- Count functions
SELECT COUNT(*) AS total_functions
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'indoapril'
  AND ROUTINE_TYPE = 'FUNCTION'
  AND ROUTINE_NAME LIKE 'fn_%';

-- Count stored procedures
SELECT COUNT(*) AS total_procedures
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'indoapril'
  AND ROUTINE_TYPE = 'PROCEDURE'
  AND ROUTINE_NAME LIKE 'sp_%';

-- Count indexes
SELECT COUNT(DISTINCT INDEX_NAME) AS total_indexes
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'indoapril'
  AND INDEX_NAME LIKE 'idx_%';

-- Count triggers (should be 3 for detail_pengadaan)
SELECT COUNT(*) AS total_triggers
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = 'indoapril';

-- ========================================================================================================
-- DEPLOYMENT SUMMARY
-- ========================================================================================================
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT 'DEPLOYMENT COMPLETED!' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT 'Summary:' AS '';
SELECT '- Functions: 9 (fn_get_stock, fn_calc_*, fn_*_total)' AS '';
SELECT '- Stored Procedures: 12 (sp_report_*, sp_filter_*)' AS '';
SELECT '- Triggers: 3 (Auto-update pengadaan totals on detail changes)' AS '';
SELECT '- Indexes: 15 (Performance optimization)' AS '';
SELECT '' AS '';
SELECT 'Triggers Details:' AS '';
SELECT '  1. trg_after_insert_detail_pengadaan - Auto calc totals on INSERT' AS '';
SELECT '  2. trg_after_update_detail_pengadaan - Auto calc totals on UPDATE' AS '';
SELECT '  3. trg_after_delete_detail_pengadaan - Auto calc totals on DELETE' AS '';
SELECT '' AS '';
SELECT 'Next steps:' AS '';
SELECT '1. Test functions: SELECT fn_get_stock(1);' AS '';
SELECT '2. Test SPs: CALL sp_report_stock_opname();' AS '';
SELECT '3. Test triggers: INSERT/UPDATE/DELETE detail_pengadaan' AS '';
SELECT '4. Test Laravel controllers (pengadaan, penerimaan)' AS '';
SELECT '5. Monitor performance with EXPLAIN queries' AS '';
SELECT '' AS '';
SELECT 'Note: Pengadaan totals now AUTO-UPDATED by triggers!' AS '';
SELECT '========================================' AS '';
