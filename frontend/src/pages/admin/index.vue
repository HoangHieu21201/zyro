<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios'; 

// IMPORT THƯ VIỆN VẼ BIỂU ĐỒ CHUYÊN NGHIỆP
import Chart from 'chart.js/auto';

const router = useRouter();
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

// Tham chiếu đến thẻ Canvas vẽ biểu đồ
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
      'pending': '<span class="badge bg-warning text-dark">Chờ xác nhận</span>', 
      'confirmed': '<span class="badge bg-info">Đã xác nhận</span>', 
      'processing': '<span class="badge bg-primary">Đang xử lý</span>', 
      'shipping': '<span class="badge bg-primary">Đang giao</span>', 
      'completed': '<span class="badge bg-success">Thành công</span>', 
      'cancelled': '<span class="badge bg-danger">Đã hủy</span>', 
      'returned': '<span class="badge bg-secondary">Hoàn trả</span>' 
  };
  return map[status] || `<span class="badge bg-secondary">${status}</span>`;
};

// HÀM RENDER BIỂU ĐỒ KẾT HỢP (BAR + LINE)
const renderChart = () => {
    if (!chartCanvas.value) return;

    if (chartInstance) {
        chartInstance.destroy(); 
    }

    const labels = chartData.value.map(item => item.date);
    const revenueData = chartData.value.map(item => item.revenue);
    const profitData = chartData.value.map(item => item.profit);

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar', 
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Doanh Thu',
                    data: revenueData,
                    backgroundColor: 'rgba(0, 153, 129, 0.2)', // Màu nền xanh Urban nhạt
                    borderColor: 'rgba(0, 153, 129, 1)', // Viền xanh Urban
                    borderWidth: 2,
                    borderRadius: 4,
                    order: 2
                },
                {
                    type: 'line',
                    label: 'Lợi Nhuận',
                    data: profitData,
                    borderColor: '#dc3545', // Đỏ danger
                    backgroundColor: '#dc3545',
                    borderWidth: 3,
                    tension: 0.4, // Tạo độ cong mềm mại cho đường Line
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#dc3545',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                tooltip: {
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
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) return (value / 1000000000) + ' Tỷ';
                            if (value >= 1000000) return (value / 1000000) + ' Tr';
                            if (value >= 1000) return (value / 1000) + ' K';
                            return value;
                        }
                    }
                }
            }
        }
    });
};

const fetchDashboardData = async () => {
    isLoading.value = true;
    try {
        const response = await api.get(`/admin/dashboard/statistics?range=${timeRange.value}`);
        
        let payload = response.data;
        if(payload.data) payload = payload.data; 
        
        stats.value = payload.summary;
        chartData.value = payload.chart.data;
        topProducts.value = payload.top_products;
        recentOrders.value = payload.recent_orders;

        // ĐÃ FIX LỖI BIỂU ĐỒ TRẮNG: 
        // Phải gán isLoading = false TRƯỚC, để Vue DOM render cái thẻ <canvas> ra
        isLoading.value = false;

        // Sau đó dùng nextTick đợi DOM vẽ xong thẻ canvas rồi mới nhét Chart.js vào
        await nextTick();
        renderChart();
        
    } catch (error) {
        console.error("Lỗi lấy dữ liệu Dashboard:", error);
        isLoading.value = false; // Lỗi cũng phải tắt loading
    }
};

onMounted(() => {
    if (!localStorage.getItem('admin_token')) {
        window.location.href = '/admin/login'; 
        return;
    }
    fetchDashboardData();
});
</script>

