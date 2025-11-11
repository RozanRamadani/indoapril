<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== BARANG STATISTICS (Consolidated) ====================
        $barangStats = DB::selectOne('
            SELECT
                COUNT(*) as total_barang,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as barang_aktif,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as barang_nonaktif
            FROM barang
        ');

        $stockStats = DB::selectOne('
            SELECT
                SUM(stock) as total_stock,
                SUM(CASE WHEN stock <= 10 AND stock > 0 THEN 1 ELSE 0 END) as stock_rendah,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as stock_habis
            FROM kartu_stok
        ');

        // Nilai Inventori
        $nilaiInventoriResult = DB::selectOne('
            SELECT SUM(b.harga * ks.stock) as total
            FROM barang b
            INNER JOIN kartu_stok ks ON b.idbarang = ks.idbarang
            WHERE b.status = 1
        ');
        $nilaiInventori = $nilaiInventoriResult->total ?? 0;

        // ==================== TRANSAKSI STATISTICS (Consolidated) ====================
        $transaksiStats = DB::selectOne('
            SELECT
                (SELECT COUNT(*) FROM pengadaan) as total_pengadaan,
                (SELECT COUNT(*) FROM penerimaan) as total_penerimaan,
                (SELECT COUNT(*) FROM penjualan) as total_penjualan,
                (SELECT COUNT(*) FROM vendor WHERE status = "Y") as vendor_aktif,
                (SELECT COUNT(*) FROM vendor) as total_vendor,
                (SELECT COUNT(*) FROM user) as total_user
        ');

        // Nilai Pengadaan dan Penjualan Bulan Ini
        $nilaiTransaksiResult = DB::selectOne('
            SELECT
                (SELECT SUM(total_nilai) FROM pengadaan WHERE created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")) as nilai_pengadaan_bulan_ini,
                (SELECT SUM(total_nilai) FROM penjualan WHERE created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")) as nilai_penjualan_bulan_ini,
                (SELECT SUM(total_nilai) FROM penjualan WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as nilai_penjualan_kemarin
        ');

        $nilaiPengadaanBulanIni = $nilaiTransaksiResult->nilai_pengadaan_bulan_ini ?? 0;
        $nilaiPenjualanBulanIni = $nilaiTransaksiResult->nilai_penjualan_bulan_ini ?? 0;
        $nilaiPenjualanKemarin = $nilaiTransaksiResult->nilai_penjualan_kemarin ?? 0;

        // Hitung Pertumbuhan
        $pertumbuhanPenjualan = ($nilaiPenjualanKemarin > 0)
            ? (($nilaiPenjualanBulanIni - $nilaiPenjualanKemarin) / $nilaiPenjualanKemarin) * 100
            : 0;

        // ==================== RECENT ACTIVITIES ====================
        // Transaksi Terbaru (8 terakhir)
        $transaksiTerbaru = DB::select("
            SELECT 'Pengadaan' as tipe, idpengadaan as id, created_at as tanggal,
                   total_nilai as nilai, 'pengadaan' as icon, 'blue' as color
            FROM pengadaan
            UNION ALL
            SELECT 'Penerimaan' as tipe, idpenerimaan as id, created_at as tanggal,
                   0 as nilai, 'penerimaan' as icon, 'green' as color
            FROM penerimaan
            UNION ALL
            SELECT 'Penjualan' as tipe, idpenjualan as id, created_at as tanggal,
                   total_nilai as nilai, 'penjualan' as icon, 'orange' as color
            FROM penjualan
            ORDER BY tanggal DESC
            LIMIT 8
        ");

        // Barang Terbaru (5 terakhir dengan JOIN)
        $barangTerbaru = DB::select('
            SELECT b.*, s.nama_satuan, ks.stock
            FROM barang b
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            LEFT JOIN kartu_stok ks ON b.idbarang = ks.idbarang
            WHERE b.status = 1
            ORDER BY b.idbarang DESC
            LIMIT 5
        ');

        // ==================== CHARTS DATA ====================
        // Statistik per Jenis Barang
        $statistikJenis = DB::select('
            SELECT jenis, COUNT(*) as total
            FROM barang
            WHERE status = 1
            GROUP BY jenis
            ORDER BY total DESC
            LIMIT 5
        ');

        // Transaksi 7 Hari Terakhir
        $transaksi7Hari = DB::select("
            SELECT DATE(created_at) as tanggal, COUNT(*) as total
            FROM (
                SELECT created_at FROM pengadaan WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                UNION ALL
                SELECT created_at FROM penerimaan WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                UNION ALL
                SELECT created_at FROM penjualan WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ) as semua_transaksi
            GROUP BY DATE(created_at)
            ORDER BY tanggal
        ");

        // Top 5 Barang Paling Laku
        $topBarang = DB::select('
            SELECT b.nama, SUM(dp.jumlah) as total_terjual
            FROM detail_penjualan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            GROUP BY dp.idbarang, b.nama
            ORDER BY total_terjual DESC
            LIMIT 5
        ');

        return view('dashboard', [
            'totalBarang' => $barangStats->total_barang,
            'barangAktif' => $barangStats->barang_aktif,
            'barangNonaktif' => $barangStats->barang_nonaktif,
            'totalStock' => $stockStats->total_stock ?? 0,
            'stockRendah' => $stockStats->stock_rendah ?? 0,
            'stockHabis' => $stockStats->stock_habis ?? 0,
            'nilaiInventori' => $nilaiInventori,
            'totalPengadaan' => $transaksiStats->total_pengadaan,
            'nilaiPengadaanBulanIni' => $nilaiPengadaanBulanIni,
            'totalPenerimaan' => $transaksiStats->total_penerimaan,
            'totalPenjualan' => $transaksiStats->total_penjualan,
            'nilaiPenjualanBulanIni' => $nilaiPenjualanBulanIni,
            'pertumbuhanPenjualan' => $pertumbuhanPenjualan,
            'totalVendor' => $transaksiStats->total_vendor,
            'vendorAktif' => $transaksiStats->vendor_aktif,
            'transaksiTerbaru' => $transaksiTerbaru,
            'barangTerbaru' => $barangTerbaru,
            'statistikJenis' => $statistikJenis,
            'transaksi7Hari' => $transaksi7Hari,
            'topBarang' => $topBarang,
            'totalUser' => $transaksiStats->total_user
        ]);
    }
}
