<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock Opname</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #3b82f6; padding-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #3b82f6; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; color: #1e40af; margin-top: 10px; }
        .report-date { font-size: 9px; color: #666; margin-top: 5px; }
        .summary { display: table; width: 100%; margin-bottom: 15px; }
        .summary-item { display: table-cell; width: 25%; padding: 10px; text-align: center; border: 1px solid #ddd; background-color: #f9fafb; }
        .summary-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .summary-value { font-size: 18px; font-weight: bold; color: #1e40af; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #3b82f6; color: white; }
        thead th { padding: 8px 6px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print {
            body { background: white; }
            .container { margin: 0; padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div style="font-size: 10px; color: #666;">Inventory Management System</div>
            <div class="report-title">LAPORAN STOCK OPNAME</div>
            <div class="report-date">Per Tanggal: {{ date('d F Y H:i') }}</div>
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Barang</div>
                <div class="summary-value">{{ number_format(count($stockOpname), 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Stock</div>
                <div class="summary-value">{{ number_format(collect($stockOpname)->sum('current_stock'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Stock Rendah (≤10)</div>
                <div class="summary-value" style="color: #f59e0b;">{{ collect($stockOpname)->where('current_stock', '<=', 10)->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Stock Habis (0)</div>
                <div class="summary-value" style="color: #ef4444;">{{ collect($stockOpname)->where('current_stock', '=', 0)->count() }}</div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 50%;">NAMA BARANG</th>
                    <th style="width: 10%;">SATUAN</th>
                    <th style="width: 12%;" class="text-right">STOCK</th>
                    <th style="width: 13%;" class="text-right">HARGA</th>
                    <th style="width: 10%;" class="text-center">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockOpname as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="text-center">{{ $item->nama_satuan }}</td>
                    <td class="text-right"><strong>{{ number_format($item->current_stock, 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item->current_stock > 10)
                            <span class="badge badge-success">Aman</span>
                        @elseif($item->current_stock > 0)
                            <span class="badge badge-warning">Rendah</span>
                        @else
                            <span class="badge badge-danger">Habis</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem IndoApril pada {{ date('d F Y H:i:s') }}</p>
            <p style="margin-top: 5px;">IndoApril © {{ date('Y') }} - Inventory Management System</p>
        </div>
    </div>
</body>
</html>
