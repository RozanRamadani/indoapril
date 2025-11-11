-- Triggers to maintain kartu_stok ledger for penjualan (OUT) operations
-- These triggers assume that `detail_penjualan` rows are inserted (by app or SP)
-- and will create the corresponding kartu_stok entries of type 'K' (keluar).
-- Important: Do NOT keep SPs that also insert into kartu_stok for penjualan to avoid duplicates.

DELIMITER $$

CREATE DEFINER=CURRENT_USER TRIGGER trg_after_insert_detail_penjualan
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
  DECLARE last_stock INT DEFAULT 0;
  -- Get the most recent stock value for this barang (if any)
  SELECT stock INTO last_stock
  FROM kartu_stok
  WHERE idbarang = NEW.idbarang
  ORDER BY created_at DESC, idtransaksi DESC
  LIMIT 1;

  IF last_stock IS NULL THEN
    SET last_stock = 0;
  END IF;

  -- Optionally enforce non-negative stock (uncomment SIGNAL to enforce)
  IF last_stock < NEW.jumlah THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok tidak cukup untuk barang ID: ';
  ELSE
    INSERT INTO kartu_stok (
      jenis_transaksi,
      masuk,
      keluar,
      stock,
      created_at,
      idtransaksi,
      idbarang
    ) VALUES (
      'K', -- K for Keluar (sale)
      0,
      NEW.jumlah,
      last_stock - NEW.jumlah,
      NOW(),
      NEW.idpenjualan,
      NEW.idbarang
    );
  END IF;
END$$

DELIMITER ;

-- Note:
-- - This trigger uses the latest `stock` value ordered by `created_at` and `idtransaksi`.
-- - For concurrent transactions, consider using application-level transactions with appropriate
--   locking or maintaining a `barang.current_stock` column that is atomically updated to
--   avoid race conditions. The trigger will run in the same transaction as the INSERT on
--   `detail_penjualan`, so it will participate in commit/rollback.
-- - Do NOT run this trigger if your SPs still insert kartu_stok for penjualan; remove that
--   insertion from the SPs to avoid duplicate ledger rows.
