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

            // 1. LẤY TẤT CẢ ĐƠN HÀNG TRONG KHOẢNG THỜI GIAN
            // Dùng Eager Loading để lấy Items, tránh lỗi N+1
            $allOrders = Order::with('items')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $orders = $allOrders->where('status', 'completed');

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
            
            $pendingOrders = $allOrders->where('status', 'pending')->count();
            $cancelledOrders = $allOrders->whereIn('status', ['cancelled', 'returned'])->count();
            $averageOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;
            $lowStockProducts = \App\Models\ProductVariant::whereHas('product')->where('stock_quantity', '<=', 5)->count();

            // Tính tỷ lệ phương thức thanh toán dựa trên DOANH THU của số đơn hoàn tất
            $totalCompletedRevenue = $orders->sum('total_amount');
            $paymentMethodStats = [
                'cod' => $totalCompletedRevenue > 0 ? round($orders->where('payment_method', 'cod')->sum('total_amount') / $totalCompletedRevenue * 100) : 0,
                'vnpay' => $totalCompletedRevenue > 0 ? round($orders->where('payment_method', 'vnpay')->sum('total_amount') / $totalCompletedRevenue * 100) : 0,
                'momo' => $totalCompletedRevenue > 0 ? round($orders->where('payment_method', 'momo')->sum('total_amount') / $totalCompletedRevenue * 100) : 0,
            ];

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
                // Nhóm theo Tháng/Năm để thấy biến động chi tiết
                if ($orders->isEmpty()) {
                    $chartDataRaw[Carbon::now()->format('m/Y')] = ['date' => Carbon::now()->format('m/Y'), 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                } else {
                    $minDate = Carbon::parse($orders->min('created_at'))->startOfMonth();
                    $maxDate = Carbon::parse($orders->max('created_at'))->endOfMonth();
                    
                    $period = CarbonPeriod::create($minDate, '1 month', $maxDate);
                    foreach ($period as $date) {
                        $dateStr = $date->format('m/Y');
                        $chartDataRaw[$dateStr] = ['date' => $dateStr, 'revenue' => 0, 'profit' => 0, 'cost' => 0];
                    }
                    foreach ($orders as $order) {
                        $key = Carbon::parse($order->created_at)->format('m/Y');
                        if (isset($chartDataRaw[$key])) {
                            $cost = $order->items->sum(fn($i) => (float)($i->cost_price * $i->quantity));
                            $chartDataRaw[$key]['revenue'] += $order->total_amount;
                            $chartDataRaw[$key]['cost'] += $cost;
                            $chartDataRaw[$key]['profit'] = $chartDataRaw[$key]['revenue'] - $chartDataRaw[$key]['cost'];
                        }
                    }
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
            // Lấy danh sách Top Combo Lookbook
            $subLookbook = \DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereNotNull('order_items.lookbook_id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'order_items.lookbook_id',
                    \DB::raw('MAX(order_items.quantity) as combo_qty'),
                    \DB::raw('SUM(order_items.total_price) as combo_revenue')
                )
                ->groupBy('order_items.order_id', 'order_items.lookbook_id');

            $topLookbooks = \DB::table(\DB::raw('(' . $subLookbook->toSql() . ') as sub'))
                ->mergeBindings($subLookbook)
                ->join('lookbooks', 'sub.lookbook_id', '=', 'lookbooks.id')
                ->select(
                    'lookbooks.id as lookbook_id',
                    'lookbooks.name as lookbook_name',
                    \DB::raw('SUM(sub.combo_qty) as total_sold'),
                    \DB::raw('SUM(sub.combo_revenue) as total_revenue')
                )
                ->groupBy('lookbooks.id', 'lookbooks.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

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

            // 6. TOP BUYERS (Khách mua nhiều nhất)
            $topBuyers = Order::with(['user:id,full_name,email,avatar_url,gender,tier_id', 'user.tier:id,name'])
                ->whereNotNull('user_id')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('user_id, sum(total_amount) as total_spent, count(id) as total_orders')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();

            $allTiers = \App\Models\MembershipTier::orderBy('min_spent', 'asc')->get();

            $topBuyers->transform(function ($buyer) use ($allTiers) {
                if ($buyer->user) {
                    // Check if tier is missing or null, calculate it dynamically based on their total_spent
                    if (!$buyer->user->tier_id || !$buyer->user->tier) {
                        $lifetimeSpent = floatval($buyer->user->total_spent ?? $buyer->total_spent);
                        $matchedTier = null;
                        foreach ($allTiers as $t) {
                            if ($lifetimeSpent >= $t->min_spent) {
                                $matchedTier = $t;
                            }
                        }
                        if ($matchedTier) {
                            $buyer->user->setRelation('tier', $matchedTier);
                        }
                    }
                }
                return $buyer;
            });

            // 7. LOW STOCK LIST (Sắp hết hàng hoặc tồn ít nhất)
            $lowStockList = \App\Models\ProductVariant::with(['product:id,name,thumbnail_image', 'attributeValues.attribute'])
                ->whereHas('product')
                ->orderBy('stock_quantity', 'asc')
                ->limit(5)
                ->get(['id', 'product_id', 'sku', 'stock_quantity']);

            // 8. RECENT REVIEWS (Đánh giá mới nhất)
            $recentReviews = \App\Models\Review::with(['user:id,full_name,avatar_url', 'product:id,name'])
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'user_id', 'product_id', 'rating', 'comment', 'created_at']);

            // 9. DOANH THU THEO DANH MỤC
            $categoryRevenue = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->whereHas('order', function($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->selectRaw('categories.name as category_name, SUM(order_items.total_price) as total_revenue')
                ->groupBy('categories.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();

            // 10. PHÂN BỔ KHÁCH HÀNG (TOP 5 KHU VỰC)
            $ordersWithRegion = Order::where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotNull('shipping_info')
                    ->get(['shipping_info', 'total_amount']);
                    
            $regions = [];
            foreach ($ordersWithRegion as $order) {
                $city = $order->shipping_info['city'] ?? 'Khác';
                if (!isset($regions[$city])) {
                    $regions[$city] = ['region_name' => $city, 'total_orders' => 0, 'total_revenue' => 0];
                }
                $regions[$city]['total_orders']++;
                $regions[$city]['total_revenue'] += $order->total_amount;
            }
            usort($regions, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);
            $topRegions = array_slice($regions, 0, 5);

            // 11. NHÓM KH CHỦ LỰC (Gender)
            $maleCount = User::whereHas('orders', function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
            })->where('gender', 'Nam')->count();
            
            $femaleCount = User::whereHas('orders', function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
            })->where('gender', 'Nữ')->count();
            
            $topGender = 'Chưa xác định';
            if ($maleCount > 0 || $femaleCount > 0) {
                $topGender = $maleCount > $femaleCount ? 'Nam' : 'Nữ';
            }

            // 12. VOUCHER USAGE CHART
            $voucherChartData = [];
            
            $vFirstDate = $startDate->copy()->startOfDay();
            $vLastDate = \Carbon\Carbon::now()->endOfDay();
            
            if ($timeRange === 'all') {
                // Mặc định lấy 6 tháng gần nhất cho "Tất cả" để biểu đồ không bị quá dày
                $vFirstDate = $vLastDate->copy()->subMonths(5)->startOfMonth(); 
            }

            $ordersWithVoucher = Order::whereNotNull('voucher_id')
                ->whereBetween('created_at', [$vFirstDate, $vLastDate])
                ->get(['id', 'created_at']);

            $isGroupingByMonth = $vFirstDate->diffInDays($vLastDate) > 35;

            if ($isGroupingByMonth) {
                // Nhóm theo tháng
                $period = \Carbon\CarbonPeriod::create($vFirstDate->copy()->startOfMonth(), '1 month', $vLastDate->copy()->startOfMonth());
                $usageMap = $ordersWithVoucher->groupBy(function($order) {
                    return \Carbon\Carbon::parse($order->created_at)->format('m/Y');
                })->map->count();

                foreach ($period as $date) {
                    $dateStr = $date->format('m/Y');
                    $voucherChartData[] = [
                        'date' => $dateStr,
                        'count' => $usageMap->get($dateStr, 0)
                    ];
                }
            } else {
                // Nhóm theo ngày
                $period = \Carbon\CarbonPeriod::create($vFirstDate, '1 day', $vLastDate);
                $usageMap = $ordersWithVoucher->groupBy(function($order) {
                    return \Carbon\Carbon::parse($order->created_at)->format('Y-m-d');
                })->map->count();

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $voucherChartData[] = [
                        'date' => $date->format('d/m'),
                        'count' => $usageMap->get($dateStr, 0)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'revenue' => $totalRevenue,
                        'profit' => $totalProfit,
                        'orders' => $totalOrders,
                        'customers' => $totalCustomers,
                        'products_sold' => $totalProductsSold,
                        'pending_orders' => $pendingOrders,
                        'cancelled_orders' => $cancelledOrders,
                        'average_order_value' => $averageOrderValue,
                        'low_stock' => $lowStockProducts,
                    ],
                    'payment_stats' => $paymentMethodStats,
                    'chart' => [
                        'data' => $chartData
                    ],
                    'top_products' => $topProducts,
                    'top_combos' => $topLookbooks,
                    'recent_orders' => $recentOrders,
                    'top_buyers' => $topBuyers,
                    'low_stock_list' => $lowStockList,
                    'recent_reviews' => $recentReviews,
                    'category_revenue' => $categoryRevenue,
                    'top_regions' => $topRegions,
                    'customer_insights' => ['topGender' => ['gender' => $topGender]],
                    'voucher_usage_chart' => $voucherChartData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi Dashboard: ' . $e->getMessage()], 500);
        }
    }
}