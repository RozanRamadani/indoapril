<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    protected $hasAlamat;
    protected $hasTelp;

    public function __construct()
    {
        // Check if alamat and telp columns exist
        $this->hasAlamat = Schema::hasColumn('vendor', 'alamat');
        $this->hasTelp = Schema::hasColumn('vendor', 'telp');
    }

    /**
     * Get the data collection for export
     */
    public function collection()
    {
        // Build columns dynamically
        $columns = "v.idvendor, v.nama_vendor, v.badan_hukum";

        if ($this->hasAlamat) {
            $columns .= ", v.alamat";
        }

        if ($this->hasTelp) {
            $columns .= ", v.telp";
        }

        $columns .= ", v.status";

        return collect(DB::select("
            SELECT {$columns}
            FROM vendor v
            ORDER BY v.idvendor DESC
        "));
    }

    /**
     * Map the data for each row
     */
    public function map($vendor): array
    {
        $row = [
            $vendor->idvendor,
            $vendor->nama_vendor,
            $vendor->badan_hukum === 'Y' ? 'Ya' : 'Tidak'
        ];

        if ($this->hasAlamat) {
            $row[] = $vendor->alamat ?? '-';
        }

        if ($this->hasTelp) {
            $row[] = $vendor->telp ?? '-';
        }

        $row[] = $vendor->status === 'Y' ? 'Aktif' : 'Tidak Aktif';

        return $row;
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        $headings = [
            'ID Vendor',
            'Nama Vendor',
            'Badan Hukum'
        ];

        if ($this->hasAlamat) {
            $headings[] = 'Alamat';
        }

        if ($this->hasTelp) {
            $headings[] = 'Telepon';
        }

        $headings[] = 'Status';

        return $headings;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0ea5e9']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 12,  // ID Vendor
            'B' => 30,  // Nama Vendor
            'C' => 15,  // Badan Hukum
        ];

        $column = 'D';
        if ($this->hasAlamat) {
            $widths[$column] = 40;  // Alamat
            $column++;
        }

        if ($this->hasTelp) {
            $widths[$column] = 18;  // Telepon
            $column++;
        }

        $widths[$column] = 15;  // Status

        return $widths;
    }
}
