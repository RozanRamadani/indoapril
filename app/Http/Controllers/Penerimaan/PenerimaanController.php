<?php

namespace App\Http\Controllers\Penerimaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * SIMPLIFIED VERSION - Menggunakan database structure existing
 * 1 Penerimaan = 1 Pengadaan (tidak support multiple pengadaan)
 *
 * Database structure:
 * - penerimaan: idpenerimaan, created_at, status, idpengadaan, iduser
 * - detail_penerimaan: iddetail_penerimaan, idpenerimaan, idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima
 */
class PenerimaanController extends Controller
{
    /**
     * Display a listing of penerimaan
     */
    public function index()
    {
        $penerimaan = DB::select("
            SELECT
                pen.idpenerimaan,
                pen.created_at,
                pen.status,
                pen.idpengadaan,
                pen.iduser,
                u.username,
                v.nama_vendor,
                COUNT(dpen.iddetail_penerimaan) AS total_item,
                SUM(dpen.jumlah_terima) AS total_jumlah
            FROM penerimaan pen
            LEFT JOIN user u ON pen.iduser = u.iduser
            LEFT JOIN pengadaan p ON pen.idpengadaan = p.idpengadaan
            LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
            LEFT JOIN detail_penerimaan dpen ON pen.idpenerimaan = dpen.idpenerimaan
            GROUP BY pen.idpenerimaan, pen.created_at, pen.status, pen.idpengadaan, pen.iduser, u.username, v.nama_vendor
            ORDER BY pen.created_at DESC
        ");

        return view('penerimaan.index', compact('penerimaan'));
    }

    /**
     * Show form to select pengadaan
     */
    public function create()
    {
        // Get pengadaan yang bisa dipilih (status=completed)
        $hasStatus = Schema::hasColumn('pengadaan', 'status_pengadaan');
        $statusWhere = $hasStatus ? "WHERE COALESCE(p.status_pengadaan, p.status) IN ('completed', 'A')" : "WHERE p.status IN ('A')";

        $sql =
            "SELECT\n" .
            "    p.idpengadaan,\n" .
            "    p.created_at AS tanggal_pengadaan,\n" .
            "    p.vendor_idvendor,\n" .
            "    v.nama_vendor,\n" .
            "    COUNT(dp.iddetail_pengadaan) AS total_item,\n" .
            "    SUM(dp.jumlah) AS total_pengadaan\n" .
            "FROM pengadaan p\n" .
            "INNER JOIN vendor v ON p.vendor_idvendor = v.idvendor\n" .
            "INNER JOIN detail_pengadaan dp ON p.idpengadaan = dp.idpengadaan\n" .
            $statusWhere . "\n" .
            "GROUP BY p.idpengadaan, p.created_at, p.vendor_idvendor, v.nama_vendor\n" .
            "ORDER BY p.created_at DESC";

        $pengadaans = DB::select($sql);

        return view('penerimaan.create', compact('pengadaans'));
    }

    /**
     * Store new penerimaan and redirect to detail (keranjang)
     */
    public function store(Request $request)
    {
        $request->validate([
            'idpengadaan' => 'required|exists:pengadaan,idpengadaan'
        ]);

    // If authentication isn't set up yet, fall back to system user id 1 for development
    $iduser = Auth::id() ?? 1;

        // Create penerimaan menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            INSERT INTO penerimaan (created_at, iduser, idpengadaan, status)
            VALUES (NOW(), ?, ?, 'P')
        ", [$iduser, $request->idpengadaan]);

        // Get last insert ID
        $idpenerimaan = DB::selectOne("SELECT LAST_INSERT_ID() as id")->id;

        return redirect()->route('penerimaan.detail', $idpenerimaan)
            ->with('success', 'Penerimaan berhasil dibuat. Silakan input jumlah yang diterima.');
    }

    /**
     * Show penerimaan detail (keranjang) - editable cart
     */
    public function detail($id)
    {
        // Get penerimaan info
        $penerimaan = DB::selectOne("
            SELECT
                pen.*,
                u.username,
                p.vendor_idvendor,
                v.nama_vendor
            FROM penerimaan pen
            LEFT JOIN user u ON pen.iduser = u.iduser
            LEFT JOIN pengadaan p ON pen.idpengadaan = p.idpengadaan
            LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
            WHERE pen.idpenerimaan = ?
        ", [$id]);

        if (!$penerimaan) {
            return redirect()->route('penerimaan.index')->with('error', 'Penerimaan tidak ditemukan');
        }

        // Cek apakah sudah finalized
        if ($penerimaan->status === 'A') {
            return redirect()->route('penerimaan.show', $id)
                ->with('info', 'Penerimaan sudah di-finalisasi. Lihat detail read-only.');
        }

        // Get pengadaan details (as reference for adding items)
        $pengadaanDetails = DB::select("
            SELECT
                dp.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan
            FROM detail_pengadaan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpengadaan = ?
            ORDER BY b.nama
        ", [$penerimaan->idpengadaan]);

        // Get keranjang (detail penerimaan)
        $keranjang = DB::select("
            SELECT
                dpen.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan
            FROM detail_penerimaan dpen
            INNER JOIN barang b ON dpen.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dpen.idpenerimaan = ?
            ORDER BY b.nama
        ", [$id]);

        return view('penerimaan.detail', compact('penerimaan', 'pengadaanDetails', 'keranjang'));
    }

    /**
     * Add item to keranjang
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'idbarang' => 'required|exists:barang,idbarang',
            'jumlah_terima' => 'required|integer|min:1',
            'harga_satuan_terima' => 'required|numeric|min:0'
        ]);

        // Cek status
        $status = DB::selectOne("SELECT status FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$status || $status->status === 'A') {
            return back()->with('error', 'Penerimaan sudah di-finalisasi');
        }

        // Cek duplicate
        $exists = DB::selectOne("
            SELECT iddetail_penerimaan
            FROM detail_penerimaan
            WHERE idpenerimaan = ? AND idbarang = ?
        ", [$id, $request->idbarang]);

        if ($exists) {
            return back()->with('error', 'Barang sudah ada di keranjang. Silakan edit jumlahnya.');
        }

        // Gunakan FUNCTION fn_calc_subtotal untuk hitung sub_total
        $sub_total_result = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as sub_total', [
            $request->jumlah_terima,
            $request->harga_satuan_terima
        ]);
        $sub_total = $sub_total_result->sub_total;

        // Validate jumlah_terima doesn't exceed pengadaan jumlah
        $p = DB::selectOne("SELECT idpengadaan FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$p) {
            return back()->with('error', 'Penerimaan tidak ditemukan');
        }
        $pengadaanQtyRow = DB::selectOne("SELECT jumlah FROM detail_pengadaan WHERE idpengadaan = ? AND idbarang = ?", [$p->idpengadaan, $request->idbarang]);
        $pengadaanJumlah = $pengadaanQtyRow ? intval($pengadaanQtyRow->jumlah) : 0;

        // Sum existing received for this penerimaan and barang (should be zero because we prevented duplicates, but keep safe)
        $existingReceivedRow = DB::selectOne("SELECT COALESCE(SUM(jumlah_terima),0) as total_received FROM detail_penerimaan WHERE idpenerimaan = ? AND idbarang = ?", [$id, $request->idbarang]);
        $existingReceived = $existingReceivedRow ? intval($existingReceivedRow->total_received) : 0;

        if ($existingReceived + intval($request->jumlah_terima) > $pengadaanJumlah) {
            return back()->with('error', 'Jumlah diterima tidak boleh melebihi jumlah pada pengadaan (tersedia: ' . $pengadaanJumlah . ')');
        }

        // Insert detail menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            INSERT INTO detail_penerimaan
            (idpenerimaan, idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
            VALUES (?, ?, ?, ?, ?)
        ", [$id, $request->idbarang, $request->jumlah_terima, $request->harga_satuan_terima, $sub_total]);

        return back()->with('success', 'Item berhasil ditambahkan ke keranjang');
    }

    /**
     * Update item in keranjang
     */
    public function updateItem(Request $request, $id, $detailId)
    {
        $request->validate([
            'jumlah_terima' => 'required|integer|min:1',
            'harga_satuan_terima' => 'required|numeric|min:0'
        ]);

        // Cek status
        $status = DB::selectOne("SELECT status FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$status || $status->status === 'A') {
            return back()->with('error', 'Penerimaan sudah di-finalisasi');
        }

        // Gunakan FUNCTION fn_calc_subtotal untuk hitung sub_total
        $sub_total_result = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as sub_total', [
            $request->jumlah_terima,
            $request->harga_satuan_terima
        ]);
        $sub_total = $sub_total_result->sub_total;

        // Validate jumlah_terima doesn't exceed pengadaan jumlah (account other received rows in this penerimaan)
        $p = DB::selectOne("SELECT idpengadaan FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$p) {
            return back()->with('error', 'Penerimaan tidak ditemukan');
        }
        $pengadaanQtyRow = DB::selectOne("SELECT jumlah FROM detail_pengadaan WHERE idpengadaan = ? AND idbarang = ?", [$p->idpengadaan, $request->idbarang]);
        $pengadaanJumlah = $pengadaanQtyRow ? intval($pengadaanQtyRow->jumlah) : 0;

        $otherReceivedRow = DB::selectOne("SELECT COALESCE(SUM(jumlah_terima),0) as total_other FROM detail_penerimaan WHERE idpenerimaan = ? AND idbarang = ? AND iddetail_penerimaan <> ?", [$id, $request->idbarang, $detailId]);
        $otherReceived = $otherReceivedRow ? intval($otherReceivedRow->total_other) : 0;

        if ($otherReceived + intval($request->jumlah_terima) > $pengadaanJumlah) {
            return back()->with('error', 'Jumlah diterima tidak boleh melebihi jumlah pada pengadaan (tersedia: ' . $pengadaanJumlah . ', sudah diterima: ' . $otherReceived . ')');
        }

        // Update menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            UPDATE detail_penerimaan
            SET jumlah_terima = ?,
                harga_satuan_terima = ?,
                sub_total_terima = ?
            WHERE iddetail_penerimaan = ?
              AND idpenerimaan = ?
        ", [$request->jumlah_terima, $request->harga_satuan_terima, $sub_total, $detailId, $id]);

        return back()->with('success', 'Item berhasil diupdate');
    }

    /**
     * Delete item from keranjang
     */
    public function deleteItem($id, $detailId)
    {
        // Cek status
        $status = DB::selectOne("SELECT status FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$status || $status->status === 'A') {
            return back()->with('error', 'Penerimaan sudah di-finalisasi');
        }

        // Delete menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            DELETE FROM detail_penerimaan
            WHERE iddetail_penerimaan = ?
              AND idpenerimaan = ?
        ", [$detailId, $id]);

        return back()->with('success', 'Item berhasil dihapus dari keranjang');
    }

    /**
     * Show read-only penerimaan
     */
    public function show($id)
    {
        // Get penerimaan info
        $penerimaan = DB::selectOne("
            SELECT
                pen.*,
                u.username,
                p.vendor_idvendor,
                v.nama_vendor
            FROM penerimaan pen
            LEFT JOIN user u ON pen.iduser = u.iduser
            LEFT JOIN pengadaan p ON pen.idpengadaan = p.idpengadaan
            LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
            WHERE pen.idpenerimaan = ?
        ", [$id]);

        if (!$penerimaan) {
            return redirect()->route('penerimaan.index')->with('error', 'Penerimaan tidak ditemukan');
        }

        // Get detail penerimaan
        $details = DB::select("
            SELECT
                dpen.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan
            FROM detail_penerimaan dpen
            INNER JOIN barang b ON dpen.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dpen.idpenerimaan = ?
            ORDER BY b.nama
        ", [$id]);

        return view('penerimaan.show', compact('penerimaan', 'details'));
    }

    /**
     * Finalize penerimaan (update kartu_stok)
     */
    public function finalize($id)
    {
        // Cek status
        $penerimaan = DB::selectOne("SELECT * FROM penerimaan WHERE idpenerimaan = ?", [$id]);
        if (!$penerimaan) {
            return back()->with('error', 'Penerimaan tidak ditemukan');
        }

        if ($penerimaan->status === 'A') {
            return back()->with('error', 'Penerimaan sudah di-finalisasi sebelumnya');
        }

        // Cek ada detail
        $count = DB::selectOne("SELECT COUNT(*) as total FROM detail_penerimaan WHERE idpenerimaan = ?", [$id]);
        if ($count->total == 0) {
            return back()->with('error', 'Tidak ada barang di keranjang. Tambahkan minimal 1 barang.');
        }

        DB::beginTransaction();

        try {
            // Get all details
            $details = DB::select("
                SELECT idbarang, jumlah_terima
                FROM detail_penerimaan
                WHERE idpenerimaan = ?
            ", [$id]);

            foreach ($details as $detail) {
                // Gunakan FUNCTION fn_get_stock untuk get last stock
                $last_stock_result = DB::selectOne('SELECT fn_get_stock(?) as stock', [$detail->idbarang]);
                $last_stock = $last_stock_result->stock;

                $new_stock = $last_stock + $detail->jumlah_terima;

                // Insert to kartu_stok menggunakan RAW SQL (NO Query Builder!)
                DB::statement("
                    INSERT INTO kartu_stok
                    (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
                    VALUES ('P', ?, 0, ?, NOW(), ?, ?)
                ", [$detail->jumlah_terima, $new_stock, $id, $detail->idbarang]);
            }

            // Update penerimaan status menggunakan RAW SQL (NO Query Builder!)
            DB::statement("
                UPDATE penerimaan
                SET status = 'A'
                WHERE idpenerimaan = ?
            ", [$id]);

            DB::commit();

            return redirect()->route('penerimaan.index')
                ->with('success', 'Penerimaan berhasil di-finalisasi dan kartu_stok terupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal finalisasi: ' . $e->getMessage());
        }
    }
}
