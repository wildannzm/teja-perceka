<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alokasi Laba - {{ $namaEntitas }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
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
        .doc-header .title   { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 4px; margin-bottom: 4px; }
        .doc-header .periode { font-size: 12px; }

        /* ── TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            vertical-align: middle;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f4f4f5;
        }

        .text-right  { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }

        /* Baris LABA BERSIH awal */
        .row-laba-bersih td {
            font-weight: bold;
            font-size: 13px;
            background-color: #e5f3e5;
        }

        /* Baris Pengurang (indent) */
        .row-pengurang td {
            padding-left: 22px;
        }

        /* Baris sub-total pengurang / laba setelah pengurang */
        .row-laba-setelah td {
            font-weight: bold;
            background-color: #f4f4f5;
            border-top: 2px solid #555;
            border-bottom: 2px solid #555;
        }

        /* Header section AD/ART */
        .row-section-header td {
            font-weight: bold;
            background-color: #eeeeee;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        /* Baris AD/ART (indent) */
        .row-ad-art td {
            padding-left: 22px;
        }

        /* Total AD/ART */
        .row-total td {
            font-weight: bold;
            background-color: #f4f4f5;
            border-top: 2px solid #000;
        }

        /* ── FOOTER ── */
        .footer-sig {
            width: 100%;
            margin-top: 50px;
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
        <div class="title">LAPORAN ALOKASI LABA</div>
        <div class="periode">{{ $periodeLabel }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">NO</th>
                <th style="width: 42%; text-align: left;">KETERANGAN</th>
                <th style="width: 20%;" class="text-right">PERSENTASE</th>
                <th style="width: 30%;" class="text-right">NOMINAL (Rp)</th>
            </tr>
        </thead>
        <tbody>
            {{-- Net profit row --}}
            <tr class="row-laba-bersih">
                <td class="text-center">—</td>
                <td>LABA BERSIH</td>
                <td class="text-right font-mono">100%</td>
                <td class="text-right font-mono">{{ number_format($labaBersih, 0, ',', '.') }}</td>
            </tr>

            {{-- Deduction section --}}
            @forelse($pengurangRows as $index => $row)
                <tr class="row-pengurang">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                    <td class="text-right font-mono">{{ number_format($row['persentase'], 2, ',', '.') }}%</td>
                    <td class="text-right font-mono">({{ number_format($row['nominal'], 0, ',', '.') }})</td>
                </tr>
            @empty
                <tr class="row-pengurang">
                    <td class="text-center">—</td>
                    <td colspan="3" style="font-style: italic; color: #555;">Tidak ada pos Pengurang.</td>
                </tr>
            @endforelse

            {{-- Profit row after deductions --}}
            <tr class="row-laba-setelah">
                <td class="text-center">—</td>
                <td>Laba/Rugi Bersih setelah Pengurang</td>
                <td class="text-right font-mono">—</td>
                <td class="text-right font-mono">{{ number_format($labaSetelahPengurang, 0, ',', '.') }}</td>
            </tr>

            {{-- Articles of Association header section --}}
            <tr class="row-section-header">
                <td colspan="4">Alokasi Laba Bersih sesuai AD/ART</td>
            </tr>

            {{-- Articles of Association section --}}
            @forelse($adArtRows as $index => $row)
                <tr class="row-ad-art">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                    <td class="text-right font-mono">{{ number_format($row['persentase'], 2, ',', '.') }}%</td>
                    <td class="text-right font-mono">{{ number_format($row['nominal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="row-ad-art">
                    <td class="text-center">—</td>
                    <td colspan="3" style="font-style: italic; color: #555;">Tidak ada pos AD/ART.</td>
                </tr>
            @endforelse

            {{-- Total Articles of Association allocation --}}
            @if(count($adArtRows) > 0)
                <tr class="row-total">
                    <td colspan="2" class="text-right">JUMLAH ALOKASI AD/ART</td>
                    <td class="text-right font-mono">{{ number_format($totalAdArtPersen, 2, ',', '.') }}%</td>
                    <td class="text-right font-mono">{{ number_format($totalAdArt, 0, ',', '.') }}</td>
                </tr>
            @endif
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
