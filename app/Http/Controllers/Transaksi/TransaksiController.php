<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // Menampilkan tiga daftar transaksi: penerimaan, pengadaan, penjualan
    public function index()
    {
        // Daftar penerimaan (agregat dari detail_penerimaan)
        $penerimaan = DB::select(
            'SELECT p.idpenerimaan, p.created_at, p.status, p.idpengadaan, u.username, COUNT(dp.iddetail_penerimaan) AS item_count, SUM(dp.sub_total_terima) AS total_nilai
             FROM penerimaan p
             LEFT JOIN `user` u ON p.iduser = u.iduser
             LEFT JOIN detail_penerimaan dp ON p.idpenerimaan = dp.idpenerimaan
             GROUP BY p.idpenerimaan, p.created_at, p.status, p.idpengadaan, u.username
             ORDER BY p.created_at DESC'
        );

        // Daftar pengadaan (agregat dari detail_pengadaan dan vendor)
        $pengadaan = DB::select(
            'SELECT pg.idpengadaan, pg.created_at, pg.status, v.nama_vendor, u.username,
                    COALESCE(pg.subtotal_nilai, SUM(dg.sub_total)) AS subtotal_nilai,
                    COALESCE(pg.total_nilai, SUM(dg.sub_total)) AS total_nilai,
                    COUNT(dg.iddetail_pengadaan) AS item_count
             FROM pengadaan pg
             LEFT JOIN vendor v ON pg.vendor_idvendor = v.idvendor
             LEFT JOIN `user` u ON pg.user_iduser = u.iduser
             LEFT JOIN detail_pengadaan dg ON pg.idpengadaan = dg.idpengadaan
             GROUP BY pg.idpengadaan, pg.created_at, pg.status, v.nama_vendor, u.username, pg.subtotal_nilai, pg.total_nilai
             ORDER BY pg.created_at DESC'
        );

        // Daftar penjualan (agregat dari detail_penjualan dan margin)
        $penjualan = DB::select(
            'SELECT pj.idpenjualan, pj.created_at, pj.subtotal_nilai, pj.ppn, pj.total_nilai, u.username, m.persen AS margin_persen, COUNT(dpj.iddetail_penjualan) AS item_count
             FROM penjualan pj
             LEFT JOIN `user` u ON pj.iduser = u.iduser
             LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
             LEFT JOIN detail_penjualan dpj ON pj.idpenjualan = dpj.idpenjualan
             GROUP BY pj.idpenjualan, pj.created_at, pj.subtotal_nilai, pj.ppn, pj.total_nilai, u.username, m.persen
             ORDER BY pj.created_at DESC'
        );

        return view('transaksi.index', compact('penerimaan', 'pengadaan', 'penjualan'));
    }
}