<template>
  <div class="dashboard-wrapper w-100">
    <div class="app-content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 text-brand fw-bold"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan Hệ Thống</h3>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <div class="d-inline-flex bg-white rounded-3 shadow-sm p-1 border">
                        <select class="form-select border-0 shadow-none fw-semibold text-muted bg-transparent cursor-pointer" v-model="timeRange" @change="fetchDashboardData">
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

    <div class="app-content">
        <div class="container-fluid">
            
            <!-- SKELETON LOADING -->
            <div v-if="isLoading" class="row g-4">
                <div class="col-lg-3 col-sm-6" v-for="i in 4" :key="'skel'+i">
                    <div class="card border-0 shadow-sm rounded-4 p-4 shimmer h-100" style="min-height: 120px;"></div>
                </div>
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 shimmer" style="height: 400px;"></div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 shimmer" style="height: 400px;"></div>
                </div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="animation-fade-in">
                
                <!-- ROW 1: SUMMARY CARDS -->
                <div class="row g-4 mb-4">
                    <!-- Doanh thu -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-primary-subtle">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Tổng Doanh Thu</h6>
                                    <h4 class="fw-black text-dark m-0">{{ formatCurrency(stats.revenue) }}</h4>
                                </div>
                                <div class="icon-box bg-white text-primary shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                    <i class="bi bi-wallet2 fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Lợi nhuận -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-success bg-opacity-10 border-success border-opacity-25">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Lợi Nhuận Gộp</h6>
                                    <h4 class="fw-black text-success m-0">{{ formatCurrency(stats.profit) }}</h4>
                                </div>
                                <div class="icon-box bg-white text-success shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                    <i class="bi bi-graph-up-arrow fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Đơn hàng -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-info-subtle">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Đơn Hàng (Đã giao)</h6>
                                    <h4 class="fw-black text-dark m-0">{{ stats.orders }} <span class="fs-6 text-muted fw-normal">đơn</span></h4>
                                </div>
                                <div class="icon-box bg-white text-info shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                    <i class="bi bi-bag-check fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sản phẩm bán ra -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card bg-danger-subtle">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Sản Phẩm Đã Bán</h6>
                                    <h4 class="fw-black text-dark m-0">{{ stats.products_sold || 0 }} <span class="fs-6 text-muted fw-normal">chiếc</span></h4>
                                </div>
                                <div class="icon-box bg-white text-danger shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                    <i class="bi bi-box-seam fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: CHARTS & TOP PRODUCTS -->
                <div class="row g-4 mb-4">
                    <!-- BIỂU ĐỒ BẰNG CHART.JS -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-dark m-0"><i class="bi bi-bar-chart-line-fill text-brand me-2"></i>Biểu đồ Doanh Thu & Lợi Nhuận</h6>
                            </div>
                            <div class="card-body p-4 position-relative" style="min-height: 350px;">
                                <!-- Canvas để Chart.js vẽ lên -->
                                <canvas ref="chartCanvas" class="w-100 h-100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- TOP SẢN PHẨM BÁN CHẠY -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
                                <h6 class="fw-bold text-dark m-0"><i class="bi bi-fire text-danger me-2"></i>Top Bán Chạy Nhất</h6>
                            </div>
                            <div class="card-body p-0 custom-scrollbar-y" style="max-height: 380px; overflow-y: auto;">
                                <div v-if="topProducts.length === 0" class="text-center py-5 text-muted fst-italic">Chưa có dữ liệu giao dịch</div>
                                <ul class="list-group list-group-flush">
                                    <li v-for="(prod, idx) in topProducts" :key="idx" class="list-group-item px-4 py-3 border-light-subtle d-flex align-items-center gap-3 transition-all hover-bg-light cursor-pointer" @click="router.push(`/admin/products/${prod.product_id}`)">
                                        <span class="fw-black fs-5" :class="idx === 0 ? 'text-warning' : (idx === 1 ? 'text-secondary' : (idx === 2 ? 'text-danger' : 'text-muted'))">#{{ idx + 1 }}</span>
                                        <img :src="getImageUrl(prod.variant_image)" class="rounded-3 object-fit-cover shadow-sm border" style="width: 50px; height: 50px;" @error="e => e.target.src='/client_placeholder.png'">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-1 text-dark fw-bold text-truncate" style="font-size: 0.9rem;">{{ prod.product_name }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Đã bán: <b class="text-dark">{{ prod.total_sold }}</b></span>
                                                <span class="text-brand fw-bold small">{{ formatCurrency(prod.total_revenue) }}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 3: RECENT ORDERS -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-dark m-0"><i class="bi bi-clock-history text-brand me-2"></i>Đơn Hàng Gần Đây</h6>
                                <router-link to="/admin/orders" class="btn btn-sm btn-light border text-primary fw-semibold rounded-pill px-3 hover-brand">Xem tất cả</router-link>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 custom-table">
                                        <thead class="bg-light text-secondary">
                                            <tr>
                                                <th class="ps-4">Mã Đơn</th>
                                                <th>Khách hàng</th>
                                                <th>Thời gian</th>
                                                <th>Tổng tiền</th>
                                                <th>PTTT</th>
                                                <th class="pe-4 text-end">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="order in recentOrders" :key="order.id" class="cursor-pointer transition-all hover-bg-light" @click="router.push(`/admin/orders/${order.id}`)">
                                                <td class="ps-4 fw-bold font-monospace text-dark">#{{ order.order_code }}</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ order.user?.full_name || 'Khách vãng lai' }}</div>
                                                    <div class="small text-muted">{{ order.user?.email || '' }}</div>
                                                </td>
                                                <td class="text-muted small">{{ formatDate(order.created_at) }}</td>
                                                <td class="text-danger fw-bold">{{ formatCurrency(order.total_amount) }}</td>
                                                <td class="text-uppercase small fw-semibold text-secondary">{{ order.payment_method }}</td>
                                                <td class="pe-4 text-end" v-html="getStatusBadge(order.status)"></td>
                                            </tr>
                                            <tr v-if="recentOrders.length === 0"><td colspan="6" class="text-center py-4 text-muted fst-italic">Chưa có đơn hàng nào.</td></tr>
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
/* COLORS */
.text-brand { color: #009981 !important; }
.bg-brand { background-color: #009981 !important; }
.btn-primary { background-color: #009981 !important; border-color: #009981 !important; color: white !important; }
.btn-primary:hover { background-color: #007a67 !important; border-color: #007a67 !important; }
.hover-brand:hover { background-color: #009981 !important; color: white !important; border-color: #009981 !important; }

/* UTILS */
.fw-black { font-weight: 900; }
.cursor-pointer { cursor: pointer; }
.hover-bg-light:hover { background-color: #f8f9fa; }
.transition-all { transition: all 0.3s ease; }
.custom-table th { font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: none; }
.custom-table td { border-bottom: 1px solid #f1f5f9; padding-top: 1rem; padding-bottom: 1rem; }

/* SUMMARY CARDS */
.summary-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid rgba(0,0,0,0.05) !important; }
.summary-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }

/* Nền nhạt cho thẻ */
.bg-primary-subtle { background-color: #e0f2fe !important; }
.bg-info-subtle { background-color: #e0f7fa !important; }
.bg-warning-subtle { background-color: #fff3e0 !important; }
.bg-danger-subtle { background-color: #fee2e2 !important; }

/* SKELETON & ANIMATION */
.animation-fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
</style>