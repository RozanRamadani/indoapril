2<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenerimaanExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
                pn.idpenerimaan,
                pn.created_at,
                p.idpengadaan,
                v.nama_vendor,
                u.username,
                pn.status
            FROM penerimaan pn
            LEFT JOIN pengadaan p ON pn.idpengadaan = p.idpengadaan
            LEFT JOIN vendor v ON p.vendor_idvendor = v.idvendor
            LEFT JOIN user u ON pn.iduser = u.iduser
            WHERE 1=1
        ";

        $bindings = [];

        if ($this->startDate) {
            $query .= " AND DATE(pn.created_at) >= ?";
            $bindings[] = $this->startDate;
        }

        if ($this->endDate) {
            $query .= " AND DATE(pn.created_at) <= ?";
            $bindings[] = $this->endDate;
        }

        $query .= " ORDER BY pn.created_at DESC";

        $data = DB::select($query, $bindings);

        return collect($data)->map(function ($item) {
            return [
                'ID Penerimaan' => $item->idpenerimaan,
                'Tanggal' => date('d/m/Y H:i', strtotime($item->created_at)),
                'ID Pengadaan' => $item->idpengadaan,
                'Vendor' => $item->nama_vendor,
                'User' => $item->username,
                'Status' => $item->status === 1 ? 'Finalized' : 'Draft',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Penerimaan',
            'Tanggal',
            'ID Pengadaan',
            'Vendor',
            'User',
            'Status'
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
            'A' => 15,
            'B' => 18,
            'C' => 15,
            'D' => 20,
            'E' => 15,
            'F' => 12,
        ];
    }
}
