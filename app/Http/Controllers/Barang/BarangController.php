<?php

namespace App\Http\Controllers\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use App\Exports\BarangExport;
use App\Exports\BarangTemplateExport;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('barang', 'public');
        }

        // TRANSACTION untuk memastikan data integrity --- IGNORE ---
        // Jika ada error, semua operasi akan di-rollback --- IGNORE ---
        DB::beginTransaction();
        try {
            // Insert barang baru
            DB::insert('INSERT INTO barang (jenis,nama,idsatuan,status,harga,image) VALUES (?,?,?,?,?,?)', [
                $data['jenis'],
                $data['nama'],
                $data['idsatuan'],
                $data['status'],
                $data['harga'],
                $imagePath,
            ]);

            // Commit transaction jika semua berhasil
            DB::commit();

            return redirect()->route('barang.index')->with('success', 'Barang ditambahkan.');
        } catch (\Exception $e) {
            // Rollback jika ada error
            DB::rollBack();

            // Delete uploaded image if transaction fails
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

        // Formulir edit barang
    public function edit($idbarang)
    {
        // Ambil data barang langsung dari tabel dengan join satuan
        $barang = DB::selectOne('SELECT b.*, s.nama_satuan
                                 FROM barang b
                                 LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                                 WHERE b.idbarang = ?', [$idbarang]);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // cek apakah barang ada
        $barang = DB::selectOne('SELECT * FROM barang WHERE idbarang = ? LIMIT 1', [$id]);
        if (!$barang) {
            abort(404);
        }

        // Handle image upload
        $imagePath = $barang->image; // Keep existing image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($barang->image && Storage::disk('public')->exists($barang->image)) {
                Storage::disk('public')->delete($barang->image);
            }
            // Store new image
            $imagePath = $request->file('image')->store('barang', 'public');
        }

        // Update barang dengan parameter binding untuk keamanan
        DB::update('UPDATE barang SET jenis = ?, nama = ?, idsatuan = ?, status = ?, harga = ?, image = ? WHERE idbarang = ?', [
            $data['jenis'],
            $data['nama'],
            $data['idsatuan'],
            $data['status'],
            $data['harga'],
            $imagePath,
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

    /**
     * Download Import Template
     */
    public function downloadTemplate()
    {
        return Excel::download(new BarangTemplateExport, 'Template-Import-Barang.xlsx');
    }

    /**
     * Import Barang from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $import = new BarangImport();
            Excel::import($import, $request->file('file'));

            $errors = [];

            // Collect validation errors
            foreach ($import->failures() as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            if (count($errors) > 0) {
                return redirect()->back()
                    ->with('import_errors', $errors)
                    ->with('warning', 'Import selesai dengan ' . count($errors) . ' error. Lihat detail di bawah.');
            }

            return redirect()->route('barang.index')
                ->with('success', 'Import berhasil! Data barang telah ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
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

    // Generate QR Code untuk barang
    public function generateQrCode($id)
    {
        $barang = DB::selectOne('SELECT b.*, s.nama_satuan FROM barang b
                                 LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                                 WHERE b.idbarang = ?', [$id]);

        if (!$barang) {
            abort(404);
        }

        // Generate QR code berisi ID barang
        $qrCode = QrCode::size(300)
            ->format('svg')
            ->generate($barang->idbarang);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    // Print label untuk single barang
    public function printLabel($id)
    {
        $barang = DB::selectOne('SELECT b.*, s.nama_satuan FROM barang b
                                 LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                                 WHERE b.idbarang = ?', [$id]);

        if (!$barang) {
            abort(404);
        }

        return view('barang.print-label', compact('barang'));
    }

    // Print bulk labels
    public function printBulkLabels(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 barang untuk print label.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $barangs = DB::select("SELECT b.*, s.nama_satuan FROM barang b
                               LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
                               WHERE b.idbarang IN ($placeholders)
                               ORDER BY b.nama", $ids);

        return view('barang.print-bulk-labels', compact('barangs'));
    }
}
