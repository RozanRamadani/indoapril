<?php

namespace App\Http\Controllers\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use App\Exports\BarangExport;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{

    // Menampilkan daftar barang (menggunakan view active/inactive)
    public function index(Request $request)
    {
        $show = $request->query('show');
        $search = $request->query('search');
        $jenis = $request->query('jenis');

        // Build query
        $query = DB::table('barang as b')
            ->leftJoin('satuan as s', 'b.idsatuan', '=', 's.idsatuan')
            ->select('b.*', 's.nama_satuan');

        // Filter by status
        if ($show !== 'all') {
            $query->where('b.status', 1);
        }

        // Search by nama
        if ($search) {
            $query->where('b.nama', 'LIKE', "%{$search}%");
        }

        // Filter by jenis
        if ($jenis) {
            $query->where('b.jenis', $jenis);
        }

        // Order and paginate
        $barangs = $query->orderBy('b.idbarang', 'DESC')->paginate(15)->withQueryString();

        // Untuk dropdown jenis
        $jenisOptions = [
            'M' => 'Makanan',
            'N' => 'Minuman',
            'A' => 'Alat Tulis Kantor',
            'K' => 'Kebersihan',
            'B' => 'Bahan Baku'
        ];

        return view('barang.index', compact('barangs', 'jenisOptions'));
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

    /**
     * Export Barang to Excel
     */
    public function export()
    {
        return Excel::download(new BarangExport, 'Master-Barang-' . date('Y-m-d') . '.xlsx');
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
