<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios'; 
import Chart from 'chart.js/auto';

const router = useRouter();

const isFirstLoad = ref(true);
const isLoading = ref(true);
const timeRange = ref('this_month');

const stats = ref({
    revenue: 0,
    profit: 0,
    orders: 0,
    customers: 0,
    products_sold: 0
});

const chartData = ref([]);
const topProducts = ref([]);
const recentOrders = ref([]);

const chartCanvas = ref(null);
let chartInstance = null;

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};

const formatDate = (dateStr) => {
  if(!dateStr) return '';
  const d = new Date(dateStr);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getStatusBadge = (status) => {
  const map = { 
      'pending': '<span class="badge bg-warning text-dark border border-warning shadow-sm">Chờ xác nhận</span>', 
      'confirmed': '<span class="badge bg-info text-dark border border-info shadow-sm">Đã xác nhận</span>', 
      'processing': '<span class="badge bg-primary text-white border border-primary shadow-sm">Đang xử lý</span>', 
      'shipping': '<span class="badge bg-primary text-white border border-primary shadow-sm">Đang giao</span>', 
      'completed': '<span class="badge bg-success text-white border border-success shadow-sm">Thành công</span>', 
      'cancelled': '<span class="badge bg-danger text-white border border-danger shadow-sm">Đã hủy</span>', 
      'returned': '<span class="badge bg-secondary text-white border border-secondary shadow-sm">Hoàn trả</span>' 
  };
  return map[status] || `<span class="badge bg-secondary border shadow-sm">${status}</span>`;
};

const renderChart = () => {
    if (!chartCanvas.value) return;
    const ctx = chartCanvas.value.getContext('2d');

    if (chartInstance) {
        chartInstance.destroy(); 
    }

    const labels = chartData.value.map(item => item.date);
    const revenueData = chartData.value.map(item => item.revenue);
    const profitData = chartData.value.map(item => item.profit);

    // Bắt Dark Mode để chỉnh màu
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#f8f9fa' : '#495057'; // Màu chữ tương phản cực mạnh
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

    // Đổ bóng Gradient dưới đường Line Lợi Nhuận cho sinh động
    const gradientProfit = ctx.createLinearGradient(0, 0, 0, 400);
    gradientProfit.addColorStop(0, isDark ? 'rgba(255, 71, 87, 0.4)' : 'rgba(255, 71, 87, 0.2)');
    gradientProfit.addColorStop(1, 'rgba(255, 71, 87, 0)');

    // Màu sắc cực rực rỡ (Solid Color) để không bị chìm
    const barColor = isDark ? '#20c997' : '#009981'; // Teal / Urban Green
    const lineProfit = '#ff4757'; // Coral Red (Đỏ dạ quang)

    chartInstance = new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Doanh Thu',
                    data: revenueData,
                    backgroundColor: barColor,
                    borderRadius: { topLeft: 4, topRight: 4 },
                    order: 2,
                    barPercentage: 0.6
                },
                {
                    type: 'line',
                    label: 'Lợi Nhuận',
                    data: profitData,
                    borderColor: lineProfit,
                    backgroundColor: gradientProfit,
                    borderWidth: 3, 
                    fill: true, // ĐÃ THÊM: Đổ màu vùng dưới đường Line (Sinh động)
                    tension: 0.4, // Bo cong mềm mại
                    pointBackgroundColor: isDark ? '#1a2533' : '#fff',
                    pointBorderColor: lineProfit,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    backgroundColor: isDark ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.95)',
                    titleColor: isDark ? '#fff' : '#000',
                    bodyColor: isDark ? '#f8f9fa' : '#212529',
                    borderColor: isDark ? '#373b3e' : '#dee2e6',
                    borderWidth: 1, padding: 12, boxPadding: 6,
                    titleFont: { size: 14 }, bodyFont: { size: 13, weight: 'bold' },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                },
                legend: { 
                    position: 'top', 
                    labels: { color: textColor, font: { family: 'inherit', weight: 'bold', size: 12 }, padding: 20 } 
                }
            },
            scales: {
                x: { 
                    ticks: { color: textColor, font: { family: 'inherit', weight: '500', size: 11 } }, 
                    grid: { display: false } 
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor, font: { family: 'inherit', weight: '500', size: 11 },
                        callback: function(value) {
                            if (value >= 1000000000) return (value / 1000000000) + ' Tỷ';
                            if (value >= 1000000) return (value / 1000000) + ' Tr';
                            if (value >= 1000) return (value / 1000) + ' K';
                            return value;
                        }
                    },
                    grid: { color: gridColor, drawBorder: false }
                }
            }
        }
    });
};

