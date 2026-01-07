<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PenjualanController - Sales Transaction Management
 *
 * Flow: Langsung final saat submit
 * - Penjualan langsung update kartu_stok (stock berkurang)
 * - Tidak ada draft mode (real penjualan langsung tercatat)
 */
class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = DB::table('penjualan as pj')
            ->leftJoin('user as u', 'pj.iduser', '=', 'u.iduser')
            ->leftJoin('margin_penjualan as m', 'pj.idmargin_penjualan', '=', 'm.idmargin_penjualan')
            ->leftJoin('detail_penjualan as dp', 'pj.idpenjualan', '=', 'dp.idpenjualan')
            ->select(
                'pj.idpenjualan',
                'pj.created_at',
                'pj.subtotal_nilai',
                'pj.ppn',
                'pj.total_nilai',
                'u.username',
                'm.persen as margin_persen',
                DB::raw('COUNT(dp.iddetail_penjualan) AS jumlah_item'),
                DB::raw('SUM(dp.jumlah) AS total_qty')
            )
            ->groupBy('pj.idpenjualan', 'pj.created_at', 'pj.subtotal_nilai',
                     'pj.ppn', 'pj.total_nilai', 'u.username', 'm.persen')
            ->orderBy('pj.created_at', 'DESC')
            ->paginate(15)
            ->withQueryString();

        return view('penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        // Get active barang with stock info
        $barangs = DB::select("
            SELECT
                b.idbarang,
                b.nama,
                b.jenis,
                s.nama_satuan,
                b.harga,
                fn_get_stock(b.idbarang) AS current_stock
            FROM barang b
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE b.status = 1
              AND fn_get_stock(b.idbarang) > 0
            ORDER BY b.nama
        ");

        // Get active margins
        $margins = DB::select('
            SELECT idmargin_penjualan, persen
            FROM margin_penjualan
            WHERE status = 1
            ORDER BY persen
        ');

        return view('penjualan.create', compact('barangs', 'margins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idmargin_penjualan' => 'required|exists:margin_penjualan,idmargin_penjualan',
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|exists:barang,idbarang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $iduser = Auth::id() ?? 1;

        DB::beginTransaction();
        try {
            // Validasi stock untuk semua item
            foreach ($request->items as $item) {
                $stock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$item['idbarang']])->stock;
                if ($stock < $item['jumlah']) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$item['idbarang']]);
                    throw new \Exception("Stock tidak cukup untuk {$barang->nama}. Stock tersedia: {$stock}");
                }
            }

            // Insert penjualan header
            DB::statement("
                INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
                VALUES (NOW(), 0, 0, 0, ?, ?)
            ", [$iduser, $request->idmargin_penjualan]);

            $idpenjualan = (int) DB::getPdo()->lastInsertId();

            // Get margin percentage
            $margin_persen = DB::selectOne('SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = ?',
                [$request->idmargin_penjualan])->persen ?? 0;

            // Insert detail penjualan dan update kartu_stok
            $subtotal_total = 0;
            foreach ($request->items as $item) {
                // Calculate harga jual (harga pokok + margin)
                // harga_jual = harga_satuan × (1 + margin%/100)
                $harga_jual = $item['harga_satuan'] * (1 + $margin_persen / 100);

                // Calculate subtotal with margin included
                $subtotal = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as subtotal', [
                    $item['jumlah'],
                    $harga_jual
                ])->subtotal;

                $subtotal_total += $subtotal;

                // Insert detail (simpan harga_satuan original, subtotal sudah include margin)
                DB::statement("
                    INSERT INTO detail_penjualan (idpenjualan, idbarang, jumlah, harga_satuan, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ", [$idpenjualan, $item['idbarang'], $item['jumlah'], $harga_jual, $subtotal]);

                // Update kartu_stok (langsung kurangi stock)
                $last_stock = DB::selectOne('SELECT fn_get_stock(?) as stock', [$item['idbarang']])->stock;
                $new_stock = $last_stock - $item['jumlah'];

                DB::statement("
                    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
                    VALUES ('K', 0, ?, ?, NOW(), ?, ?)
                ", [$item['jumlah'], $new_stock, $idpenjualan, $item['idbarang']]);
            }

            // Update penjualan totals
            $ppn = DB::selectOne('SELECT fn_calc_ppn(?) as ppn', [$subtotal_total])->ppn;
            $total = DB::selectOne('SELECT fn_calc_total(?, ?) as total', [$subtotal_total, $ppn])->total;

            DB::statement("
                UPDATE penjualan
                SET subtotal_nilai = ?, ppn = ?, total_nilai = ?
                WHERE idpenjualan = ?
            ", [$subtotal_total, $ppn, $total, $idpenjualan]);

            DB::commit();

            return redirect()->route('penjualan.show', $idpenjualan)
                ->with('success', 'Penjualan berhasil disimpan dan stock telah diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan penjualan: ' . $e->getMessage());
        }
    }



    /**
     * Show read-only penjualan
     */
    public function show($id)
    {
        $penjualan = DB::selectOne("
            SELECT
                pj.*,
                u.username,
                m.persen AS margin_persen
            FROM penjualan pj
            LEFT JOIN user u ON pj.iduser = u.iduser
            LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
            WHERE pj.idpenjualan = ?
        ", [$id]);

        if (!$penjualan) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Penjualan tidak ditemukan');
        }

        // Get detail penjualan dengan margin calculation menggunakan fn_calc_margin()
        $details = DB::select("
            SELECT
                dp.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan,
                fn_calc_margin(dp.subtotal, ?) AS nilai_margin
            FROM detail_penjualan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpenjualan = ?
            ORDER BY b.nama
        ", [$penjualan->margin_persen ?? 0, $id]);

        return view('penjualan.show', compact('penjualan', 'details'));
    }

    /**
     * Generate PDF Invoice for penjualan
     */
    public function printInvoice($id)
    {
        // Get penjualan header
        $penjualan = DB::selectOne("
            SELECT
                pj.*,
                u.username,
                m.persen AS margin_persen
            FROM penjualan pj
            LEFT JOIN user u ON pj.iduser = u.iduser
            LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
            WHERE pj.idpenjualan = ?
        ", [$id]);

        if (!$penjualan) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Penjualan tidak ditemukan');
        }

        // Get detail penjualan
        $details = DB::select("
            SELECT
                dp.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan,
                fn_calc_margin(dp.subtotal, ?) AS nilai_margin
            FROM detail_penjualan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpenjualan = ?
            ORDER BY b.nama
        ", [$penjualan->margin_persen ?? 0, $id]);

        $pdf = Pdf::loadView('penjualan.invoice', compact('penjualan', 'details'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Invoice-' . $penjualan->idpenjualan . '.pdf');
    }


}
