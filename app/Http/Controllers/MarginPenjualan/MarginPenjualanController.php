<?php

namespace App\Http\Controllers\MarginPenjualan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarginPenjualanController extends Controller
{

    // Menampilkan daftar margin penjualan (menggunakan view yang sudah ada status_text)
    public function index(Request $request)
    {
        $showAll = $request->query('show') === 'all';

        // Gunakan view yang sudah include JOIN ke user dan role
        if ($showAll) {
            $margins = DB::select('SELECT * FROM master_margin_penjualan_view ORDER BY created_at DESC');
        } else {
            $margins = DB::select('SELECT * FROM master_margin_penjualan_active_view ORDER BY created_at DESC');
        }

        // Statistik dalam satu query (lebih efisien)
        $stats = DB::selectOne('
            SELECT
                COUNT(*) AS total,
                SUM(status = 1) AS active,
                SUM(status = 0) AS inactive,
                AVG(CASE WHEN status = 1 THEN persen END) AS avg_active
            FROM margin_penjualan
        ');

        $totalMargin    = (int) ($stats->total ?? 0);
        $marginAktif    = (int) ($stats->active ?? 0);
        $marginNonaktif = (int) ($stats->inactive ?? 0);
        $avgPersen      = (float) ($stats->avg_active ?? 0);

        return view('margin_penjualan.index', compact('margins', 'totalMargin', 'marginAktif', 'marginNonaktif', 'avgPersen'));
    }

    // Fungsi untuk menambah margin penjualan
    public function create()
    {
        // Ambil daftar user menggunakan view
        $users = DB::select('SELECT * FROM master_user_role_view ORDER BY username ASC');

        return view('margin_penjualan.create', compact('users'));
    }

    // Simpan margin penjualan baru
    public function store(Request $request)
    {

        // Validasi input
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|integer|in:0,1',
            'iduser' => 'required|integer|exists:user,iduser'
        ], [
            'persen.required' => 'Persentase margin wajib diisi',
            'persen.numeric' => 'Persentase margin harus berupa angka',
            'persen.min' => 'Persentase margin minimal 0%',
            'persen.max' => 'Persentase margin maksimal 100%',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status hanya boleh 0 (Nonaktif) atau 1 (Aktif)',
            'iduser.required' => 'User wajib dipilih',
            'iduser.exists' => 'User tidak valid'
        ]);

        try {

            // INSERT margin penjualan baru
            DB::transaction(function () use ($validated) {
                DB::insert('INSERT INTO margin_penjualan (persen, status, iduser, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
                    $validated['persen'],
                    $validated['status'],
                    $validated['iduser']
                ]);
            });

            // Redirect dengan pesan sukses
            return redirect()->route('margin_penjualan.index')->with('success', 'Margin penjualan berhasil ditambahkan!');

            // Catch error jika terjadi kegagalan
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan margin penjualan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengedit margin penjualan (GET)
    public function edit(string $id)
    {
        // Ambil data margin penjualan untuk diedit
        $rows = DB::select('SELECT * FROM master_margin_penjualan_view WHERE idmargin_penjualan = ? LIMIT 1', [$id]);

        if (empty($rows)) {
            return redirect()->route('margin_penjualan.index')->with('error', 'Margin penjualan tidak ditemukan!');
        }

        // Ambil daftar user menggunakan view
        $users = DB::select('SELECT * FROM master_user_role_view ORDER BY username ASC');

        return view('margin_penjualan.edit', ['margin' => $rows[0], 'users' => $users]);
    }

    // Fungsi untuk mengirim form update margin penjualan (PUT/PATCH)
    public function update(Request $request, string $id)
    {

        // Validasi input
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|integer|in:0,1',
            'iduser' => 'required|integer|exists:user,iduser'
        ], [
            'persen.required' => 'Persentase margin wajib diisi',
            'persen.numeric' => 'Persentase margin harus berupa angka',
            'persen.min' => 'Persentase margin minimal 0%',
            'persen.max' => 'Persentase margin maksimal 100%',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status hanya boleh 0 (Nonaktif) atau 1 (Aktif)',
            'iduser.required' => 'User wajib dipilih',
            'iduser.exists' => 'User tidak valid'
        ]);

        try {

            // UPDATE margin penjualan
            $affected = DB::update('UPDATE margin_penjualan SET persen = ?, status = ?, iduser = ?, updated_at = NOW() WHERE idmargin_penjualan = ?', [
                $validated['persen'],
                $validated['status'],
                $validated['iduser'],
                $id
            ]);

            // cek hasil update
            if ($affected > 0) {
                return redirect()->route('margin_penjualan.index')->with('success', 'Margin penjualan berhasil diperbarui!');
            } else {
                return redirect()->back()->with('error', 'Tidak ada perubahan atau margin penjualan tidak ditemukan!');
            }

            // Tangani error jika terjadi kegagalan
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui margin penjualan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menghapus (soft delete) margin penjualan
    public function destroy(string $id)
    {
        try {

            // Cek apakah margin penjualan ada
            $exists = DB::select('SELECT idmargin_penjualan FROM margin_penjualan WHERE idmargin_penjualan = ? LIMIT 1', [$id]);

            // Redirect jika tidak ditemukan
            if (empty($exists)) {
                return redirect()->route('margin_penjualan.index')->with('error', 'Margin penjualan tidak ditemukan!');
            }

            // Soft delete: set status to 0
            DB::update('UPDATE margin_penjualan SET status = 0, updated_at = NOW() WHERE idmargin_penjualan = ?', [$id]);
            return redirect()->route('margin_penjualan.index')->with('success', 'Margin penjualan berhasil dinonaktifkan!');
        } catch (\Exception $e) {
            return redirect()->route('margin_penjualan.index')->with('error', 'Gagal menonaktifkan margin penjualan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengaktifkan/mematikan margin penjualan
    public function toggleStatus(string $id)
    {
        try {
            $currentStatus = DB::select('SELECT status FROM margin_penjualan WHERE idmargin_penjualan = ?', [$id]);
            if (empty($currentStatus)) {
                return redirect()->route('margin_penjualan.index')->with('error', 'Margin penjualan tidak ditemukan!');
            }

            $newStatus = $currentStatus[0]->status == 1 ? 0 : 1;
            $statusText = $newStatus == 1 ? 'Aktif' : 'Nonaktif';

            DB::update('UPDATE margin_penjualan SET status = ?, updated_at = NOW() WHERE idmargin_penjualan = ?', [$newStatus, $id]);
            return redirect()->route('margin_penjualan.index')->with('success', "Status margin penjualan berhasil diubah menjadi {$statusText}!");
        } catch (\Exception $e) {
            return redirect()->route('margin_penjualan.index')->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
}
