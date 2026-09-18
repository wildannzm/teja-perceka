<?php

namespace App\Livewire\Secretary;

use App\Models\JournalEntry;
use App\Traits\DashboardChartData;
use Livewire\Component;

class Dashboard extends Component
{
    use DashboardChartData;

    public function render()
    {
        // Income accounts: 4- and 7- prefixes (credit column)
        $income = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
        })->sum('credit');

        // Expense accounts: 5- and 6- prefixes (debit column)
        $expenses = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
        })->sum('debit');

        // Cash balance: debit - credit on Cash (1-1100) and Bank (1-1200)
        $cashDebit = JournalEntry::whereHas('account', function ($q) {
            $q->whereIn('code', ['1-1100', '1-1200']);
        })->sum('debit');

        $cashCredit = JournalEntry::whereHas('account', function ($q) {
            $q->whereIn('code', ['1-1100', '1-1200']);
        })->sum('credit');

        $balance = $cashDebit - $cashCredit;
        $chartData = $this->getChartData();

        return view('livewire.secretary.dashboard', [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $balance,
            'incomeChart' => $chartData['income'],
            'expenseChart' => $chartData['expenses'],
        ])->layout('layouts.app', ['title' => 'Dashboard Sekretaris']);
    }
}
