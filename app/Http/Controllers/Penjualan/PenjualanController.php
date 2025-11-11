<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = DB::select("
         SELECT pj.*, u.username,
             COUNT(dp.iddetail_penjualan) as item_count
         FROM penjualan pj
         LEFT JOIN user u ON pj.iduser = u.iduser
         LEFT JOIN detail_penjualan dp ON pj.idpenjualan = dp.idpenjualan
         GROUP BY pj.idpenjualan, pj.created_at, pj.subtotal_nilai, pj.ppn, pj.total_nilai, pj.iduser, u.username
         ORDER BY pj.created_at DESC
        ");

        return view('penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        $barangs = DB::select('SELECT * FROM master_barang_view WHERE status = 1 ORDER BY nama');
        $margins = DB::select('SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY persen');

        return view('penjualan.create', compact('barangs', 'margins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|integer|exists:barang,idbarang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|integer|min:0',
            'items.*.idmargin_penjualan' => 'required|integer|exists:margin_penjualan,idmargin_penjualan',
        ]);

        try {
            // Get current user (hardcoded for now, replace with auth later)
            $iduser = 1;

            DB::beginTransaction();

            // Validate stock for all items first (gunakan fn_get_stock)
            foreach ($data['items'] as $item) {
                $stock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$item['idbarang']]);
                if ($stock->stock < $item['jumlah']) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$item['idbarang']]);
                    throw new \Exception("Stok tidak cukup untuk {$barang->nama}. Stok tersedia: {$stock->stock}");
                }
            }

            // Use margin from first item as header margin
            $headerMarginId = $data['items'][0]['idmargin_penjualan'] ?? null;

            // INSERT penjualan header menggunakan RAW SQL (NO Query Builder!)
            DB::statement("
                INSERT INTO penjualan (iduser, idmargin_penjualan, subtotal_nilai, ppn, total_nilai, created_at)
                VALUES (?, ?, 0, 0, 0, NOW())
            ", [$iduser, $headerMarginId]);

            // Get last insert ID
            $idpenjualan = DB::selectOne("SELECT LAST_INSERT_ID() as id")->id;

            // INSERT detail penjualan dan hitung subtotal
            // Gunakan FUNCTION fn_calc_subtotal untuk hitung item subtotal
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                // Hitung subtotal dengan function: fn_calc_subtotal(jumlah, harga_satuan)
                $itemSubtotal = DB::selectOne(
                    'SELECT fn_calc_subtotal(?, ?) as subtotal',
                    [$item['jumlah'], $item['harga_satuan']]
                )->subtotal;

                // INSERT detail menggunakan RAW SQL (NO Query Builder!)
                DB::statement("
                    INSERT INTO detail_penjualan (idpenjualan, idbarang, jumlah, harga_satuan, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ", [$idpenjualan, $item['idbarang'], $item['jumlah'], $item['harga_satuan'], $itemSubtotal]);

                // Update kartu_stok (KELUAR) menggunakan RAW SQL (NO Query Builder!)
                $currentStock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$item['idbarang']])->stock;
                $newStock = $currentStock - $item['jumlah'];

                DB::statement("
                    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
                    VALUES ('K', 0, ?, ?, NOW(), ?, ?)
                ", [$item['jumlah'], $newStock, $idpenjualan, $item['idbarang']]);

                $subtotal += $itemSubtotal;
            }

            // Hitung PPN dan total dengan function
            $ppn = DB::selectOne('SELECT fn_calc_ppn(?) as ppn', [$subtotal])->ppn;
            $total = DB::selectOne('SELECT fn_calc_total(?, ?) as total', [$subtotal, $ppn])->total;

            // UPDATE penjualan header menggunakan RAW SQL (NO Query Builder!)
            DB::statement("
                UPDATE penjualan
                SET subtotal_nilai = ?,
                    ppn = ?,
                    total_nilai = ?
                WHERE idpenjualan = ?
            ", [$subtotal, $ppn, $total, $idpenjualan]);

            DB::commit();

            return redirect()->route('penjualan.show', $idpenjualan)
                ->with('success', 'Penjualan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat penjualan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $penjualan = DB::selectOne("
            SELECT pj.*, u.username
            FROM penjualan pj
            LEFT JOIN user u ON pj.iduser = u.iduser
            WHERE pj.idpenjualan = ?
        ", [$id]);

        if (!$penjualan) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Penjualan tidak ditemukan');
        }

    // Join penjualan to read header margin (detail table does not store margin per-row)
    // Also compute nilai_margin per row: jumlah * harga_satuan * margin_persen / 100
    $details = DB::select("\n            SELECT dp.*, dp.subtotal as sub_total, b.nama as nama_barang, s.nama_satuan, m.persen as margin_persen,\n                   COALESCE(FLOOR(dp.jumlah * dp.harga_satuan * COALESCE(m.persen, 0) / 100), 0) as nilai_margin\n            FROM detail_penjualan dp\n            LEFT JOIN barang b ON dp.idbarang = b.idbarang\n            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan\n            LEFT JOIN penjualan pj ON dp.idpenjualan = pj.idpenjualan\n            LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan\n            WHERE dp.idpenjualan = ?\n        ", [$id]);

        return view('penjualan.show', compact('penjualan', 'details'));
    }
}
