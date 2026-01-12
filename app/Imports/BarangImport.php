<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class BarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * Map Excel row to Barang model
     */
    public function model(array $row)
    {
        // Map jenis from full name to code
        $jenisMap = [
            'Makanan' => 'M',
            'Minuman' => 'N',
            'Alat Tulis Kantor' => 'A',
            'ATK' => 'A',
            'Kebersihan' => 'K',
            'Bahan Baku' => 'B'
        ];

        $jenis = $jenisMap[$row['jenis']] ?? strtoupper(substr($row['jenis'], 0, 1));

        // Get satuan ID from nama_satuan
        $satuan = DB::table('satuan')
            ->where('nama_satuan', 'LIKE', '%' . $row['satuan'] . '%')
            ->first();

        if (!$satuan) {
            // Create satuan if not exists
            $satuanId = DB::table('satuan')->insertGetId([
                'nama_satuan' => strtoupper($row['satuan'])
            ]);
        } else {
            $satuanId = $satuan->idsatuan;
        }

        // Create barang
        $barang = Barang::create([
            'jenis' => $jenis,
            'nama' => $row['nama_barang'],
            'idsatuan' => $satuanId,
            'harga' => (int) $row['harga'],
            'status' => 1 // Default aktif
        ]);

        // Initialize kartu stok with opening stock if provided
        if (isset($row['stock_awal']) && $row['stock_awal'] > 0) {
            DB::table('kartu_stok')->insert([
                'idbarang' => $barang->idbarang,
                'stock' => (int) $row['stock_awal']
            ]);
        } else {
            DB::table('kartu_stok')->insert([
                'idbarang' => $barang->idbarang,
                'stock' => 0
            ]);
        }

        return $barang;
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string|max:255',
            'jenis' => 'required|string',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stock_awal' => 'nullable|numeric|min:0'
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'jenis.required' => 'Jenis barang wajib diisi',
            'satuan.required' => 'Satuan wajib diisi',
            'harga.required' => 'Harga wajib diisi',
            'harga.numeric' => 'Harga harus berupa angka',
            'harga.min' => 'Harga tidak boleh negatif',
        ];
    }
}
