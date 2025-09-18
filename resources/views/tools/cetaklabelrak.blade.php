<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 5mm;
            font-family: Arial, sans-serif;
        }

        .row {
            display: flex;
            margin-bottom: 5mm;
        }

        .label {
            width: 90mm;
            height: 40mm;
            border: 1px solid #000;
            /* margin: 5mm; */
            padding: 3mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .info {
            font-size: 10pt;
            line-height: 1.3;
        }

        .harga-list {
            margin-top: 2mm;
            font-size: 9pt;
            font-weight: bold;
        }

        .barcode {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            margin-top: auto;
            transform: rotate(-90deg);
            position: absolute;
            right: -40px;
            top: 60px;
            text-align: center;
        }

        .barcode img {
            width: 40mm;
            /* lebar barcode */
            height: 10mm;
            /* tinggi barcode */
            /* tinggi barcode */
        }

        .barcode small {
            font-size: 8pt;
            margin-top: 1mm;
            text-align: center;
        }
    </style>
</head>

<body>
    @foreach (array_chunk($produkList->toArray(), 2) as $chunk)
        <div class="row">
            @foreach ($chunk as $p)
                <div class="label">
                    <div class="info">
                        <strong>{{ $p['nama_barang'] }}</strong><br>
                        {{ $p['merek'] }} | {{ $p['satuan'] }}
                        <div class="harga-list">
                            @foreach ($p['harga_jual'] as $hj)
                                <div>Qty {{ $hj['qty'] }} : Rp {{ number_format($hj['harga_jual'], 0, ',', '.') }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="barcode">
                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($p['kode'], 'C128') }}" alt="barcode">
                        <br>
                        <small>{{ $p['kode'] }}</small>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>

</html>
