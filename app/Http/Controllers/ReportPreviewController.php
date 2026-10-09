<?php

namespace App\Http\Controllers;

use App\Livewire\ProfitLoss\ProfitAllocation;
use App\Livewire\ProfitLoss\ProfitLoss;
use App\Livewire\Reports\GeneralLedger;
use App\Livewire\Reports\TrialBalance;
use App\Livewire\Transactions\JournalTab;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReportPreviewController extends Controller
{
    /**
     * Stream the report PDF inline so the browser opens it in a new tab
     * where the user can print directly (Ctrl+P) without downloading first.
     */
    public function __invoke(Request $request, string $report): Response
    {
        // Blade emits empty filter values as `''`; treat them as absent
        // so `nullable` + format rules pass.
        $request->merge(collect($request->only([
            'unit', 'mode', 'period', 'semester', 'semester_year',
            'account', 'date', 'week', 'month', 'year', 'view', 'sort', 'dir',
        ]))->map(fn ($value) => $value === '' ? null : $value)->all());

        $validated = $request->validate([
            'unit' => ['nullable', 'string', 'max:50'],
            'mode' => ['nullable', 'in:daily,weekly,monthly,semester,yearly'],
            'period' => ['nullable', 'string', 'max:7'],
            'semester' => ['nullable', 'in:1,2'],
            'semester_year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'account' => ['nullable', 'integer', 'exists:accounts,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'week' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'sort' => ['nullable', 'in:transaction_date,voucher_number'],
            'dir' => ['nullable', 'in:asc,desc'],
            'view' => ['nullable', 'in:summary,detailed'],
        ]);

        // Fail closed on unknown unit scopes: builders only understand
        // bumdes/all/numeric, anything else would aggregate unfiltered.
        $unit = $validated['unit'] ?? null;
        if ($unit !== null && $unit !== 'bumdes' && $unit !== 'all' && ! is_numeric($unit)) {
            abort(422, 'Unit tidak dikenal.');
        }

        $user = Auth::user();

        if ($report === 'jurnal-umum') {
            return $this->journalPreview($validated, $user);
        }

        $tab = match ($report) {
            'laba-rugi' => $this->profitLoss($validated, $user),
            'alokasi-laba' => $this->profitAllocation($validated),
            'buku-besar' => $this->generalLedger($validated, $user),
            'neraca-saldo' => $this->trialBalance($validated, $user),
            default => abort(404),
        };

        // Widen the execution window for large period exports.
        set_time_limit(120);

        [$pdf, $filename] = $tab->buildReportPdf();

        return $pdf->stream($filename);
    }

    private function applyPeriod(object $tab, array $validated): void
    {
        // Profit reports only support monthly/semester/yearly: anything else
        // is a wrong-period request, not a monthly report.
        $mode = $validated['mode'] ?? 'monthly';
        abort_unless(in_array($mode, ['monthly', 'semester', 'yearly'], true), 422, 'Mode periode tidak didukung.');
        $tab->mode = $mode;
        $tab->period = $validated['period'] ?? '';
        $tab->semester = $validated['semester'] ?? '1';
        $tab->semesterYear = isset($validated['semester_year']) ? (string) $validated['semester_year'] : '';
    }

    private function resolveUnit(array $validated, object $user): mixed
    {
        // kepala_unit is locked to their own unit, same as each render().
        // A unit head without an assigned unit cannot preview anything.
        if ($user->hasRole('kepala_unit')) {
            abort_unless($user->business_unit_id, 403);

            return $user->business_unit_id;
        }

        return $validated['unit'] ?? null;
    }

    private function ensureCanPrint(object $user): void
    {
        // Mirrors each report's canPrint: read-only roles (kepala_desa,
        // pengawas) may open the page but must not export the PDF.
        abort_unless($user->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes']), 403);
    }

    private function profitLoss(array $validated, object $user): ProfitLoss
    {
        $this->ensureCanPrint($user);
        $tab = new ProfitLoss;
        $tab->unit_id = $this->resolveUnit($validated, $user);
        $this->applyPeriod($tab, $validated);

        return $tab;
    }

    private function profitAllocation(array $validated): ProfitAllocation
    {
        $tab = new ProfitAllocation;
        $this->applyPeriod($tab, $validated);

        return $tab;
    }

    private function generalLedger(array $validated, object $user): GeneralLedger
    {
        $this->ensureCanPrint($user);
        $tab = new GeneralLedger;
        $tab->unit_id = $this->resolveUnit($validated, $user);
        $tab->period = $validated['period'] ?? '';
        $tab->account_id = isset($validated['account']) ? (int) $validated['account'] : null;

        return $tab;
    }

    private function trialBalance(array $validated, object $user): TrialBalance
    {
        $this->ensureCanPrint($user);
        $tab = new TrialBalance;
        $tab->unit_id = $this->resolveUnit($validated, $user);
        $tab->period = $validated['period'] ?? '';

        return $tab;
    }

    /**
     * Journal preview keeps its own validation: only reporting modes
     * (monthly/semester/yearly) are printable.
     */
    private function journalPreview(array $validated, object $user): Response
    {
        $request = request();
        $request->validate([
            'mode' => ['required', 'in:monthly,semester,yearly'],
        ]);

        $tab = new JournalTab;
        // Same unit lock as JournalTab::render(); a unit head without an
        // assigned unit gets 403 via resolveUnit instead of unscoped rows.
        $tab->unitId = $this->resolveUnit($validated, $user);
        $tab->mode = $validated['mode'];
        $tab->transactionDate = $validated['date'] ?? '';
        $tab->week = $validated['week'] ?? '';
        $tab->month = $validated['month'] ?? '';
        $tab->semester = $validated['semester'] ?? '1';
        $tab->semesterYear = isset($validated['semester_year']) ? (string) $validated['semester_year'] : '';
        $tab->year = isset($validated['year']) ? (string) $validated['year'] : '';
        $tab->sortField = $validated['sort'] ?? 'transaction_date';
        $tab->sortDirection = $validated['dir'] ?? 'asc';
        $tab->viewMode = $validated['view'] ?? 'detailed';

        // Widen the execution window for large period exports.
        set_time_limit(120);

        [$pdf, $filename] = $tab->buildReportPdf();

        return $pdf->stream($filename);
    }
}
