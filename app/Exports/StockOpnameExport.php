<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockOpnameExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $search;
    protected $jenis;

    public function __construct($search = null, $jenis = null)
    {
        $this->search = $search;
        $this->jenis = $jenis;
    }

    public function collection()
    {
        $query = "
            SELECT
                b.idbarang,
                b.nama,
                b.jenis,
                s.nama_satuan,
                b.harga,
                COALESCE(ks.stock, 0) as stock,
                (b.harga * COALESCE(ks.stock, 0)) as nilai_stock
            FROM barang b
            LEFT JOIN satuan s ON b.idsatuan = s.idsatuan
            LEFT JOIN kartu_stok ks ON b.idbarang = ks.idbarang
            WHERE b.status = 1
        ";

        $bindings = [];

        if ($this->search) {
            $query .= " AND b.nama LIKE ?";
            $bindings[] = "%{$this->search}%";
        }

        if ($this->jenis) {
            $query .= " AND b.jenis = ?";
            $bindings[] = $this->jenis;
        }

        $query .= " ORDER BY b.nama ASC";

        $data = DB::select($query, $bindings);

        return collect($data)->map(function ($item) {
            return [
                'ID' => $item->idbarang,
                'Nama Barang' => $item->nama,
                'Jenis' => $item->jenis,
                'Satuan' => $item->nama_satuan,
                'Harga' => $item->harga,
                'Stock' => $item->stock,
                'Nilai Stock' => $item->nilai_stock,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'Jenis',
            'Satuan',
            'Harga',
            'Stock',
            'Nilai Stock'
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
            'B' => 30,
            'C' => 15,
            'D' => 12,
            'E' => 15,
            'F' => 10,
            'G' => 15,
        ];
    }
}
