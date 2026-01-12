<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameExport;
use App\Exports\PenjualanExport;
use App\Exports\PengadaanExport;
use App\Exports\PenerimaanExport;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * atau sp_kartu_stok_semua_barang(start_date, end_date) untuk semua barang
     */
    public function kartuStok(Request $request)
    {
        $idbarang = $request->input('idbarang');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $viewAll = $request->input('view_all', false); // Opsi lihat semua barang

        // Get list barang untuk dropdown
        $barangList = DB::select('SELECT idbarang, nama FROM barang WHERE status = 1 ORDER BY nama');

        $kartuStok = [];
        $namaBarang = '';

        try {
            if ($viewAll || $idbarang === 'all') {
                // Tampilkan kartu stok semua barang
                $kartuStok = collect(DB::select('CALL sp_kartu_stok_semua_barang(?, ?)', [$startDate, $endDate]));
                $namaBarang = 'Semua Barang';
            } elseif ($idbarang) {
                // Tampilkan kartu stok barang tertentu
                $kartuStok = collect(DB::select('CALL sp_filter_kartu_stok(?, ?, ?)', [$idbarang, $startDate, $endDate]));

                // Get nama barang
                $barang = collect($barangList)->firstWhere('idbarang', $idbarang);
                $namaBarang = $barang ? $barang->nama : '';
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat kartu stok: ' . $e->getMessage());
        }

        return view('laporan.kartu_stok', compact('barangList', 'kartuStok', 'namaBarang', 'idbarang', 'startDate', 'endDate', 'viewAll'));
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

    /**
     * Laporan Penerimaan (Periode)
     * Menggunakan SP: sp_report_penerimaan_periode(start_date, end_date, limit, offset)
     */
    public function penerimaan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $laporan = [];
        try {
            // CALL sp_report_penerimaan_periode(start_date, end_date, limit, offset)
            $laporan = collect(DB::select('CALL sp_report_penerimaan_periode(?, ?, ?, ?)',
                [$startDate, $endDate, 500, 0]));

            // Map total_barang and total_nilai with fallbacks
            $laporan = $laporan->map(function ($row) {
                // Pastikan total_barang dan total_nilai selalu ada
                $row->total_barang = $row->total_qty ?? $row->jumlah_item ?? 0;
                $row->total_nilai = $row->total_nilai ?? 0;
                return $row;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat laporan penerimaan: ' . $e->getMessage());
        }

        return view('laporan.penerimaan', compact('laporan', 'startDate', 'endDate'));
    }

    /**
     * Export Stock Opname ke Excel
     */
    public function exportStockOpname(Request $request)
    {
        $search = $request->input('search');
        $jenis = $request->input('jenis');

        return Excel::download(
            new StockOpnameExport($search, $jenis),
            'stock-opname-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export Stock Opname ke PDF
     */
    public function exportStockOpnamePdf()
    {
        try {
            $stockOpname = DB::select('CALL sp_report_stock_opname()');

            $pdf = Pdf::loadView('laporan.pdf.stock-opname', compact('stockOpname'))
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Laporan-Stock-Opname-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Stock Rendah ke PDF
     */
    public function exportStockRendahPdf(Request $request)
    {
        $threshold = $request->input('threshold', 10);

        try {
            $stockRendah = DB::select('CALL sp_filter_barang_stock_rendah(?)', [$threshold]);

            $pdf = Pdf::loadView('laporan.pdf.stock-rendah', compact('stockRendah', 'threshold'))
                ->setPaper('a4', 'portrait');

            return $pdf->stream('Laporan-Stock-Rendah-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Kartu Stok ke PDF
     */
    public function exportKartuStokPdf(Request $request)
    {
        $idbarang = $request->input('idbarang');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $viewAll = $request->input('view_all', false);

        $barangList = DB::select('SELECT idbarang, nama FROM barang WHERE status = 1 ORDER BY nama');
        $kartuStok = [];
        $namaBarang = '';

        try {
            if ($viewAll || $idbarang === 'all') {
                $kartuStok = collect(DB::select('CALL sp_kartu_stok_semua_barang(?, ?)', [$startDate, $endDate]));
                $namaBarang = 'Semua Barang';
            } elseif ($idbarang) {
                $kartuStok = collect(DB::select('CALL sp_filter_kartu_stok(?, ?, ?)', [$idbarang, $startDate, $endDate]));
                $barang = collect($barangList)->firstWhere('idbarang', $idbarang);
                $namaBarang = $barang ? $barang->nama : '';
            }

            $pdf = Pdf::loadView('laporan.pdf.kartu-stok', compact('kartuStok', 'namaBarang', 'idbarang', 'startDate', 'endDate', 'viewAll'))
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Kartu-Stok-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Laporan Penjualan ke Excel
     */
    public function exportPenjualan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        return Excel::download(
            new PenjualanExport($startDate, $endDate),
            'laporan-penjualan-' . $startDate . '-' . $endDate . '.xlsx'
        );
    }

    /**
     * Export Laporan Penjualan ke PDF
     */
    public function exportPenjualanPdf(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        try {
            $laporan = collect(DB::select('CALL sp_report_penjualan_periode(?, ?, ?, ?)',
                [$startDate, $endDate, 500, 0]));

            $pdf = Pdf::loadView('laporan.pdf.penjualan', compact('laporan', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Laporan-Penjualan-' . $startDate . '-' . $endDate . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Laporan Pengadaan ke Excel
     */
    public function exportPengadaan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        return Excel::download(
            new PengadaanExport($startDate, $endDate),
            'laporan-pengadaan-' . $startDate . '-' . $endDate . '.xlsx'
        );
    }

    /**
     * Export Laporan Pengadaan ke PDF
     */
    public function exportPengadaanPdf(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        try {
            $laporan = collect(DB::select('CALL sp_report_pengadaan_periode(?, ?, ?, ?)',
                [$startDate, $endDate, 500, 0]));

            $pdf = Pdf::loadView('laporan.pdf.pengadaan', compact('laporan', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Laporan-Pengadaan-' . $startDate . '-' . $endDate . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Laporan Penerimaan ke Excel
     */
    public function exportPenerimaan(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        return Excel::download(
            new PenerimaanExport($startDate, $endDate),
            'laporan-penerimaan-' . $startDate . '-' . $endDate . '.xlsx'
        );
    }

    /**
     * Export Laporan Penerimaan ke PDF
     */
    public function exportPenerimaanPdf(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        try {
            $laporan = collect(DB::select('CALL sp_report_penerimaan_periode(?, ?, ?, ?)',
                [$startDate, $endDate, 500, 0]));

            $laporan = $laporan->map(function ($row) {
                $row->total_barang = $row->total_qty ?? $row->jumlah_item ?? 0;
                $row->total_nilai = $row->total_nilai ?? 0;
                return $row;
            });

            $pdf = Pdf::loadView('laporan.pdf.penerimaan', compact('laporan', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Laporan-Penerimaan-' . $startDate . '-' . $endDate . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
}
