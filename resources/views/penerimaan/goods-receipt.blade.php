<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penerimaan Barang #{{ $penerimaan->idpenerimaan }}</title>
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
            border-bottom: 2px solid #0ea5e9;
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
            color: #0ea5e9;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        .grn-title {
            font-size: 20px;
            color: #0ea5e9;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .grn-subtitle {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
        }
        .grn-number {
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
            color: #0ea5e9;
            margin-bottom: 5px;
            border-bottom: 1px solid #0ea5e9;
            padding-bottom: 3px;
        }
        .info-row {
            margin-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 90px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        thead {
            background-color: #0ea5e9;
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
            background-color: #0ea5e9;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .notes {
            clear: both;
            margin-top: 15px;
            padding: 10px;
            background-color: #f0f9ff;
            border-left: 3px solid #0ea5e9;
            font-size: 8px;
        }
        .notes h3 {
            font-size: 9px;
            margin-bottom: 5px;
            color: #0ea5e9;
        }
        .notes ul {
            margin-left: 15px;
            line-height: 1.6;
        }
        .signature-section {
            clear: both;
            margin-top: 20px;
            width: 100%;
        }
        .signature-box {
            width: 30%;
            float: left;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 9px;
            margin-right: 5%;
        }
        .signature-box:last-child {
            margin-right: 0;
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
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-completed {
            background-color: #22c55e;
            color: white;
        }
        .status-draft {
            background-color: #fbbf24;
            color: white;
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
                <div class="grn-title">SURAT PENERIMAAN</div>
                <div class="grn-subtitle">Goods Receipt Note</div>
                <div class="grn-number">GRN #{{ str_pad($penerimaan->idpenerimaan, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>Informasi Penerimaan:</h3>
                <div class="info-row">
                    <span class="info-label">Tanggal Terima:</span>
                    <span>{{ date('d F Y', strtotime($penerimaan->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Waktu:</span>
                    <span>{{ date('H:i:s', strtotime($penerimaan->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Diterima oleh:</span>
                    <span>{{ $penerimaan->username ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ref. PO:</span>
                    <span><strong>#{{ str_pad($penerimaan->idpengadaan, 6, '0', STR_PAD_LEFT) }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge {{ isset($penerimaan->status) && $penerimaan->status === 'A' ? 'status-completed' : 'status-draft' }}">
                        {{ isset($penerimaan->status) && $penerimaan->status === 'A' ? 'COMPLETED' : 'DRAFT' }}
                    </span>
                </div>
            </div>
            <div class="info-box">
                <h3>Dari Vendor:</h3>
                <div class="info-row">
                    <strong>{{ $penerimaan->nama_vendor }}</strong>
                </div>
                @if(isset($penerimaan->alamat) && $penerimaan->alamat)
                <div class="info-row">
                    {{ $penerimaan->alamat }}
                </div>
                @endif
                @if(isset($penerimaan->telp) && $penerimaan->telp)
                <div class="info-row">
                    Telp: {{ $penerimaan->telp }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">NO</th>
                    <th style="width: 36%;">NAMA BARANG</th>
                    <th style="width: 12%;">JENIS</th>
                    <th style="width: 10%;">SATUAN</th>
                    <th style="width: 10%;" class="text-right">QTY TERIMA</th>
                    <th style="width: 14%;" class="text-right">HARGA SATUAN</th>
                    <th style="width: 14%;" class="text-right">SUB TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalNilai = 0;
                @endphp
                @foreach($details as $index => $detail)
                @php
                    $totalNilai += $detail->sub_total_terima;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td>{{ $detail->jenis }}</td>
                    <td class="text-center">{{ $detail->nama_satuan }}</td>
                    <td class="text-right"><strong>{{ number_format($detail->jumlah_terima, 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan_terima, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($detail->sub_total_terima, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section clearfix">
            <div class="totals">
                <table>
                    <tr class="grand-total">
                        <td class="label" style="padding: 8px;">TOTAL NILAI:</td>
                        <td class="value" style="padding: 8px;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes">
            <h3>Catatan Penerimaan:</h3>
            <ul>
                <li>Semua barang telah diterima dan diperiksa sesuai dengan PO #{{ str_pad($penerimaan->idpengadaan, 6, '0', STR_PAD_LEFT) }}</li>
                <li>Barang dalam kondisi baik dan sesuai spesifikasi</li>
                <li>Dokumen ini merupakan bukti sah penerimaan barang</li>
                <li>Stock inventory telah diupdate sesuai dengan barang yang diterima</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section clearfix">
            <div class="signature-box">
                <h4>Diterima Oleh:</h4>
                <div class="signature-line">
                    <strong>{{ $penerimaan->username ?? '_____________' }}</strong><br>
                    <small>Warehouse Staff</small>
                </div>
            </div>
            <div class="signature-box">
                <h4>Diperiksa Oleh:</h4>
                <div class="signature-line">
                    <strong>_________________</strong><br>
                    <small>QC Inspector</small>
                </div>
            </div>
            <div class="signature-box">
                <h4>Disetujui Oleh:</h4>
                <div class="signature-line">
                    <strong>_________________</strong><br>
                    <small>Warehouse Manager</small>
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
