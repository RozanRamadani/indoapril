<?php

namespace App\Http\Controllers\Retur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class ReturController extends Controller
{
    /**
     * Display a listing of retur
     */
    public function index()
    {
        $returs = DB::select("
            SELECT
                r.idretur,
                r.created_at,
                pen.idpenerimaan,
                v.nama_vendor,
                u.username,
                COUNT(dr.iddetail_retur) AS jumlah_item,
                SUM(dr.jumlah) AS total_qty_retur
            FROM retur r
            LEFT JOIN penerimaan pen ON r.idpenerimaan = pen.idpenerimaan
            LEFT JOIN pengadaan pg ON pen.idpengadaan = pg.idpengadaan
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON r.iduser = u.iduser
            LEFT JOIN detail_retur dr ON r.idretur = dr.idretur
            GROUP BY r.idretur, r.created_at, pen.idpenerimaan, v.nama_vendor, u.username
            ORDER BY r.created_at DESC
        ");

        return view('retur.index', compact('returs'));
    }

    /**
     * Show form to select penerimaan for retur
     */
    public function create()
    {
        // Ambil penerimaan yang sudah finalized (status = A)
        $penerimaans = DB::select("
            SELECT
                pen.idpenerimaan,
                pen.created_at,
                v.nama_vendor,
                COUNT(dp.iddetail_penerimaan) AS jumlah_item
            FROM penerimaan pen
            LEFT JOIN pengadaan pg ON pen.idpengadaan = pg.idpengadaan
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            LEFT JOIN detail_penerimaan dp ON pen.idpenerimaan = dp.idpenerimaan
            WHERE pen.status = 'A'
            GROUP BY pen.idpenerimaan, pen.created_at, v.nama_vendor
            ORDER BY pen.created_at DESC
        ");

        return view('retur.create', compact('penerimaans'));
    }

    /**
     * Show form to input retur details for selected penerimaan
     */
    public function detail($idpenerimaan)
    {
        $penerimaan = DB::selectOne("
            SELECT pen.*, v.nama_vendor
            FROM penerimaan pen
            LEFT JOIN pengadaan pg ON pen.idpengadaan = pg.idpengadaan
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            WHERE pen.idpenerimaan = ?
        ", [$idpenerimaan]);

        if (!$penerimaan) {
            return redirect()->route('retur.index')
                ->with('error', 'Penerimaan tidak ditemukan');
        }

        // Ambil detail items yang bisa diretur
        $items = DB::select("
            SELECT
                dp.*,
                b.nama AS nama_barang,
                s.nama_satuan,
                fn_get_stock(dp.idbarang) AS current_stock
            FROM detail_penerimaan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpenerimaan = ?
            ORDER BY b.nama
        ", [$idpenerimaan]);

        return view('retur.detail', compact('penerimaan', 'items'));
    }

    /**
     * Store retur to database
     */
    public function store(Request $request)
    {
        $request->validate([
            'idpenerimaan' => 'required|exists:penerimaan,idpenerimaan',
            'items' => 'required|array|min:1',
            'items.*.iddetail_penerimaan' => 'required|exists:detail_penerimaan,iddetail_penerimaan',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.alasan' => 'required|string|max:200',
        ]);

        $iduser = Auth::id() ?? 1;

        DB::beginTransaction();
        try {
            // Insert retur header
            DB::statement("
                INSERT INTO retur (created_at, idpenerimaan, iduser)
                VALUES (NOW(), ?, ?)
            ", [$request->idpenerimaan, $iduser]);

            $idretur = (int) DB::getPdo()->lastInsertId();

            // Insert detail retur dan update kartu_stok
            foreach ($request->items as $item) {
                $detailPenerimaan = DB::selectOne('SELECT * FROM detail_penerimaan WHERE iddetail_penerimaan = ?', [$item['iddetail_penerimaan']]);

                // Validasi: jumlah retur tidak boleh melebihi jumlah terima
                if ($item['jumlah'] > $detailPenerimaan->jumlah_terima) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$detailPenerimaan->idbarang]);
                    throw new \Exception("Jumlah retur melebihi jumlah penerimaan untuk barang: {$barang->nama}");
                }

                // Validasi: stock harus cukup
                $currentStock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$detailPenerimaan->idbarang])->stock;
                if ($currentStock < $item['jumlah']) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$detailPenerimaan->idbarang]);
                    throw new \Exception("Stock tidak cukup untuk retur barang: {$barang->nama}. Stock tersedia: {$currentStock}");
                }

