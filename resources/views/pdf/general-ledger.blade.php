<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Besar - {{ $entityName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; background: #fff; margin: 40px; line-height: 1.5; }
        .doc-header { text-align: center; margin-bottom: 20px; }
        .doc-header .entity  { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .doc-header .title   { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .doc-header .periode { font-size: 11px; }
        .doc-sub { margin-bottom: 10px; font-size: 11px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px 8px; font-size: 11px; vertical-align: middle; }
        .data-table th { text-align: center; font-weight: bold; text-transform: uppercase; background-color: #f4f4f5; }
        .row-subtotal td { font-weight: bold; background-color: #f4f4f5; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .footer-sig { width: 100%; margin-top: 40px; }
        .sig-box { float: right; text-align: center; width: 250px; }
        .sig-name { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; margin-bottom: 2px; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div class="entity">{{ $entityName }}</div>
        <div class="title">BUKU BESAR - {{ $selectedAccount->name }}</div>
        <div class="period">{{ $printDate }}</div>
    </div>
    <div class="doc-sub">
        Kode Akun: {{ $selectedAccount->code }} | Saldo Normal: {{ strtoupper($normalBalance) }} | Saldo Awal: Rp {{ number_format($openingBalance, 0, ',', '.') }}
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:12%">Tanggal</th>
                <th style="width:13%">Nomor Bukti</th>
                <th style="width:30%">Keterangan</th>
                <th style="width:15%" class="text-right">Debit</th>
                <th style="width:15%" class="text-right">Kredit</th>
                <th style="width:15%" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = $openingBalance; @endphp
            @forelse($transactions as $journal)
                @php
                    if($normalBalance === 'debit') {
                        $runningBalance += $journal->debit - $journal->credit;
                    } else {
                        $runningBalance += $journal->credit - $journal->debit;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $journal->transaction_date->translatedFormat('d F Y') }}</td>
                    <td class="font-mono text-center">{{ $journal->voucher_number }}</td>
                    <td>
                        {{ $journal->description }}
                        @if($journal->businessUnit)
                            <br><small>({{ $journal->businessUnit->name }})</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $journal->debit > 0 ? number_format($journal->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $journal->credit > 0 ? number_format($journal->credit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi.</td>
                </tr>
            @endforelse
            @if(count($transactions) > 0)
                <tr class="row-subtotal">
                    <td colspan="3" class="text-right">MUTASI BULAN INI</td>
                    <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <div class="footer-sig">
        <div class="sig-box">
            <p style="margin-bottom: 60px;">TEJA, {{ $signatureDate }}</p>
            <div><span class="sig-name">{{ $signatory }}</span></div>
            <p>{{ $position }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
