-- ========================================================================================================
-- TRIGGERS: Operasi Otomatis untuk Kartu Stok
-- ========================================================================================================
-- Triggers untuk maintain kartu_stok ledger secara otomatis dengan running balance yang benar
--
-- KONSEP PENTING:
-- 1. kartu_stok adalah LEDGER (append-only) - tidak boleh UPDATE/DELETE row yang sudah ada
-- 2. Setiap trigger INSERT row BARU dengan running balance (last_stock ± qty)
-- 3. jenis_transaksi: 'M' = Masuk (penerimaan), 'K' = Keluar (penjualan), 'R' = Retur
-- 4. stock column = running balance (BUKAN transaction qty)
--
-- FLOW:
-- - INSERT detail_penerimaan → trigger insert kartu_stok MASUK (stock bertambah)
-- - INSERT detail_penjualan → trigger cek stock + insert kartu_stok KELUAR (stock berkurang)
-- - UPDATE detail → trigger insert ADJUSTMENT row baru (bisa MASUK atau KELUAR)
-- - DELETE detail → trigger insert REVERSAL row baru (kebalikan dari transaksi awal)
--
-- PENTING:
-- - Semua trigger AFTER (bukan BEFORE) agar detail sudah pasti tersimpan
-- - Gunakan fn_get_last_stock() untuk ambil running balance terakhir
-- - Stock check hanya untuk KELUAR (penjualan), tidak untuk MASUK (penerimaan)
-- ========================================================================================================

USE indoapril;

-- ========================================================================================================
-- TRIGGER 1: trg_after_insert_detail_penerimaan
-- ========================================================================================================
-- Event     : AFTER INSERT pada detail_penerimaan
-- Tujuan    : Auto-create kartu_stok MASUK saat barang diterima dari vendor
-- Logika    :
--   1. Ambil last_stock untuk barang ini (gunakan fn_get_last_stock)
--   2. Insert kartu_stok dengan jenis 'M' (Masuk)
--   3. Stock baru = last_stock + jumlah_terima (running balance bertambah)
-- Contoh    : Terima 10 unit barang A (last_stock=50) → new stock=60
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_insert_detail_penerimaan;
DELIMITER $$
CREATE TRIGGER trg_after_insert_detail_penerimaan
AFTER INSERT ON detail_penerimaan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;

  -- Ambil stock terakhir untuk barang ini (running balance dari row terakhir)
  SET v_last_stock = fn_get_last_stock(NEW.idbarang);

  -- Insert kartu_stok MASUK dengan running balance baru
  INSERT INTO kartu_stok (
    jenis_transaksi,
    masuk,
    keluar,
    stock,              -- Running balance (last_stock + masuk)
    created_at,
    idtransaksi,        -- ID penerimaan
    idbarang
  ) VALUES (
    'M',                -- M for Masuk (penerimaan)
    NEW.jumlah_terima,  -- Jumlah yang masuk
    0,                  -- Tidak ada keluar
    v_last_stock + NEW.jumlah_terima,  -- Running balance baru
    NOW(),
    NEW.idpenerimaan,
    NEW.idbarang
  );
END$$
DELIMITER ;

-- ========================================================================================================
-- TRIGGER 2: trg_after_insert_detail_penjualan
-- ========================================================================================================
-- Event     : AFTER INSERT pada detail_penjualan
-- Tujuan    : Auto-create kartu_stok KELUAR saat barang dijual
-- Logika    :
--   1. Ambil last_stock untuk barang ini
--   2. CEK: Jika stock tidak cukup → SIGNAL error (batalkan transaksi)
--   3. Insert kartu_stok dengan jenis 'K' (Keluar)
--   4. Stock baru = last_stock - jumlah (running balance berkurang)
-- Contoh    : Jual 5 unit barang A (last_stock=60) → new stock=55
-- Error     : Jual 70 unit barang A (last_stock=60) → SIGNAL ERROR (stock tidak cukup)
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_insert_detail_penjualan;
DELIMITER $$
CREATE TRIGGER trg_after_insert_detail_penjualan
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;

  -- Ambil stock terakhir untuk barang ini
  SET v_last_stock = fn_get_last_stock(NEW.idbarang);

  -- VALIDASI: Cek apakah stok cukup (harus dilakukan SEBELUM insert kartu_stok)
  IF v_last_stock < NEW.jumlah THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Stok tidak cukup untuk penjualan';
  END IF;

  -- Insert kartu_stok KELUAR dengan running balance baru
  INSERT INTO kartu_stok (
    jenis_transaksi,
    masuk,
    keluar,
    stock,              -- Running balance (last_stock - keluar)
    created_at,
    idtransaksi,        -- ID penjualan
    idbarang
  ) VALUES (
    'K',                -- K for Keluar (penjualan)
    0,                  -- Tidak ada masuk
    NEW.jumlah,         -- Jumlah yang keluar
    v_last_stock - NEW.jumlah,  -- Running balance baru
    NOW(),
    NEW.idpenjualan,
    NEW.idbarang
  );
