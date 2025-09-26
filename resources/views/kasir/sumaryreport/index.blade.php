<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Summary Report</title>
    <style>
        body {
            font-family: monospace, sans-serif;
            font-size: 12px;
        }

        .center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        td,
        th {
            padding: 2px 0;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="center">
        <strong>{{ $outlet }}</strong><br>
        {{ $alamat ?? '' }}<br>
        <div class="line"></div>
        <strong>SUMMARY REPORT</strong><br>
        {{ $report['tanggal'] ?? '' }}<br>
        Kasir : {{ $report['kasir'] ?? '' }}<br>
    </div>

    <div class="line"></div>

    <table>
        <tr>
            <td class="left">SALDO AWAL KASIR</td>
            <td class="right">Rp {{ number_format($report['saldo_awal'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="left">1. PENJUALAN NON TUNAI</td>
            <td class="right">Rp {{ number_format($report['penjualan_non_tunai'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="left">2. PENJUALAN TUNAI</td>
            <td class="right">Rp {{ number_format($report['penjualan_tunai'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td class="left">TOTAL PENJUALAN + SALDO AWAL</td>
            <td class="right">Rp {{ number_format($report['total_penjualan'] + $report['saldo_awal'], 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <strong>RINCIAN TRANSAKSI NON TUNAI :</strong>
    <table>
        <tr>
            <th class="left">JENIS</th>
            <th class="left">NO INVOICE</th>
            <th class="right">NOMINAL</th>
        </tr>
        @foreach ($report['detail_non_tunai'] as $item)
            <tr>
                <td class="left">{{ $item['jenis'] }}</td>
                <td class="left">{{ $item['no_invoice'] }}</td>
                <td class="right">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td class="left">TOTAL NON TUNAI</td>
            <td class="right">Rp {{ number_format($report['total_non_tunai'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="left">SETORAN TUNAI</td>
            <td class="right">Rp {{ number_format($report['setoran_tunai'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="left">BATAL</td>
            <td class="right">Rp {{ number_format($report['batal'], 0, ',', '.') }}</td>
        </tr>
    </table>
</body>

</html>
