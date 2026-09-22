<?php

namespace App\Livewire\BumdesDirector;

use App\Models\JournalEntry;
use App\Traits\DashboardChartData;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    use DashboardChartData;

    public function render()
    {
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth()->format('Y-m-d');
        $end = $now->copy()->endOfMonth()->format('Y-m-d');

        $scope = fn ($q) => $q->whereBetween('transaction_date', [$start, $end]);

        $income = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
        })->tap($scope)->sum('credit');

        $expenses = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
        })->tap($scope)->sum('debit');

        $netIncome = $income - $expenses;
        $chartData = $this->getChartData();

        return view('livewire.bumdes-director.dashboard', [
            'income' => $income,
            'expenses' => $expenses,
            'netIncome' => $netIncome,
            'incomeChart' => $chartData['income'],
            'expenseChart' => $chartData['expenses'],
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