const fetchDashboardData = async () => {
    if (!isFirstLoad.value) {
        isLoading.value = true;
    }
    try {
        const response = await api.get(`/admin/dashboard/statistics?range=${timeRange.value}`);
        let payload = response.data;
        if(payload.data) payload = payload.data; 
        
        stats.value = payload.summary;
        chartData.value = payload.chart.data;
        topProducts.value = payload.top_products;
        recentOrders.value = payload.recent_orders;
    } catch (error) {
        console.error("Lỗi lấy dữ liệu Dashboard:", error);
    } finally {
        isLoading.value = false;
        isFirstLoad.value = false; 
        await nextTick();
        renderChart();
    }
};

watch(() => document.documentElement.classList.contains('dark'), () => {
    if (chartInstance) renderChart();
});

onMounted(() => {
    if (!localStorage.getItem('admin_token')) {
        window.location.href = '/admin/login'; 
        return;
    }
    fetchDashboardData();
});
</script>

<template>
  <div class="dashboard-wrapper w-100 pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
        <h1 class="logo-shimmer mb-3">ZYRO</h1>
        <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-else class="animation-fade-in">
        
        <!-- HEADER -->
        <div class="app-content-header mb-4">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6 d-flex align-items-center">
                        <h3 class="mb-0 text-urban fw-bold"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan Hệ Thống</h3>
                        <span v-if="isLoading" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                        <div class="d-inline-flex bg-white dark:bg-[#1a2533] rounded-3 shadow-sm p-1 border dark:border-gray-700 hover-urban-border transition-all">
                            <select class="form-select border-0 shadow-none fw-semibold text-muted dark:text-gray-300 bg-transparent cursor-pointer px-3" v-model="timeRange" @change="fetchDashboardData" :disabled="isLoading">
                                <option value="today">Hôm nay</option>
                                <option value="this_week">Tuần này</option>
                                <option value="this_month">Tháng này</option>
                                <option value="this_year">Năm nay</option>
                                <option value="all">Toàn thời gian</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content" :class="{'opacity-50 pe-none': isLoading}">
            <div class="container-fluid">
                
                <!-- ROW 1: SUMMARY CARDS -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted dark:text-gray-400 fw-bold text-uppercase mb-2 tracking-wide" style="font-size: 0.75rem;">Tổng Doanh Thu</h6>
                                    <h4 class="fw-black text-dark dark:text-white m-0">{{ formatCurrency(stats.revenue) }}</h4>
                                </div>
                                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 55px; height: 55px;">
                                    <i class="bi bi-wallet2 fs-4 text-urban"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted dark:text-gray-400 fw-bold text-uppercase mb-2 tracking-wide" style="font-size: 0.75rem;">Lợi Nhuận Gộp</h6>
                                    <h4 class="fw-black text-success dark:text-green-400 m-0">{{ formatCurrency(stats.profit) }}</h4>
                                </div>
                                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 55px; height: 55px;">
                                    <i class="bi bi-graph-up-arrow fs-4 text-success dark:text-green-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted dark:text-gray-400 fw-bold text-uppercase mb-2 tracking-wide" style="font-size: 0.75rem;">Đơn Hàng (Đã giao)</h6>
                                    <h4 class="fw-black text-dark dark:text-white m-0">{{ stats.orders }} <span class="fs-6 text-muted dark:text-gray-400 fw-normal">đơn</span></h4>
                                </div>
                                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 55px; height: 55px;">
                                    <i class="bi bi-bag-check fs-4 text-info dark:text-cyan-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted dark:text-gray-400 fw-bold text-uppercase mb-2 tracking-wide" style="font-size: 0.75rem;">Sản Phẩm Đã Bán</h6>
                                    <h4 class="fw-black text-dark dark:text-white m-0">{{ stats.products_sold || 0 }} <span class="fs-6 text-muted dark:text-gray-400 fw-normal">chiếc</span></h4>
                                </div>
                                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 55px; height: 55px;">
                                    <i class="bi bi-box-seam fs-4 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- BIỂU ĐỒ BẰNG CHART.JS -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-0 px-4 rounded-top-4">
                                <h6 class="fw-bold text-dark dark:text-white m-0"><i class="bi bi-bar-chart-line-fill text-urban me-2"></i>Biểu đồ Thống kê Giao dịch</h6>
                            </div>
                            <div class="card-body p-4 position-relative" style="min-height: 380px;">
                                <canvas ref="chartCanvas" class="w-100 h-100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- TOP SẢN PHẨM BÁN CHẠY -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-[#1a2533] transition-all">
                            <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 rounded-top-4">
                                <h6 class="fw-bold text-dark dark:text-white m-0"><i class="bi bi-fire text-danger me-2"></i>Top Bán Chạy Nhất</h6>
                            </div>
                            <div class="card-body p-0 custom-scrollbar-y" style="max-height: 410px; overflow-y: auto;">
                                <div v-if="topProducts.length === 0" class="text-center py-5 text-muted fst-italic">Chưa có dữ liệu giao dịch</div>
                                <ul class="list-group list-group-flush border-0">
                                    <!-- ĐÃ FIX: Chặn click và CSS nếu sản phẩm bị xóa -->
                                    <li v-for="(prod, idx) in topProducts" :key="idx" 
                                        class="list-group-item px-4 py-3 bg-transparent border-light-subtle dark:border-gray-700 d-flex align-items-center gap-3 transition-all" 
                                        :class="prod.product_deleted_at ? 'opacity-75' : 'hover-bg-light cursor-pointer'"
                                        @click="!prod.product_deleted_at ? router.push({ path: '/admin/inventory', query: { search: prod.product_name } }) : null">
                                        
                                        <span class="fw-black fs-5" :class="idx === 0 ? 'text-warning' : (idx === 1 ? 'text-secondary' : (idx === 2 ? 'text-danger' : 'text-muted'))">#{{ idx + 1 }}</span>
                                        
                                        <div class="position-relative flex-shrink-0">
                                            <img :src="getImageUrl(prod.variant_image)" class="rounded-3 object-fit-cover shadow-sm border dark:border-gray-600 bg-white" style="width: 50px; height: 50px;" @error="e => e.target.src='/client_placeholder.png'">
                                        </div>
                                        
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h6 class="m-0 text-dark dark:text-white fw-bold text-truncate" style="font-size: 0.9rem;" :class="{'text-decoration-line-through text-muted': prod.product_deleted_at}">{{ prod.product_name }}</h6>
                                                <span v-if="prod.product_deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.6rem; padding: 2px 4px;">Đã xóa</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted dark:text-gray-400 small">Đã bán: <b class="text-dark dark:text-white">{{ prod.total_sold }}</b></span>
                                                <span class="text-urban fw-bold small" :class="{'text-muted': prod.product_deleted_at}">{{ formatCurrency(prod.total_revenue) }}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ĐƠN HÀNG MỚI NHẤT -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 bg-white dark:bg-[#1a2533] transition-all mb-4">
                            <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center rounded-top-4">
                                <h6 class="fw-bold text-dark dark:text-white m-0"><i class="bi bi-clock-history text-urban me-2"></i>Đơn Hàng Mới Nhất</h6>
                                <router-link to="/admin/orders" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 border shadow-sm fw-semibold rounded-pill px-3 transition-all hover-urban-btn">Xem tất cả</router-link>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 custom-table border-0">
                                        <thead class="bg-light dark:bg-[#212529]">
                                            <tr>
                                                <th class="ps-4 text-secondary dark:text-gray-400 border-0">Mã Đơn</th>
                                                <th class="text-secondary dark:text-gray-400 border-0">Khách hàng</th>
                                                <th class="text-secondary dark:text-gray-400 border-0">Thời gian</th>
                                                <th class="text-secondary dark:text-gray-400 border-0">Tổng tiền</th>
                                                <th class="text-secondary dark:text-gray-400 border-0">PTTT</th>
                                                <th class="pe-4 text-end text-secondary dark:text-gray-400 border-0">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody class="dark:border-gray-700">
                                            <tr v-for="order in recentOrders" :key="order.id" class="bg-white dark:bg-[#1a2533] cursor-pointer transition-all hover-bg-light" @click="router.push(`/admin/orders/${order.id}`)">
                                                <td class="ps-4 fw-bold font-monospace text-dark dark:text-white border-light-subtle dark:border-gray-700">#{{ order.order_code }}</td>
                                                <td class="border-light-subtle dark:border-gray-700">
                                                    <div class="fw-semibold text-dark dark:text-white">{{ order.user?.full_name || 'Khách vãng lai' }}</div>
                                                    <div class="small text-muted dark:text-gray-400">{{ order.user?.email || '' }}</div>
                                                </td>
                                                <td class="text-muted dark:text-gray-400 small border-light-subtle dark:border-gray-700">{{ formatDate(order.created_at) }}</td>
                                                <td class="text-danger fw-bold border-light-subtle dark:border-gray-700">{{ formatCurrency(order.total_amount) }}</td>
                                                <td class="text-uppercase small fw-semibold text-secondary border-light-subtle dark:border-gray-700">{{ order.payment_method }}</td>
                                                <td class="pe-4 text-end border-light-subtle dark:border-gray-700" v-html="getStatusBadge(order.status)"></td>
                                            </tr>
                                            <tr v-if="recentOrders.length === 0"><td colspan="6" class="text-center py-4 text-muted fst-italic bg-white dark:bg-[#1a2533] border-0">Chưa có đơn hàng nào.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
/* ZYRO BRAND COLORS */
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }

