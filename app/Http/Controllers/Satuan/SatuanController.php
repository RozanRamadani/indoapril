<?php

namespace App\Http\Controllers\Satuan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{

    // Menampilkan daftar satuan
    public function index(Request $request)
    {
        // Gunakan view untuk filter status
        if ($request->query('show') === 'all') {
            $satuans = DB::select('SELECT * FROM master_satuan_view ORDER BY nama_satuan');
        } else {
            $satuans = DB::select('SELECT * FROM master_satuan_active_view ORDER BY nama_satuan');
        }

        // Ringkas counts menggunakan single aggregate query
        $stats = DB::selectOne('
            SELECT
                COUNT(*) as total_satuan,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as satuan_aktif,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as satuan_nonaktif
            FROM satuan
        ');

        return view('satuan.index', [
            'satuans' => $satuans,
            'totalSatuan' => $stats->total_satuan,
            'satuanAktif' => $stats->satuan_aktif,
            'satuanNonaktif' => $stats->satuan_nonaktif
        ]);
    }

    // Formulir tambah satuan
    public function create()
    {
        return view('satuan.create');
    }

    // Simpan satuan baru
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:45|unique:satuan,nama_satuan',
            'status' => 'required|integer|in:0,1'
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi',
            'nama_satuan.unique' => 'Nama satuan sudah ada dalam database',
            'nama_satuan.max' => 'Nama satuan maksimal 45 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status hanya boleh 0 (Nonaktif) atau 1 (Aktif)'
        ]);

        try {
            // INSERT satuan baru
            DB::insert("
                INSERT INTO satuan (nama_satuan, status)
                VALUES (?, ?)
            ", [
                $validated['nama_satuan'],
                $validated['status']
            ]);

            // Redirect dengan pesan sukses
            return redirect()->route('satuan.index')
                ->with('success', 'Satuan berhasil ditambahkan!');

            // Tangani error jika terjadi
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan satuan: ' . $e->getMessage());
        }
    }

    // Formulir edit satuan
    public function edit(string $id)
    {
        // Ambil data satuan untuk diedit
        $rows = DB::select('SELECT idsatuan, nama_satuan, status FROM master_satuan_view WHERE idsatuan = ? LIMIT 1', [$id]);
        if (empty($rows)) {
            return redirect()->route('satuan.index')->with('error', 'Satuan tidak ditemukan!');
        }

        // Tampilkan view edit dengan data satuan
        return view('satuan.edit', ['satuan' => $rows[0]]);
    }

    // Update satuan
    public function update(Request $request, string $id)
    {
        // Validasi input (unique kecuali untuk ID yang sedang diedit)
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:45|unique:satuan,nama_satuan,' . $id . ',idsatuan',
            'status' => 'required|integer|in:0,1'
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi',
            'nama_satuan.unique' => 'Nama satuan sudah ada dalam database',
            'nama_satuan.max' => 'Nama satuan maksimal 45 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status hanya boleh 0 (Nonaktif) atau 1 (Aktif)'
        ]);

        try {
            // Update satuan
            $affected = DB::update("
                UPDATE satuan
                SET nama_satuan = ?,
                    status = ?
                WHERE idsatuan = ?
            ", [
                $validated['nama_satuan'],
                $validated['status'],
                $id
            ]);

            // cek hasil update
            if ($affected > 0) {
                return redirect()->route('satuan.index')
                    ->with('success', 'Satuan berhasil diperbarui!');
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada perubahan atau satuan tidak ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui satuan: ' . $e->getMessage());
        }
    }

    // Soft delete satuan (set status ke 0)
    public function destroy(string $id)
    {
        try {
            // Cek apakah satuan ada
            $satuan = DB::select("
                SELECT idsatuan, nama_satuan, status
                FROM satuan
                WHERE idsatuan = ?
            ", [$id]);

            if (empty($satuan)) {
                return redirect()->route('satuan.index')
                    ->with('error', 'Satuan tidak ditemukan!');
            }

            // Cek status saat ini
            if ($satuan[0]->status == 0) {
                return redirect()->route('satuan.index')
                    ->with('info', 'Satuan sudah dalam status nonaktif!');
            }

            // Soft Delete: Ubah status menjadi 0 (nonaktif)
            DB::update("
                UPDATE satuan
                SET status = 0
                WHERE idsatuan = ?
            ", [$id]);

            return redirect()->route('satuan.index')
                ->with('success', 'Satuan berhasil dinonaktifkan! Data masih tersimpan dan dapat diaktifkan kembali.');
        } catch (\Exception $e) {
            return redirect()->route('satuan.index')
                ->with('error', 'Gagal menonaktifkan satuan: ' . $e->getMessage());
        }
    }

    // Toggle status satuan (aktif/nonaktif)
    public function toggleStatus(string $id)
    {
        try {
            // Ambil status saat ini
            $currentStatus = DB::select("
                SELECT status
                FROM satuan
                WHERE idsatuan = ?
            ", [$id]);

            if (empty($currentStatus)) {
                return redirect()->route('satuan.index')
                    ->with('error', 'Satuan tidak ditemukan!');
            }

            // Toggle: jika 1 jadikan 0, jika 0 jadikan 1
            $newStatus = $currentStatus[0]->status == 1 ? 0 : 1;
            $statusText = $newStatus == 1 ? 'Aktif' : 'Nonaktif';

            // Update status
            DB::update("
                UPDATE satuan
                SET status = ?
                WHERE idsatuan = ?
            ", [$newStatus, $id]);

            return redirect()->route('satuan.index')
                ->with('success', "Status satuan berhasil diubah menjadi {$statusText}!");
        } catch (\Exception $e) {
            return redirect()->route('satuan.index')
                ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // Get satuan yang aktif saja (untuk dropdown di form lain)
    public function getActive()
    {
        $satuans = DB::select('SELECT idsatuan, nama_satuan FROM master_satuan_active_view ORDER BY nama_satuan');

        return response()->json($satuans);
    }
}
