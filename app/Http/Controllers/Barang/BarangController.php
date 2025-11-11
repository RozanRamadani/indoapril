<?php

namespace App\Http\Controllers\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{

    // Menampilkan daftar barang (menggunakan view active/inactive)
    public function index()
    {
        $show = request()->query('show');

        // Query langsung dengan JOIN ke satuan
        if ($show === 'all') {
            $barangs = DB::select('
                SELECT b.*, s.nama_satuan
                FROM barang b
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                ORDER BY b.idbarang DESC
            ');
        } else {
            $barangs = DB::select('
                SELECT b.*, s.nama_satuan
                FROM barang b
                LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                WHERE b.status = 1
                ORDER BY b.idbarang DESC
            ', []);
        }

        return view('barang.index', compact('barangs'));
    }

    // Formulir tambah barang
    public function create()
    {
        // Gunakan view satuan aktif
        $satuans = DB::select('SELECT * FROM master_satuan_active_view ORDER BY nama_satuan');

        return view('barang.create', compact('satuans'));
    }

    // Simpan barang baru
    public function store(Request $request)
    {

        // Validasi input
        $data = $request->validate([
            'jenis' => 'required|string|size:1|in:M,N,A,K,B',
            'nama' => 'required|string|max:45',
            'idsatuan' => 'required|integer|exists:satuan,idsatuan',
            'status' => 'required|in:0,1',
            'harga' => 'required|integer|min:0',
        ]);

        // TRANSACTION untuk memastikan data integrity --- IGNORE ---
        // Jika ada error, semua operasi akan di-rollback --- IGNORE ---
        DB::beginTransaction();
        try {
            // Insert barang baru
            DB::insert('INSERT INTO barang (jenis,nama,idsatuan,status,harga) VALUES (?,?,?,?,?)', [
                $data['jenis'],
                $data['nama'],
                $data['idsatuan'],
                $data['status'],
                $data['harga'],
            ]);

            // Commit transaction jika semua berhasil
            DB::commit();

            return redirect()->route('barang.index')->with('success', 'Barang ditambahkan.');
        } catch (\Exception $e) {
            // Rollback jika ada error
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

        // Formulir edit barang
    public function edit($idbarang)
    {
        // Ambil data barang dari view
        $barang = DB::selectOne('SELECT * FROM master_barang_view WHERE idbarang = ?', [$idbarang]);

        if (!$barang) {
            return redirect()->route('barang.index')->with('error', 'Barang tidak ditemukan!');
        }

        // Gunakan view satuan aktif
        $satuans = DB::select('SELECT * FROM master_satuan_active_view ORDER BY nama_satuan');

        return view('barang.edit', compact('barang', 'satuans'));
    }

    // Fungsi update barang
    public function update(Request $request, $id)
    {
        // Validasi input
        $data = $request->validate([
            'jenis' => 'required|string|size:1|in:M,N,A,K,B',
            'nama' => 'required|string|max:45',
            'idsatuan' => 'required|integer|exists:satuan,idsatuan',
            'status' => 'required|in:0,1',
            'harga' => 'required|integer|min:0',
        ]);

        // cek apakah barang ada
        $exists = DB::select('SELECT idbarang FROM barang WHERE idbarang = ? LIMIT 1', [$id]);
        if (! $exists || count($exists) === 0) {
            abort(404);
        }

        // Update barang dengan parameter binding untuk keamanan
        DB::update('UPDATE barang SET jenis = ?, nama = ?, idsatuan = ?, status = ?, harga = ? WHERE idbarang = ?', [
            $data['jenis'],
            $data['nama'],
            $data['idsatuan'],
            $data['status'],
            $data['harga'],
            $id,
        ]);

        // Redirect dengan pesan sukses
        return redirect()->route('barang.index')->with('success', 'Barang diperbarui.');
    }

    // Fungsi soft delete barang
    public function destroy($id)
    {

        // Soft-delete by setting status = 0 (nonaktif)
        $exists = DB::select('SELECT idbarang FROM barang WHERE idbarang = ? LIMIT 1', [$id]);
        if (! $exists || count($exists) === 0) {
            abort(404);
        }

        // Soft delete
        DB::update('UPDATE barang SET status = 0 WHERE idbarang = ?', [$id]);
        return redirect()->route('barang.index')->with('success', 'Barang dinonaktifkan.');
    }
}