/* Nút Hover Xanh Urban */
.hover-urban-btn:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }
.hover-urban-border:hover { border-color: var(--color-c-hover, #547792) !important; }

/* UTILS */
.fw-black { font-weight: 900; }
.tracking-wide { letter-spacing: 1px; }
.tracking-widest { letter-spacing: 2px; }
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }

/* HIỆU ỨNG HOVER BẢNG DỮ LIỆU ĐƯỢC FIX TƯƠNG PHẢN DARKMODE */
.hover-bg-light:hover { background-color: #f8f9fa !important; }
html.dark .hover-bg-light:hover,
body[data-bs-theme="dark"] .hover-bg-light:hover,
[data-bs-theme="dark"] .hover-bg-light:hover { background-color: rgba(255, 255, 255, 0.06) !important; }

.custom-table th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border-bottom: none; }
.custom-table td { padding-top: 1.2rem; padding-bottom: 1.2rem; }

/* THẺ TỔNG QUAN (SUMMARY CARDS) TRẢ VỀ ĐÚNG CONCEPT TRẮNG/ĐEN ZYRO */
.summary-card { 
  border: 1px solid rgba(0,0,0,0.05) !important; 
}
html.dark .summary-card { 
  border: 1px solid rgba(255,255,255,0.05) !important; 
}
.summary-card:hover { 
  transform: translateY(-5px); 
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; 
}
html.dark .summary-card:hover {
  box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
  border-color: rgba(255,255,255,0.15) !important;
}

.animation-fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

</style>