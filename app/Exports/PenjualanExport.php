<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
                pj.idpenjualan,
                pj.created_at,
                u.username,
                m.persen as margin,
                pj.subtotal_nilai,
                pj.ppn,
                pj.total_nilai
            FROM penjualan pj
            LEFT JOIN user u ON pj.iduser = u.iduser
            LEFT JOIN margin_penjualan m ON pj.idmargin_penjualan = m.idmargin_penjualan
            WHERE 1=1
        ";

        $bindings = [];

        if ($this->startDate) {
            $query .= " AND DATE(pj.created_at) >= ?";
            $bindings[] = $this->startDate;
        }

        if ($this->endDate) {
            $query .= " AND DATE(pj.created_at) <= ?";
            $bindings[] = $this->endDate;
        }

        $query .= " ORDER BY pj.created_at DESC";

        $data = DB::select($query, $bindings);

        return collect($data)->map(function ($item) {
            return [
                'ID' => $item->idpenjualan,
                'Tanggal' => date('d/m/Y H:i', strtotime($item->created_at)),
                'User' => $item->username,
                'Margin (%)' => $item->margin,
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
            'User',
            'Margin (%)',
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
            'C' => 15,
            'D' => 12,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }
}
