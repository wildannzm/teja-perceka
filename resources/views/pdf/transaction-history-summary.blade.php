<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Umum - {{ $unit ? $unit->name : 'Semua Unit' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif; /* Standard font for formal financial reports */
            font-size: 11px;
            color: #000000;
            line-height: 1.5;
            background: #ffffff;
            margin: 40px;
        }

        /* === DOCUMENT HEADER === */
        .doc-header {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .doc-header .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .doc-header .report-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .doc-header .report-period {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* === GENERAL JOURNAL TABLE === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000000;
            padding: 4px 6px; /* Compact padding matching spreadsheet format */
            font-size: 11px;
            vertical-align: middle;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            /* Table header styling */
        }

        .data-table td {
            text-align: left;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .font-bold {
            font-weight: bold;
        }

        /* === FOOTER === */
        .doc-footer {
            margin-top: 24px;
            font-size: 10px;
            text-align: left;
        }
    </style>
</head>

<body>

    <div class="doc-header">
        <div class="company-name">{{ $unit ? strtoupper($unit->name) : 'BUMDESA TEJA PERCEKA' }}</div>
        <div class="report-title">JURNAL UMUM</div>
        <div class="report-period">
            @if ($period === 'Laporan Jurnal Umum')
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            @else
                {{ $period }}
            @endif
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:10%">TANGGAL</th>
                <th style="width:15%">BUKTI TRANSAKSI</th>
                <th style="width:30%">KETERANGAN</th>
                <th style="width:10%">KODE AKUN</th>
                <th style="width:9%" class="text-center">KODE BANTU</th>
                <th style="width:13%" class="text-center">DEBET</th>
                <th style="width:13%" class="text-center">KREDIT</th>
            </tr>
        </thead>
        <tbody>
            @if ($groups->isNotEmpty())
                @foreach ($groups as $group)
                    @php
                        $groupRows = is_array($group) ? $group['rows'] : $group;
                        $groupDisplayDate = is_array($group) ? ($group['displayDate'] ?? $displayDate) : $displayDate;
                        $groupFirst = $groupRows->first();
                    @endphp
                    @foreach ($groupRows as $row)
                        <tr>
                            <td class="text-center">{{ \Carbon\Carbon::parse($groupDisplayDate)->translatedFormat('d F Y') }}</td>
                            <td class="text-center">{{ $groupFirst->firstVoucher }}</td>
                            <td>{{ $groupFirst->description }}</td>
                            <td class="text-center">{{ $row->code ?? '-' }}</td>
                            <td class="text-center"></td>
                            <td class="text-right">
                                {{ $row->totalDebit > 0 ? number_format($row->totalDebit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $row->totalCredit > 0 ? number_format($row->totalCredit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="font-style: italic;">Belum ada catatan jurnal umum untuk periode ini.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right font-bold">TOTAL KESELURUHAN</td>
                <td class="text-right font-bold">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($totalCredit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
