<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Umum - {{ $unit->nama }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1c1917;
            line-height: 1.5;
            background: #fff;
        }

        /* === HEADER DOKUMEN === */
        .doc-header {
            text-align: center;
            border-bottom: 3px solid #14532d;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .doc-header .org-name {
            font-size: 20px;
            font-weight: 700;
            color: #14532d;
            letter-spacing: 0.5px;
        }
        .doc-header .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #166534;
            margin-top: 4px;
            letter-spacing: 1px;
        }
        .doc-header .org-subtitle {
            font-size: 13px;
            color: #4b5563;
            margin-top: 4px;
        }

        /* === TABEL DATA JURNAL UMUM === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f0fdf4;
            color: #14532d;
            font-weight: 700;
            font-size: 10px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #bbf7d0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        .data-table tfoot tr td {
            background-color: #dcfce7;
            font-weight: 700;
            color: #14532d;
            font-size: 12px;
            border: 1px solid #bbf7d0;
            padding: 8px 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .font-mono { font-family: 'Courier New', Courier, monospace; font-size: 10px; color: #4b5563; }
        .text-debet { color: #15803d; font-weight: 600; }
        .text-kredit { color: #dc2626; font-weight: 600; }

        /* === FOOTER === */
        .doc-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
        }

        /* === KOSONG === */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="doc-header">
        <div class="org-name">WISATA {{ strtoupper($unit->nama) }}</div>
        <div class="report-title">JURNAL UMUM</div>
        <div class="org-subtitle">{{ $periode }}</div>
    </div>

    @if ($transactions->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:10%">TANGGAL</th>
                    <th style="width:14%">BUKTI TRANSAKSI</th>
                    <th style="width:30%">KETERANGAN</th>
                    <th style="width:10%">KODE AKUN</th>
                    <th style="width:10%" class="text-center">KODE BANTU</th>
                    <th style="width:13%" class="text-right">DEBET</th>
                    <th style="width:13%" class="text-right">KREDIT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $trx)
                    <tr>
                        <td>{{ $trx->tanggal->format('d/m/Y') }}</td>
                        <td class="font-mono">{{ $trx->nomor_bukti }}</td>
                        <td>{{ $trx->keterangan }}</td>
                        <td class="font-mono">{{ $trx->kodeAkun->kode ?? '-' }}</td>
                        <td class="text-center">-</td>
                        <td class="text-right text-debet">
                            {{ $trx->debet > 0 ? number_format($trx->debet, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-kredit">
                            {{ $trx->kredit > 0 ? number_format($trx->kredit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right">{{ number_format($totalDebet, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="empty-state">Belum ada catatan jurnal umum untuk periode ini.</div>
    @endif

    <div class="doc-footer">
        Dicetak pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

</body>
</html>
