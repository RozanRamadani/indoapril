<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengadaanExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = "
            SELECT
                p.idpengadaan,
                p.created_at,
                v.nama_vendor,
                u.username,
                p.status,
                p.subtotal_nilai,
                p.ppn,
                p.total_nilai
            FROM pengadaan p
            LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON p.iduser = u.iduser
            WHERE 1=1
        ";

        $bindings = [];

        if ($this->startDate) {
            $query .= " AND DATE(p.created_at) >= ?";
            $bindings[] = $this->startDate;
        }

        if ($this->endDate) {
            $query .= " AND DATE(p.created_at) <= ?";
            $bindings[] = $this->endDate;
        }

        $query .= " ORDER BY p.created_at DESC";

        $data = DB::select($query, $bindings);

        return collect($data)->map(function ($item) {
            return [
                'ID' => $item->idpengadaan,
                'Tanggal' => date('d/m/Y H:i', strtotime($item->created_at)),
                'Vendor' => $item->nama_vendor,
                'User' => $item->username,
                'Status' => $item->status === 1 ? 'Finalized' : 'Draft',
                'Subtotal' => $item->subtotal_nilai,
                'PPN' => $item->ppn,
                'Total' => $item->total_nilai,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Vendor',
            'User',
            'Status',
            'Subtotal',
            'PPN',
            'Total'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 18,
            'C' => 20,
            'D' => 15,
            'E' => 12,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }
}
