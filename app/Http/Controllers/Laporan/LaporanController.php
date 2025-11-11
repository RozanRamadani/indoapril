<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Laporan Stock Opname
     * Menggunakan SP: sp_report_stock_opname()
     */
    public function stockOpname()
    {
        try {
            // CALL SP READ-ONLY untuk stock opname
            $stockOpname = DB::select('CALL sp_report_stock_opname()');

            return view('laporan.stock_opname', compact('stockOpname'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat laporan stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Stock Rendah
     * Menggunakan SP: sp_filter_barang_stock_rendah(threshold)
     */
    public function stockRendah(Request $request)
    {
        $threshold = $request->input('threshold', 10); // Default threshold: 10

        try {
            // CALL SP READ-ONLY untuk filter stock rendah
            $stockRendah = DB::select('CALL sp_filter_barang_stock_rendah(?)', [$threshold]);

            return view('laporan.stock_rendah', compact('stockRendah', 'threshold'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat laporan stock rendah: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Kartu Stok
     * Menggunakan SP: sp_filter_kartu_stok(idbarang, start_date, end_date)
     */
    public function kartuStok(Request $request)
    {
        $idbarang = $request->input('idbarang');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // Get list barang untuk dropdown
        $barangList = DB::select('SELECT idbarang, nama FROM barang WHERE status = 1 ORDER BY nama');

        $kartuStok = [];
        $namaBarang = '';

        if ($idbarang) {
            try {
                // CALL SP READ-ONLY untuk kartu stok
                $kartuStok = collect(DB::select('CALL sp_filter_kartu_stok(?, ?, ?)', [$idbarang, $startDate, $endDate]));

                // Get nama barang
                $barang = collect($barangList)->firstWhere('idbarang', $idbarang);
                $namaBarang = $barang ? $barang->nama : '';
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal memuat kartu stok: ' . $e->getMessage());
            }
        }

        return view('laporan.kartu_stok', compact('barangList', 'kartuStok', 'namaBarang'));
    }

    /**
     * Laporan Penjualan (Periode/Mingguan/Bulanan/Tahunan)
     */
    public function penjualan(Request $request)
    {
        $type = $request->input('type', 'tahunan'); // Default: tahunan (overview bulan-bulan dalam setahun)
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $laporan = [];
        try {
            switch ($type) {
                case 'periode':
                    // CALL sp_report_penjualan_periode(start_date, end_date, limit, offset)
                    // Ensure limit > 0; previously passing limit=0 produced empty results
                    $laporan = DB::select('CALL sp_report_penjualan_periode(?, ?, ?, ?)',
                        [$startDate, $endDate, 500, 0]);
                    break;

                case 'mingguan':
                    // CALL sp_report_penjualan_mingguan(tahun, bulan)
                    $laporan = DB::select('CALL sp_report_penjualan_mingguan(?, ?)',
                        [$tahun, $bulan]);
                    break;

                case 'bulanan':
                    // CALL sp_report_penjualan_bulanan(tahun, bulan)
                    $laporan = DB::select('CALL sp_report_penjualan_bulanan(?, ?)',
                        [$tahun, $bulan]);
                    break;

                case 'tahunan':
                    // CALL sp_report_penjualan_tahunan(tahun)
                    $laporan = DB::select('CALL sp_report_penjualan_tahunan(?)',
                        [$tahun]);
                    break;
            }

            $laporan = collect($laporan);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat laporan penjualan: ' . $e->getMessage());
        }

        return view('laporan.penjualan', compact('laporan', 'type'));
    }

    /**
     * Laporan Pengadaan (Periode)
     */
    public function pengadaan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $laporan = [];
        try {
            // CALL sp_report_pengadaan_periode(start_date, end_date, limit, offset)
            // Use a sensible default limit (500) and offset 0 to avoid LIMIT 0 returning no rows
            $laporan = collect(DB::select('CALL sp_report_pengadaan_periode(?, ?, ?, ?)',
                [$startDate, $endDate, 500, 0]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat laporan pengadaan: ' . $e->getMessage());
        }

        return view('laporan.pengadaan', compact('laporan', 'startDate', 'endDate'));
    }
}
