<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendances;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboard(Request $request)
    {

        $user = $request->user();


        $attendance = Attendances::where('employee_id', $user->employee_id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        $data = [
            'attendance' => [
                'attendance_id' => $attendance ? $attendance->id : null,
                'check_in_time' => $attendance ? $attendance->check_in_time : null,
                'check_out_time' => $attendance ? $attendance->check_out_time : null,
            ],
        ];

        return response()->json($data);
    }

    public function getDashboardAdmin(Request $request)
    {
        $filter = $request->query('filter', 'monthly');

        // 1. Tentukan rentang tanggal dan label
        if ($filter === 'weekly') {
            $startDate = now()->startOfWeek();
            $endDate   = now()->endOfWeek();
            $dateFormat = '%a';
            $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        } elseif ($filter === 'yearly') {
            $startDate = now()->startOfYear();
            $endDate   = now()->endOfYear();
            $dateFormat = '%b';
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        } else {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
            $dateFormat = '%d';
            $daysInMonth = $endDate->day;
            $labels = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT); // 01, 02, dst
            }
        }

        // 2. Ambil data penjualan (untuk chart range/median)
        $sales = DB::table('sales')
            ->whereBetween('sales_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(sales_date, '{$dateFormat}') as period, sales_amount")
            ->get()
            ->groupBy('period');

        // 3. Ambil data pembelian (untuk chart range/median)
        $expenses = DB::table('purchases')
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(purchase_date, '{$dateFormat}') as period, total_amount")
            ->get()
            ->groupBy('period');

        // 4. Siapkan hasil final untuk chart
        $profitRange   = [];
        $expenseRange  = [];
        $profitMedian  = [];
        $expenseMedian = [];

        foreach ($labels as $label) {
            // Penjualan
            $salesData = $sales[$label] ?? collect();
            $salesAmounts = $salesData->pluck('sales_amount')->sort()->values();
            $salesMin = $salesAmounts->first() ?? 0;
            $salesMax = $salesAmounts->last() ?? 0;
            $salesMedian = $salesAmounts->isEmpty() ? 0 : $this->calculateMedian($salesAmounts);

            $profitRange[]  = ['x' => $label, 'y' => [(int) $salesMin, (int) $salesMax]];
            $profitMedian[] = ['x' => $label, 'y' => (int) $salesMedian];

            // Pengeluaran
            $expenseData = $expenses[$label] ?? collect();
            $expenseAmounts = $expenseData->pluck('total_amount')->sort()->values();
            $expenseMin = $expenseAmounts->first() ?? 0;
            $expenseMax = $expenseAmounts->last() ?? 0;
            $expenseMedianVal = $expenseAmounts->isEmpty() ? 0 : $this->calculateMedian($expenseAmounts);

            $expenseRange[]  = ['x' => $label, 'y' => [(int) $expenseMin, (int) $expenseMax]];
            $expenseMedian[] = ['x' => $label, 'y' => (int) $expenseMedianVal];
        }

        // 5. Total keseluruhan
        $totalSales = DB::table('sales')
            ->whereBetween('sales_date', [$startDate, $endDate])
            ->sum('sales_amount');

        $totalExpenses = DB::table('purchases')
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_amount');

        $totalProfit = $totalSales - $totalExpenses;

        // 6. List Top Selling Products (top 5)
        $topProducts = DB::table('sales_items')
            ->join('sales', 'sales_items.sales_id', '=', 'sales.id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sales_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sales_items.quantity) as total_quantity'),
                DB::raw('SUM(sales_items.subtotal) as total_subtotal')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // 7. Cashflow IN (sales) & OUT (purchase) per tanggal
        //    format: { "date": "2025-09-17", "total_purchase": "100000.00", "total_sales": "80000.00" }
        $purchaseCF = DB::table('purchases')
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->selectRaw('purchase_date as date, SUM(total_amount) as total_purchase')
            ->groupBy('purchase_date')
            ->orderBy('purchase_date', 'desc')
            ->get();

        $salesCF = DB::table('sales')
            ->whereBetween('sales_date', [$startDate, $endDate])
            ->selectRaw('sales_date as date, SUM(sales_amount) as total_sales')
            ->groupBy('sales_date')
            ->orderBy('sales_date', 'desc')
            ->get();

        // Merge seperti di totalPerDate()
        $cashflowCombined = [];

        foreach ($purchaseCF as $p) {
            $cashflowCombined[$p->date] = [
                'date'           => $p->date,
                'total_purchase' => $p->total_purchase,
                'total_sales'    => "0",
            ];
        }

        foreach ($salesCF as $s) {
            if (!isset($cashflowCombined[$s->date])) {
                $cashflowCombined[$s->date] = [
                    'date'           => $s->date,
                    'total_purchase' => "0",
                    'total_sales'    => $s->total_sales,
                ];
            } else {
                $cashflowCombined[$s->date]['total_sales'] = $s->total_sales;
            }
        }

        // sort desc by date, lalu jadikan array
        krsort($cashflowCombined);
        $cashflow = array_values($cashflowCombined);

        // 8. Response
        return response()->json([
            'filter'           => $filter,
            'profit_range'     => $profitRange,
            'expense_range'    => $expenseRange,
            'profit_median'    => $profitMedian,
            'expense_median'   => $expenseMedian,
            'total_sales'      => $totalSales,
            'total_expenses'   => $totalExpenses,
            'total_profit'     => $totalProfit,
            'top_selling_products' => $topProducts,
            'cashflow'         => $cashflow,
        ]);
    }


    private function calculateMedian($values)
    {
        $count = $values->count();
        if ($count === 0) return 0;

        if ($count % 2 === 0) {
            $mid1 = $values[($count / 2) - 1];
            $mid2 = $values[$count / 2];
            return ($mid1 + $mid2) / 2;
        } else {
            return $values[floor($count / 2)];
        }
    }

    private function getMonthName($number)
    {
        $months = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        return $months[$number] ?? $number;
    }
}
