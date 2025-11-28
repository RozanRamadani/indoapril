-- ========================================================================================================
-- TRIGGER AUTO KARTU STOK UNTUK PENJUALAN
-- ========================================================================================================
-- Trigger ini akan otomatis insert ke kartu_stok ketika ada INSERT di detail_penjualan
-- Penjualan TIDAK punya status draft, langsung finalized saat dibuat
--
-- PENTING:
-- - Jika pakai trigger ini, HAPUS kode manual insert kartu_stok di PenjualanController::store()
-- - Trigger akan langsung jalan setiap ada INSERT detail_penjualan
-- - Menggunakan FOR UPDATE lock untuk prevent race condition
-- - Stock akan BERKURANG (keluar)
--
-- CREATED: November 27, 2025
-- ========================================================================================================

USE indoapril;

-- Drop trigger lama jika ada
DROP TRIGGER IF EXISTS trg_after_insert_detail_penjualan;

DELIMITER $$

CREATE TRIGGER trg_after_insert_detail_penjualan
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE v_last_stock INT DEFAULT 0;
  DECLARE v_new_stock INT DEFAULT 0;

  -- Get last stock dengan locking untuk prevent race condition
  SELECT COALESCE(stock, 0) INTO v_last_stock
  FROM kartu_stok
  WHERE idbarang = NEW.idbarang
  ORDER BY idkartu_stok DESC
  LIMIT 1
  FOR UPDATE;

  -- Hitung stock baru (stock lama - qty jual)
  SET v_new_stock = v_last_stock - NEW.jumlah;

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
    'K',                          -- K = Penjualan ke Customer (Keluar)
    0,                            -- Qty masuk = 0
    NEW.jumlah,                   -- Qty keluar
    v_new_stock,                  -- Stock baru (berkurang)
    NOW(),                        -- Timestamp
    NEW.idpenjualan,              -- ID transaksi
    NEW.idbarang                  -- ID barang
  );
END$$

DELIMITER ;

-- ========================================================================================================
-- VERIFICATION
-- ========================================================================================================
SELECT 'Trigger trg_after_insert_detail_penjualan created successfully!' AS status;

-- Show trigger details
SHOW TRIGGERS WHERE `Trigger` = 'trg_after_insert_detail_penjualan'\G

-- ========================================================================================================
-- TESTING GUIDE
-- ========================================================================================================
-- 1. Hapus kode manual di PenjualanController::store() yang insert kartu_stok
-- 2. Test flow:
--    - Buat Penjualan baru
--    - Tambah items ke detail_penjualan -> trigger langsung jalan otomatis
-- 3. Cek kartu_stok: SELECT * FROM kartu_stok WHERE jenis_transaksi = 'K' ORDER BY created_at DESC LIMIT 10;
-- 4. Pastikan stock BERKURANG dengan benar
-- ========================================================================================================
