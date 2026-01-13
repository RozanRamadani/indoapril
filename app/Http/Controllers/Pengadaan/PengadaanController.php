<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class PengadaanController extends Controller
{

    // Menampilkan daftar pengadaan
    public function index()
    {
        // Build a normalized status selection as 'draft', 'progress', or 'completed'
        $hasStatus = Schema::hasColumn('pengadaan', 'status_pengadaan');
        if ($hasStatus) {
            // Jika kolom status_pengadaan ada, gunakan itu untuk mapping
            // P=draft, A=progress (approved), C=completed
            $statusSelect = "(CASE WHEN p.status_pengadaan IS NOT NULL AND p.status_pengadaan <> '' THEN p.status_pengadaan WHEN p.status = 'C' THEN 'completed' WHEN p.status = 'A' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        } else {
            // Jika kolom status_pengadaan tidak ada, gunakan kolom status legacy
            // P=draft, A=progress (approved), C=completed
            $statusSelect = "(CASE WHEN p.status = 'C' THEN 'completed' WHEN p.status = 'A' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        }
        // Menhindari error jika kolom jumlah_diterima tidak ada
        $hasJumlahDiterima = Schema::hasColumn('detail_pengadaan', 'jumlah_diterima');
        $totalDiterimaSelect = $hasJumlahDiterima ? "COALESCE(dp.jumlah_diterima, 0)" : "0";

        // Query dengan Query Builder dan pagination
        $pengadaan = DB::table('pengadaan as p')
            ->join('vendor as v', 'p.vendor_idvendor', '=', 'v.idvendor')
            ->leftJoin('user as u', 'p.user_iduser', '=', 'u.iduser')
            ->leftJoin('detail_pengadaan as dp', 'p.idpengadaan', '=', 'dp.idpengadaan')
            ->select(
                'p.idpengadaan',
                'p.created_at',
                'p.vendor_idvendor as idvendor',
                'v.nama_vendor',
                'p.subtotal_nilai',
                'p.ppn',
                'p.total_nilai',
                DB::raw($statusSelect),
                'u.username',
                DB::raw('COUNT(dp.iddetail_pengadaan) AS total_item'),
                DB::raw('SUM(dp.jumlah) AS total_jumlah'),
                DB::raw("SUM({$totalDiterimaSelect}) AS total_diterima")
            )
            ->groupBy('p.idpengadaan', 'p.created_at', 'p.vendor_idvendor', 'v.nama_vendor',
                     'p.subtotal_nilai', 'p.ppn', 'p.total_nilai', 'p.status', 'u.username')
            ->orderBy('p.created_at', 'DESC')
            ->paginate(15)
            ->withQueryString();

        return view('pengadaan.index', compact('pengadaan'));
    }

    /**
     * Show the form for creating a new pengadaan (pilih vendor saja)
     */
    public function create()
    {
        // Get vendor dropdown (hanya vendor aktif). Guard for different schema names.
        $hasStatusVendor = Schema::hasColumn('vendor', 'status_vendor');
        $hasStatus = Schema::hasColumn('vendor', 'status');

        if ($hasStatusVendor) {
            $where = "WHERE status_vendor = 1";
        } elseif ($hasStatus) {
            // vendor.status may be 'Y'/'N' or '1'/0; accept common active values
            $where = "WHERE status IN ('1','Y')";
        } else {
            $where = "";
        }

        $sql = "SELECT idvendor, nama_vendor FROM vendor " . $where . " ORDER BY nama_vendor";
        $vendors = DB::select($sql);

        return view('pengadaan.create', compact('vendors'));
    }

    /**
     * Store a newly created pengadaan (save vendor, redirect ke detail/keranjang)
     */
    public function store(Request $request)
    {
        $request->validate([
            'idvendor' => 'required|exists:vendor,idvendor',
        ]);

        // Jka user tidak login, gunakan user id 1
        // so the feature can be used during development without auth.
        $iduser = Auth::id() ?? 1;

        // Insert pengadaan dengan status draft
        $hasStatusPengadaan = Schema::hasColumn('pengadaan', 'status_pengadaan');

        if ($hasStatusPengadaan) {
            // Run single INSERT statement; do not combine with SELECT in same call.
            DB::insert(
                "INSERT INTO pengadaan
                (created_at, subtotal_nilai, ppn, total_nilai, vendor_idvendor, user_iduser, status, status_pengadaan)
                VALUES (NOW(), 0, 0, 0, ?, ?, 'P', 'draft')",
                [$request->idvendor, $iduser]
            );
        } else {
            DB::insert(
                "INSERT INTO pengadaan
                (created_at, subtotal_nilai, ppn, total_nilai, vendor_idvendor, user_iduser, status)
                VALUES (NOW(), 0, 0, 0, ?, ?, 'P')",
                [$request->idvendor, $iduser]
            );
        }

        // Retrieve the last insert id via PDO (safe single-statement method)
        $idpengadaan = (int) DB::getPdo()->lastInsertId();

        return redirect()->route('pengadaan.detail', $idpengadaan)
            ->with('success', 'Pengadaan berhasil dibuat. Silakan tambah barang.');
    }

    /**
     * Show detail pengadaan (keranjang barang)
     */
    public function detail($id)
    {
        // Get pengadaan info with normalized status (draft, progress, completed)
        $hasStatus = Schema::hasColumn('pengadaan', 'status_pengadaan');
        if ($hasStatus) {
            $statusSelect = "(CASE WHEN p.status_pengadaan IS NOT NULL AND p.status_pengadaan <> '' THEN p.status_pengadaan WHEN p.status = 'A' THEN 'completed' WHEN p.status = 'progress' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        } else {
            $statusSelect = "(CASE WHEN p.status = 'A' THEN 'completed' WHEN p.status = 'progress' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        }

        $sql =
            "SELECT\n" .
            "    p.*,\n" .
            "    v.nama_vendor,\n" .
            "    u.username,\n" .
            "    " . $statusSelect . "\n" .
            "FROM pengadaan p\n" .
            "INNER JOIN vendor v ON p.vendor_idvendor = v.idvendor\n" .
            "LEFT JOIN user u ON p.user_iduser = u.iduser\n" .
            "WHERE p.idpengadaan = ?";

        $pengadaan = DB::selectOne($sql, [$id]);
        // Cek apakah sudah completed atau progress (tidak bisa edit lagi)
        if ($this->isPengadaanFinalized($pengadaan) || $this->isPengadaanProgress($pengadaan)) {
            return redirect()->route('pengadaan.show', $id)
                ->with('info', 'Pengadaan sudah di-finalisasi atau dalam progress. Lihat detail read-only.');
        }

        // Get detail pengadaan (keranjang)
            $orderCol = Schema::hasColumn('detail_pengadaan', 'created_at') ? 'dp.created_at' : 'dp.iddetail_pengadaan';
            $details = DB::select("
                SELECT
                    dp.*,
                    b.nama AS nama_barang,
                    b.jenis,
                    s.nama_satuan
                FROM detail_pengadaan dp
                INNER JOIN barang b ON dp.idbarang = b.idbarang
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                WHERE dp.idpengadaan = ?
                ORDER BY " . $orderCol . " DESC
            ", [$id]);

        // Get barang dropdown (barang aktif)
        $hasHargaJual = Schema::hasColumn('barang', 'harga_jual');
        $hasHarga = Schema::hasColumn('barang', 'harga');
        if ($hasHargaJual) {
            $hargaSelect = "b.harga_jual as harga_jual";
        } elseif ($hasHarga) {
            $hargaSelect = "b.harga as harga_jual";
        } else {
            $hargaSelect = "0 as harga_jual";
        }

        $barangs = DB::select(
            "SELECT\n" .
            "    b.idbarang,\n" .
            "    b.nama,\n" .
            "    b.jenis,\n" .
            "    s.nama_satuan,\n" .
            "    " . $hargaSelect . "\n" .
            "FROM barang b\n" .
            "LEFT JOIN satuan s ON b.idsatuan = s.idsatuan\n" .
            "WHERE b.status = 1\n" .
            "ORDER BY b.nama\n"
        );

        return view('pengadaan.detail', compact('pengadaan', 'details', 'barangs'));
    }

    /**
     * Show detail pengadaan (read-only untuk completed)
     */
    public function show($id)
    {
        // Get pengadaan info with normalized status
        $hasStatus = Schema::hasColumn('pengadaan', 'status_pengadaan');
        if ($hasStatus) {
            $statusSelect = "(CASE WHEN p.status_pengadaan IS NOT NULL AND p.status_pengadaan <> '' THEN p.status_pengadaan WHEN p.status = 'A' THEN 'completed' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        } else {
            $statusSelect = "(CASE WHEN p.status = 'A' THEN 'completed' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        }

        // Get pengadaan info
        $sql =
            "SELECT\n" .
            "    p.*,\n" .
            "    v.nama_vendor,\n" .
            "    u.username,\n" .
            "    " . $statusSelect . "\n" .
            "FROM pengadaan p\n" .
            "INNER JOIN vendor v ON p.vendor_idvendor = v.idvendor\n" .
            "LEFT JOIN user u ON p.user_iduser = u.iduser\n" .
            "WHERE p.idpengadaan = ?";

        $pengadaan = DB::selectOne($sql, [$id]);
        // Get detail pengadaan
            $orderCol = Schema::hasColumn('detail_pengadaan', 'created_at') ? 'dp.created_at' : 'dp.iddetail_pengadaan';
            $details = DB::select("
                SELECT
                    dp.*,
                    b.nama AS nama_barang,
                    b.jenis,
                    s.nama_satuan
                FROM detail_pengadaan dp
                INNER JOIN barang b ON dp.idbarang = b.idbarang
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                WHERE dp.idpengadaan = ?
                ORDER BY " . $orderCol . " DESC
            ", [$id]);

        return view('pengadaan.show', compact('pengadaan', 'details'));
    }

    /**
     * Add item to detail pengadaan (keranjang)
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'idbarang' => 'required|exists:barang,idbarang',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Cek apakah pengadaan masih draft
        if (Schema::hasColumn('pengadaan', 'status_pengadaan')) {
            $status = DB::selectOne("SELECT status_pengadaan as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        } else {
            $status = DB::selectOne("SELECT status as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        }
        if (!$status || $this->isPengadaanFinalized($status)) {
            return back()->with('error', 'Pengadaan sudah di-finalisasi');
        }

        // Cek apakah barang sudah ada di keranjang
        $exists = DB::selectOne("
            SELECT iddetail_pengadaan
            FROM detail_pengadaan
            WHERE idpengadaan = ? AND idbarang = ?
        ", [$id, $request->idbarang]);

        if ($exists) {
            return back()->with('error', 'Barang sudah ada di keranjang. Silakan edit jumlahnya.');
        }

        // Calculate sub_total menggunakan FUNCTION fn_calc_subtotal
        $sub_total_result = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as sub_total', [
            $request->jumlah,
            $request->harga_satuan
        ]);
        $sub_total = $sub_total_result->sub_total;

        $created_at = Schema::hasColumn('detail_pengadaan', 'created_at') ? ', created_at' : '';
        $created_val = Schema::hasColumn('detail_pengadaan', 'created_at') ? ', NOW()' : '';
        $jumlah_diterima = Schema::hasColumn('detail_pengadaan', 'jumlah_diterima') ? ', jumlah_diterima' : '';
        $jumlah_val = Schema::hasColumn('detail_pengadaan', 'jumlah_diterima') ? ', 0' : '';

        DB::statement("
            INSERT INTO detail_pengadaan
            (jumlah, harga_satuan, sub_total, idpengadaan, idbarang{$created_at}{$jumlah_diterima})
            VALUES (?, ?, ?, ?, ?{$created_val}{$jumlah_val})
        ", [$request->jumlah, $request->harga_satuan, $sub_total, $id, $request->idbarang]);

        // TRIGGER trg_after_insert_detail_pengadaan akan otomatis update totals!
        // FALLBACK: Panggil manual untuk double-safety
        $this->updatePengadaanTotals($id);

        return back()->with('success', 'Barang berhasil ditambahkan ke keranjang');
    }

    /**
     * Update item in detail pengadaan
     */
    public function updateItem(Request $request, $id, $detailId)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        // Cek apakah pengadaan masih draft
        if (Schema::hasColumn('pengadaan', 'status_pengadaan')) {
            $status = DB::selectOne("SELECT status_pengadaan as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        } else {
            $status = DB::selectOne("SELECT status as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        }
        if (!$status || $this->isPengadaanFinalized($status)) {
            return back()->with('error', 'Pengadaan sudah di-finalisasi');
        }

        // Calculate sub_total menggunakan FUNCTION fn_calc_subtotal
        $sub_total_result = DB::selectOne('SELECT fn_calc_subtotal(?, ?) as sub_total', [
            $request->jumlah,
            $request->harga_satuan
        ]);
        $sub_total = $sub_total_result->sub_total;

        // Update detail menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            UPDATE detail_pengadaan
            SET jumlah = ?, harga_satuan = ?, sub_total = ?
            WHERE iddetail_pengadaan = ? AND idpengadaan = ?
        ", [$request->jumlah, $request->harga_satuan, $sub_total, $detailId, $id]);

        // TRIGGER trg_after_update_detail_pengadaan akan otomatis update totals!
        // FALLBACK: Panggil manual untuk double-safety
        $this->updatePengadaanTotals($id);

        return back()->with('success', 'Item berhasil diupdate');
    }

    /**
     * Delete item from detail pengadaan
     */
    public function deleteItem($id, $detailId)
    {
        // Cek apakah pengadaan masih draft
        if (Schema::hasColumn('pengadaan', 'status_pengadaan')) {
            $status = DB::selectOne("SELECT status_pengadaan as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        } else {
            $status = DB::selectOne("SELECT status as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        }
        if (!$status || $this->isPengadaanFinalized($status)) {
            return back()->with('error', 'Pengadaan sudah di-finalisasi');
        }

        // Delete detail menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            DELETE FROM detail_pengadaan
            WHERE iddetail_pengadaan = ? AND idpengadaan = ?
        ", [$detailId, $id]);

        // TRIGGER trg_after_delete_detail_pengadaan akan otomatis update totals
        // FALLBACK: Panggil manual untuk double-safety
        $this->updatePengadaanTotals($id);

        return back()->with('success', 'Item berhasil dihapus');
    }

    /**
     * Finalize pengadaan (set status = progress)
     * NEW FLOW: Draft → Progress (siap dikirim ke vendor & terima barang)
     * Progress → Completed (otomatis saat semua qty diterima)
     */
    public function finalize($id)
    {
        // Cek apakah pengadaan masih draft
        if (Schema::hasColumn('pengadaan', 'status_pengadaan')) {
            $pengadaan = DB::selectOne("SELECT status_pengadaan as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        } else {
            $pengadaan = DB::selectOne("SELECT status as status_pengadaan FROM pengadaan WHERE idpengadaan = ?", [$id]);
        }
        if (!$pengadaan) {
            return back()->with('error', 'Pengadaan tidak ditemukan');
        }

        if ($this->isPengadaanFinalized($pengadaan) || $this->isPengadaanProgress($pengadaan)) {
            return back()->with('error', 'Pengadaan sudah di-finalisasi atau dalam progress');
        }

        // Cek apakah ada detail
        $count = DB::selectOne("SELECT COUNT(*) as total FROM detail_pengadaan WHERE idpengadaan = ?", [$id]);
        if ($count->total == 0) {
            return back()->with('error', 'Tidak ada barang di keranjang. Tambahkan minimal 1 barang.');
        }

        // Update status pengadaan ke 'progress' (bukan 'completed')
        // Progress = PO sudah disetujui dan dikirim ke vendor, siap menerima barang
        // NOTE: legacy schema may only have a short `status` column (char(1) or enum)
        // which cannot hold the full text 'progress'. To avoid "Data too long"
        // errors we only set the textual `status_pengadaan` if the column exists.
        // If `status_pengadaan` is not present, use legacy status 'A' (approved/progress).
        if (Schema::hasColumn('pengadaan', 'status_pengadaan')) {
            DB::statement("
                UPDATE pengadaan
                SET status_pengadaan = 'progress'
                WHERE idpengadaan = ?
            ", [$id]);
        } else {
            // Legacy schema: set status to 'A' (approved/in progress)
            // Map: 'P' = draft, 'A' = approved/progress (can receive goods)
            DB::statement("
                UPDATE pengadaan
                SET status = 'A'
                WHERE idpengadaan = ?
            ", [$id]);
        }

        return redirect()->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil di-finalisasi dan siap untuk penerimaan barang (status: Progress)');
    }

    /**
     * Helper: Update pengadaan totals (subtotal + PPN 11% + total)
     * NOTE: Method ini sekarang OPTIONAL karena sudah ada TRIGGER otomatis!
     * Trigger: trg_after_insert/update/delete_detail_pengadaan
     * Trigger akan otomatis update subtotal_nilai, ppn, dan total_nilai
     */
    private function updatePengadaanTotals($idpengadaan)
    {
        // DEPRECATED: Trigger sudah handle ini otomatis
        // Keeping this method untuk backward compatibility jika trigger disabled

        // Gunakan FUNCTION fn_pengadaan_total untuk get subtotal
        $result = DB::selectOne('SELECT fn_pengadaan_total(?) as subtotal', [$idpengadaan]);
        $subtotal = $result->subtotal;

        // Gunakan FUNCTION fn_calc_ppn untuk hitung PPN
        $ppn_result = DB::selectOne('SELECT fn_calc_ppn(?) as ppn', [$subtotal]);
        $ppn = $ppn_result->ppn;

        // Gunakan FUNCTION fn_calc_total untuk hitung total
        $total_result = DB::selectOne('SELECT fn_calc_total(?, ?) as total', [$subtotal, $ppn]);
        $total = $total_result->total;

        // Update pengadaan menggunakan RAW SQL (NO Query Builder!)
        DB::statement("
            UPDATE pengadaan
            SET subtotal_nilai = ?, ppn = ?, total_nilai = ?
            WHERE idpengadaan = ?
        ", [$subtotal, $ppn, $total, $idpengadaan]);
    }

    /**
     * Normalize status object/row to determine if pengadaan is finalized.
     * Accepts objects returned from select queries that may have either
     * - status_pengadaan (text 'draft'/'progress'/'completed') or
     * - status (legacy 'P'/'progress'/'A')
     */
    private function isPengadaanFinalized($row)
    {
        if (!$row) return false;
        // Prefer normalized alias if present
        if (isset($row->status_pengadaan) && $row->status_pengadaan !== null) {
            return strtolower($row->status_pengadaan) === 'completed';
        }
        if (isset($row->status) && $row->status !== null) {
            return strtoupper($row->status) === 'C';
        }
        return false;
    }

    /**
     * Check if pengadaan is in progress status
     */
    private function isPengadaanProgress($row)
    {
        if (!$row) return false;
        if (isset($row->status_pengadaan) && $row->status_pengadaan !== null) {
            return strtolower($row->status_pengadaan) === 'progress';
        }
        if (isset($row->status) && $row->status !== null) {
            return strtolower($row->status) === 'progress';
        }
        return false;
    }

    /**
     * Delete pengadaan (hanya jika status = 'P' / Draft)
     */
    public function destroy($id)
    {
        // Cek status pengadaan
        $pengadaan = DB::selectOne("SELECT status FROM pengadaan WHERE idpengadaan = ?", [$id]);

        if (!$pengadaan) {
            return redirect()->route('pengadaan.index')
                ->with('error', 'Pengadaan tidak ditemukan');
        }

        // Hanya izinkan hapus jika status = 'P' (Draft)
        if ($pengadaan->status !== 'P') {
            return redirect()->route('pengadaan.show', $id)
                ->with('error', 'Tidak dapat menghapus pengadaan yang sudah diproses (status bukan Draft)');
        }

        DB::beginTransaction();
        try {
            // Hapus detail pengadaan (cascade)
            DB::statement("DELETE FROM detail_pengadaan WHERE idpengadaan = ?", [$id]);

            // Hapus pengadaan
            DB::statement("DELETE FROM pengadaan WHERE idpengadaan = ?", [$id]);

            DB::commit();

            return redirect()->route('pengadaan.index')
                ->with('success', 'Pengadaan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pengadaan.show', $id)
                ->with('error', 'Gagal menghapus pengadaan: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF Purchase Order
     */
    public function printPO($id)
    {
        // Get pengadaan info
        $hasStatus = Schema::hasColumn('pengadaan', 'status_pengadaan');

        if ($hasStatus) {
            $statusSelect = "(CASE WHEN p.status_pengadaan IS NOT NULL AND p.status_pengadaan <> '' THEN p.status_pengadaan WHEN p.status = 'C' THEN 'completed' WHEN p.status = 'A' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        } else {
            $statusSelect = "(CASE WHEN p.status = 'C' THEN 'completed' WHEN p.status = 'A' THEN 'progress' WHEN p.status = 'P' THEN 'draft' ELSE p.status END) as status_pengadaan";
        }

        // Check kolom vendor yang tersedia
        $hasAlamat = Schema::hasColumn('vendor', 'alamat');
        $hasTelp = Schema::hasColumn('vendor', 'telp');

        $vendorColumns = "v.nama_vendor";
        if ($hasAlamat) {
            $vendorColumns .= ", v.alamat";
        }
        if ($hasTelp) {
            $vendorColumns .= ", v.telp";
        }

        $sql =
            "SELECT\n" .
            "    p.*,\n" .
            "    " . $vendorColumns . ",\n" .
            "    u.username,\n" .
            "    " . $statusSelect . "\n" .
            "FROM pengadaan p\n" .
            "INNER JOIN vendor v ON p.vendor_idvendor = v.idvendor\n" .
            "LEFT JOIN user u ON p.user_iduser = u.iduser\n" .
            "WHERE p.idpengadaan = ?";

        $pengadaan = DB::selectOne($sql, [$id]);

        if (!$pengadaan) {
            return redirect()->route('pengadaan.index')
                ->with('error', 'Pengadaan tidak ditemukan');
        }

        // Get detail pengadaan
        $orderCol = Schema::hasColumn('detail_pengadaan', 'created_at') ? 'dp.created_at' : 'dp.iddetail_pengadaan';
        $details = DB::select("
            SELECT
                dp.*,
                b.nama AS nama_barang,
                b.jenis,
                s.nama_satuan
            FROM detail_pengadaan dp
            INNER JOIN barang b ON dp.idbarang = b.idbarang
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.idpengadaan = ?
            ORDER BY " . $orderCol . " DESC
        ", [$id]);

        $pdf = Pdf::loadView('pengadaan.purchase-order', compact('pengadaan', 'details'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('PO-' . $pengadaan->idpengadaan . '.pdf');
    }
}
