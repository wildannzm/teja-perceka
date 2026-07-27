<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neraca Saldo - {{ $namaEntitas }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; background: #fff; margin: 40px; line-height: 1.5; }
        .doc-header { text-align: center; margin-bottom: 20px; }
        .doc-header .entity  { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .doc-header .title   { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .doc-header .periode { font-size: 11px; }
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
        .status { margin-bottom: 10px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div class="entity">{{ $namaEntitas }}</div>
        <div class="title">NERACA SALDO</div>
        <div class="periode">Per Akhir Bulan: {{ $periodeLabel }}</div>
    </div>
    <div class="status">
        Status: {{ $totalDebit === $totalKredit ? 'SEIMBANG (BALANCE)' : 'TIDAK SEIMBANG' }}
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:15%">KODE AKUN</th>
                <th style="width:45%">NAMA AKUN</th>
                <th style="width:20%" class="text-right">DEBIT (Rp)</th>
                <th style="width:20%" class="text-right">KREDIT (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($neracaData as $row)
                <tr>
                    <td class="font-mono text-center">{{ $row->kode }}</td>
                    <td>{{ $row->nama }}</td>
                    <td class="text-right">{{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data saldo akun hingga periode yang dipilih.</td>
                </tr>
            @endforelse
            @if(count($neracaData) > 0)
                <tr class="row-subtotal">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <div class="footer-sig">
        <div class="sig-box">
            <p style="margin-bottom: 60px;">TEJA, {{ $tanggalTtd }}</p>
            <div><span class="sig-name">{{ $penandatangan }}</span></div>
            <p>{{ $jabatan }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
