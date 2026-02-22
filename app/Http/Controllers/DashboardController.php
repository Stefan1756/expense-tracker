<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $month = $request->get('month', now()->format('Y-m'));

        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $currentTotal = Expense::where('user_id', $user->id)
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
        
        $prevStart = $start->copy()->subMonth()->startOfMonth();
        $prevEnd = $start->copy()->subMonth()->endOfMonth();

        $previousTotal = Expense::where('user_id', $user->id)
            ->whereBetween('spent_at', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('amount');

        $thisMonthStart = $start->copy()->startOfMonth();
        $thisMonthEnd = $start->copy()->endOfMonth();

        $lastMonthStart = $prevStart->copy()->startOfMonth();
        $lastMonthEnd = $prevStart->copy()->endOfMonth();

        $today = now()->toDateString();

        $effectiveDate = now()->greaterThan($end) ? $end->copy() : now();
        if ($effectiveDate->lessThan($start)) {
            $effectiveDate = $start->copy();
        }

        $daysPassed = max(1, $start->diffInDays($effectiveDate) + 1);
        $daysInMonth = $start->daysInMonth;

        $dailyAvg = $currentTotal / $daysPassed;
        $forecastTotal = $dailyAvg * $daysInMonth;

        $forecastOverBudget = false;
        $forecastRemaining = null;

        if (!empty($monthlyBudget) && $monthlyBudget > 0) {
            $forecastRemaining = $monthlyBudget - $forecastTotal;
            $forecastOverBudget = $forecastTotal > $monthlyBudget;
        }

        $daysInThisMonth = $thisMonthStart->daysInMonth;
        $dailyLabels = range(1, $daysInThisMonth);

        $thisMonthByDay = Expense::where('user_id', $user->id)
            ->whereBetween('spent_at', [$thisMonthStart->toDateString(), $thisMonthEnd->toDateString()])
            ->selectRaw('DAY(spent_at) as d, SUM(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');
        
        $thisMonthDaily = [];
        $lastMonthDaily = [];

        for ($d = 1; $d<= $daysInThisMonth; $d++) {
            $thisMonthDaily[] = (float) ($thisMonthByDay[$d] ?? 0);
            $lastMonthDaily[] = (float) ($lastMonthByDay[$d] ?? 0);
        }

        $thisMonthLabel = $thisMonthStart->format('M Y');
        $lastMonthLabel = $lastMonthStart->format('M Y');
        
        $changePercent = null;
        $changeDirection = null;

        if ($previousTotal > 0){
            $changePercent = (($currentTotal - $previousTotal) / $previousTotal) * 100;

            if ($changePercent > 0) $changeDirection = 'up';
            elseif ($changePercent < 0) $changeDirection = 'down';
            else $changeDirection = 'flat';
        } else {
            $changeDirection = $currentTotal > 0 ? 'up' : 'flat';
        }

        $search = trim((string) $request->get('q', ''));

        $recentQuery = Expense::with('category')
            ->where('user_id', $user->id);
        
        if ($search !== '') {
            $recentQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $recentExpenses = $recentQuery
            ->orderByDesc('spent_at')
            ->limit(10)
            ->get();
        
        $categoryTotals = Expense::selectRaw('category_id, SUM(amount) as total')
            ->where('user_id', $user->id)
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $donutLabels = $categoryTotals->map(fn ($row) => optional($row->category)->name ?? 'Unknown')->values()->all();
        $donutSeries = $categoryTotals->map(fn ($row) => (float) $row->total)->values()->all();

        $topCategoryName = null;
        $topCategoryTotal = 0;

        if ($categoryTotals->isNotEmpty()) {
            $topRow = $categoryTotals->first();
            $topCategoryName = optional($topRow->category)->name ?? 'Unknown';
            $topCategoryTotal = (float) $topRow->total;
        }

        $topCategoryShare = ($currentTotal > 0)
            ? round(($topCategoryTotal / $currentTotal) * 100, 1)
            : 0;

        $recentExpenses = Expense::with('category')
            ->where('user_id', $user->id)
            ->orderByDesc('spent_at')
            ->limit(10)
            ->get();

        $categories = Category::where('user_id', $user->id)->orderBy('name')->get();

        $trendLabels = [];
        $trendData = [];

        $anchor = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        for ($i = 5; $i >= 0; $i--) {
            $mStart = $anchor->copy()->subMonths($i)->startOfMonth();
            $mEnd = $anchor->copy()->subMonths($i)->endOfMonth();

            $trendLabels[] = $mStart->format('M Y');

            $mTotal = Expense::where('user_id', $user->id)
                ->whereBetween('spent_at', [$mStart->toDateString(), $mEnd->toDateString()])
                ->sum('amount');

            $trendData[] = (float) $mTotal;
        }

        $monthlyBudget = (float) ($user->monthly_budget ?? 0);

        $budgetEnabled = $monthlyBudget > 0;
        $budgetUsedPercent = $budgetEnabled ? round(($currentTotal / $monthlyBudget) * 100, 1) : 0;
        $budgetRemaining = $budgetEnabled ? ($monthlyBudget - $currentTotal) : 0;

        $budgetProgress = $budgetEnabled ? min(100, $budgetUsedPercent) : 0;

        $budgetStatus = 'ok';
        if ($budgetEnabled) {
            if ($budgetUsedPercent >= 100) $budgetStatus = 'over';
            elseif ($budgetUsedPercent >= 80) $budgetStatus = 'danger';
            elseif ($budgetUsedPercent >= 60) $budgetStatus = 'warning';
        }

        $insights = [];

        if ($changePercent !== null) {
            if($changeDirection === 'up') {
                $insights[] = [
                    'type' => 'good',
                    'title' => 'Momentum up',
                    'text' => 'Your spending increased by '. number_format(abs($changePercent), 1) . '% compared to last month.'
                ];
            } elseif ($changeDirection === 'down') {
                $insights[] = [
                    'type' => 'risk',
                    'title' => 'Spending down',
                    'text' => 'Your spending decreased by '. number_format(abs($changePercent), 1) . '% compared to last month.'
                ];
            } else {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Stable month',
                    'text' => 'Your spending is flat compared to last month.'
                ];
            }
        } else {
            $insights[] = [
                    'type' => 'info',
                    'title' => 'Baseline building',
                    'text' => 'No spending data for last month. Keep logging expenses to unlock stronger comparisons.'
                ];
        }

        if (!empty($topCategoryName) && ($topCategoryShare ?? 0) > 0) {
            $insights[] = [
                'type' => ($topCategoryShare >= 40 ? 'risk' : 'info'),
                'title' => 'Top category driver',
                'text' => $topCategoryName . ' is driving ' . number_format($topCategoryShare, 1) . '% of your spend this month.'
            ];
        }

        if (!empty($monthlyBudget) && $monthlyBudget > 0) {
            if ($budgetUsedPercent >= 100) {
                $insights[] = [
                    'type' => 'risk',
                    'title' => 'Budget exceeded',
                    'text' => 'You have exceeded your monthly budget. Consider reducing discretionary categories.'
                ];
            } elseif ($budgetUsedPercent >= 80) {
                $insights[] = [
                    'type' => 'risk',
                    'title' => 'Budget risk',
                    'text' => 'You have used ' . number_format($budgetUsedPercent, 1) . '% of your budget. Tighten controls to stay on track.'
                ];
            }else{
                $insights[] = [
                    'type' => 'good',
                    'title' => 'Budget healthy',
                    'text' => 'You have used ' . number_format($budgetUsedPercent, 1) . '% of your budget. You are tracking well.'
                ];
           }
        } else {
            $insights[] = [
                'type' => 'info',
                'title' => 'Enable budget controls',
                'text' => 'Set a monthly budget to unlock budget health signals and stronger forecasting.'
            ];
        }

        if (isset($forecastTotal)) {
            if (!empty($monthlyBudget) && $monthlyBudget > 0) {
                if ($forecastTotal > $monthlyBudget) {
                    $insights[] = [
                        'type' => 'risk',
                        'title' => 'Forecast overspend',
                        'text' => 'At your current pace, you are projected to exceed budget by ' . number_format(abs($monthlyBudget - $forecastTotal), 2) . '.'
                    ];
                } else {
                    $insights[] = [
                        'type' => 'good',
                        'title' => 'Forecast on track',
                        'text' => 'At your current pace, you are projected to stay under budget by Tsh ' . number_format($monthlyBudget - $forecastTotal, 2) . '.'
                    ];
                }
            } else {
                $insights[] = [
                'type' => 'info',
                'title' => 'Forecast ready',
                'text' => 'Projected month-end speed is ' . number_format($forecastTotal, 2) . '. Add a budget to measure performance.'
                ];
            }
        }

        $biggestExpense = \App\Models\Expense::where('user_id', $user->id)
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('amount')
            ->with('category')
            ->first();
        
        if ($biggestExpense) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Largest expense',
                'text' => $biggestExpense->title . ' (Tsh' . number_format($biggestExpense->amount, 2) . ') in ' . ($biggestExpense->category?->name ?? 'Uncategorized') . '.'
            ];
        }

        $insights = array_slice($insights, 0, 6);

        return view('dashboard', compact(
            'month', 'search', 'start', 'end', 
            'currentTotal', 'previousTotal', 'changePercent', 'changeDirection',
            'categoryTotals', 'recentExpenses', 'categories',
            'topCategoryName', 'topCategoryTotal', 'topCategoryShare',
            'trendLabels', 'trendData',
            'dailyLabels', 'thisMonthDaily', 'lastMonthDaily', 'thisMonthLabel', 'lastMonthLabel',
            'donutLabels', 'donutSeries',
            'monthlyBudget', 'budgetEnabled', 'budgetUsedPercent', 'budgetRemaining', 'budgetProgress', 'budgetStatus',
            'daysPassed', 'daysInMonth', 'dailyAvg', 'forecastTotal', 'forecastOverBudget', 'forecastRemaining',
            'insights'
        ));
    }
}