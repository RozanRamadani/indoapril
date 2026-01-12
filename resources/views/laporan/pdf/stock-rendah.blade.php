<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stock Rendah</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .container { width: 100%; max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #f59e0b; padding-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #f59e0b; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; color: #d97706; margin-top: 10px; }
        .report-info { font-size: 9px; color: #666; margin-top: 5px; }
        .alert-box { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; margin-bottom: 15px; }
        .alert-box p { font-size: 9px; color: #78350f; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #f59e0b; color: white; }
        thead th { padding: 8px 6px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #fffbeb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-critical { background-color: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-warning { background-color: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { body { background: white; } .container { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div style="font-size: 10px; color: #666;">Inventory Management System</div>
            <div class="report-title">⚠ LAPORAN STOCK RENDAH</div>
            <div class="report-info">Threshold: {{ $threshold }} unit | Tanggal: {{ date('d F Y H:i') }}</div>
        </div>

        @if(count($stockRendah) > 0)
        <div class="alert-box">
            <p><strong>Peringatan:</strong> Terdapat {{ count($stockRendah) }} barang dengan stock di bawah threshold ({{ $threshold }} unit). Segera lakukan pengadaan!</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 50%;">NAMA BARANG</th>
                    <th style="width: 10%;">SATUAN</th>
                    <th style="width: 12%;" class="text-right">STOCK</th>
                    <th style="width: 15%;" class="text-right">HARGA</th>
                    <th style="width: 10%;" class="text-center">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockRendah as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="text-center">{{ $item->satuan }}</td>
                    <td class="text-right"><strong>{{ number_format($item->current_stock, 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item->current_stock == 0)
                            <span class="badge-critical">HABIS</span>
                        @else
                            <span class="badge-warning">RENDAH</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 40px; background-color: #d1fae5; border-radius: 8px;">
            <p style="font-size: 12px; color: #065f46; font-weight: bold;">✓ Semua barang memiliki stock yang cukup!</p>
        </div>
        @endif

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem IndoApril pada {{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
