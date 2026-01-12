<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barang - {{ $barang->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .label-container {
            background: white;
            width: 10cm;
            height: 7cm;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 1px;
        }

        .label-title {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .content {
            flex: 1;
            display: flex;
            gap: 12px;
        }

        .qr-section {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 4px;
            background: white;
        }

        .info-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }

        .product-name {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
        }

        .info-label {
            color: #6b7280;
            font-weight: 600;
            min-width: 50px;
        }

        .info-value {
            color: #1f2937;
            font-weight: 500;
        }

        .price {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin-top: 4px;
        }

        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            color: #6b7280;
        }

        .product-id {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #4f46e5;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                display: block;
            }

            .label-container {
                box-shadow: none;
                page-break-after: always;
                margin: 0;
            }

            @page {
                size: 10cm 7cm;
                margin: 0;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .print-button:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Print Label
    </button>

    <div class="label-container">
        <div class="header">
            <div class="company-name">INDOAPRIL</div>
            <div class="label-title">Product Label</div>
        </div>

        <div class="content">
            <div class="qr-section">
                <img src="{{ route('barang.qrcode', $barang->idbarang) }}" alt="QR Code" class="qr-code">
            </div>

            <div class="info-section">
                <div class="product-name">{{ $barang->nama }}</div>

                <div class="info-row">
                    <span class="info-label">Jenis:</span>
                    <span class="info-value">
                        @if($barang->jenis === 'M') Makanan
                        @elseif($barang->jenis === 'N') Minuman
                        @elseif($barang->jenis === 'A') ATK
                        @elseif($barang->jenis === 'K') Kebersihan
                        @elseif($barang->jenis === 'B') Bahan Baku
                        @else {{ $barang->jenis }}
                        @endif
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Satuan:</span>
                    <span class="info-value">{{ $barang->nama_satuan ?? '-' }}</span>
                </div>

                <div class="price">Rp {{ number_format($barang->harga, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="footer">
            <div class="product-id">ID: {{ str_pad($barang->idbarang, 6, '0', STR_PAD_LEFT) }}</div>
            <div>{{ date('d/m/Y') }}</div>
        </div>
    </div>
</body>
</html>
