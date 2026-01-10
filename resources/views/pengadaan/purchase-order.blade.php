<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order #{{ $pengadaan->idpengadaan }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            width: 100%;
            max-width: 750px;
            margin: 15px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
            margin-bottom: 12px;
            position: relative;
        }
        .header-left {
            width: 60%;
            float: left;
        }
        .header-right {
            width: 38%;
            float: right;
            text-align: right;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        .po-title {
            font-size: 26px;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .po-number {
            font-size: 11px;
            color: #666;
            font-weight: normal;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .info-section {
            margin-bottom: 12px;
        }
        .info-box {
            width: 48%;
            float: left;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            font-size: 9px;
        }
        .info-box:first-child {
            margin-right: 4%;
        }
        .info-box h3 {
            font-size: 10px;
            color: #e74c3c;
            margin-bottom: 5px;
            border-bottom: 1px solid #e74c3c;
            padding-bottom: 3px;
        }
        .info-row {
            margin-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 70px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        thead {
            background-color: #e74c3c;
            color: white;
        }
        thead th {
            padding: 6px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: none;
        }
        tbody td {
            padding: 5px 5px;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 10px;
            width: 100%;
        }
        .totals {
            float: right;
            width: 48%;
            font-size: 9px;
        }
        .totals table {
            margin-bottom: 0;
            width: 100%;
        }
        .totals td {
            padding: 5px 8px;
            border: none;
        }
        .totals .label {
            text-align: left;
            font-weight: bold;
            color: #555;
            width: 50%;
        }
        .totals .value {
            text-align: right;
            width: 50%;
        }
        .grand-total {
            background-color: #e74c3c;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .terms {
            clear: both;
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #e74c3c;
            font-size: 8px;
        }
        .terms h3 {
            font-size: 9px;
            margin-bottom: 5px;
            color: #e74c3c;
        }
        .terms ul {
            margin-left: 15px;
            line-height: 1.6;
        }
        .signature-section {
            clear: both;
            margin-top: 20px;
            width: 100%;
        }
        .signature-box {
            width: 48%;
            float: left;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .signature-box:first-child {
            margin-right: 4%;
        }
        .signature-box h4 {
            font-size: 9px;
            margin-bottom: 40px;
            color: #555;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            clear: both;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="header-left">
                <div class="company-name">IndoApril</div>
                <div class="company-info">
                    Inventory Management System<br>
                    Jl. Kenanga No. 123, Surabaya<br>
                    Telp: +6285225221311 | Email: info@indoapril.com
                </div>
            </div>
            <div class="header-right">
                <div class="po-title">PURCHASE ORDER</div>
                <div class="po-number">PO #{{ str_pad($pengadaan->idpengadaan, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>Kepada Vendor:</h3>
                <div class="info-row">
                    <strong>{{ $pengadaan->nama_vendor }}</strong>
                </div>
                @if(isset($pengadaan->alamat) && $pengadaan->alamat)
                <div class="info-row">
                    {{ $pengadaan->alamat }}
                </div>
                @endif
                @if(isset($pengadaan->telp) && $pengadaan->telp)
                <div class="info-row">
                    Telp: {{ $pengadaan->telp }}
                </div>
                @endif
            </div>
            <div class="info-box">
                <h3>Informasi PO:</h3>
                <div class="info-row">
                    <span class="info-label">Tanggal:</span>
                    <span>{{ date('d F Y', strtotime($pengadaan->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Waktu:</span>
                    <span>{{ date('H:i:s', strtotime($pengadaan->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat oleh:</span>
                    <span>{{ $pengadaan->username }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span><strong>{{ $pengadaan->status === 'A' ? 'APPROVED' : 'DRAFT' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">NO</th>
                    <th style="width: 36%;">NAMA BARANG</th>
                    <th style="width: 10%;">SATUAN</th>
                    <th style="width: 10%;" class="text-right">QTY</th>
                    <th style="width: 18%;" class="text-right">HARGA SATUAN</th>
                    <th style="width: 22%;" class="text-right">SUB TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td class="text-center">{{ $detail->nama_satuan }}</td>
                    <td class="text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section clearfix">
            <div class="totals">
                <table>
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">PPN ({{ $pengadaan->ppn > 0 ? '10%' : '0%' }}):</td>
                        <td class="value">Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="label" style="padding: 8px;">TOTAL:</td>
                        <td class="value" style="padding: 8px;">Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="terms">
            <h3>Syarat & Ketentuan:</h3>
            <ul>
                <li>Harap mengirimkan barang sesuai dengan spesifikasi yang tercantum</li>
                <li>Pengiriman barang paling lambat 7 hari kerja setelah PO diterima</li>
                <li>Pembayaran akan dilakukan setelah barang diterima dan diverifikasi</li>
                <li>Barang yang tidak sesuai spesifikasi akan diretur</li>
                <li>Harap menyertakan surat jalan dan faktur asli saat pengiriman</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section clearfix">
            <div class="signature-box">
                <h4>Dibuat Oleh:</h4>
                <div class="signature-line">
                    <strong>{{ $pengadaan->username }}</strong><br>
                    <small>Purchasing Staff</small>
                </div>
            </div>
            <div class="signature-box">
                <h4>Disetujui Oleh:</h4>
                <div class="signature-line">
                    <strong>_________________</strong><br>
                    <small>Manager</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem IndoApril</p>
            <p>Dicetak pada: {{ date('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
