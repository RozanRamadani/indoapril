-- ========================================================================================================
-- TRIGGER AUTO KARTU STOK UNTUK PENERIMAAN
-- ========================================================================================================
-- Trigger ini akan otomatis insert ke kartu_stok ketika ada INSERT di detail_penerimaan
-- HANYA jika penerimaan sudah status 'A' (finalized)
--
-- PENTING:
-- - Jika pakai trigger ini, HAPUS kode manual insert kartu_stok di PenerimaanController::finalize()
-- - Trigger hanya jalan untuk penerimaan yang sudah finalized (status = 'A')
-- - Menggunakan FOR UPDATE lock untuk prevent race condition
--
-- CREATED: November 27, 2025
-- ========================================================================================================

USE indoapril;

-- Drop trigger lama jika ada
DROP TRIGGER IF EXISTS trg_after_insert_detail_penerimaan;

DELIMITER $$

CREATE TRIGGER trg_after_insert_detail_penerimaan
AFTER INSERT ON detail_penerimaan
FOR EACH ROW
BEGIN
  DECLARE v_status VARCHAR(1);
  DECLARE v_last_stock INT DEFAULT 0;
  DECLARE v_new_stock INT DEFAULT 0;

  -- Cek status penerimaan
  SELECT status INTO v_status
  FROM penerimaan
  WHERE idpenerimaan = NEW.idpenerimaan;

  -- HANYA insert kartu_stok jika penerimaan sudah finalized (status = 'A')
  IF v_status = 'A' THEN
    -- Get last stock dengan locking untuk prevent race condition
    SELECT COALESCE(stock, 0) INTO v_last_stock
    FROM kartu_stok
    WHERE idbarang = NEW.idbarang
    ORDER BY idkartu_stok DESC
    LIMIT 1
    FOR UPDATE;

    -- Hitung stock baru (stock lama + qty terima)
    SET v_new_stock = v_last_stock + NEW.jumlah_terima;

    -- Insert ke kartu_stok
    INSERT INTO kartu_stok (
      jenis_transaksi,
      masuk,
      keluar,
      stock,
      created_at,
      idtransaksi,
      idbarang
    ) VALUES (
      'P',                          -- P = Penerimaan dari Vendor
      NEW.jumlah_terima,            -- Qty masuk
      0,                            -- Qty keluar = 0
      v_new_stock,                  -- Stock baru
      NOW(),                        -- Timestamp
      NEW.idpenerimaan,             -- ID transaksi
      NEW.idbarang                  -- ID barang
    );
  END IF;
END$$

DELIMITER ;

-- ========================================================================================================
-- VERIFICATION
-- ========================================================================================================
SELECT 'Trigger trg_after_insert_detail_penerimaan created successfully!' AS status;

-- Show trigger details
SHOW TRIGGERS WHERE `Trigger` = 'trg_after_insert_detail_penerimaan'\G

-- ========================================================================================================
-- TESTING GUIDE
-- ========================================================================================================
-- 1. Hapus kode manual di PenerimaanController::finalize() yang insert kartu_stok
-- 2. Test flow:
--    - Buat PO baru
--    - Buat Penerimaan dari PO (status = 'P')
--    - Tambah items ke detail_penerimaan -> trigger TIDAK jalan (status masih 'P')
--    - Finalize penerimaan (update status = 'A') -> trigger jalan otomatis
-- 3. Cek kartu_stok: SELECT * FROM kartu_stok WHERE jenis_transaksi = 'P' ORDER BY created_at DESC LIMIT 10;
-- ========================================================================================================
