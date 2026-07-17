<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\Account;

class DummyTPSSeeder extends Seeder
{
    public function run(): void
    {
        $tps = Unit::where('name', 'TPS')->first();
        if (!$tps) return;

        $date = date('Y-m-d');
        
        // Cek jika sudah ada agar tidak duplikat
        if (Journal::where('unit_id', $tps->id)->whereDate('date', $date)->exists()) {
            return;
        }

        $metadata = [
            'sampah_rumah' => 10,
            'sampah_kegiatan' => 2,
            'sampah_wisata_nominal' => 50000,
            'setoran_lahan' => 10000
        ];

        $total_pemasukan = (10 * 15000) + (2 * 100000) + 50000; // 400.000
        $total_pengeluaran = 10000;

        $journal = Journal::create([
            'unit_id' => $tps->id,
            'date' => $date,
            'description' => 'Laporan Harian TPS (DRAFT dari Unit)',
            'metadata' => $metadata
        ]);

        $kasAccount = Account::where('code', '1-1100')->first();
        $pendapatanAccount = Account::where('code', '4-2000')->first();
        $biayaAccount = Account::where('code', '6-0023')->first();

        // Pemasukan
        JournalDetail::create(['journal_id' => $journal->id, 'account_id' => $kasAccount->id, 'debit' => $total_pemasukan, 'credit' => 0]);
        JournalDetail::create(['journal_id' => $journal->id, 'account_id' => $pendapatanAccount->id, 'debit' => 0, 'credit' => $total_pemasukan]);

        // Pengeluaran
        JournalDetail::create(['journal_id' => $journal->id, 'account_id' => $biayaAccount->id, 'debit' => $total_pengeluaran, 'credit' => 0]);
        JournalDetail::create(['journal_id' => $journal->id, 'account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $total_pengeluaran]);
    }
}
