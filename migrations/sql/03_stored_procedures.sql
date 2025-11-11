-- ========================================================================================================
-- STORED PROCEDURES: READ-ONLY Operations
-- ========================================================================================================
-- SP untuk laporan, filtering, dan query kompleks (READ-ONLY saja, tidak ada INSERT/UPDATE/DELETE)
--
-- PRINSIP PENTING:
-- 1. SP HANYA untuk SELECT (laporan, filtering, aggregation)
-- 2. TIDAK BOLEH ada INSERT/UPDATE/DELETE/TRANSACTION CONTROL di SP
-- 3. Semua WRITE operations dilakukan di Laravel Controller dengan DB::transaction()
--
-- KENAPA SP READ-ONLY?
-- - Debugging mudah (error langsung terlihat di Laravel stack trace)
-- - Testing mudah (bisa mock, unit test tidak perlu DB)
-- - Transaction control jelas (Laravel yang kontrol commit/rollback)
-- - Code review mudah (logic di Git, bukan manual SQL di server)
--
-- STRUKTUR SP:
-- 1. Laporan Penjualan (per periode, mingguan, bulanan, tahunan)
-- 2. Laporan Pengadaan (per periode)
-- 3. Laporan Stock (opname, stock rendah)
-- 4. Filter Detail (penjualan, kartu_stok)
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- SP 1: sp_report_penjualan_periode
-- ========================================================================================================
-- Deskripsi : Laporan penjualan dalam rentang tanggal tertentu (dengan pagination)
-- Parameter :
--   - p_tanggal_awal (DATE) : Tanggal mulai laporan
--   - p_tanggal_akhir (DATE) : Tanggal akhir laporan
--   - p_limit (INT) : Jumlah data per halaman (pagination)
--   - p_offset (INT) : Offset data (untuk halaman ke-N)
-- Return    : Result set dengan detail penjualan, user, margin, dan jumlah item
-- Digunakan : Controller untuk tampilkan laporan penjualan dengan filter tanggal
-- Contoh    : CALL sp_report_penjualan_periode('2025-01-01', '2025-01-31', 10, 0);
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_report_penjualan_periode;
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_periode(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE,
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  -- SELECT data penjualan dengan JOIN ke user, margin_penjualan, dan count detail
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

-- ========================================================================================================
-- SP 2: sp_report_penjualan_mingguan
-- ========================================================================================================
-- Deskripsi : Laporan summary penjualan per hari dalam 1 minggu tertentu
-- Parameter :
--   - p_tahun (INT) : Tahun (misal: 2025)
--   - p_minggu (INT) : Minggu ke-N dalam tahun (1-52)
-- Return    : Result set dengan summary per hari (tanggal, jumlah transaksi, total penjualan, qty terjual)
-- Digunakan : Controller untuk dashboard/laporan mingguan
-- Contoh    : CALL sp_report_penjualan_mingguan(2025, 5); -- Minggu ke-5 tahun 2025
-- Note      : WEEK(date, 1) = mode ISO 8601 (Senin sebagai hari pertama)
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_report_penjualan_mingguan;
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_mingguan(
  IN p_tahun INT,
  IN p_minggu INT
)
BEGIN
  -- Agregasi per hari dalam minggu yang dipilih
  SELECT
    DATE(pj.created_at) AS tanggal,
    COUNT(DISTINCT pj.idpenjualan) AS jumlah_transaksi,
    SUM(pj.subtotal_nilai) AS total_subtotal,
    SUM(pj.ppn) AS total_ppn,
    SUM(pj.total_nilai) AS total_penjualan,
    SUM(dp.jumlah) AS total_qty_terjual
  FROM penjualan pj
  LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
  WHERE YEAR(pj.created_at) = p_tahun
    AND WEEK(pj.created_at, 1) = p_minggu  -- Mode 1 = ISO 8601 (Senin first day)
  GROUP BY DATE(pj.created_at)
  ORDER BY tanggal;
END$$
DELIMITER ;

-- ========================================================================================================
-- SP 3: sp_report_penjualan_bulanan
-- ========================================================================================================
-- Deskripsi : Laporan summary penjualan per hari dalam 1 bulan tertentu
-- Parameter :
--   - p_tahun (INT) : Tahun (misal: 2025)
--   - p_bulan (INT) : Bulan (1-12)
-- Return    : Result set dengan summary per hari dalam bulan tersebut
-- Digunakan : Controller untuk laporan bulanan
-- Contoh    : CALL sp_report_penjualan_bulanan(2025, 10); -- Oktober 2025
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_report_penjualan_bulanan;
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_bulanan(
  IN p_tahun INT,
  IN p_bulan INT
)
BEGIN
  -- Agregasi per hari dalam bulan yang dipilih
  SELECT
    DATE(pj.created_at) AS tanggal,
    COUNT(DISTINCT pj.idpenjualan) AS jumlah_transaksi,
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

-- ========================================================================================================
-- SP 4: sp_report_penjualan_tahunan
-- ========================================================================================================
-- Deskripsi : Laporan summary penjualan per bulan dalam 1 tahun tertentu
-- Parameter :
--   - p_tahun (INT) : Tahun (misal: 2025)
-- Return    : Result set dengan summary per bulan (bulan, nama bulan, jumlah transaksi, total)
-- Digunakan : Controller untuk laporan tahunan / dashboard annual report
-- Contoh    : CALL sp_report_penjualan_tahunan(2025); -- Seluruh tahun 2025
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_report_penjualan_tahunan;
DELIMITER $$
CREATE PROCEDURE sp_report_penjualan_tahunan(
  IN p_tahun INT
)
BEGIN
  -- Agregasi per bulan dalam tahun yang dipilih
  SELECT
    MONTH(pj.created_at) AS bulan,
    MONTHNAME(pj.created_at) AS nama_bulan,
    COUNT(DISTINCT pj.idpenjualan) AS jumlah_transaksi,
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

-- ========================================================================================================
-- SP 5: sp_report_pengadaan_periode
-- ========================================================================================================
-- Deskripsi : Laporan pengadaan dalam rentang tanggal tertentu (dengan pagination)
-- Parameter :
--   - p_tanggal_awal (DATE) : Tanggal mulai laporan
--   - p_tanggal_akhir (DATE) : Tanggal akhir laporan
--   - p_limit (INT) : Jumlah data per halaman
--   - p_offset (INT) : Offset data
-- Return    : Result set dengan detail pengadaan, vendor, user, status, dan jumlah item
-- Digunakan : Controller untuk tampilkan laporan pengadaan dengan filter tanggal
-- Contoh    : CALL sp_report_pengadaan_periode('2025-01-01', '2025-01-31', 10, 0);
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_report_pengadaan_periode;
DELIMITER $$
CREATE PROCEDURE sp_report_pengadaan_periode(
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE,
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  -- SELECT data pengadaan dengan JOIN ke user, vendor, dan count detail
  SELECT
    pg.idpengadaan,
    pg.created_at AS tanggal_pengadaan,
    u.username,
    v.nama_vendor AS nama_vendor,
    pg.status,
    pg.subtotal_nilai,
    pg.ppn,
    pg.total_nilai,
    COUNT(dp.iddetail_pengadaan) AS jumlah_item
  FROM pengadaan pg
  LEFT JOIN user u ON pg.user_iduser = u.iduser
  LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
  LEFT JOIN detail_pengadaan dp ON pg.idpengadaan = dp.idpengadaan
  WHERE DATE(pg.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  GROUP BY pg.idpengadaan, pg.created_at, u.username, v.nama,
           pg.status, pg.subtotal_nilai, pg.ppn, pg.total_nilai
  ORDER BY pg.created_at DESC
  LIMIT p_limit OFFSET p_offset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_report_stock_opname;
DELIMITER $$
CREATE PROCEDURE sp_report_stock_opname()
BEGIN
  -- OPTIMIZED: 1 query dengan JOIN (bukan N+1 queries dengan function call per row)
  -- Performa: O(n) vs O(n*m) - jauh lebih cepat untuk data besar
  SELECT
    b.idbarang,
    b.nama,
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
  WHERE b.status = 1  -- Hanya barang aktif
  GROUP BY b.idbarang, b.nama, s.nama_satuan, b.status  -- GROUP BY untuk aggregate (removed b.kode)
  ORDER BY b.nama;
END$$
    GROUP BY b.idbarang, b.nama_barang, s.nama_satuan, b.status  -- GROUP BY untuk aggregate
    ORDER BY b.nama;
DROP PROCEDURE IF EXISTS sp_filter_barang_stock_rendah;
DELIMITER $$
CREATE PROCEDURE sp_filter_barang_stock_rendah(
  IN p_min_stock INT
)
BEGIN
  -- OPTIMIZED: 1 query dengan JOIN (bukan N+1 queries dengan function call per row)
  -- Performa: O(n) vs O(n*m) - jauh lebih cepat untuk data besar
  SELECT
    b.idbarang,
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
-- Deskripsi : Filter detail penjualan untuk 1 transaksi penjualan (dengan kalkulasi margin)
-- Parameter :
--   - p_idpenjualan (INT) : ID penjualan yang ingin dilihat detailnya
-- Return    : Result set dengan detail barang, qty, harga, subtotal, margin persen, nilai margin
-- Digunakan : Controller untuk tampilkan detail penjualan dengan margin calculation
-- Contoh    : CALL sp_filter_detail_penjualan(1); -- Detail penjualan ID 1
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_filter_detail_penjualan;
DELIMITER $$
CREATE PROCEDURE sp_filter_detail_penjualan(
  IN p_idpenjualan INT
)
BEGIN
  -- SELECT detail penjualan dengan kalkulasi margin (via function fn_calc_margin)
  SELECT
    dp.iddetail_penjualan,
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.jumlah,
    dp.harga_satuan,
    dp.subtotal,
    m.persen AS margin_persen,
    fn_calc_margin(dp.subtotal, m.persen) AS nilai_margin  -- Call function untuk hitung margin
  FROM detail_penjualan dp
  LEFT JOIN barang b ON dp.idbarang = b.idbarang
  LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
  LEFT JOIN penjualan pj ON dp.idpenjualan = pj.idpenjualan
  LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
  WHERE dp.idpenjualan = p_idpenjualan
  ORDER BY dp.iddetail_penjualan;
END$$
DELIMITER ;

-- ========================================================================================================
-- SP 9: sp_filter_kartu_stok
-- ========================================================================================================
-- Deskripsi : Filter kartu stok untuk 1 barang dalam rentang tanggal tertentu
-- Parameter :
--   - p_idbarang (INT) : ID barang yang ingin dilihat kartu stocknya
--   - p_tanggal_awal (DATE) : Tanggal mulai
--   - p_tanggal_akhir (DATE) : Tanggal akhir
-- Return    : Result set dengan riwayat kartu_stok (masuk, keluar, stock, keterangan)
-- Digunakan : Controller untuk tampilkan riwayat pergerakan stock barang
-- Contoh    : CALL sp_filter_kartu_stok(5, '2025-01-01', '2025-01-31'); -- Kartu stock barang ID 5
-- Note      : jenis_transaksi: M=Masuk(penerimaan), K=Keluar(penjualan), R=Retur
-- ========================================================================================================
DROP PROCEDURE IF EXISTS sp_filter_kartu_stok;
DELIMITER $$
CREATE PROCEDURE sp_filter_kartu_stok(
  IN p_idbarang INT,
  IN p_tanggal_awal DATE,
  IN p_tanggal_akhir DATE
)
BEGIN
  -- SELECT kartu_stok dengan keterangan jenis transaksi
  SELECT
    ks.idkartu_stok,
    ks.jenis_transaksi,
    ks.masuk,
    ks.keluar,
    ks.stock,  -- Running balance
    ks.created_at,
    ks.idtransaksi,
    CASE
      WHEN ks.jenis_transaksi = 'M' THEN 'Penerimaan'
      WHEN ks.jenis_transaksi = 'K' THEN 'Penjualan'
      ELSE 'Adjustment'
    END AS keterangan
  FROM kartu_stok ks
  WHERE ks.idbarang = p_idbarang
    AND DATE(ks.created_at) BETWEEN p_tanggal_awal AND p_tanggal_akhir
  ORDER BY ks.created_at DESC, ks.idkartu_stok DESC;  -- Urutkan dari yang terbaru
END$$
DELIMITER ;

-- ========================================================================================================
-- STATUS CHECK
-- ========================================================================================================
SELECT 'Stored Procedures (READ-ONLY) created successfully! Total: 9 SP' AS status;
