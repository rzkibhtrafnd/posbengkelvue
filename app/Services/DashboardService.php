<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function getAdminDashboard(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $hasDateRange = $dateFrom !== null && $dateTo !== null;
        $rangeStart = $hasDateRange ? Carbon::parse($dateFrom)->startOfDay() : null;
        $rangeEnd = $hasDateRange ? Carbon::parse($dateTo)->endOfDay() : null;
        $periodSummary = $hasDateRange
            ? $this->getTransactionSummary($rangeStart, $rangeEnd)
            : $this->getTransactionSummary(today()->startOfDay(), today()->endOfDay());
        $monthSummary = $this->getTransactionSummary(now()->startOfMonth(), now()->endOfMonth());
        $dailyStart = $hasDateRange ? $rangeStart : Carbon::now()->startOfYear();
        $dailyEnd = $hasDateRange ? $rangeEnd : Carbon::now()->endOfYear();

        return [
            'stats' => [
                'periodRevenue' => (float) $periodSummary->revenue,
                'periodTransactions' => (int) $periodSummary->count,
                'totalTransactions' => Transaction::query()->count(),
                'monthRevenue' => (float) $monthSummary->revenue,
                'monthTransactions' => (int) $monthSummary->count,
                'products' => Product::query()->count(),
                'lowStockCount' => Product::query()
                    ->where('type', ProductType::BARANG->value)
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->count(),
                'vehicles' => Vehicle::query()->count(),
                'customers' => Customer::query()->count(),
                'users' => User::query()->count(),
            ],
            'dailyTrend' => $this->getWeekdayTrend(Transaction::query(), $dailyStart, $dailyEnd),
            'monthlyTrend' => $this->getMonthlyTrend($rangeStart, $rangeEnd),
            'recentTransactions' => Transaction::query()
                ->latest('transaction_date')
                ->limit(6)
                ->get([
                    'id',
                    'invoice_number',
                    'transaction_date',
                    'customer_name',
                    'vehicle_plate_number',
                    'cashier_name',
                    'grand_total',
                    'payment_method',
                ]),
            'lowStockProducts' => Product::query()
                ->where('type', ProductType::BARANG->value)
                ->whereColumn('stock', '<=', 'min_stock')
                ->orderBy('stock')
                ->limit(6)
                ->get(['id', 'name', 'code', 'stock', 'min_stock', 'unit']),
            'topProducts' => TransactionItem::query()
                ->selectRaw('product_name, SUM(qty) as quantity, SUM(subtotal) as revenue')
                ->whereHas('transaction', fn (Builder $query) => $query
                    ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()]))
                ->groupBy('product_name')
                ->orderByDesc('quantity')
                ->limit(5)
                ->get(),
        ];
    }

    public function getCashierDashboard(int $userId): array
    {
        $todaySummary = $this->getTransactionSummary(
            today()->startOfDay(),
            today()->endOfDay(),
            $userId,
        );
        $monthSummary = $this->getTransactionSummary(
            now()->startOfMonth(),
            now()->endOfMonth(),
            $userId,
        );

        return [
            'stats' => [
                'todayRevenue' => (float) $todaySummary->revenue,
                'todayTransactions' => (int) $todaySummary->count,
                'monthRevenue' => (float) $monthSummary->revenue,
                'monthTransactions' => (int) $monthSummary->count,
            ],
            'salesTrend' => $this->getSalesTrend(
                Transaction::query()->where('user_id', $userId),
            ),
            'recentTransactions' => Transaction::query()
                ->where('user_id', $userId)
                ->latest('transaction_date')
                ->limit(5)
                ->get([
                    'id',
                    'invoice_number',
                    'transaction_date',
                    'customer_name',
                    'vehicle_plate_number',
                    'grand_total',
                    'payment_method',
                ]),
        ];
    }

    private function getSalesTrend(Builder $transactions): array
    {
        return $this->getDailyTrend(
            $transactions,
            today()->subDays(6)->startOfDay(),
            today()->endOfDay(),
            false,
        );
    }

    private function getTransactionSummary(DateTimeInterface $start, DateTimeInterface $end, ?int $userId = null): Transaction
    {
        return Transaction::query()
            ->when($userId !== null, fn (Builder $query) => $query->where('user_id', $userId))
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(grand_total), 0) as revenue')
            ->firstOrFail();
    }

    private function getDailyTrend(Builder $transactions, Carbon $start, Carbon $end, bool $includeDate = true): array
    {
        $salesByDate = $transactions
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('DATE(transaction_date) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(grand_total), 0) as revenue')
            ->groupByRaw('DATE(transaction_date)')
            ->get()
            ->keyBy('sale_date');
        $dayLabels = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 0 => 'Min'];

        $dailyTrend = [];
        $date = $start->copy()->startOfDay();
        $lastDate = $end->copy()->startOfDay();

        while ($date->lte($lastDate)) {
            $sale = $salesByDate->get($date->toDateString());
            $dailyTrend[] = [
                'date' => $date->toDateString(),
                'label' => $dayLabels[$date->dayOfWeek].($includeDate ? ' '.$date->format('d/m') : ''),
                'revenue' => (float) ($sale?->revenue ?? 0),
                'transactions' => (int) ($sale?->transaction_count ?? 0),
            ];

            $date->addDay();
        }

        return $dailyTrend;
    }

    private function getWeekdayTrend(Builder $transactions, Carbon $start, Carbon $end): array
    {
        $salesByWeekday = $transactions
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('WEEKDAY(transaction_date) as sale_weekday, COUNT(*) as transaction_count, COALESCE(SUM(grand_total), 0) as revenue')
            ->groupByRaw('WEEKDAY(transaction_date)')
            ->get()
            ->keyBy('sale_weekday');
        $dayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $weekdayTrend = [];

        foreach ($dayLabels as $weekday => $label) {
            $sale = $salesByWeekday->get($weekday);
            $weekdayTrend[] = [
                'date' => 'weekday-'.$weekday,
                'label' => $label,
                'revenue' => (float) ($sale?->revenue ?? 0),
                'transactions' => (int) ($sale?->transaction_count ?? 0),
            ];
        }

        return $weekdayTrend;
    }

    private function getMonthlyTrend(?Carbon $dateFrom = null, ?Carbon $dateTo = null): array
    {
        $monthLabels = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];
        $start = $dateFrom?->copy()->startOfYear() ?? now()->startOfYear();
        $end = $dateTo?->copy()->endOfYear() ?? now()->endOfYear();
        $salesByMonth = Transaction::query()
            ->whereBetween('transaction_date', [
                $dateFrom ?? $start->copy()->startOfYear(),
                $dateTo ?? $end->copy()->endOfYear(),
            ])
            ->selectRaw('YEAR(transaction_date) as sale_year, MONTH(transaction_date) as sale_month, COUNT(*) as transaction_count, COALESCE(SUM(grand_total), 0) as revenue')
            ->groupByRaw('YEAR(transaction_date), MONTH(transaction_date)')
            ->get()
            ->keyBy(fn ($sale) => sprintf('%04d-%02d', $sale->sale_year, $sale->sale_month));

        $monthlyTrend = [];
        $month = $start->copy()->startOfMonth();
        $lastMonth = $end->copy()->startOfMonth();
        $includeYear = $month->year !== $lastMonth->year;

        while ($month->lte($lastMonth)) {
            $sale = $salesByMonth->get($month->format('Y-m'));
            $monthlyTrend[] = [
                'date' => $month->format('Y-m'),
                'label' => $monthLabels[(int) $month->format('n')]
                    .($includeYear ? ' '.$month->format('y') : ''),
                'revenue' => (float) ($sale?->revenue ?? 0),
                'transactions' => (int) ($sale?->transaction_count ?? 0),
            ];
            $month->addMonth();
        }

        return $monthlyTrend;
    }
}
