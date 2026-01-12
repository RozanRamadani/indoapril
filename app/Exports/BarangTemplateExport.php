<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BarangTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Return sample data (empty or with examples)
     */
    public function collection()
    {
        return collect([
            [
                'Mie Instan Premium',
                'Makanan',
                'PCS',
                5000,
                100
            ],
            [
                'Air Mineral 600ml',
                'Minuman',
                'BOTOL',
                3000,
                200
            ],
            [
                'Pulpen Biru',
                'Alat Tulis Kantor',
                'PCS',
                2500,
                50
            ]
        ]);
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Nama Barang *',
            'Jenis (Makanan/Minuman/ATK/Kebersihan/Bahan Baku) *',
            'Satuan (PCS/BOX/KG/LITER) *',
            'Harga *',
            'Stock Awal (Optional)'
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 45,
            'C' => 30,
            'D' => 15,
            'E' => 20
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
                    'startColor' => ['rgb' => '4F46E5']
                ]
            ],
        ];
    }
}
