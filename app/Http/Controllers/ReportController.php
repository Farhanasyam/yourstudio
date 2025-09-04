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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'type.required' => 'Jenis laporan harus dipilih.',
            'type.in' => 'Jenis laporan tidak valid.',
            'name.required' => 'Nama laporan harus diisi.',
            'name.max' => 'Nama laporan maksimal 255 karakter.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $type = $request->type;
        
        // Check if report type needs date range
        $needsDateRange = in_array($type, [
            Report::TYPE_DAILY_SALES,
            Report::TYPE_MONTHLY_SALES,
            Report::TYPE_ITEM_TRENDS,
            Report::TYPE_CASHIER_PERFORMANCE,
            Report::TYPE_SALES_BY_CATEGORY,
            Report::TYPE_PROFIT_ANALYSIS
        ]);

        if ($needsDateRange) {
            if (!$request->start_date || !$request->end_date) {
                return back()->withErrors(['date_range' => 'Tanggal mulai dan selesai diperlukan untuk jenis laporan ini.'])->withInput();
            }
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        try {
            // Generate report data
            $data = $this->generateReportData($type, $startDate, $endDate);

            // Create report record
            $report = Report::create([
                'name' => $request->name,
                'type' => $type,
                'parameters' => [
                    'start_date' => $startDate ? $startDate->toDateString() : null,
                    'end_date' => $endDate ? $endDate->toDateString() : null,
                ],
                'data' => $data,
                'generated_by' => Auth::id(),
                'generated_at' => now()->setTimezone('Asia/Jakarta'),
            ]);

            return redirect()->route('reports.show', $report)
                ->with('success', 'Laporan berhasil dibuat!');

        } catch (\Exception $e) {
            \Log::error('Report generation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat membuat laporan. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Display generated report
     */
    public function show(Report $report)
    {
        return view('pages.reports.show', compact('report'));
    }



    /**
     * Delete report
     */
    public function destroy(Report $report)
    {
        try {
            $report->delete();

            return redirect()->route('reports.index')
                ->with('success', 'Laporan berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Report deletion failed: ' . $e->getMessage());
            return redirect()->route('reports.index')
                ->with('error', 'Gagal menghapus laporan. Silakan coba lagi.');
        }
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData($type, $startDate, $endDate)
    {
        try {
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
                    return [
                        'type' => $type,
                        'summary' => ['error' => 'Jenis laporan tidak valid'],
                        'data' => []
                    ];
            }
        } catch (\Exception $e) {
            \Log::error('Error generating report: ' . $e->getMessage());
            return [
                'type' => $type,
                'summary' => ['error' => 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage()],
                'data' => []
            ];
        }
    }

    /**
     * Generate daily sales report
     */
    private function generateDailySalesReport($startDate, $endDate)
    {
        $dailySales = Transaction::select(
                DB::raw('DATE(COALESCE(transaction_date, created_at)) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(COALESCE(subtotal, total_amount)) as subtotal'),
            )
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->whereDate('created_at', '>=', $startDate->toDateString())
                            ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
            })
            ->where(function($query) {
                $query->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_transactions' => $dailySales->sum('transaction_count'),
            'total_sales' => $dailySales->sum('total_sales'),
            'total_subtotal' => $dailySales->sum('subtotal'),
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
                DB::raw('YEAR(COALESCE(transaction_date, created_at)) as year'),
                DB::raw('MONTH(COALESCE(transaction_date, created_at)) as month'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(COALESCE(subtotal, total_amount)) as subtotal'),
            )
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->whereDate('created_at', '>=', $startDate->toDateString())
                            ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
            })
            ->where(function($query) {
                $query->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
            })
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $summary = [
            'total_transactions' => $monthlySales->sum('transaction_count'),
            'total_sales' => $monthlySales->sum('total_sales'),
            'total_subtotal' => $monthlySales->sum('subtotal'),
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
            ->select([
                'id', 'name', 'description', 'sku', 'category_id', 'supplier_id',
                'purchase_price', 'selling_price', 'stock_quantity', 'minimum_stock', 'unit', 'is_active'
            ])
            ->orderBy('stock_quantity', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'sku' => $item->sku,
                    'category_name' => $item->category ? $item->category->name : 'Tidak Ada Kategori',
                    'supplier_name' => $item->supplier ? $item->supplier->name : 'Tidak Ada Supplier',
                    'purchase_price' => $item->purchase_price,
                    'selling_price' => $item->selling_price,
                    'stock_quantity' => $item->stock_quantity,
                    'minimum_stock' => $item->minimum_stock,
                    'unit' => $item->unit,
                    'is_active' => $item->is_active ? 'Aktif' : 'Tidak Aktif',
                    'stock_value' => $item->stock_quantity * $item->purchase_price,
                    'is_low_stock' => $item->stock_quantity <= $item->minimum_stock ? 'Ya' : 'Tidak',
                ];
            });

        $summary = [
            'total_items' => $items->count(),
            'total_stock_value' => $items->sum('stock_value'),
            'low_stock_items' => $items->where('is_low_stock', 'Ya')->count(),
            'out_of_stock_items' => $items->where('stock_quantity', 0)->count(),
            'categories_count' => $items->groupBy('category_name')->count(),
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
            ->select([
                'id', 'name', 'description', 'sku', 'category_id', 'supplier_id',
                'purchase_price', 'selling_price', 'stock_quantity', 'minimum_stock', 'unit', 'is_active'
            ])
            ->get()
            ->filter(function($item) {
                return $item->stock_quantity <= $item->minimum_stock;
            })
            ->sortBy('stock_quantity')
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'sku' => $item->sku,
                    'category_name' => $item->category ? $item->category->name : 'Tidak Ada Kategori',
                    'supplier_name' => $item->supplier ? $item->supplier->name : 'Tidak Ada Supplier',
                    'purchase_price' => $item->purchase_price,
                    'selling_price' => $item->selling_price,
                    'stock_quantity' => $item->stock_quantity,
                    'minimum_stock' => $item->minimum_stock,
                    'unit' => $item->unit,
                    'is_active' => $item->is_active ? 'Aktif' : 'Tidak Aktif',
                    'stock_value' => $item->stock_quantity * $item->purchase_price,
                    'is_low_stock' => $item->stock_quantity <= $item->minimum_stock ? 'Ya' : 'Tidak',
                ];
            });

        $summary = [
            'total_low_stock_items' => $lowStockItems->count(),
            'out_of_stock_items' => $lowStockItems->where('stock_quantity', 0)->count(),
            'critical_stock_items' => $lowStockItems->filter(function($item) {
                return $item['stock_quantity'] <= ($item['minimum_stock'] * 0.5);
            })->count(),
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
            ->with(['item' => function($query) {
                $query->with('category');
            }])
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereDate('created_at', '>=', $startDate->toDateString())
                               ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
                })
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
                });
            })
            ->groupBy('item_id')
            ->orderBy('total_sold', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item ? $item->item->name : 'Item Tidak Ditemukan',
                    'item_sku' => $item->item ? $item->item->sku : '-',
                    'category_name' => $item->item && $item->item->category ? $item->item->category->name : 'Tidak Ada Kategori',
                    'total_sold' => $item->total_sold,
                    'total_revenue' => $item->total_revenue,
                    'transaction_count' => $item->transaction_count,
                    'average_price' => $item->total_sold > 0 ? $item->total_revenue / $item->total_sold : 0,
                ];
            });

        $topSellingItem = $itemTrends->first();
        $summary = [
            'total_items_sold' => $itemTrends->count(),
            'total_quantity_sold' => $itemTrends->sum('total_sold'),
            'total_revenue' => $itemTrends->sum('total_revenue'),
            'top_selling_item' => $topSellingItem ? [
                'name' => $topSellingItem['item_name'],
                'sku' => $topSellingItem['item_sku'],
                'total_sold' => $topSellingItem['total_sold'],
                'total_revenue' => $topSellingItem['total_revenue'],
            ] : null,
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
                DB::raw('SUM(COALESCE(subtotal, total_amount)) as subtotal'),
            )
            ->with('cashier')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->whereDate('created_at', '>=', $startDate->toDateString())
                            ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
            })
            ->where(function($query) {
                $query->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
            })
            ->groupBy('cashier_id')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function($transaction) {
                return [
                    'cashier_id' => $transaction->cashier_id,
                    'cashier_name' => $transaction->cashier ? $transaction->cashier->name : 'Kasir Tidak Ditemukan',
                    'cashier_email' => $transaction->cashier ? $transaction->cashier->email : '-',
                    'transaction_count' => $transaction->transaction_count,
                    'total_sales' => $transaction->total_sales,
                    'average_transaction' => $transaction->average_transaction,
                    'subtotal' => $transaction->subtotal,
                ];
            });

        $topPerformer = $cashierPerformance->first();
        $summary = [
            'total_cashiers' => $cashierPerformance->count(),
            'total_transactions' => $cashierPerformance->sum('transaction_count'),
            'total_sales' => $cashierPerformance->sum('total_sales'),
            'top_performer' => $topPerformer ? [
                'name' => $topPerformer['cashier_name'],
                'email' => $topPerformer['cashier_email'],
                'total_sales' => $topPerformer['total_sales'],
                'transaction_count' => $topPerformer['transaction_count'],
            ] : null,
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
                'categories.name as category_name',
                'categories.code as category_code',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT transaction_items.transaction_id) as transaction_count')
            )
            ->join('items', 'transaction_items.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereDate('created_at', '>=', $startDate->toDateString())
                               ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
                })
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
                });
            })
            ->groupBy('items.category_id', 'categories.name', 'categories.code')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category_name ?? 'Tidak Ada Kategori',
                    'category_code' => $item->category_code ?? '-',
                    'total_quantity' => $item->total_quantity,
                    'total_revenue' => $item->total_revenue,
                    'transaction_count' => $item->transaction_count,
                    'average_revenue_per_transaction' => $item->transaction_count > 0 ? $item->total_revenue / $item->transaction_count : 0,
                ];
            });

        $topCategory = $salesByCategory->first();
        $summary = [
            'total_categories' => $salesByCategory->count(),
            'total_quantity_sold' => $salesByCategory->sum('total_quantity'),
            'total_revenue' => $salesByCategory->sum('total_revenue'),
            'top_category' => $topCategory ? [
                'name' => $topCategory['category_name'],
                'code' => $topCategory['category_code'],
                'total_revenue' => $topCategory['total_revenue'],
                'total_quantity' => $topCategory['total_quantity'],
            ] : null,
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
                'items.sku',
                'items.purchase_price',
                DB::raw('SUM(transaction_items.quantity) as total_sold'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_revenue'),
                DB::raw('SUM(transaction_items.quantity * items.purchase_price) as total_cost'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) - SUM(transaction_items.quantity * items.purchase_price) as total_profit')
            )
            ->join('items', 'transaction_items.item_id', '=', 'items.id')
            ->whereHas('transaction', function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereDate('transaction_date', '>=', $startDate->toDateString())
                      ->whereDate('transaction_date', '<=', $endDate->toDateString())
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereDate('created_at', '>=', $startDate->toDateString())
                               ->whereDate('created_at', '<=', $endDate->toDateString());
                      });
                })
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhereNull('status')
                      ->orWhere('status', '');
                });
            })
            ->groupBy('items.id', 'items.name', 'items.sku', 'items.purchase_price')
            ->orderBy('total_profit', 'desc')
            ->get()
            ->map(function($item) {
                $profitMargin = $item->total_revenue > 0 ? ($item->total_profit / $item->total_revenue) * 100 : 0;
                return [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_sku' => $item->sku,
                    'purchase_price' => $item->purchase_price,
                    'total_sold' => $item->total_sold,
                    'total_revenue' => $item->total_revenue,
                    'total_cost' => $item->total_cost,
                    'total_profit' => $item->total_profit,
                    'profit_margin' => $profitMargin,
                    'average_selling_price' => $item->total_sold > 0 ? $item->total_revenue / $item->total_sold : 0,
                ];
            });

        $topProfitItem = $profitAnalysis->first();
        $summary = [
            'total_items' => $profitAnalysis->count(),
            'total_revenue' => $profitAnalysis->sum('total_revenue'),
            'total_cost' => $profitAnalysis->sum('total_cost'),
            'total_profit' => $profitAnalysis->sum('total_profit'),
            'profit_margin' => $profitAnalysis->sum('total_revenue') > 0 
                ? ($profitAnalysis->sum('total_profit') / $profitAnalysis->sum('total_revenue')) * 100 
                : 0,
            'top_profit_item' => $topProfitItem ? [
                'name' => $topProfitItem['item_name'],
                'sku' => $topProfitItem['item_sku'],
                'total_profit' => $topProfitItem['total_profit'],
                'profit_margin' => $topProfitItem['profit_margin'],
            ] : null,
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



}

