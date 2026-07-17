<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Kas - {{ $unit->nama }}</title>
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
            border-bottom: 3px solid #14532d;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .doc-header .org-name {
            font-size: 20px;
            font-weight: 700;
            color: #14532d;
            letter-spacing: -0.3px;
        }
        .doc-header .org-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        .doc-header .report-title {
            font-size: 14px;
            font-weight: 600;
            color: #166534;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #d1fae5;
        }

        /* === INFO META === */
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
            font-size: 12px;
            color: #374151;
        }
        .meta-table td:first-child {
            width: 100px;
            font-weight: 600;
            color: #111827;
        }
        .meta-table td:nth-child(2) {
            width: 16px;
            color: #9ca3af;
        }

        /* === TABEL DATA BUKU KAS === */
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
        .text-saldo { font-weight: 700; background-color: #f0fdf4; }

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
        <div class="org-name">BUMDes Teja Perceka</div>
        <div class="org-subtitle">Badan Usaha Milik Desa</div>
        <div class="report-title">Buku Kas &mdash; {{ $unit->nama }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td>Unit Wisata</td>
            <td>:</td>
            <td>{{ $unit->nama }}</td>
        </tr>
        @if ($transactions->isNotEmpty() && $transactions->first()->kodeAkun)
        <tr>
            <td>Akun</td>
            <td>:</td>
            <td>{{ $transactions->first()->kodeAkun->nama }} - <span class="font-mono">{{ $transactions->first()->kodeAkun->kode }}</span></td>
        </tr>
        @endif
        <tr>
            <td>Periode</td>
            <td>:</td>
            <td>{{ $periode }}</td>
        </tr>
        <tr>
            <td>Total Entri</td>
            <td>:</td>
            <td>{{ $transactions->count() }} catatan</td>
        </tr>
    </table>

    @if ($transactions->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:12%">Tanggal</th>
                    <th style="width:16%">Bukti Transaksi</th>
                    <th style="width:34%">Keterangan</th>
                    <th style="width:12%" class="text-right">Debet</th>
                    <th style="width:12%" class="text-right">Kredit</th>
                    <th style="width:14%" class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $trx)
                    <tr>
                        <td>{{ $trx->tanggal->format('d/m/Y') }}</td>
                        <td class="font-mono">{{ $trx->nomor_bukti }}</td>
                        <td>{{ $trx->keterangan }}</td>
                        <td class="text-right text-debet">
                            {{ $trx->debet > 0 ? number_format($trx->debet, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-kredit">
                            {{ $trx->kredit > 0 ? number_format($trx->kredit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-saldo">
                            {{ number_format($trx->saldo_berjalan, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">SALDO AKHIR PERIODE INI</td>
                    <td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="empty-state">Belum ada catatan buku kas untuk periode ini.</div>
    @endif

    <div class="doc-footer">
        Dicetak pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>

</body>
</html>
