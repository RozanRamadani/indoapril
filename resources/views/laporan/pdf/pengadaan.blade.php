<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengadaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #f43f5e; padding-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #f43f5e; margin-bottom: 5px; }
        .report-title { font-size: 16px; font-weight: bold; color: #be123c; margin-top: 10px; }
        .report-period { font-size: 9px; color: #666; margin-top: 5px; }
        .summary { display: table; width: 100%; margin-bottom: 15px; }
        .summary-item { display: table-cell; width: 33.33%; padding: 10px; text-align: center; border: 1px solid #ddd; background-color: #fff1f2; }
        .summary-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .summary-value { font-size: 18px; font-weight: bold; color: #be123c; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #f43f5e; color: white; }
        thead th { padding: 8px 6px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background-color: #fff1f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-draft { background-color: #fef3c7; color: #92400e; }
        .badge-progress { background-color: #dbeafe; color: #1e40af; }
        .badge-completed { background-color: #d1fae5; color: #065f46; }
        .grand-total { background-color: #f43f5e; color: white; font-weight: bold; font-size: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { body { background: white; } .container { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div style="font-size: 10px; color: #666;">Inventory Management System</div>
            <div class="report-title">LAPORAN PENGADAAN</div>
            <div class="report-period">Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</div>
        </div>

        @php
            $totalPO = count($laporan);
            $totalNilai = collect($laporan)->sum('total_nilai');
            $totalQty = collect($laporan)->sum('total_qty');
        @endphp

        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total PO</div>
                <div class="summary-value">{{ number_format($totalPO, 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Qty</div>
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
                    <th style="width: 10%;">ID PO</th>
                    <th style="width: 15%;">TANGGAL</th>
                    <th style="width: 25%;">VENDOR</th>
                    <th style="width: 12%;" class="text-right">TOTAL QTY</th>
                    <th style="width: 18%;" class="text-right">TOTAL NILAI</th>
                    <th style="width: 15%;" class="text-center">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>#{{ str_pad($item->idpengadaan, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ date('d M Y H:i', strtotime($item->tanggal_pengadaan)) }}</td>
                    <td>{{ $item->nama_vendor }}</td>
                    <td class="text-right">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</strong></td>
                    <td class="text-center">
                        @if($item->status_pengadaan === 'completed')
                            <span class="badge badge-draft">SELESAI</span>
                        @elseif($item->status_pengadaan === 'approved')
                            <span class="badge badge-progress">APPROVED</span>
                        @else
                            <span class="badge badge-completed">COMPLETED</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="4" style="padding: 10px;" class="text-right">GRAND TOTAL:</td>
                    <td class="text-right" style="padding: 10px;">{{ number_format($totalQty, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 10px;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem IndoApril pada {{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
