<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Retur Barang #{{ $retur->idretur }}</title>
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
            border-bottom: 2px solid #ea580c;
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
            color: #ea580c;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        .retur-title {
            font-size: 20px;
            color: #ea580c;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .retur-subtitle {
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
        }
        .retur-number {
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
            color: #ea580c;
            margin-bottom: 5px;
            border-bottom: 1px solid #ea580c;
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
        .alert-box {
            clear: both;
            margin-bottom: 12px;
            padding: 8px 10px;
            background-color: #fef3c7;
            border-left: 3px solid #f59e0b;
            font-size: 9px;
        }
        .alert-box strong {
            color: #92400e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        thead {
            background-color: #ea580c;
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
        .reason-cell {
            background-color: #fee2e2;
            font-style: italic;
            color: #991b1b;
            font-size: 8px;
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
            background-color: #ea580c;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .notes {
            clear: both;
            margin-top: 15px;
            padding: 10px;
            background-color: #fff7ed;
            border-left: 3px solid #ea580c;
            font-size: 8px;
        }
        .notes h3 {
            font-size: 9px;
            margin-bottom: 5px;
            color: #ea580c;
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
                <div class="retur-title">SURAT RETUR</div>
                <div class="retur-subtitle">Return Goods Note</div>
                <div class="retur-number">RETUR #{{ str_pad($retur->idretur, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Alert Box -->
        <div class="alert-box">
            <strong>⚠ DOKUMEN RETUR:</strong> Surat ini merupakan bukti pengembalian barang kepada vendor akibat kerusakan atau ketidaksesuaian.
        </div>

        <!-- Info Section -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>Informasi Retur:</h3>
                <div class="info-row">
                    <span class="info-label">Tanggal Retur:</span>
                    <span>{{ date('d F Y', strtotime($retur->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Waktu:</span>
                    <span>{{ date('H:i:s', strtotime($retur->created_at)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat oleh:</span>
                    <span>{{ $retur->username ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ref. Penerimaan:</span>
                    <span><strong>#{{ str_pad($retur->idpenerimaan, 6, '0', STR_PAD_LEFT) }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ref. PO:</span>
                    <span><strong>#{{ str_pad($retur->idpengadaan, 6, '0', STR_PAD_LEFT) }}</strong></span>
                </div>
            </div>
            <div class="info-box">
                <h3>Kepada Vendor:</h3>
                <div class="info-row">
                    <strong>{{ $retur->nama_vendor }}</strong>
                </div>
                @if(isset($retur->alamat) && $retur->alamat)
                <div class="info-row">
                    {{ $retur->alamat }}
                </div>
                @endif
                @if(isset($retur->telp) && $retur->telp)
                <div class="info-row">
                    Telp: {{ $retur->telp }}
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">NO</th>
                    <th style="width: 28%;">NAMA BARANG</th>
                    <th style="width: 10%;">JENIS</th>
                    <th style="width: 8%;">SATUAN</th>
                    <th style="width: 10%;" class="text-right">QTY TERIMA</th>
                    <th style="width: 10%;" class="text-right">QTY RETUR</th>
                    <th style="width: 12%;" class="text-right">HARGA SATUAN</th>
                    <th style="width: 12%;" class="text-right">NILAI RETUR</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQtyRetur = 0;
                    $totalNilaiRetur = 0;
                @endphp
                @foreach($details as $index => $detail)
                @php
                    $nilaiRetur = $detail->jumlah * $detail->harga_satuan_terima;
                    $totalQtyRetur += $detail->jumlah;
                    $totalNilaiRetur += $nilaiRetur;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_barang }}</td>
                    <td>{{ $detail->jenis }}</td>
                    <td class="text-center">{{ $detail->nama_satuan }}</td>
                    <td class="text-right">{{ number_format($detail->jumlah_penerimaan_asal, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ number_format($detail->jumlah, 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan_terima, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($nilaiRetur, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="8" class="reason-cell">
                        <strong>Alasan Retur:</strong> {{ $detail->alasan }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section clearfix">
            <div class="totals">
                <table>
                    <tr>
                        <td class="label">Total Qty Retur:</td>
                        <td class="value"><strong>{{ number_format($totalQtyRetur, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr class="grand-total">
                        <td class="label" style="padding: 8px;">TOTAL NILAI RETUR:</td>
                        <td class="value" style="padding: 8px;">Rp {{ number_format($totalNilaiRetur, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes">
            <h3>Catatan Penting:</h3>
            <ul>
                <li>Barang yang diretur telah dikurangi dari stock inventory sistem IndoApril</li>
                <li>Vendor diharapkan mengirimkan penggantian atau melakukan refund sesuai kesepakatan</li>
                <li>Dokumen ini merupakan bukti sah pengembalian barang</li>
                <li>Harap konfirmasi penerimaan retur dan tindak lanjut dalam 3 hari kerja</li>
                <li>Barang retur harap diambil maksimal 7 hari kerja sejak tanggal retur</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section clearfix">
            <div class="signature-box">
                <h4>Dibuat Oleh:</h4>
                <div class="signature-line">
                    <strong>{{ $retur->username ?? '_____________' }}</strong><br>
                    <small>Staff Purchasing</small>
                </div>
            </div>
            <div class="signature-box">
                <h4>Diperiksa Oleh:</h4>
                <div class="signature-line">
                    <strong>_________________</strong><br>
                    <small>Purchasing Manager</small>
                </div>
            </div>
            <div class="signature-box">
                <h4>Disetujui Oleh:</h4>
                <div class="signature-line">
                    <strong>_________________</strong><br>
                    <small>Operations Director</small>
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
