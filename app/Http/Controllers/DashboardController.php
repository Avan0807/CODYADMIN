<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Doctor;
use App\Models\AffiliateOrder;
use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Main dashboard page
     */
    public function index()
    {
        // User registration data for pie chart (last 7 days)
        $users = $this->getUserRegistrationData();
        dd($users);
        return view('backend.index', compact('users'));
    }

    /**
     * Get user registration data for pie chart
     */
    private function getUserRegistrationData()
    {
        $users = [];
        $users[] = ['Day', 'Users'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('l'); // Monday, Tuesday, etc.
            $count = User::whereDate('created_at', $date->toDateString())->count();
            $users[] = [$dayName, $count];
        }
        
        return json_encode($users);
    }

    /**
     * API: Top selling products
     */
    public function topProducts()
    {
        $topProducts = Product::select('products.id', 'products.title')
            ->leftJoin('carts', 'products.id', '=', 'carts.product_id')
            ->leftJoin('orders', 'carts.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->groupBy('products.id', 'products.title')
            ->selectRaw('products.id, products.title, SUM(carts.quantity) as total_sold')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json($topProducts);
    }

    /**
     * API: Order status trend over time
     */
    public function orderStatusTrend()
    {
        $months = [];
        $statuses = ['new', 'process', 'delivered', 'cancel'];
        $result = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('M Y');
            $months[] = $monthKey;
        }

        // Initialize result array
        foreach ($statuses as $status) {
            $result[$status] = [];
            foreach ($months as $month) {
                $result[$status][$month] = 0;
            }
        }

        // Get order counts by status and month
        $orderData = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
            'status',
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->whereIn('status', $statuses)
        ->groupBy('month', 'status')
        ->get();

        // Fill result with actual data
        foreach ($orderData as $data) {
            if (isset($result[$data->status][$data->month])) {
                $result[$data->status][$data->month] = (int) $data->count;
            }
        }

        return response()->json($result);
    }

    /**
     * API: Revenue vs Commission comparison
     */
    public function revenueVsCommission()
    {
        $months = [];
        $revenue = [];
        $commission = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('M Y');
            $months[] = $monthKey;

            // Calculate revenue (delivered orders)
            $monthRevenue = Order::where('status', 'delivered')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');

            // Calculate commission for the month
            $monthCommission = AffiliateOrder::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('commission');

            $revenue[$monthKey] = (float) $monthRevenue;
            $commission[$monthKey] = (float) $monthCommission;
        }

        return response()->json([
            'revenue' => $revenue,
            'commission' => $commission
        ]);
    }

    /**
     * API: Top doctors by commission
     */
    public function topDoctors()
    {
        $topDoctors = Doctor::select('doctors.id', 'doctors.name')
            ->leftJoin('affiliate_orders', 'doctors.id', '=', 'affiliate_orders.doctor_id')
            ->groupBy('doctors.id', 'doctors.name')
            ->selectRaw('doctors.id, doctors.name, COALESCE(SUM(affiliate_orders.commission), 0) as total_commission')
            ->orderByDesc('total_commission')
            ->having('total_commission', '>', 0)
            ->limit(10)
            ->get();

        return response()->json($topDoctors);
    }

    /**
     * API: Order growth percentage
     */
    public function orderGrowth()
    {
        $growthData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $currentMonth = Carbon::now()->subMonths($i);
            $previousMonth = Carbon::now()->subMonths($i + 1);
            
            $monthKey = $currentMonth->format('M Y');
            
            // Current month orders
            $currentCount = Order::whereYear('created_at', $currentMonth->year)
                ->whereMonth('created_at', $currentMonth->month)
                ->count();
            
            // Previous month orders
            $previousCount = Order::whereYear('created_at', $previousMonth->year)
                ->whereMonth('created_at', $previousMonth->month)
                ->count();
            
            // Calculate growth percentage
            if ($previousCount > 0) {
                $growth = (($currentCount - $previousCount) / $previousCount) * 100;
            } else {
                $growth = $currentCount > 0 ? 100 : 0;
            }
            
            $growthData[$monthKey] = round($growth, 2);
        }

        return response()->json($growthData);
    }

    /**
     * API: Monthly income (existing route)
     */
    public function incomeChart()
    {
        $year = date('Y');
        $incomes = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            
            $income = Order::where('status', 'delivered')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount');

            $incomes[$monthName] = (float) $income;
        }

        return response()->json($incomes);
    }

    /**
     * API: Doctor income chart (existing route)
     */
    public function doctorIncomeChart()
    {
        $year = date('Y');
        $incomes = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            
            $commission = AffiliateOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('commission');

            $incomes[$monthName] = (float) $commission;
        }

        return response()->json($incomes);
    }

    /**
     * Dashboard statistics summary
     */
    public function getStats()
    {
        $stats = [
            // Basic counts
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_posts' => Post::count(),
            
            // Order status counts
            'new_orders' => Order::where('status', 'new')->count(),
            'processing_orders' => Order::where('status', 'process')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancel')->count(),
            
            // Affiliate stats
            'total_affiliate_orders' => AffiliateOrder::count(),
            'pending_commission' => AffiliateOrder::where('status', 'pending')->sum('commission'),
            'paid_commission' => AffiliateOrder::where('status', 'paid')->sum('commission'),
            'total_doctors' => Doctor::count(),
            
            // Revenue stats
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
            'monthly_revenue' => Order::where('status', 'delivered')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount'),
            
            // Growth stats
            'revenue_growth' => $this->calculateRevenueGrowth(),
            'order_growth' => $this->calculateOrderGrowth(),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate revenue growth percentage (current month vs previous month)
     */
    private function calculateRevenueGrowth()
    {
        $currentMonth = Order::where('status', 'delivered')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');

        $previousMonth = Order::where('status', 'delivered')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_amount');

        if ($previousMonth > 0) {
            return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
        }

        return $currentMonth > 0 ? 100 : 0;
    }

    /**
     * Calculate order growth percentage (current month vs previous month)
     */
    private function calculateOrderGrowth()
    {
        $currentMonth = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $previousMonth = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        if ($previousMonth > 0) {
            return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
        }

        return $currentMonth > 0 ? 100 : 0;
    }

    /**
     * Export dashboard data
     */
    public function exportData(Request $request)
    {
        $type = $request->get('type', 'revenue'); // revenue, orders, commissions

        switch ($type) {
            case 'revenue':
                return $this->exportRevenueData();
            case 'orders':
                return $this->exportOrderData();
            case 'commissions':
                return $this->exportCommissionData();
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }
    }

    /**
     * Export revenue data as CSV
     */
    private function exportRevenueData()
    {
        $data = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as order_count')
        )
        ->where('status', 'delivered')
        ->where('created_at', '>=', Carbon::now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, ['Tháng', 'Doanh thu', 'Số đơn hàng']);
            
            // Data
            foreach ($data as $row) {
                fputcsv($handle, [
                    Carbon::parse($row->month . '-01')->format('m/Y'),
                    number_format($row->revenue, 0),
                    $row->order_count
                ]);
            }
            
            fclose($handle);
        }, 'revenue_report_' . date('Y-m-d') . '.csv');
    }

    /**
     * Export order data as CSV
     */
    private function exportOrderData()
    {
        $data = Order::with(['user:id,name,email'])
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'Mã đơn hàng', 'Khách hàng', 'Email', 'Tổng tiền', 
                'Trạng thái', 'Ngày đặt', 'Phương thức thanh toán'
            ]);
            
            // Data
            foreach ($data as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->first_name . ' ' . $order->last_name,
                    $order->email,
                    number_format($order->total_amount, 0),
                    $order->status,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->payment_method
                ]);
            }
            
            fclose($handle);
        }, 'orders_report_' . date('Y-m-d') . '.csv');
    }

    /**
     * Export commission data as CSV
     */
    private function exportCommissionData()
    {
        $data = AffiliateOrder::with(['order:id,order_number', 'doctor:id,name'])
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'Mã đơn hàng', 'Bác sĩ', 'Hoa hồng', 'Trạng thái', 
                'Ngày tạo', 'Ngày thanh toán'
            ]);
            
            // Data
            foreach ($data as $affiliate) {
                fputcsv($handle, [
                    $affiliate->order->order_number ?? 'N/A',
                    $affiliate->doctor->name ?? 'N/A',
                    number_format($affiliate->commission, 0),
                    $affiliate->status,
                    $affiliate->created_at->format('d/m/Y'),
                    $affiliate->paid_at ? $affiliate->paid_at->format('d/m/Y') : ''
                ]);
            }
            
            fclose($handle);
        }, 'commissions_report_' . date('Y-m-d') . '.csv');
    }
}