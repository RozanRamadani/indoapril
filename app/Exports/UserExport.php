<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    /**
     * Get the data collection for export
     */
    public function collection()
    {
        return collect(DB::select("
            SELECT
                u.iduser,
                u.username,
                u.nama_user,
                r.nama_role,
                u.created_at
            FROM user u
            LEFT JOIN role r ON u.idrole = r.idrole
            ORDER BY u.username
        "));
    }

    /**
     * Map the data for each row
     */
    public function map($user): array
    {
        return [
            $user->iduser,
            $user->username,
            $user->nama_user,
            $user->nama_role ?? '-',
            $user->created_at ? date('d-m-Y H:i', strtotime($user->created_at)) : '-'
        ];
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'ID User',
            'Username',
            'Nama Lengkap',
            'Role',
            'Terdaftar Sejak'
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
                    'startColor' => ['rgb' => '8b5cf6']
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
            'A' => 10,  // ID User
            'B' => 20,  // Username
            'C' => 30,  // Nama Lengkap
            'D' => 20,  // Role
            'E' => 20,  // Terdaftar Sejak
        ];
    }
}
