<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename Indonesian domain tables to English.
 *
 * Pure metadata renames: no rows are added, changed, or deleted.
 * Foreign keys are dropped before the renames and re-created after
 * so constraint names follow the new tables and columns.
 */
return new class extends Migration
{
    /**
     * @return array<string, string>
     */
    private function tableMap(): array
    {
        return [
            'unit_wisata' => 'business_units',
            'kode_akun' => 'accounts',
            'kategori_transaksi' => 'transaction_categories',
            'kategori_harga_riwayat' => 'category_price_history',
            'transaksi_harian' => 'daily_transactions',
            'transaksi_detail' => 'transaction_items',
            'jurnal_umum' => 'journal_entries',
            'alokasi_laba_riwayat' => 'profit_allocation_history',
        ];
    }

    private function isSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }

    private function dropConstraint(string $table, string $column): void
    {
        if ($this->isSqlite()) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->dropForeign([$column]);
        });
    }

    private function dropAllForeignKeys(): void
    {
        $this->dropConstraint('kategori_transaksi', 'business_unit_id');
        $this->dropConstraint('kategori_transaksi', 'account_id');
        $this->dropConstraint('kategori_harga_riwayat', 'transaction_category_id');
        $this->dropConstraint('users', 'business_unit_id');
        $this->dropConstraint('transaksi_harian', 'business_unit_id');
        $this->dropConstraint('transaksi_harian', 'user_id');
        $this->dropConstraint('transaksi_detail', 'daily_transaction_id');
        $this->dropConstraint('transaksi_detail', 'transaction_category_id');
        $this->dropConstraint('jurnal_umum', 'account_id');
        $this->dropConstraint('jurnal_umum', 'daily_transaction_id');
        $this->dropConstraint('jurnal_umum', 'business_unit_id');
        $this->dropConstraint('alokasi_laba_riwayat', 'business_unit_id');
    }

    private function createAllForeignKeys(): void
    {
        if ($this->isSqlite()) {
            return;
        }

        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->foreign('business_unit_id')->references('id')->on('business_units')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
        Schema::table('category_price_history', function (Blueprint $table) {
            $table->foreign('transaction_category_id')->references('id')->on('transaction_categories')->cascadeOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('business_unit_id')->references('id')->on('business_units')->nullOnDelete();
        });
        Schema::table('daily_transactions', function (Blueprint $table) {
            $table->foreign('business_unit_id')->references('id')->on('business_units')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreign('daily_transaction_id')->references('id')->on('daily_transactions')->cascadeOnDelete();
            $table->foreign('transaction_category_id')->references('id')->on('transaction_categories')->cascadeOnDelete();
        });
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('daily_transaction_id')->references('id')->on('daily_transactions')->nullOnDelete();
            $table->foreign('business_unit_id')->references('id')->on('business_units')->cascadeOnDelete();
        });
        Schema::table('profit_allocation_history', function (Blueprint $table) {
            $table->foreign('business_unit_id')->references('id')->on('business_units')->onDelete('cascade');
        });
    }

    public function up(): void
    {
        $this->dropAllForeignKeys();

        foreach ($this->tableMap() as $from => $to) {
            Schema::rename($from, $to);
        }

        $this->createAllForeignKeys();
    }

    public function down(): void
    {
        $this->dropAllForeignKeys();

        foreach ($this->tableMap() as $from => $to) {
            Schema::rename($to, $from);
        }

        // Re-create constraints on the original table and column names.
        if ($this->isSqlite()) {
            return;
        }

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
};
