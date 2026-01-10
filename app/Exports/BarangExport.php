<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    /**
     * Get the data collection for export
     */
    public function collection()
    {
        return collect(DB::select("
            SELECT
                b.idbarang,
                b.jenis,
                b.nama,
                s.nama_satuan,
                b.harga,
                fn_get_stock(b.idbarang) as stock,
                CASE
                    WHEN b.status = 1 THEN 'Aktif'
                    ELSE 'Tidak Aktif'
                END as status_text
            FROM barang b
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            ORDER BY b.idbarang DESC
        "));
    }

    /**
     * Map the data for each row
     */
    public function map($barang): array
    {
        // Map jenis to full name
        $jenisMap = [
            'M' => 'Makanan',
            'N' => 'Minuman',
            'A' => 'Alat Tulis Kantor',
            'K' => 'Kebersihan',
            'B' => 'Bahan Baku'
        ];

        return [
            $barang->idbarang,
            $jenisMap[$barang->jenis] ?? $barang->jenis,
            $barang->nama,
            $barang->nama_satuan,
            'Rp ' . number_format($barang->harga, 0, ',', '.'),
            $barang->stock,
            $barang->status_text
        ];
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'ID Barang',
            'Jenis',
            'Nama Barang',
            'Satuan',
            'Harga',
            'Stock',
            'Status'
        ];
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
                    'startColor' => ['rgb' => 'e74c3c']
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
        return [
            'A' => 12,  // ID Barang
            'B' => 20,  // Jenis
            'C' => 35,  // Nama Barang
            'D' => 15,  // Satuan
            'E' => 18,  // Harga
            'F' => 10,  // Stock
            'G' => 15,  // Status
        ];
    }
}
