<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF;
use Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

    /**
     * Display the reports dashboard
     */
    public function index()
    {
        $recentReports = Report::with('generatedBy')
            ->recent(7)
            ->orderBy('generated_at', 'desc')
            ->get();

        $reportTypes = Report::getTypes();

        // Get quick statistics
        $stats = [
            'total_reports' => Report::count(),
            'reports_today' => Report::whereDate('generated_at', today())->count(),
            'reports_this_week' => Report::whereBetween('generated_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'reports_this_month' => Report::whereMonth('generated_at', now()->month)->count(),
        ];

        return view('pages.reports.index', compact('recentReports', 'reportTypes', 'stats'));
    }

    /**
     * Show report generation form
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'daily_sales');
        $reportTypes = Report::getTypes();
        
        return view('pages.reports.create', compact('type', 'reportTypes'));
    }

    /**
     * Generate and store report
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Report::getTypes())),
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'nullable|in:pdf,excel,json',
        ]);

        $type = $request->type;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Generate report data
        $data = $this->generateReportData($type, $startDate, $endDate);

        // Create report record
        $report = Report::create([
            'name' => $request->name,
            'type' => $type,
            'parameters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'format' => $request->format ?? 'json',
            ],
            'data' => $data,
            'generated_by' => Auth::id(),
            'generated_at' => now(),
        ]);

        // Export if format specified
        if ($request->format) {
            $filePath = $this->exportReport($report, $request->format);
            $report->update(['file_path' => $filePath]);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Report generated successfully!');
    }

    /**
     * Display generated report
     */
    public function show(Report $report)
    {
        return view('pages.reports.show', compact('report'));
    }

    /**
     * Export report
     */
    public function export(Report $report, Request $request)
    {
        $format = $request->get('format', 'pdf');
        
        $filePath = $this->exportReport($report, $format);
        $report->update(['file_path' => $filePath]);

        return response()->download(storage_path('app/' . $filePath));
    }

    /**
     * Download report file
     */
    public function download(Report $report)
    {
        if (!$report->file_path || !file_exists(storage_path('app/' . $report->file_path))) {
            return back()->with('error', 'Report file not found.');
        }

        return response()->download(storage_path('app/' . $report->file_path));
    }

    /**
     * Delete report
     */
    public function destroy(Report $report)
    {
        // Delete file if exists
        if ($report->file_path && file_exists(storage_path('app/' . $report->file_path))) {
            unlink(storage_path('app/' . $report->file_path));
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully!');
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData($type, $startDate, $endDate)
    {
        switch ($type) {
            case Report::TYPE_DAILY_SALES:
                return $this->generateDailySalesReport($startDate, $endDate);
            
            case Report::TYPE_MONTHLY_SALES:
                return $this->generateMonthlySalesReport($startDate, $endDate);
            
            case Report::TYPE_STOCK_REPORT:
                return $this->generateStockReport();
            
            case Report::TYPE_LOW_STOCK:
                return $this->generateLowStockReport();
            
            case Report::TYPE_ITEM_TRENDS:
                return $this->generateItemTrendsReport($startDate, $endDate);
            
            case Report::TYPE_CASHIER_PERFORMANCE:
                return $this->generateCashierPerformanceReport($startDate, $endDate);
            
            case Report::TYPE_SALES_BY_CATEGORY:
                return $this->generateSalesByCategoryReport($startDate, $endDate);
            
            case Report::TYPE_PROFIT_ANALYSIS:
                return $this->generateProfitAnalysisReport($startDate, $endDate);
            
            default:
                return [];
        }
    }

    /**
     * Generate daily sales report
     */
    private function generateDailySalesReport($startDate, $endDate)
    {
        $dailySales = Transaction::select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(discount_amount) as total_discount')
            )
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_transactions' => $dailySales->sum('transaction_count'),
            'total_sales' => $dailySales->sum('total_sales'),
            'total_subtotal' => $dailySales->sum('subtotal'),
            'total_tax' => $dailySales->sum('total_tax'),
            'total_discount' => $dailySales->sum('total_discount'),
            'average_daily_sales' => $dailySales->avg('total_sales'),
            'best_day' => $dailySales->sortByDesc('total_sales')->first(),
        ];

        return [
            'type' => 'daily_sales',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $dailySales,
        ];
    }

    /**
     * Generate monthly sales report
     */
    private function generateMonthlySalesReport($startDate, $endDate)
    {
        $monthlySales = Transaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(discount_amount) as total_discount')
            )
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $summary = [
            'total_transactions' => $monthlySales->sum('transaction_count'),
            'total_sales' => $monthlySales->sum('total_sales'),
            'total_subtotal' => $monthlySales->sum('subtotal'),
            'total_tax' => $monthlySales->sum('total_tax'),
            'total_discount' => $monthlySales->sum('total_discount'),
            'average_monthly_sales' => $monthlySales->avg('total_sales'),
            'best_month' => $monthlySales->sortByDesc('total_sales')->first(),
        ];

        return [
            'type' => 'monthly_sales',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $monthlySales,
        ];
    }

    /**
     * Generate stock report
     */
    private function generateStockReport()
    {
        $items = Item::with(['category', 'supplier'])
            ->orderBy('stock_quantity', 'desc')
            ->get();

        $summary = [
            'total_items' => $items->count(),
            'total_stock_value' => $items->sum(function($item) {
                return $item->stock_quantity * $item->purchase_price;
            }),
            'low_stock_items' => $items->where('stock_quantity', '<=', DB::raw('minimum_stock'))->count(),
            'out_of_stock_items' => $items->where('stock_quantity', 0)->count(),
            'categories_count' => $items->groupBy('category_id')->count(),
        ];

        return [
            'type' => 'stock_report',
            'summary' => $summary,
            'data' => $items,
        ];
    }

    /**
     * Generate low stock report
     */
    private function generateLowStockReport()
    {
        $lowStockItems = Item::with(['category', 'supplier'])
            ->where('stock_quantity', '<=', DB::raw('minimum_stock'))
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $summary = [
            'total_low_stock_items' => $lowStockItems->count(),
            'out_of_stock_items' => $lowStockItems->where('stock_quantity', 0)->count(),
            'critical_stock_items' => $lowStockItems->where('stock_quantity', '<=', DB::raw('minimum_stock * 0.5'))->count(),
        ];

        return [
            'type' => 'low_stock',
            'summary' => $summary,
            'data' => $lowStockItems,
        ];
    }

    /**
     * Generate item trends report
     */
    private function generateItemTrendsReport($startDate, $endDate)
    {
        $itemTrends = TransactionItem::select(
                'item_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(quantity * unit_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count')
            )
            ->with('item.category')
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate])
                      ->where('status', 'completed');
            })
            ->groupBy('item_id')
            ->orderBy('total_sold', 'desc')
            ->get();

        $summary = [
            'total_items_sold' => $itemTrends->count(),
            'total_quantity_sold' => $itemTrends->sum('total_sold'),
            'total_revenue' => $itemTrends->sum('total_revenue'),
            'top_selling_item' => $itemTrends->first(),
        ];

        return [
            'type' => 'item_trends',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $itemTrends,
        ];
    }

    /**
     * Generate cashier performance report
     */
    private function generateCashierPerformanceReport($startDate, $endDate)
    {
        $cashierPerformance = Transaction::select(
                'cashier_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_transaction'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(discount_amount) as total_discount')
            )
            ->with('cashier')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('cashier_id')
            ->orderBy('total_sales', 'desc')
            ->get();

        $summary = [
            'total_cashiers' => $cashierPerformance->count(),
            'total_transactions' => $cashierPerformance->sum('transaction_count'),
            'total_sales' => $cashierPerformance->sum('total_sales'),
            'top_performer' => $cashierPerformance->first(),
        ];

        return [
            'type' => 'cashier_performance',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $cashierPerformance,
        ];
    }

    /**
     * Generate sales by category report
     */
    private function generateSalesByCategoryReport($startDate, $endDate)
    {
        $salesByCategory = TransactionItem::select(
                'items.category_id',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT transaction_items.transaction_id) as transaction_count')
            )
            ->join('items', 'transaction_items.item_id', '=', 'items.id')
            ->with('item.category')
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate])
                      ->where('status', 'completed');
            })
            ->groupBy('items.category_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $summary = [
            'total_categories' => $salesByCategory->count(),
            'total_quantity_sold' => $salesByCategory->sum('total_quantity'),
            'total_revenue' => $salesByCategory->sum('total_revenue'),
            'top_category' => $salesByCategory->first(),
        ];

        return [
            'type' => 'sales_by_category',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $salesByCategory,
        ];
    }

    /**
     * Generate profit analysis report
     */
    private function generateProfitAnalysisReport($startDate, $endDate)
    {
        $profitAnalysis = TransactionItem::select(
                'items.id',
                'items.name',
                'items.purchase_price',
                DB::raw('SUM(transaction_items.quantity) as total_sold'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_revenue'),
                DB::raw('SUM(transaction_items.quantity * items.purchase_price) as total_cost'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) - SUM(transaction_items.quantity * items.purchase_price) as total_profit')
            )
            ->join('items', 'transaction_items.item_id', '=', 'items.id')
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate])
                      ->where('status', 'completed');
            })
            ->groupBy('items.id', 'items.name', 'items.purchase_price')
            ->orderBy('total_profit', 'desc')
            ->get();

        $summary = [
            'total_items' => $profitAnalysis->count(),
            'total_revenue' => $profitAnalysis->sum('total_revenue'),
            'total_cost' => $profitAnalysis->sum('total_cost'),
            'total_profit' => $profitAnalysis->sum('total_profit'),
            'profit_margin' => $profitAnalysis->sum('total_revenue') > 0 
                ? ($profitAnalysis->sum('total_profit') / $profitAnalysis->sum('total_revenue')) * 100 
                : 0,
            'top_profit_item' => $profitAnalysis->first(),
        ];

        return [
            'type' => 'profit_analysis',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'data' => $profitAnalysis,
        ];
    }

    /**
     * Export report to file
     */
    private function exportReport($report, $format)
    {
        $fileName = 'reports/' . $report->type . '_' . date('Y-m-d_H-i-s') . '.' . $format;
        
        switch ($format) {
            case 'pdf':
                $pdf = PDF::loadView('pages.reports.pdf.' . $report->type, compact('report'));
                $pdf->save(storage_path('app/' . $fileName));
                break;
                
            case 'excel':
                // Excel export implementation would go here
                // For now, we'll create a simple CSV
                $this->exportToCsv($report, $fileName);
                break;
                
            default:
                // JSON export
                file_put_contents(storage_path('app/' . $fileName), json_encode($report->data, JSON_PRETTY_PRINT));
                break;
        }
        
        return $fileName;
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($report, $fileName)
    {
        $data = $report->data;
        $file = fopen(storage_path('app/' . $fileName), 'w');
        
        // Write headers
        if (!empty($data['data'])) {
            fputcsv($file, array_keys((array) $data['data'][0]));
            
            // Write data
            foreach ($data['data'] as $row) {
                fputcsv($file, (array) $row);
            }
        }
        
        fclose($file);
    }
}

