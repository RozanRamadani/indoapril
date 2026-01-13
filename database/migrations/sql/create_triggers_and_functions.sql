USE indoapril;

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
  RETURN FLOOR(p_subtotal * 0.10);
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
  -- Hitung PPN 10%
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
  -- Hitung PPN 10%
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
  -- Hitung PPN 10%
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

-- Trigger 4: trg_after_insert_detail_retur
-- Buat trigger baru dengan table lock
DELIMITER $$
CREATE TRIGGER trg_after_insert_detail_retur
AFTER INSERT ON detail_retur
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;
  DECLARE v_idbarang INT DEFAULT 0;
  DECLARE v_new_stock INT DEFAULT 0;
  -- Get idbarang from detail_penerimaan
  SELECT idbarang INTO v_idbarang
  FROM detail_penerimaan
  WHERE iddetail_penerimaan = NEW.iddetail_penerimaan
  LIMIT 1;
  -- Hitung stock terakhir dengan LOCK untuk menghindari race condition
  SELECT COALESCE(stock, 0) INTO v_last_stock
  FROM kartu_stok
  WHERE idbarang = v_idbarang
  ORDER BY idkartu_stok DESC
  LIMIT 1
  FOR UPDATE;
  -- Hitung stock baru
  SET v_new_stock = v_last_stock - NEW.jumlah;
  -- Insert kartu_stok (KELUAR) - langsung pakai NEW.idretur
  INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
  VALUES ('R', 0, NEW.jumlah, v_new_stock, NOW(), NEW.idretur, v_idbarang);
