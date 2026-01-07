<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $penjualan->idpenjualan }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
        .invoice-title {
            text-align: right;
            margin-top: -60px;
        }
        .invoice-title h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background-color: #e74c3c;
            color: white;
        }
        thead th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totals table {
            margin-bottom: 0;
        }
        .totals td {
            padding: 8px;
            border: none;
        }
        .totals .label {
            text-align: right;
            font-weight: bold;
            color: #555;
        }
        .totals .value {
            text-align: right;
            width: 150px;
        }
        .grand-total {
            background-color: #e74c3c;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
            text-align: center;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #e74c3c;
        }
        .notes h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #e74c3c;
        }
        .notes p {
            font-size: 11px;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">IndoApril</div>
            <div class="company-info">
                Inventory Management System<br>
                Jl. Contoh Alamat No. 123, Jakarta<br>
                Telp: (021) 1234-5678 | Email: info@indoapril.com
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="invoice-number">#{{ str_pad($penjualan->idpenjualan, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-left">
                <p><span class="info-label">Tanggal:</span> {{ date('d F Y', strtotime($penjualan->created_at)) }}</p>
                <p><span class="info-label">Waktu:</span> {{ date('H:i:s', strtotime($penjualan->created_at)) }}</p>
                <p><span class="info-label">Petugas:</span> {{ $penjualan->username }}</p>
            </div>
            <div class="invoice-info-right">
                <p><strong>Margin Penjualan:</strong> {{ $penjualan->margin_persen ?? 0 }}%</p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Nama Barang</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 10%;" class="text-right">Qty</th>
                    <th style="width: 15%;" class="text-right">Harga Satuan</th>
                    <th style="width: 10%;" class="text-right">Margin</th>
                    <th style="width: 15%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td>{{ $detail->nama_satuan }}</td>
                    <td class="text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->nilai_margin, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">Rp {{ number_format($penjualan->subtotal_nilai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">PPN (10%):</td>
                    <td class="value">Rp {{ number_format($penjualan->ppn, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="label" style="padding: 12px 8px;">TOTAL:</td>
                    <td class="value" style="padding: 12px 8px;">Rp {{ number_format($penjualan->total_nilai, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        <div class="notes">
            <h3>Catatan:</h3>
            <p>
                Terima kasih atas pembelian Anda. Barang yang sudah dibeli tidak dapat dikembalikan kecuali ada kesepakatan khusus.
                Untuk pertanyaan lebih lanjut, silakan hubungi customer service kami.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem IndoApril</p>
            <p>{{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
