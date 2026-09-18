<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename Indonesian domain columns to English.
 *
 * Pure metadata renames: no rows are added, changed, or deleted.
 * Stored values (enum strings, business codes, role names) are untouched.
 *
 * Raw ALTER statements are used instead of renameColumn so no
 * doctrine/dbal dependency is required. Foreign keys are dropped and
 * re-created around the renames because MySQL forbids renaming a
 * column that participates in a foreign key.
 */
return new class extends Migration
{
    /**
     * @return array<string, array<string, string>>
     */
    private function columnMap(): array
    {
        return [
            'unit_wisata' => [
                'nama' => 'name',
                'kode' => 'code',
                'frekuensi_input' => 'input_frequency',
            ],
            'kode_akun' => [
                'kode' => 'code',
                'nama' => 'name',
                'tipe' => 'type',
                'urutan' => 'sort_order',
            ],
            'kategori_transaksi' => [
                'unit_wisata_id' => 'business_unit_id',
                'kode_akun_id' => 'account_id',
                'nama' => 'name',
                'tipe' => 'type',
                'jenis' => 'direction',
            ],
            'kategori_harga_riwayat' => [
                'kategori_transaksi_id' => 'transaction_category_id',
                'harga' => 'price',
                'berlaku_dari' => 'effective_from',
            ],
            'users' => [
                'unit_wisata_id' => 'business_unit_id',
            ],
            'transaksi_harian' => [
                'unit_wisata_id' => 'business_unit_id',
                'tanggal' => 'transaction_date',
                'tanggal_akhir' => 'end_date',
                'total_pemasukan' => 'total_income',
                'total_pengeluaran' => 'total_expense',
                'catatan' => 'notes',
            ],
            'transaksi_detail' => [
                'transaksi_harian_id' => 'daily_transaction_id',
                'kategori_transaksi_id' => 'transaction_category_id',
                'qty' => 'quantity',
                'harga_satuan' => 'unit_price',
            ],
            'jurnal_umum' => [
                'nomor_bukti' => 'voucher_number',
                'tanggal' => 'transaction_date',
                'keterangan' => 'description',
                'kode_akun_id' => 'account_id',
                'debet' => 'debit',
                'kredit' => 'credit',
                'transaksi_harian_id' => 'daily_transaction_id',
                'unit_wisata_id' => 'business_unit_id',
            ],
            'assets' => [
                'nama_aset' => 'name',
                'jumlah' => 'quantity',
                'satuan' => 'unit',
                'harga' => 'price',
                'keterangan' => 'description',
            ],
            'alokasi_laba_riwayat' => [
                'keterangan' => 'description',
                'persentase' => 'percentage',
                'kelompok' => 'allocation_group',
                'berlaku_dari' => 'effective_from',
                'unit_wisata_id' => 'business_unit_id',
            ],
        ];
    }

    private function isSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }

    private function renameColumn(string $table, string $from, string $to): void
    {
        DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");
    }

    /**
     * @param  array<string, string>  $columns
     */
    private function dropForeignKeys(string $table, array $columns): void
    {
        if ($this->isSqlite()) {
            return;
        }

        foreach (array_keys($columns) as $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });
        }
    }

    public function up(): void
    {
        // kategori_transaksi: business_unit_id (cascade), account_id (null on delete)
        $this->dropForeignKeys('kategori_transaksi', [
            'unit_wisata_id' => 'business_unit_id',
            'kode_akun_id' => 'account_id',
        ]);

        // kategori_harga_riwayat: transaction_category_id (cascade)
        $this->dropForeignKeys('kategori_harga_riwayat', [
            'kategori_transaksi_id' => 'transaction_category_id',
        ]);

        // users: business_unit_id (null on delete)
        $this->dropForeignKeys('users', [
            'unit_wisata_id' => 'business_unit_id',
        ]);

        // transaksi_harian: business_unit_id + user_id (cascade)
        $this->dropForeignKeys('transaksi_harian', [
            'unit_wisata_id' => 'business_unit_id',
            'user_id' => 'user_id',
        ]);

        // transaksi_detail: daily_transaction_id + transaction_category_id (cascade)
        $this->dropForeignKeys('transaksi_detail', [
            'transaksi_harian_id' => 'daily_transaction_id',
            'kategori_transaksi_id' => 'transaction_category_id',
        ]);

        // jurnal_umum: account_id (cascade), daily_transaction_id (null on delete), business_unit_id (cascade)
        $this->dropForeignKeys('jurnal_umum', [
            'kode_akun_id' => 'account_id',
            'transaksi_harian_id' => 'daily_transaction_id',
            'unit_wisata_id' => 'business_unit_id',
        ]);

        // alokasi_laba_riwayat: business_unit_id (cascade)
        $this->dropForeignKeys('alokasi_laba_riwayat', [
            'unit_wisata_id' => 'business_unit_id',
        ]);

        foreach ($this->columnMap() as $table => $columns) {
            foreach ($columns as $from => $to) {
                $this->renameColumn($table, $from, $to);
            }
        }

        // Convert stored frequency values to English (1:1 mapping, no data loss).
        DB::table('unit_wisata')->where('input_frequency', 'harian')->update(['input_frequency' => 'daily']);
        DB::table('unit_wisata')->where('input_frequency', 'mingguan')->update(['input_frequency' => 'weekly']);

        if (! $this->isSqlite()) {
            Schema::table('kategori_transaksi', function (Blueprint $table) {
                $table->foreign('business_unit_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
                $table->foreign('account_id')->references('id')->on('kode_akun')->nullOnDelete();
            });
            Schema::table('kategori_harga_riwayat', function (Blueprint $table) {
                $table->foreign('transaction_category_id')->references('id')->on('kategori_transaksi')->cascadeOnDelete();
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('business_unit_id')->references('id')->on('unit_wisata')->nullOnDelete();
            });
            Schema::table('transaksi_harian', function (Blueprint $table) {
                $table->foreign('business_unit_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
            Schema::table('transaksi_detail', function (Blueprint $table) {
                $table->foreign('daily_transaction_id')->references('id')->on('transaksi_harian')->cascadeOnDelete();
                $table->foreign('transaction_category_id')->references('id')->on('kategori_transaksi')->cascadeOnDelete();
            });
            Schema::table('jurnal_umum', function (Blueprint $table) {
                $table->foreign('account_id')->references('id')->on('kode_akun')->cascadeOnDelete();
                $table->foreign('daily_transaction_id')->references('id')->on('transaksi_harian')->nullOnDelete();
                $table->foreign('business_unit_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
            });
            Schema::table('alokasi_laba_riwayat', function (Blueprint $table) {
                $table->foreign('business_unit_id')->references('id')->on('unit_wisata')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        DB::table('unit_wisata')->where('input_frequency', 'daily')->update(['input_frequency' => 'harian']);
        DB::table('unit_wisata')->where('input_frequency', 'weekly')->update(['input_frequency' => 'mingguan']);

        if (! $this->isSqlite()) {
            Schema::table('kategori_transaksi', function (Blueprint $table) {
                $table->dropForeign(['business_unit_id']);
                $table->dropForeign(['account_id']);
            });
            Schema::table('kategori_harga_riwayat', function (Blueprint $table) {
                $table->dropForeign(['transaction_category_id']);
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['business_unit_id']);
            });
            Schema::table('transaksi_harian', function (Blueprint $table) {
                $table->dropForeign(['business_unit_id']);
                $table->dropForeign(['user_id']);
            });
            Schema::table('transaksi_detail', function (Blueprint $table) {
                $table->dropForeign(['daily_transaction_id']);
                $table->dropForeign(['transaction_category_id']);
            });
            Schema::table('jurnal_umum', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropForeign(['daily_transaction_id']);
                $table->dropForeign(['business_unit_id']);
            });
            Schema::table('alokasi_laba_riwayat', function (Blueprint $table) {
                $table->dropForeign(['business_unit_id']);
            });
        }

        foreach ($this->columnMap() as $table => $columns) {
            foreach ($columns as $from => $to) {
                $this->renameColumn($table, $to, $from);
            }
        }

        if (! $this->isSqlite()) {
            Schema::table('kategori_transaksi', function (Blueprint $table) {
                $table->foreign('unit_wisata_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
                $table->foreign('kode_akun_id')->references('id')->on('kode_akun')->nullOnDelete();
            });
            Schema::table('kategori_harga_riwayat', function (Blueprint $table) {
                $table->foreign('kategori_transaksi_id')->references('id')->on('kategori_transaksi')->cascadeOnDelete();
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('unit_wisata_id')->references('id')->on('unit_wisata')->nullOnDelete();
            });
            Schema::table('transaksi_harian', function (Blueprint $table) {
                $table->foreign('unit_wisata_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
            Schema::table('transaksi_detail', function (Blueprint $table) {
                $table->foreign('transaksi_harian_id')->references('id')->on('transaksi_harian')->cascadeOnDelete();
                $table->foreign('kategori_transaksi_id')->references('id')->on('kategori_transaksi')->cascadeOnDelete();
            });
            Schema::table('jurnal_umum', function (Blueprint $table) {
                $table->foreign('kode_akun_id')->references('id')->on('kode_akun')->cascadeOnDelete();
                $table->foreign('transaksi_harian_id')->references('id')->on('transaksi_harian')->nullOnDelete();
                $table->foreign('unit_wisata_id')->references('id')->on('unit_wisata')->cascadeOnDelete();
            });
            Schema::table('alokasi_laba_riwayat', function (Blueprint $table) {
                $table->foreign('unit_wisata_id')->references('id')->on('unit_wisata')->onDelete('cascade');
            });
        }
    }
};