END$$
DELIMITER ;


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
  GROUP BY pj.idpenjualan, pj.created_at, u.username, m.persen, pj.subtotal_nilai, pj.ppn, pj.total_nilai
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
    pg.idpengadaan, pg.created_at AS tanggal_pengadaan, u.username, v.nama_vendor AS nama_vendor,
    CASE
      WHEN pg.status = 'C' THEN 'completed'
      WHEN pg.status = 'A' THEN 'progress'
      WHEN pg.status = 'P' THEN 'draft'
      ELSE pg.status
    END AS status_pengadaan, pg.subtotal_nilai, pg.ppn, pg.total_nilai, COUNT(dp.iddetail_pengadaan) AS jumlah_item
  FROM pengadaan pg
  LEFT JOIN user u ON pg.user_iduser = u.iduser
  LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
  LEFT JOIN detail_pengadaan dp ON pg.idpengadaan = dp.idpengadaan
  WHERE DATE(pg.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pg.idpengadaan, pg.created_at, u.username, v.nama_vendor, pg.status, pg.subtotal_nilai, pg.ppn, pg.total_nilai
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
    pen.idpenerimaan, pen.created_at, pen.status, pen.idpengadaan, u.username, v.nama_vendor,
    COUNT(dpen.iddetail_penerimaan) AS jumlah_item,
    SUM(dpen.jumlah_terima) AS total_jumlah_terima,
    SUM(dpen.sub_total_terima) AS total_nilai
  FROM penerimaan pen
  LEFT JOIN user u ON pen.iduser = u.iduser
  LEFT JOIN pengadaan p ON pen.idpengadaan = p.idpengadaan
  LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
  LEFT JOIN detail_penerimaan dpen ON pen.idpenerimaan = dpen.idpenerimaan
  WHERE DATE(pen.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pen.idpenerimaan, pen.created_at, pen.status, pen.idpengadaan, u.username, v.nama_vendor
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
  SELECT
    t.idbarang, t.nama_barang, t.satuan, t.harga, t.current_stock, t.status
  FROM (
    SELECT
      b.idbarang, b.nama AS nama_barang, s.nama_satuan AS satuan, b.harga,
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
  dp.iddetail_penjualan, dp.idpenjualan, dp.idbarang, b.nama AS nama_barang, b.idbarang AS kode_barang, s.nama_satuan,
  dp.jumlah, dp.harga_satuan, dp.subtotal,
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
    ks.idkartu_stok, ks.created_at AS tanggal,
    -- Keterangan transaksi
    CASE
        WHEN ks.jenis_transaksi = 'P' THEN 'Penerimaan dari Vendor'
        WHEN ks.jenis_transaksi = 'K' THEN 'Penjualan ke Customer'
        WHEN ks.jenis_transaksi = 'R' THEN 'Retur dari Customer'
        ELSE 'Transaksi Lain'
    END AS keterangan_transaksi,
    CONCAT('#', ks.idtransaksi) AS nomor_transaksi, ks.idbarang, b.nama AS nama_barang,
    -- Label : Masuk, Keluar, Retur
    CASE
        WHEN ks.jenis_transaksi = 'P' THEN 'Masuk'
        WHEN ks.jenis_transaksi = 'K' THEN 'Keluar'
        WHEN ks.jenis_transaksi = 'R' THEN 'Retur'
        ELSE 'Unknown'
    END AS tipe_mutasi,
    -- Jumlah barang yang masuk (untuk penerimaan)
    COALESCE(ks.masuk, 0) AS qty_masuk,
    -- Jumlah barang yang keluar (untuk penjualan)
    COALESCE(ks.keluar, 0) AS qty_keluar,
    -- Total nilai transaksi
    COALESCE(
        (SELECT SUM(dpen.harga_satuan_terima * dpen.jumlah_terima)
        FROM detail_penerimaan dpen
        WHERE dpen.idpenerimaan = ks.idtransaksi AND dpen.idbarang = ks.idbarang),
        (SELECT SUM(dpj.harga_satuan * dpj.jumlah)
        FROM detail_penjualan dpj
        WHERE dpj.idpenjualan = ks.idtransaksi AND dpj.idbarang = ks.idbarang),
        0
    ) AS nilai_transaksi,
    -- Sisa stok setelah transaksi ini
    ks.stock AS sisa_stok
    FROM kartu_stok ks
    LEFT JOIN barang b ON ks.idbarang = b.idbarang
    WHERE ks.idbarang = p_idbarang
    AND ks.created_at BETWEEN CONCAT(p_tanggal_awal, ' 00:00:00') AND CONCAT(p_tanggal_akhir, ' 23:59:59')
    ORDER BY ks.created_at DESC, ks.idkartu_stok DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_kartu_stok_semua_barang;

-- SP 13: sp_kartu_stok_semua_barang (untuk melihat kartu stok semua barang)
DELIMITER $$
CREATE PROCEDURE sp_kartu_stok_semua_barang(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE
)
BEGIN
  SELECT
    ks.idkartu_stok, ks.created_at AS tanggal,
    -- Keterangan berdasarkan jenis transaksi
    CASE
      WHEN ks.jenis_transaksi = 'P' THEN 'Penerimaan dari Vendor'
      WHEN ks.jenis_transaksi = 'K' THEN 'Penjualan ke Customer'
      WHEN ks.jenis_transaksi = 'R' THEN 'Retur dari Customer'
      ELSE 'Transaksi Lain'
    END AS keterangan,
    CONCAT('#', ks.idtransaksi) AS nomor_transaksi, ks.idbarang, b.nama AS nama_barang,
    -- Tipe mutasi untuk badge
    CASE
      WHEN ks.jenis_transaksi = 'P' THEN 'Masuk'
      WHEN ks.jenis_transaksi = 'K' THEN 'Keluar'
      WHEN ks.jenis_transaksi = 'R' THEN 'Retur'
      ELSE 'Unknown'
    END AS tipe_mutasi,
    -- Qty masuk dan keluar terpisah
    COALESCE(ks.masuk, 0) AS qty_masuk,
    COALESCE(ks.keluar, 0) AS qty_keluar,
    -- Nilai transaksi
    COALESCE(
      CASE
        WHEN ks.jenis_transaksi = 'P' THEN
          (SELECT SUM(dpen.harga_satuan_terima * dpen.jumlah_terima)
           FROM detail_penerimaan dpen
           WHERE dpen.idpenerimaan = ks.idtransaksi AND dpen.idbarang = ks.idbarang)
        WHEN ks.jenis_transaksi = 'K' THEN
          (SELECT SUM(dpj.harga_satuan * dpj.jumlah)
           FROM detail_penjualan dpj
           WHERE dpj.idpenjualan = ks.idtransaksi AND dpj.idbarang = ks.idbarang)
        ELSE 0
      END, 0
    ) AS nilai_transaksi,
    -- Sisa stok
    ks.stock AS sisa_stok
  FROM kartu_stok ks
  LEFT JOIN barang b ON ks.idbarang = b.idbarang
  WHERE DATE(ks.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
    AND b.status = 1
  ORDER BY ks.created_at DESC, ks.idkartu_stok DESC;
END$$
DELIMITER ;