                // Insert detail_retur
                // Trigger trg_after_insert_detail_retur akan otomatis insert ke kartu_stok
                DB::statement("
                    INSERT INTO detail_retur (idretur, iddetail_penerimaan, jumlah, alasan)
                    VALUES (?, ?, ?, ?)
                ", [$idretur, $item['iddetail_penerimaan'], $item['jumlah'], $item['alasan']]);
            }

            DB::commit();

            return redirect()->route('retur.show', $idretur)
                ->with('success', 'Retur berhasil disimpan dan stock telah diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan retur: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified retur
     */
    public function show($id)
    {
        $retur = DB::selectOne("
            SELECT r.*, pen.idpenerimaan, v.nama_vendor, u.username
            FROM retur r
            LEFT JOIN penerimaan pen ON r.idpenerimaan = pen.idpenerimaan
            LEFT JOIN pengadaan pg ON pen.idpengadaan = pg.idpengadaan
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON r.iduser = u.iduser
            WHERE r.idretur = ?
        ", [$id]);

        if (!$retur) {
            return redirect()->route('retur.index')
                ->with('error', 'Retur tidak ditemukan');
        }

        $details = DB::select("
            SELECT
                dr.*,
                b.nama AS nama_barang,
                s.nama_satuan,
                dp.jumlah_terima AS jumlah_penerimaan_asal,
                dp.harga_satuan_terima
            FROM detail_retur dr
            INNER JOIN detail_penerimaan dp ON dr.iddetail_penerimaan = dp.iddetail_penerimaan
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dr.idretur = ?
            ORDER BY b.nama
        ", [$id]);

        return view('retur.show', compact('retur', 'details'));
    }

    /**
     * Print Surat Retur PDF
     */
    public function printRetur($id)
    {
        // Check if vendor table has alamat and telp columns
        $hasAlamat = Schema::hasColumn('vendor', 'alamat');
        $hasTelp = Schema::hasColumn('vendor', 'telp');

        // Build vendor columns dynamically
        $vendorColumns = "v.nama_vendor";
        if ($hasAlamat) {
            $vendorColumns .= ", v.alamat";
        }
        if ($hasTelp) {
            $vendorColumns .= ", v.telp";
        }

        $retur = DB::selectOne("
            SELECT r.*, pen.idpenerimaan, pg.idpengadaan, {$vendorColumns}, u.username
            FROM retur r
            LEFT JOIN penerimaan pen ON r.idpenerimaan = pen.idpenerimaan
            LEFT JOIN pengadaan pg ON pen.idpengadaan = pg.idpengadaan
            LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON r.iduser = u.iduser
            WHERE r.idretur = ?
        ", [$id]);

        if (!$retur) {
            return redirect()->route('retur.index')
                ->with('error', 'Retur tidak ditemukan');
        }

        $details = DB::select("
            SELECT
                dr.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan,
                dp.jumlah_terima AS jumlah_penerimaan_asal,
                dp.harga_satuan_terima
            FROM detail_retur dr
            INNER JOIN detail_penerimaan dp ON dr.iddetail_penerimaan = dp.iddetail_penerimaan
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dr.idretur = ?
            ORDER BY b.nama
        ", [$id]);

        $pdf = Pdf::loadView('retur.return-note', compact('retur', 'details'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Surat-Retur-' . $retur->idretur . '.pdf');
    }
}
