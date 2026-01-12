<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penerimaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #0ea5e9; padding-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #0ea5e9; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; color: #0369a1; margin-top: 10px; }
        .report-period { font-size: 9px; color: #666; margin-top: 5px; }
        .summary { display: table; width: 100%; margin-bottom: 15px; }
        .summary-item { display: table-cell; width: 33.33%; padding: 10px; text-align: center; border: 1px solid #ddd; background-color: #f0f9ff; }
        .summary-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .summary-value { font-size: 18px; font-weight: bold; color: #0369a1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #0ea5e9; color: white; }
        thead th { padding: 8px 6px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #f0f9ff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total { background-color: #0ea5e9; color: white; font-weight: bold; font-size: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { body { background: white; } .container { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div style="font-size: 10px; color: #666;">Inventory Management System</div>
            <div class="report-title">LAPORAN PENERIMAAN BARANG</div>
            <div class="report-period">Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</div>
        </div>

        @php
            $totalGRN = count($laporan);
            $totalNilai = collect($laporan)->sum('total_nilai');
            $totalQty = collect($laporan)->sum('total_barang');
        @endphp

        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total GRN</div>
                <div class="summary-value">{{ number_format($totalGRN, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Qty Diterima</div>
                <div class="summary-value">{{ number_format($totalQty, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Nilai</div>
                <div class="summary-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 10%;">ID GRN</th>
                    <th style="width: 12%;">REF PO</th>
                    <th style="width: 15%;">TANGGAL</th>
                    <th style="width: 23%;">VENDOR</th>
                    <th style="width: 12%;" class="text-right">TOTAL QTY</th>
                    <th style="width: 18%;" class="text-right">TOTAL NILAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>#{{ str_pad($item->idpenerimaan, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>#{{ str_pad($item->idpengadaan, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ date('d M Y H:i', strtotime($item->tanggal_penerimaan)) }}</td>
                    <td>{{ $item->nama_vendor }}</td>
                    <td class="text-right">{{ number_format($item->total_barang, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="5" style="padding: 10px;" class="text-right">GRAND TOTAL:</td>
                    <td class="text-right" style="padding: 10px;">{{ number_format($totalQty, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 10px;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem IndoApril pada {{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
