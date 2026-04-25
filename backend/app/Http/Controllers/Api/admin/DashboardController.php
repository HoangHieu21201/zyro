<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function getStatistics(Request $request)
    {
        try {
            $timeRange = $request->query('range', 'this_month'); 
            
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            switch ($timeRange) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                case 'all':
                    $startDate = Carbon::create(2020, 1, 1); // Tránh lỗi từ năm 1970
                    $endDate = Carbon::now()->endOfDay();
                    break;
            }

            // 1. LẤY TẤT CẢ ĐƠN HÀNG THÀNH CÔNG TRONG KHOẢNG THỜI GIAN
            // Dùng Eager Loading để lấy Items, tránh lỗi N+1
            $orders = Order::with('items')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // 2. TÍNH TỔNG QUAN (SUMMARY)
            $totalRevenue = $orders->sum('total_amount');
            
            // Tính tổng giá vốn (cost_price * quantity)
            $totalCost = $orders->flatMap->items->sum(function ($item) {
                return (float) ($item->cost_price * $item->quantity);
            });
            $totalProfit = $totalRevenue - $totalCost;

            $totalOrders = $orders->count();
            $totalProductsSold = $orders->flatMap->items->sum('quantity');
            $totalCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();

            // 3. TẠO DỮ LIỆU BIỂU ĐỒ (CHART DATA) ĐỘNG THEO RANGE
            $chartDataRaw = [];

            if ($timeRange === 'today') {
                // Nhóm theo Giờ (00:00 -> 23:00)
                for ($i = 0; $i < 24; $i++) {
                    $hourStr = sprintf('%02d:00', $i);
                    $chartDataRaw[$hourStr] = ['date' => $hourStr, 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                }
                foreach ($orders as $order) {
                    $key = Carbon::parse($order->created_at)->format('H:00');
                    $cost = $order->items->sum(fn($i) => (float)($i->cost_price * $i->quantity));
                    $chartDataRaw[$key]['revenue'] += $order->total_amount;
                    $chartDataRaw[$key]['cost'] += $cost;
                    $chartDataRaw[$key]['profit'] = $chartDataRaw[$key]['revenue'] - $chartDataRaw[$key]['cost'];
                }

            } elseif ($timeRange === 'this_year') {
                // Nhóm theo Tháng (Tháng 1 -> Tháng 12)
                for ($i = 1; $i <= 12; $i++) {
                    $monthStr = "Tháng $i";
                    $chartDataRaw[$monthStr] = ['date' => $monthStr, 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                }
                foreach ($orders as $order) {
                    $key = "Tháng " . Carbon::parse($order->created_at)->format('n');
                    $cost = $order->items->sum(fn($i) => (float)($i->cost_price * $i->quantity));
                    $chartDataRaw[$key]['revenue'] += $order->total_amount;
                    $chartDataRaw[$key]['cost'] += $cost;
                    $chartDataRaw[$key]['profit'] = $chartDataRaw[$key]['revenue'] - $chartDataRaw[$key]['cost'];
                }

            } elseif ($timeRange === 'all') {
                // Nhóm theo Năm
                $years = $orders->pluck('created_at')->map(fn($d) => Carbon::parse($d)->format('Y'))->unique()->sort();
                foreach ($years as $year) {
                    $chartDataRaw[$year] = ['date' => $year, 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                }
                foreach ($orders as $order) {
                    $key = Carbon::parse($order->created_at)->format('Y');
                    $cost = $order->items->sum(fn($i) => (float)($i->cost_price * $i->quantity));
                    $chartDataRaw[$key]['revenue'] += $order->total_amount;
                    $chartDataRaw[$key]['cost'] += $cost;
                    $chartDataRaw[$key]['profit'] = $chartDataRaw[$key]['revenue'] - $chartDataRaw[$key]['cost'];
                }

            } else {
                // Nhóm theo Ngày (this_week, this_month)
                $period = CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->endOfDay());
                foreach ($period as $date) {
                    $dateStr = $date->format('d/m');
                    $chartDataRaw[$dateStr] = ['date' => $dateStr, 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                }
                foreach ($orders as $order) {
                    $key = Carbon::parse($order->created_at)->format('d/m');
                    if (isset($chartDataRaw[$key])) {
                        $cost = $order->items->sum(fn($i) => (float)($i->cost_price * $i->quantity));
                        $chartDataRaw[$key]['revenue'] += $order->total_amount;
                        $chartDataRaw[$key]['cost'] += $cost;
                        $chartDataRaw[$key]['profit'] = $chartDataRaw[$key]['revenue'] - $chartDataRaw[$key]['cost'];
                    }
                }
            }

            $chartData = array_values($chartDataRaw);

            // 4. TOP 5 SẢN PHẨM BÁN CHẠY NHẤT
            // ĐÃ NÂNG CẤP: Left Join với bảng products để lấy thông tin deleted_at
            $topProducts = OrderItem::leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->select('order_items.product_id', 'order_items.product_name', 'order_items.variant_image', 'products.deleted_at as product_deleted_at')
                ->selectRaw('SUM(order_items.quantity) as total_sold')
                ->selectRaw('SUM(order_items.total_price) as total_revenue')
                ->whereHas('order', function($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                      ->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->groupBy('order_items.product_id', 'order_items.product_name', 'order_items.variant_image', 'products.deleted_at')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            // 5. 5 ĐƠN HÀNG MỚI NHẤT
            $recentOrders = Order::with('user:id,full_name,email')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'order_code', 'user_id', 'total_amount', 'status', 'payment_method', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'revenue' => $totalRevenue,
                        'profit' => $totalProfit,
                        'orders' => $totalOrders,
                        'customers' => $totalCustomers,
                        'products_sold' => $totalProductsSold,
                    ],
                    'chart' => [
                        'data' => $chartData
                    ],
                    'top_products' => $topProducts,
                    'recent_orders' => $recentOrders
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Dashboard: ' . $e->getMessage()], 500);
        }
    }
}