-- ========================================================================================================
-- FUNCTIONS: Kalkulasi dan Pembacaan Data
-- ========================================================================================================
-- Functions untuk operasi READ-ONLY dan kalkulasi yang dapat digunakan di:
-- - Laravel Controllers (via DB::select)
-- - Stored Procedures (CALL)
-- - Triggers (direct call)
--
-- PENTING:
-- - Semua function harus DETERMINISTIC atau READS SQL DATA
-- - Tidak boleh mengandung INSERT/UPDATE/DELETE
-- - Hanya untuk kalkulasi dan pembacaan data
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- FUNCTION 1: fn_get_stock
-- ========================================================================================================
-- Deskripsi : Menghitung stok saat ini untuk barang berdasarkan kartu_stok (masuk - keluar)
-- Parameter : p_idbarang (INT) - ID barang yang ingin dicek stocknya
-- Return    : INT - Jumlah stock saat ini (bisa 0 atau positif)
-- Digunakan : Controller, SP Report, Trigger (untuk validasi stock sebelum penjualan)
-- Contoh    : SELECT fn_get_stock(5) AS stock_barang_5;
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_get_stock;
DELIMITER $$
CREATE FUNCTION fn_get_stock(p_idbarang INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_stock INT DEFAULT 0;

  -- Hitung total MASUK dikurangi total KELUAR dari kartu_stok
  -- COALESCE digunakan untuk handle NULL (jika barang belum pernah ada transaksi)
  SELECT COALESCE(SUM(masuk) - SUM(keluar), 0)
  INTO v_stock
  FROM kartu_stok
  WHERE idbarang = p_idbarang;

  RETURN v_stock;
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 2: fn_get_last_stock
-- ========================================================================================================
-- Deskripsi : Mengambil nilai stock terakhir dari baris paling akhir di kartu_stok untuk barang tertentu
-- Parameter : p_idbarang (INT) - ID barang yang ingin dicek last stocknya
-- Return    : INT - Stock terakhir dari running balance (untuk trigger gunakan ini)
-- Digunakan : Trigger (sebelum insert/update/delete untuk hitung running balance)
-- Contoh    : SELECT fn_get_last_stock(5) AS last_stock_barang_5;
-- Note      : Ini lebih efisien untuk trigger daripada fn_get_stock karena langsung ambil 1 row terakhir
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_get_last_stock;
DELIMITER $$
CREATE FUNCTION fn_get_last_stock(p_idbarang INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;

  -- Ambil nilai stock dari row terakhir (ORDER BY created_at DESC + idkartu_stok DESC)
  -- Ini untuk running balance calculation di trigger
  SELECT COALESCE(stock, 0)
  INTO v_last_stock
  FROM kartu_stok
  WHERE idbarang = p_idbarang
  ORDER BY created_at DESC, idkartu_stok DESC
  LIMIT 1;

  RETURN v_last_stock;
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 3: fn_calc_subtotal
-- ========================================================================================================
-- Deskripsi : Menghitung subtotal dari jumlah barang dikali harga satuan
-- Parameter : p_jumlah (INT) - Jumlah/quantity barang
--           : p_harga_satuan (DECIMAL) - Harga per satuan barang
-- Return    : DECIMAL(15,2) - Subtotal (jumlah * harga_satuan)
-- Digunakan : Controller saat insert detail_pengadaan / detail_penjualan
-- Contoh    : SELECT fn_calc_subtotal(10, 15000.00) AS subtotal; -- Result: 150000.00
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_calc_subtotal;
DELIMITER $$
CREATE FUNCTION fn_calc_subtotal(p_jumlah INT, p_harga_satuan DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  -- Kalkulasi sederhana: qty * harga
  RETURN p_jumlah * p_harga_satuan;
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 4: fn_calc_ppn
-- ========================================================================================================
-- Deskripsi : Menghitung PPN 11% dari subtotal (dibulatkan ke bawah)
-- Parameter : p_subtotal (DECIMAL) - Subtotal yang akan dihitung PPN-nya
-- Return    : DECIMAL(15,2) - Nilai PPN 11% (FLOOR untuk pembulatan ke bawah)
-- Digunakan : Controller saat finalize pengadaan / penjualan
-- Contoh    : SELECT fn_calc_ppn(100000.00) AS ppn; -- Result: 11000.00
-- Note      : FLOOR digunakan agar PPN tidak lebih dari seharusnya (pembulatan ke bawah)
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_calc_ppn;
DELIMITER $$
CREATE FUNCTION fn_calc_ppn(p_subtotal DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  -- PPN 11% dengan pembulatan ke bawah (FLOOR)
  RETURN FLOOR(p_subtotal * 0.11);
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 5: fn_calc_total
-- ========================================================================================================
-- Deskripsi : Menghitung total akhir (subtotal + ppn)
-- Parameter : p_subtotal (DECIMAL) - Subtotal sebelum PPN
--           : p_ppn (DECIMAL) - Nilai PPN yang sudah dihitung
-- Return    : DECIMAL(15,2) - Total akhir (subtotal + ppn)
-- Digunakan : Controller saat finalize pengadaan / penjualan
-- Contoh    : SELECT fn_calc_total(100000.00, 11000.00) AS total; -- Result: 111000.00
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_calc_total;
DELIMITER $$
CREATE FUNCTION fn_calc_total(p_subtotal DECIMAL(15,2), p_ppn DECIMAL(15,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  -- Total = subtotal + ppn (sederhana)
  RETURN p_subtotal + p_ppn;
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 6: fn_calc_margin
-- ========================================================================================================
-- Deskripsi : Menghitung nilai margin penjualan berdasarkan persentase (dibulatkan ke bawah)
-- Parameter : p_subtotal (DECIMAL) - Subtotal penjualan
--           : p_persen (DECIMAL) - Persentase margin (misal: 10.00 untuk 10%)
-- Return    : DECIMAL(15,2) - Nilai margin dalam rupiah (FLOOR untuk pembulatan ke bawah)
-- Digunakan : Controller saat create penjualan (untuk hitung margin dari margin_penjualan.persen)
-- Contoh    : SELECT fn_calc_margin(100000.00, 10.00) AS margin; -- Result: 10000.00
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_calc_margin;
DELIMITER $$
CREATE FUNCTION fn_calc_margin(p_subtotal DECIMAL(15,2), p_persen DECIMAL(5,2))
RETURNS DECIMAL(15,2)
DETERMINISTIC
NO SQL
BEGIN
  -- Margin = subtotal * persen / 100, dengan pembulatan ke bawah (FLOOR)
  RETURN FLOOR(p_subtotal * p_persen / 100);
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 7: fn_penjualan_total
-- ========================================================================================================
-- Deskripsi : Menghitung total subtotal dari semua detail_penjualan untuk 1 transaksi penjualan
-- Parameter : p_idpenjualan (INT) - ID penjualan yang ingin dihitung totalnya
-- Return    : DECIMAL(15,2) - SUM dari semua detail.subtotal
-- Digunakan : Controller saat finalize penjualan (untuk update penjualan.subtotal_nilai)
-- Contoh    : SELECT fn_penjualan_total(1) AS total_penjualan_1;
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_penjualan_total;
DELIMITER $$
CREATE FUNCTION fn_penjualan_total(p_idpenjualan INT)
RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_total DECIMAL(15,2) DEFAULT 0;

  -- SUM semua subtotal dari detail_penjualan untuk idpenjualan tertentu
  -- COALESCE untuk handle jika tidak ada detail (return 0)
  SELECT COALESCE(SUM(subtotal), 0)
  INTO v_total
  FROM detail_penjualan
  WHERE idpenjualan = p_idpenjualan;

  RETURN v_total;
END$$
DELIMITER ;

-- ========================================================================================================
-- FUNCTION 8: fn_pengadaan_total
-- ========================================================================================================
-- Deskripsi : Menghitung total sub_total dari semua detail_pengadaan untuk 1 transaksi pengadaan
-- Parameter : p_idpengadaan (INT) - ID pengadaan yang ingin dihitung totalnya
-- Return    : DECIMAL(15,2) - SUM dari semua detail.sub_total
-- Digunakan : Controller saat finalize pengadaan (untuk update pengadaan.subtotal_nilai)
-- Contoh    : SELECT fn_pengadaan_total(1) AS total_pengadaan_1;
-- ========================================================================================================
DROP FUNCTION IF EXISTS fn_pengadaan_total;
DELIMITER $$
CREATE FUNCTION fn_pengadaan_total(p_idpengadaan INT)
RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_total DECIMAL(15,2) DEFAULT 0;

  -- SUM semua sub_total dari detail_pengadaan untuk idpengadaan tertentu
  -- COALESCE untuk handle jika tidak ada detail (return 0)
  SELECT COALESCE(SUM(sub_total), 0)
  INTO v_total
  FROM detail_pengadaan
  WHERE idpengadaan = p_idpengadaan;

  RETURN v_total;
END$$
DELIMITER ;

-- ========================================================================================================
-- STATUS CHECK
-- ========================================================================================================
SELECT 'Functions created successfully! Total: 8 functions' AS status;
