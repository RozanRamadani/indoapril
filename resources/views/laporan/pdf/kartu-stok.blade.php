<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Stok - {{ $namaBarang }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #8b5cf6; padding-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #8b5cf6; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; color: #6d28d9; margin-top: 10px; }
        .report-info { font-size: 9px; color: #666; margin-top: 5px; }
        .info-box { background-color: #f5f3ff; border: 1px solid #8b5cf6; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .info-row { display: inline-block; margin-right: 20px; }
        .info-label { font-weight: bold; color: #6d28d9; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #8b5cf6; color: white; }
        thead th { padding: 8px 6px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #f5f3ff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-masuk { background-color: #d1fae5; color: #065f46; }
        .badge-keluar { background-color: #fee2e2; color: #991b1b; }
        .badge-retur { background-color: #fef3c7; color: #92400e; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { body { background: white; } .container { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div style="font-size: 10px; color: #666;">Inventory Management System</div>
            <div class="report-title">KARTU STOK</div>
            <div class="report-info">Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</div>
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Nama Barang:</span> {{ $namaBarang }}
            </div>
            <div class="info-row">
                <span class="info-label">Total Transaksi:</span> {{ count($kartuStok) }}
            </div>
        </div>

        @if(count($kartuStok) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 12%;">TANGGAL</th>
                    @if($idbarang === 'all' || $viewAll)
                    <th style="width: 25%;">NAMA BARANG</th>
                    @endif
                    <th style="width: 8%;" class="text-center">TIPE</th>
                    <th style="width: 10%;" class="text-right">MASUK</th>
                    <th style="width: 10%;" class="text-right">KELUAR</th>
                    <th style="width: 12%;" class="text-right">SISA STOCK</th>
                    <th style="width: 18%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kartuStok as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ date('d M Y H:i', strtotime($item->tanggal)) }}</td>
                    @if($idbarang === 'all' || $viewAll)
                    <td>{{ $item->nama_barang ?? '-' }}</td>
                    @endif
                    <td class="text-center">
                        @if(stripos($item->tipe_mutasi, 'Penerimaan') !== false || stripos($item->tipe_mutasi, 'masuk') !== false)
                            <span class="badge badge-masuk">MASUK</span>
                        @elseif(stripos($item->tipe_mutasi, 'Penjualan') !== false || stripos($item->tipe_mutasi, 'keluar') !== false)
                            <span class="badge badge-keluar">KELUAR</span>
                        @else
                            <span class="badge badge-retur">RETUR</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->qty_masuk > 0 ? number_format($item->qty_masuk, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->qty_keluar > 0 ? number_format($item->qty_keluar, 0, ',', '.') : '-' }}</td>
                    <td class="text-right"><strong>{{ number_format($item->sisa_stok, 0, ',', '.') }}</strong></td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 40px; background-color: #f3f4f6; border-radius: 8px;">
            <p style="font-size: 12px; color: #6b7280;">Tidak ada transaksi dalam periode ini.</p>
        </div>
        @endif

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem IndoApril pada {{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
