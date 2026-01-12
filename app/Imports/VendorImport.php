<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class VendorImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * Map Excel row to Vendor model
     */
    public function model(array $row)
    {
        return new Vendor([
            'nama_vendor' => $row['nama_vendor'],
            'alamat_vendor' => $row['alamat'] ?? '',
            'telp_vendor' => $row['telepon'] ?? '',
            'status' => 'Y' // Default active
        ]);
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'nama_vendor' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'telepon' => 'nullable|string|max:20'
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nama_vendor.required' => 'Nama vendor wajib diisi',
            'nama_vendor.max' => 'Nama vendor maksimal 255 karakter',
        ];
    }
}
