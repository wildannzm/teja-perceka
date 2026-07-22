<!DOCTYPE html>
<html lang="id">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Jurnal Umum - {{ $unit ? $unit->nama : 'Semua Unit' }}</title>
 <style>
 * { box-sizing: border-box; margin: 0; padding: 0; }

 body {
 font-family: Arial, sans-serif;
 font-size: 11px;
 color: #000000;
 line-height: 1.5;
 background: #ffffff;
 margin: 30px; /* Memberikan space agar tidak mepet ke tepi kertas */
 }

 /* === HEADER DOKUMEN === */
 .doc-header {
 text-align: left;
 margin-bottom: 24px;
 margin-top: 10px;
 }
 .doc-header div {
 font-weight: bold;
 margin-bottom: 2px;
 text-transform: uppercase;
 }

 /* === TABEL DATA JURNAL UMUM === */
 .data-table {
 width: 100%;
 border-collapse: collapse;
 margin-bottom: 20px;
 }
 .data-table th, .data-table td {
 border: 1px solid #000000;
 padding: 6px;
 font-size: 11px;
 vertical-align: middle;
 }
 .data-table th {
 text-align: center;
 font-weight: bold;
 text-transform: uppercase;
 }
 .data-table td {
 text-align: left;
 }
 .text-right { text-align: right !important; }
 .text-center { text-align: center !important; }
 .font-bold { font-weight: bold; }
 
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
 <div>{{ $unit ? 'WISATA ' . strtoupper($unit->nama) : 'SEMUA UNIT BUMDES' }}</div>
 <div>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
 <div>JURNAL UMUM</div>
 <div>Jam : {{ \Carbon\Carbon::now()->format('H:i') }}</div>
 <div>{{ $periode }}</div>
 </div>

 <table class="data-table">
 <thead>
 <tr>
 <th style="width:10%">TANGGAL</th>
 <th style="width:14%">BUKTI TRANSAKSI</th>
 <th style="width:25%">KETERANGAN</th>
 <th style="width:12%">POS AKUN</th>
 <th style="width:9%" class="text-center">KODE BANTU</th>
 <th style="width:15%" class="text-right">Debit</th>
 <th style="width:15%" class="text-right">KREDIT</th>
 </tr>
 </thead>
 <tbody>
 @if ($transactions->isNotEmpty())
 @foreach ($transactions as $trx)
 <tr>
 <td class="text-center">{{ $trx->tanggal->format('d-m-y') }}</td>
 <td class="text-center">{{ $trx->nomor_bukti }}</td>
 <td>{{ $trx->keterangan }}</td>
 <td>{{ $trx->kodeAkun->nama ?? '-' }}<br>{{ $trx->kodeAkun->kode ?? '-' }}</td>
 <td class="text-center">0</td>
 <td class="text-right">
 {{ $trx->debet > 0 ? number_format($trx->debet, 0, ',', '.') : '' }}
 </td>
 <td class="text-right">
 {{ $trx->kredit > 0 ? number_format($trx->kredit, 0, ',', '.') : '' }}
 </td>
 </tr>
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
 <td class="text-right font-bold">{{ number_format($totalDebet, 0, ',', '.') }}</td>
 <td class="text-right font-bold">{{ number_format($totalKredit, 0, ',', '.') }}</td>
 </tr>
 </tfoot>
 </table>

</body>
</html>