END$$
DELIMITER ;

-- ========================================================================================================
-- TRIGGER 3: trg_after_update_detail_penerimaan
-- ========================================================================================================
-- Event     : AFTER UPDATE pada detail_penerimaan
-- Tujuan    : Auto-adjust kartu_stok saat jumlah penerimaan berubah (koreksi penerimaan)
-- Logika    :
--   1. Hitung selisih (NEW.jumlah_terima - OLD.jumlah_terima)
--   2. Jika selisih != 0, insert ADJUSTMENT row baru
--   3. Jika selisih > 0 (bertambah) → insert MASUK
--   4. Jika selisih < 0 (berkurang) → insert KELUAR
-- Contoh    :
--   - Terima awal 100 unit, dikoreksi jadi 110 unit → insert MASUK 10 unit
--   - Terima awal 100 unit, dikoreksi jadi 95 unit → insert KELUAR 5 unit
-- Note      : Tidak menghapus row lama di kartu_stok (ledger harus append-only)
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_update_detail_penerimaan;
DELIMITER $$
CREATE TRIGGER trg_after_update_detail_penerimaan
AFTER UPDATE ON detail_penerimaan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;
  DECLARE v_diff INT;

  -- Hitung selisih jumlah (positif = bertambah, negatif = berkurang)
  SET v_diff = NEW.jumlah_terima - OLD.jumlah_terima;

  -- Jika ada perubahan jumlah (selisih tidak nol)
  IF v_diff != 0 THEN
    -- Ambil stock terakhir
    SET v_last_stock = fn_get_last_stock(NEW.idbarang);

    -- Insert ADJUSTMENT row ke kartu_stok
    INSERT INTO kartu_stok (
      jenis_transaksi,
      masuk,
      keluar,
      stock,              -- Running balance disesuaikan
      created_at,
      idtransaksi,
      idbarang
    ) VALUES (
      IF(v_diff > 0, 'M', 'K'),           -- M jika tambah, K jika kurang
      IF(v_diff > 0, v_diff, 0),          -- Masuk jika bertambah
      IF(v_diff < 0, ABS(v_diff), 0),     -- Keluar jika berkurang
      v_last_stock + v_diff,              -- Running balance: + jika tambah, - jika kurang
      NOW(),
      NEW.idpenerimaan,
      NEW.idbarang
    );
  END IF;
END$$
DELIMITER ;

-- ========================================================================================================
-- TRIGGER 4: trg_after_update_detail_penjualan
-- ========================================================================================================
-- Event     : AFTER UPDATE pada detail_penjualan
-- Tujuan    : Auto-adjust kartu_stok saat jumlah penjualan berubah (koreksi penjualan)
-- Logika    :
--   1. Hitung selisih (NEW.jumlah - OLD.jumlah)
--   2. Jika selisih > 0 (penjualan bertambah) → CEK stock cukup, lalu insert KELUAR
--   3. Jika selisih < 0 (penjualan berkurang/cancel) → insert MASUK (return)
-- Contoh    :
--   - Jual awal 10 unit, dikoreksi jadi 15 unit → insert KELUAR 5 unit (cek stock dulu)
--   - Jual awal 10 unit, dikoreksi jadi 7 unit → insert MASUK 3 unit (return)
-- Note      : Validasi stock hanya untuk penambahan penjualan (selisih > 0)
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_update_detail_penjualan;
DELIMITER $$
CREATE TRIGGER trg_after_update_detail_penjualan
AFTER UPDATE ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;
  DECLARE v_diff INT;

  -- Hitung selisih jumlah (positif = penjualan bertambah, negatif = berkurang)
  SET v_diff = NEW.jumlah - OLD.jumlah;

  -- Jika ada perubahan jumlah
  IF v_diff != 0 THEN
    -- Ambil stock terakhir
    SET v_last_stock = fn_get_last_stock(NEW.idbarang);

    -- VALIDASI: Cek stock cukup jika penambahan jumlah penjualan (lebih banyak keluar)
    IF v_diff > 0 AND v_last_stock < v_diff THEN
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Stok tidak cukup untuk update penjualan';
    END IF;

    -- Insert ADJUSTMENT row ke kartu_stok
    -- Note: Jika v_diff < 0 (penjualan berkurang), artinya barang "kembali" (MASUK)
    --       Jika v_diff > 0 (penjualan bertambah), artinya barang keluar lebih banyak (KELUAR)
    INSERT INTO kartu_stok (
      jenis_transaksi,
      masuk,
      keluar,
      stock,              -- Running balance disesuaikan
      created_at,
      idtransaksi,
      idbarang
    ) VALUES (
      IF(v_diff < 0, 'M', 'K'),           -- M jika kurangi penjualan (return), K jika tambah
      IF(v_diff < 0, ABS(v_diff), 0),     -- Masuk jika return
      IF(v_diff > 0, v_diff, 0),          -- Keluar jika tambah penjualan
      v_last_stock - v_diff,              -- Running balance: - jika tambah keluar, + jika return
      NOW(),
      NEW.idpenjualan,
      NEW.idbarang
    );
  END IF;
