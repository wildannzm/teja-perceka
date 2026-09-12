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
        <div class="periode">Periode: {{ $periodeLabel }}</div>
    </div>
    <div class="status">
        Status: {{ $totalAktiva === $totalPasiva ? 'SEIMBANG (BALANCE)' : 'TIDAK SEIMBANG' }}
    </div>

    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 50%; border: 1px solid #000; padding: 6px; text-align: center; background-color: #f4f4f5; text-transform: uppercase;">AKTIVA</th>
                <th style="width: 50%; border: 1px solid #000; padding: 6px; text-align: center; background-color: #f4f4f5; text-transform: uppercase;">PASIVA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align: top; border: 1px solid #000; padding: 0;">
                    <!-- ASSETS SIDE -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td colspan="3" style="padding: 4px; font-weight: bold;">1-1000 AKTIVA LANCAR</td></tr>
                        @foreach($aktivaLancar as $item)
                        <tr>
                            <td class="font-mono" style="width: 15%; padding: 4px; padding-left: 8px;">{{ $item->kode }}</td>
                            <td style="width: 55%; padding: 4px;">{{ $item->nama }}</td>
                            <td class="text-right" style="width: 30%; padding: 4px; padding-right: 8px;">{{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" style="padding: 6px; font-weight: bold; text-align: center; background-color: #f9fafb;">JUMLAH AKTIVA LANCAR</td>
                            <td class="text-right" style="padding: 6px; padding-right: 8px; font-weight: bold; background-color: #f9fafb;">{{ number_format($totalAktivaLancar, 0, ',', '.') }}</td>
                        </tr>
                        
                        <tr><td colspan="3" style="padding: 4px; font-weight: bold;">1-2000 AKTIVA TIDAK LANCAR</td></tr>
                        @foreach($aktivaTetap as $item)
                        <tr>
                            <td class="font-mono" style="width: 15%; padding: 4px; padding-left: 8px;">{{ $item->kode }}</td>
                            <td style="width: 55%; padding: 4px;">{{ $item->nama }}</td>
                            <td class="text-right" style="width: 30%; padding: 4px; padding-right: 8px;">{{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" style="padding: 6px; font-weight: bold; text-align: center; background-color: #f9fafb;">JUMLAH AKTIVA TETAP</td>
                            <td class="text-right" style="padding: 6px; padding-right: 8px; font-weight: bold; background-color: #f9fafb;">{{ number_format($totalAktivaTetap, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; border: 1px solid #000; padding: 0;">
                    <!-- LIABILITIES & EQUITY SIDE -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td colspan="3" style="padding: 4px; font-weight: bold;">2-0000 KEWAJIBAN</td></tr>
                        <tr><td colspan="3" style="padding: 4px; font-weight: bold; padding-left: 8px;">2-1000 KEWAJIBAN JANGKA PENDEK</td></tr>
                        @foreach($kewajibanPendek as $item)
                        <tr>
                            <td class="font-mono" style="width: 15%; padding: 4px; padding-left: 12px;">{{ $item->kode }}</td>
                            <td style="width: 55%; padding: 4px;">{{ $item->nama }}</td>
                            <td class="text-right" style="width: 30%; padding: 4px; padding-right: 8px;">{{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach

                        <tr><td colspan="3" style="padding: 4px; font-weight: bold; padding-left: 8px;">2-2000 KEWAJIBAN JANGKA PANJANG</td></tr>
                        @foreach($kewajibanPanjang as $item)
                        <tr>
                            <td class="font-mono" style="width: 15%; padding: 4px; padding-left: 12px;">{{ $item->kode }}</td>
                            <td style="width: 55%; padding: 4px;">{{ $item->nama }}</td>
                            <td class="text-right" style="width: 30%; padding: 4px; padding-right: 8px;">{{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach

                        <tr>
                            <td colspan="2" style="padding: 6px; font-weight: bold; text-align: center; background-color: #f9fafb;">JUMLAH KEWAJIBAN</td>
                            <td class="text-right" style="padding: 6px; padding-right: 8px; font-weight: bold; background-color: #f9fafb;">{{ number_format($totalKewajiban, 0, ',', '.') }}</td>
                        </tr>
                        
                        <tr><td colspan="3" style="padding: 4px; font-weight: bold;">3-0000 EKUITAS</td></tr>
                        @foreach($ekuitas as $item)
                        <tr>
                            <td class="font-mono" style="width: 15%; padding: 4px; padding-left: 8px;">{{ $item->kode }}</td>
                            <td style="width: 55%; padding: 4px; {{ $item->nama === 'LABA BERSIH' ? 'text-transform: uppercase;' : '' }}">{{ $item->nama }}</td>
                            <td class="text-right" style="width: 30%; padding: 4px; padding-right: 8px;">{{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        
                        <tr>
                            <td colspan="2" style="padding: 6px; font-weight: bold; text-align: center; background-color: #f9fafb;">JUMLAH EKUITAS</td>
                            <td class="text-right" style="padding: 6px; padding-right: 8px; font-weight: bold; background-color: #f9fafb;">{{ number_format($totalEkuitas, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #78a2a8;">
                <td style="border: 1px solid #000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 70%; padding: 8px; font-weight: bold; text-align: center; text-transform: uppercase;">TOTAL AKTIVA</td>
                            <td class="text-right" style="width: 30%; padding: 8px; padding-right: 8px; font-weight: bold;">{{ number_format($totalAktiva, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="border: 1px solid #000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 70%; padding: 8px; font-weight: bold; text-align: center; text-transform: uppercase;">TOTAL PASIVA</td>
                            <td class="text-right" style="width: 30%; padding: 8px; padding-right: 8px; font-weight: bold;">{{ number_format($totalPasiva, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
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
