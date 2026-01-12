<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class VendorTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Return sample data (empty or with examples)
     */
    public function collection()
    {
        return collect([
            [
                'PT. Distributor Makanan Sejahtera',
                'Jl. Raya Industri No. 123, Jakarta',
                '021-12345678'
            ],
            [
                'CV. Sumber Rejeki',
                'Jl. Perdagangan No. 45, Bandung',
                '022-87654321'
            ],
            [
                'Toko Grosir Maju Jaya',
                'Jl. Pasar Besar No. 67, Surabaya',
                '031-11223344'
            ]
        ]);
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Nama Vendor *',
            'Alamat (Optional)',
            'Telepon (Optional)'
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 50,
            'C' => 20
        ];
    }

    /**
     * Styling for the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
                ]
            ],
        ];
    }
}