END$$
DELIMITER ;

-- ========================================================================================================
-- TRIGGER 5: trg_after_delete_detail_penerimaan
-- ========================================================================================================
-- Event     : AFTER DELETE pada detail_penerimaan
-- Tujuan    : Auto-create REVERSAL row saat detail penerimaan dihapus (cancel penerimaan)
-- Logika    :
--   1. Insert KELUAR untuk cancel MASUK yang sudah terjadi
--   2. Stock baru = last_stock - jumlah_terima yang dihapus
-- Contoh    :
--   - Hapus penerimaan 50 unit → insert KELUAR 50 unit (stock berkurang)
-- Warning   : Bisa menyebabkan stock negatif jika barang sudah terjual
-- Note      : Ledger tetap append-only, tidak menghapus row lama
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_delete_detail_penerimaan;
DELIMITER $$
CREATE TRIGGER trg_after_delete_detail_penerimaan
AFTER DELETE ON detail_penerimaan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;

  -- Ambil stock terakhir
  SET v_last_stock = fn_get_last_stock(OLD.idbarang);

  -- Insert REVERSAL row ke kartu_stok (KELUAR untuk cancel MASUK)
  INSERT INTO kartu_stok (
    jenis_transaksi,
    masuk,
    keluar,
    stock,              -- Running balance berkurang (bisa negatif jika sudah terjual)
    created_at,
    idtransaksi,
    idbarang
  ) VALUES (
    'K',                -- K untuk reverse MASUK
    0,
    OLD.jumlah_terima,  -- Jumlah yang di-reverse
    v_last_stock - OLD.jumlah_terima,  -- Running balance baru (warning: bisa negatif!)
    NOW(),
    OLD.idpenerimaan,
    OLD.idbarang
  );
END$$
DELIMITER ;

-- ========================================================================================================
-- TRIGGER 6: trg_after_delete_detail_penjualan
-- ========================================================================================================
-- Event     : AFTER DELETE pada detail_penjualan
-- Tujuan    : Auto-create REVERSAL row saat detail penjualan dihapus (cancel penjualan)
-- Logika    :
--   1. Insert MASUK untuk cancel KELUAR yang sudah terjadi (barang kembali)
--   2. Stock baru = last_stock + jumlah yang dihapus
-- Contoh    :
--   - Hapus penjualan 10 unit → insert MASUK 10 unit (stock bertambah kembali)
-- Note      : Ledger tetap append-only, tidak menghapus row lama
-- ========================================================================================================
DROP TRIGGER IF EXISTS trg_after_delete_detail_penjualan;
DELIMITER $$
CREATE TRIGGER trg_after_delete_detail_penjualan
AFTER DELETE ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;

  -- Ambil stock terakhir
  SET v_last_stock = fn_get_last_stock(OLD.idbarang);

  -- Insert REVERSAL row ke kartu_stok (MASUK untuk cancel KELUAR)
  INSERT INTO kartu_stok (
    jenis_transaksi,
    masuk,
    keluar,
    stock,              -- Running balance bertambah (barang kembali)
    created_at,
    idtransaksi,
    idbarang
  ) VALUES (
    'M',                -- M untuk reverse KELUAR (barang return)
    OLD.jumlah,         -- Jumlah yang di-reverse
    0,
    v_last_stock + OLD.jumlah,  -- Running balance baru (bertambah)
    NOW(),
    OLD.idpenjualan,
    OLD.idbarang
  );
END$$
DELIMITER ;

-- ========================================================================================================
-- STATUS CHECK
-- ========================================================================================================
SELECT 'Triggers created successfully! Total: 6 triggers (INSERT/UPDATE/DELETE for penerimaan & penjualan)' AS status;
