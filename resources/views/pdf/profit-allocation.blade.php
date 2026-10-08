<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alokasi Laba - {{ $entityName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #171717;
            background: #fff;
            margin: 40px;
            line-height: 1.5;
        }

        /* ── HEADER ── */
        .doc-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .doc-header .entity  { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-header .title   { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
        .doc-header .periode { font-size: 12px; color: #525252; margin-top: 2px; }

        /* ── TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #525252;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 2px solid #171717;
        }
        .data-table th.c { text-align: center; }

        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e5e5;
            vertical-align: middle;
        }

        .c { text-align: center; }

        /* LABA BERSIH - baris pembuka tebal */
        .row-laba td {
            font-weight: bold;
            font-size: 13px;
            border-bottom: 2px solid #171717;
        }

        /* Baris pos alokasi (indent) */
        .row-item td:first-child {
            padding-left: 24px;
        }

        /* Laba setelah pengurang - penutup seksi */
        .row-setelah td {
            font-weight: bold;
            border-top: 1px solid #a3a3a3;
            border-bottom: 2px solid #171717;
        }

        /* Judul seksi AD/ART */
        .row-section td {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.8px;
            color: #525252;
            padding-top: 16px;
            border-bottom: 1px solid #e5e5e5;
        }

        /* Total AD/ART */
        .row-total td {
            font-weight: bold;
            border-bottom: none;
            border-top: 2px solid #171717;
        }

        .empty {
            font-style: italic;
            color: #737373;
        }

        /* ── FOOTER ── */
        .footer-sig {
            width: 100%;
            margin-top: 48px;
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
        <div class="entity">{{ $entityName }}</div>
        <div class="title">Laporan Alokasi Laba</div>
        <div class="periode">{{ $periodLabel }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="c" style="width: 18%;">%</th>
                <th style="width: 32%;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            {{-- Laba bersih --}}
            <tr class="row-laba">
                <td>LABA BERSIH</td>
                <td class="c">-</td>
                <td>Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
            </tr>

            {{-- Pos pengurang --}}
            @forelse($deductionRows as $row)
                <tr class="row-item">
                    <td>{{ $row['description'] }}</td>
                    <td class="c">{{ number_format($row['percentage'], 2, '.', '') }}%</td>
                    <td>Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">Belum ada pos Pengurang.</td>
                </tr>
            @endforelse

            {{-- Laba setelah pengurang --}}
            <tr class="row-setelah">
                <td>Laba/Rugi Bersih setelah {{ $deductionLabel }}</td>
                <td class="c">-</td>
                <td>Rp {{ number_format($netIncomeAfterDeductions, 0, ',', '.') }}</td>
            </tr>

            {{-- Seksi AD/ART --}}
            <tr class="row-section">
                <td colspan="3">Alokasi Laba Bersih sesuai AD/ART</td>
            </tr>

            @forelse($adArtRows as $row)
                <tr class="row-item">
                    <td>{{ $row['description'] }}</td>
                    <td class="c">{{ number_format($row['percentage'], 2, '.', '') }}%</td>
                    <td>Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">Belum ada pos AD/ART.</td>
                </tr>
            @endforelse

            {{-- Total AD/ART --}}
            @if(count($adArtRows) > 0)
                <tr class="row-total">
                    <td>TOTAL AD/ART</td>
                    <td class="c">{{ number_format($totalAdArtPercent, 2, '.', '') }}%</td>
                    <td>Rp {{ number_format($totalAdArtAmount, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if(count($deductionRows) === 0 && count($adArtRows) === 0)
                <tr>
                    <td colspan="3" class="empty" style="text-align: center; padding: 16px;">
                        Belum ada pos alokasi yang diatur.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-box">
            <p style="margin-bottom: 60px;">TEJA, {{ $signatureDate }}</p>
            <div>
                <span class="sig-name">{{ $signatory }}</span>
            </div>
            <p>{{ $position }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
