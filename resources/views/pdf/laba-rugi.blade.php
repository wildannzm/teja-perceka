<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laba Rugi - {{ $namaEntitas }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 40px;
            line-height: 1.5;
        }

        /* ── HEADER ── */
        .doc-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-header .entity  { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .doc-header .title   { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .doc-header .periode { font-size: 11px; }

        /* ── TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
            vertical-align: middle;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Group header rows */
        .row-group-header td {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 3px 8px;
            background-color: #f4f4f5;
        }

        /* Subtotal rows */
        .row-subtotal td {
            font-weight: bold;
        }

        /* LABA BERSIH */
        .row-laba td {
            font-weight: bold;
            font-size: 12px;
            background-color: #f4f4f5;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .indent { padding-left: 20px !important; }

        /* ── FOOTER ── */
        .footer-sig {
            width: 100%;
            margin-top: 40px;
        }
        .sig-box {
            float: right;
            text-align: center;
            width: 250px;
        }
        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            display: inline-block;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <div class="doc-header">
        <div class="entity">{{ $namaEntitas }}</div>
        <div class="title">LABA RUGI</div>
        <div class="periode">{{ $tanggalCetak }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:14%">KODE AKUN</th>
                <th style="width:40%">NAMA AKUN</th>
                <th style="width:23%" class="text-right">JUMLAH</th>
                <th style="width:23%" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>

            {{-- ── PENDAPATAN USAHA ── --}}
            <tr class="row-group-header">
                <td colspan="4">PENDAPATAN USAHA</td>
            </tr>
            @foreach($pendapatanRows as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td class="indent">{{ $row->nama }}</td>
                    <td class="text-right">
                        {{ $row->jumlah != 0 ? number_format($row->jumlah, 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
            <tr class="row-subtotal">
                <td colspan="3" class="text-right">JUMLAH PENDAPATAN</td>
                <td class="text-right">{{ $totalPendapatan != 0 ? number_format($totalPendapatan, 0, ',', '.') : '-' }}</td>
            </tr>

            {{-- ── HARGA POKOK PENJUALAN ── --}}
            <tr class="row-group-header">
                <td colspan="4">HARGA POKOK PENJUALAN</td>
            </tr>
            @foreach($hppRows as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td class="indent">{{ $row->nama }}</td>
                    <td class="text-right">
                        {{ $row->jumlah != 0 ? number_format($row->jumlah, 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
            <tr class="row-subtotal">
                <td colspan="3" class="text-right">JUMLAH HPP</td>
                <td class="text-right">{{ $totalHpp != 0 ? number_format($totalHpp, 0, ',', '.') : '-' }}</td>
            </tr>
            <tr class="row-subtotal">
                <td colspan="3" class="text-right">LABA KOTOR</td>
                <td class="text-right">{{ $labaKotor != 0 ? number_format($labaKotor, 0, ',', '.') : '-' }}</td>
            </tr>

            {{-- ── BIAYA USAHA ── --}}
            <tr class="row-group-header">
                <td colspan="4">BIAYA USAHA</td>
            </tr>
            @foreach($bebanRows as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td class="indent">{{ $row->nama }}</td>
                    <td class="text-right">
                        {{ $row->jumlah != 0 ? number_format($row->jumlah, 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
            <tr class="row-subtotal">
                <td colspan="3" class="text-right">JUMLAH BIAYA USAHA</td>
                <td class="text-right">{{ $totalBeban != 0 ? number_format($totalBeban, 0, ',', '.') : '-' }}</td>
            </tr>

            {{-- ── PENDAPATAN & BIAYA LAIN-LAIN ── --}}
            <tr class="row-group-header">
                <td colspan="4">PENDAPATAN & BIAYA LAIN-LAIN</td>
            </tr>
            @foreach($pendapatanLainRows as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td class="indent">{{ $row->nama }}</td>
                    <td class="text-right">
                        {{ $row->jumlah != 0 ? number_format($row->jumlah, 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
            @foreach($bebanLainRows as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td class="indent">{{ $row->nama }}</td>
                    <td class="text-right">
                        {{ $row->jumlah != 0 ? number_format($row->jumlah, 0, ',', '.') : '-' }}
                    </td>
                    <td></td>
                </tr>
            @endforeach

            {{-- ── LABA BERSIH ── --}}
            <tr class="row-laba">
                <td colspan="3" class="text-right">LABA BERSIH</td>
                <td class="text-right">{{ $labaBersih != 0 ? number_format($labaBersih, 0, ',', '.') : '-' }}</td>
            </tr>

        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-box">
            <p style="margin-bottom: 60px;">TEJA, {{ $tanggalTtd }}</p>
            <div>
                <span class="sig-name">{{ $penandatangan }}</span>
            </div>
            <p>{{ $jabatan }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
