<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\VendorExport;
use App\Exports\VendorTemplateExport;
use App\Imports\VendorImport;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{

    // Menampilkan data vendor
    public function index(Request $request)
    {
        $show = $request->query('show');
        $search = $request->query('search');
        $badanHukum = $request->query('badan_hukum');

        // Build query with pagination
        $query = DB::table('vendor');

        // Filter by status
        if ($show !== 'all') {
            $query->where('status', 'Y');
        }

        // Search by name
        if ($search) {
            $query->where('nama_vendor', 'LIKE', "%{$search}%");
        }

        // Filter by badan hukum
        if ($badanHukum) {
            $query->where('badan_hukum', $badanHukum);
        }

        // Paginate results
        $vendors = $query->orderBy('idvendor', 'DESC')->paginate(15)->withQueryString();

        return view('vendor.index', compact('vendors'));
    }


    // Menampilkan form untuk membuat data
    public function create()
    {
        return view('vendor.create');
    }

    /**
     * Store a newly created vendor in storage.
     */
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:Y,N',
            'status' => 'required|in:Y,N'
        ]);

        // Raw SQL INSERT
        DB::insert('INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)', [
            $validated['nama_vendor'],
            $validated['badan_hukum'],
            $validated['status']
        ]);

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit($id)
    {
        $vendorResult = DB::select('SELECT * FROM master_vendor_view WHERE idvendor = ? LIMIT 1', [$id]);

        if (!$vendorResult || count($vendorResult) === 0) {
            abort(404);
        }

        $vendor = $vendorResult[0];
        return view('vendor.edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:Y,N',
            'status' => 'required|in:Y,N'
        ]);

        // Check if vendor exists
        $exists = DB::select('SELECT idvendor FROM vendor WHERE idvendor = ? LIMIT 1', [$id]);
        if (!$exists || count($exists) === 0) {
            abort(404);
        }

        // Raw SQL UPDATE
        DB::update('UPDATE vendor SET nama_vendor = ?, badan_hukum = ?, status = ? WHERE idvendor = ?', [
            $validated['nama_vendor'],
            $validated['badan_hukum'],
            $validated['status'],
            $id
        ]);

        return redirect()->route('vendor.index')
            ->with('success', 'Data vendor berhasil diperbarui!');
    }

    /**
     * Export Vendor to Excel
     */
    public function export()
    {
        return Excel::download(new VendorExport, 'Master-Vendor-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Download Import Template
     */
    public function downloadTemplate()
    {
        return Excel::download(new VendorTemplateExport, 'Template-Import-Vendor.xlsx');
    }

    /**
     * Import Vendor from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $import = new VendorImport();
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

            return redirect()->route('vendor.index')
                ->with('success', 'Import berhasil! Data vendor telah ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete vendor (set status to 'N').
     */
    public function destroy($id)
    {
        // Check if vendor exists - Raw SQL
        $exists = DB::select('SELECT idvendor FROM vendor WHERE idvendor = ? LIMIT 1', [$id]);
        if (!$exists || count($exists) === 0) {
            abort(404);
        }

        // Raw SQL UPDATE (soft delete)
        DB::update('UPDATE vendor SET status = ? WHERE idvendor = ?', ['N', $id]);

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor berhasil dinonaktifkan!');
    }
}
