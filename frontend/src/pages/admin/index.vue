<script setup>
import { ref, onMounted, nextTick, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios'; 
import Chart from 'chart.js/auto';
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue';
import * as XLSX from 'xlsx-js-style';
import Swal from 'sweetalert2';


const router = useRouter();

const isFirstLoad = ref(true);
const isLoading = ref(true);
const timeRange = ref('all');

const periodPresets = [
  { key: 'today', label: 'Hôm nay' },
  { key: 'last_7_days', label: '7 ngày' },
  { key: 'last_30_days', label: '30 ngày' },
  { key: 'this_month', label: 'Tháng này' },
  { key: 'last_month', label: 'Tháng trước' },
  { key: 'all', label: 'Tất cả' },
  { key: 'custom', label: 'Tùy chỉnh' }
];

const selectPeriod = (key) => {
  if (key === 'custom') return; // Mở modal tùy chỉnh sau
  timeRange.value = key;
  fetchDashboardData();
};

const stats = ref({
    revenue: 0,
    profit: 0,
    orders: 0,
    customers: 0,
    products_sold: 0,
    pending_orders: 0,
    cancelled_orders: 0,
    average_order_value: 0,
    low_stock: 0
});

const paymentStats = ref({
    cod: 0,
    vnpay: 0,
    momo: 0
});

const chartData = ref([]);
const topProducts = ref([]);
const topCombos = ref([]);
const recentOrders = ref([]);
const topBuyers = ref([]);
const lowStockList = ref([]);
const recentReviews = ref([]);
const categoryRevenue = ref([]);
const topRegions = ref([]);
const customerInsights = ref({ topGender: { gender: 'Chưa xác định' } });
const voucherUsageChart = ref([]);

const chartCanvas = ref(null);
const paymentChartCanvas = ref(null);
const categoryChartCanvas = ref(null);
const voucherChartCanvas = ref(null);
let chartInstance = null;
let paymentChartInstance = null;
let categoryChartInstance = null;
let voucherChartInstance = null;


  const getTierColor = (name) => {
    if (!name) return '#9ca3af';
    const lName = name.toLowerCase();
    if (lName.includes('khởi đầu') || lName.includes('member')) return '#9ca3af';
    if (lName.includes('fan cứng') || lName.includes('bronze')) return '#3b82f6';
    if (lName.includes('đồng')) return '#f59e0b';
    if (lName.includes('bạc') || lName.includes('silver')) return '#00d2ff';
    if (lName.includes('vàng') || lName.includes('gold')) return '#ffc107';
    if (lName.includes('kim cương') || lName.includes('diamond')) return '#8b5cf6';
    return '#9ca3af';
  };
  
  const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);
const formatCompactCurrency = (value) => {
    if (value >= 1000000000) return (value / 1000000000).toFixed(1).replace(/\.0$/, '') + ' Tỷ';
    if (value >= 1000000) return (value / 1000000).toFixed(1).replace(/\.0$/, '') + ' Tr';
    if (value >= 1000) return (value / 1000).toFixed(1).replace(/\.0$/, '') + ' K';
    return formatCurrency(value);
};

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_STORAGE_URL + path;
};

const formatDate = (dateStr) => {
  if(!dateStr) return '';
  const d = new Date(dateStr);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getStatusBadge = (status) => {
  const map = { 
      'pending': '<span class="badge bg-warning-soft text-warning fw-bold px-2 py-1 border border-warning shadow-sm">Chờ xác nhận</span>', 
      'confirmed': '<span class="badge bg-info-soft text-info fw-bold px-2 py-1 border border-info shadow-sm">Đã xác nhận</span>', 
      'processing': '<span class="badge bg-primary-soft text-primary fw-bold px-2 py-1 border border-primary shadow-sm">Đang xử lý</span>', 
      'shipping': '<span class="badge bg-primary-soft text-primary fw-bold px-2 py-1 border border-primary shadow-sm">Đang giao</span>', 
      'completed': '<span class="badge bg-success-soft text-success fw-bold px-2 py-1 border border-success shadow-sm">Thành công</span>', 
      'cancelled': '<span class="badge bg-danger-soft text-danger fw-bold px-2 py-1 border border-danger shadow-sm">Đã hủy</span>', 
      'returned': '<span class="badge bg-secondary-soft text-secondary fw-bold px-2 py-1 border border-secondary shadow-sm">Hoàn trả</span>' 
  };
  return map[status] || `<span class="badge bg-secondary-soft text-secondary fw-bold px-2 py-1 border shadow-sm">${status}</span>`;
};

const getRankClass = (idx) => {
    if(idx === 0) return 'rank-1';
    if(idx === 1) return 'rank-2';
    if(idx === 2) return 'rank-3';
    return 'bg-secondary text-white';
};

const getRankBgStyle = (idx) => {
    if(idx === 0) return 'rgba(255, 215, 0, 0.05)';
    if(idx === 1) return 'rgba(224, 224, 224, 0.05)';
    if(idx === 2) return 'rgba(255, 183, 94, 0.05)';
    return 'transparent';
};

const renderChart = () => {
    if (!chartCanvas.value) return;
    const ctx = chartCanvas.value.getContext('2d');
    if (chartInstance) chartInstance.destroy(); 
    
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)';

    chartInstance = new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: chartData.value.map(item => item.date),
            datasets: [
                {
                    type: 'bar', label: 'Doanh Thu', data: chartData.value.map(item => item.revenue),
                    backgroundColor: isDark ? 'rgba(59, 130, 246, 0.8)' : 'rgba(59, 130, 246, 0.6)',
                    borderRadius: 6, order: 2
                },
                {
                    type: 'line', label: 'Lợi Nhuận', data: chartData.value.map(item => item.profit),
                    borderColor: '#10b981', borderWidth: 3, tension: 0.4, order: 1
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { ticks: { color: textColor }, grid: { display: false } },
                y: { ticks: { color: textColor, callback: (val) => formatCompactCurrency(val) }, grid: { color: gridColor } }
            },
            plugins: { legend: { labels: { color: textColor } } }
        }
    });
};

const renderPaymentChart = () => {
    if (!paymentChartCanvas.value) return;
    const ctx = paymentChartCanvas.value.getContext('2d');
    if (paymentChartInstance) paymentChartInstance.destroy(); 

    paymentChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['VNPay', 'MoMo', 'Tiền mặt (COD)'],
            datasets: [{
                data: [paymentStats.value.vnpay, paymentStats.value.momo, paymentStats.value.cod],
                backgroundColor: ['#005baa', '#a50064', '#10b981'],
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '75%', 
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            } 
        }
    });
};

const renderCategoryChart = () => {
    if (!categoryChartCanvas.value) return;
    const ctx = categoryChartCanvas.value.getContext('2d');
    if (categoryChartInstance) categoryChartInstance.destroy();

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#adb5bd' : '#6c757d';

    categoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryRevenue.value.map(c => c.category_name),
            datasets: [{
                data: categoryRevenue.value.map(c => c.total_revenue),
                backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#ffffff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'right', labels: { color: textColor, usePointStyle: true, boxWidth: 8, font: { size: 11 } } }
            }
        }
    });
};

const renderVoucherChart = () => {
    if (!voucherChartCanvas.value) return;
    const ctx = voucherChartCanvas.value.getContext('2d');
    if (voucherChartInstance) voucherChartInstance.destroy();

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)';

    const gradientVoucher = ctx.createLinearGradient(0, 0, 0, 200);
    gradientVoucher.addColorStop(0, 'rgba(139, 92, 246, 0.4)');
    gradientVoucher.addColorStop(1, 'rgba(139, 92, 246, 0)');

    voucherChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: voucherUsageChart.value.map(item => item.date),
            datasets: [{
                label: 'Số lần dùng',
                data: voucherUsageChart.value.map(item => item.count),
                borderColor: '#8b5cf6',
                backgroundColor: gradientVoucher,
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#8b5cf6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 }, border: { display: false }, grid: { color: gridColor, drawBorder: false } }
            },
            plugins: { legend: { display: false } }
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
        topProducts.value = payload.top_products || [];
        topCombos.value = payload.top_combos || [];
        recentOrders.value = payload.recent_orders || [];
        paymentStats.value = payload.payment_stats || {cod:0, vnpay:0, momo:0};
        topBuyers.value = payload.top_buyers || [];
        lowStockList.value = payload.low_stock_list || [];
        recentReviews.value = payload.recent_reviews || [];
        categoryRevenue.value = payload.category_revenue || [];
        topRegions.value = payload.top_regions || [];
        customerInsights.value = payload.customer_insights || { topGender: { gender: 'Chưa xác định' } };
        voucherUsageChart.value = payload.voucher_usage_chart || [];
    } catch (error) {
        console.error("Lỗi lấy dữ liệu Dashboard:", error);
    } finally {
        isLoading.value = false;
        isFirstLoad.value = false; 
        await nextTick();
        renderChart();
        renderPaymentChart();
        renderCategoryChart();
        renderVoucherChart();
    }
};

let resizeObserver = null;
watch(() => document.documentElement.classList.contains('dark'), () => {
    if (chartInstance) renderChart();
    if (paymentChartInstance) renderPaymentChart();
    if (categoryChartInstance) renderCategoryChart();
    if (voucherChartInstance) renderVoucherChart();
});

onMounted(() => {
    fetchDashboardData();
    
    resizeObserver = new ResizeObserver(() => {
        if(chartInstance) chartInstance.resize();
        if(paymentChartInstance) paymentChartInstance.resize();
        if(categoryChartInstance) categoryChartInstance.resize();
        if(voucherChartInstance) voucherChartInstance.resize();
    });
    if(document.querySelector('.dashboard-wrapper')) {
        resizeObserver.observe(document.querySelector('.dashboard-wrapper'));
    }
});

onUnmounted(() => {
    if(resizeObserver) resizeObserver.disconnect();
    if (chartInstance) chartInstance.destroy();
    if (paymentChartInstance) paymentChartInstance.destroy();
    if (categoryChartInstance) categoryChartInstance.destroy();
    if (voucherChartInstance) voucherChartInstance.destroy();
});

const isExporting = ref(false);
const showExportMenu = ref(false);

const exportWithPeriod = async (period) => {
    if (period === 'current') {
        exportToExcel();
        return;
    }
    
    isExporting.value = true;
    const previousPeriod = timeRange.value;
    
    // Switch to new period and fetch
    timeRange.value = period;
    
    try {
        await fetchDashboardData();
        exportToExcel();
    } catch (err) {
        console.error("Lỗi xuất Excel:", err);
        Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tải dữ liệu báo cáo.' });
        isExporting.value = false;
    } finally {
        // Revert back
        timeRange.value = previousPeriod;
        fetchDashboardData();
    }
};

const exportToExcel = () => {
    isExporting.value = true;
    try {
        const wb = XLSX.utils.book_new();

        const formatMoney = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
        const formatNumber = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);

        const exportedAt = new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date());

        const createReportSheet = ({ title, headers, rows, widths, rightAligned = [] }) => {
            const data = [
                [title],
                ['Xuất báo cáo: ' + exportedAt],
                [],
                headers,
                ...rows,
            ];
            const ws = XLSX.utils.aoa_to_sheet(data);
            const lastColumn = headers.length - 1;
            const lastRow = rows.length + 3;
            const borderColor = '000000';

            ws['!cols'] = widths.map(w => ({ wch: w }));

            // Style title
            ws['A1'].s = {
                font: { bold: true, sz: 16, color: { rgb: "000000" } },
                alignment: { horizontal: 'left' }
            };
            ws['A2'].s = {
                font: { italic: true, sz: 11, color: { rgb: "555555" } }
            };

            for (let R = 3; R <= lastRow; R++) {
                for (let C = 0; C <= lastColumn; C++) {
                    const address = XLSX.utils.encode_cell({ r: R, c: C });
                    if (!ws[address]) ws[address] = { t: 's', v: '' };
                    
                    const isHeader = R === 3;
                    const isDataRow = R > 3;

                    let cellStyle = {
                        font: { sz: 11, bold: isHeader, color: { rgb: isHeader ? 'FFFFFF' : '000000' } },
                        fill: isHeader ? { fgColor: { rgb: '0055A4' } } : (R % 2 === 0 ? { fgColor: { rgb: 'F5F5F5' } } : null),
                        alignment: {
                            vertical: 'center',
                            horizontal: isHeader ? 'center' : (rightAligned.includes(C) ? 'right' : (C === 0 ? 'center' : 'left')),
                            wrapText: true,
                        },
                    };
                    if (isHeader || isDataRow) {
                        cellStyle.border = {
                            top: { style: 'thin', color: { rgb: borderColor } },
                            bottom: { style: 'thin', color: { rgb: borderColor } },
                            left: { style: 'thin', color: { rgb: borderColor } },
                            right: { style: 'thin', color: { rgb: borderColor } },
                        };
                    }
                    if (cellStyle.fill === null) delete cellStyle.fill;
                    ws[address].s = cellStyle;
                }
            }
            return ws;
        };

        // 1. TỔNG QUAN
        const overviewData = [
            { "Chỉ số": "Doanh thu", "Giá trị": formatMoney(stats.value.revenue) },
            { "Chỉ số": "Lợi nhuận", "Giá trị": formatMoney(stats.value.profit) },
            { "Chỉ số": "Đơn hoàn tất", "Giá trị": formatNumber(stats.value.orders) },
            { "Chỉ số": "Khách hàng mới", "Giá trị": formatNumber(stats.value.customers) },
            { "Chỉ số": "Sản phẩm bán ra", "Giá trị": formatNumber(stats.value.products_sold) },
            { "Chỉ số": "Đơn chờ xử lý", "Giá trị": formatNumber(stats.value.pending_orders) },
            { "Chỉ số": "Đơn bị hủy", "Giá trị": formatNumber(stats.value.cancelled_orders) },
            { "Chỉ số": "Giá trị ĐH Trung bình", "Giá trị": formatMoney(stats.value.average_order_value) },
            { "Chỉ số": "Sản phẩm sắp hết hàng", "Giá trị": formatNumber(stats.value.low_stock) },
            { "Chỉ số": "Tỷ lệ COD", "Giá trị": `${paymentStats.value.cod}%` },
            { "Chỉ số": "Tỷ lệ VNPay", "Giá trị": `${paymentStats.value.vnpay}%` },
            { "Chỉ số": "Tỷ lệ MoMo", "Giá trị": `${paymentStats.value.momo}%` }
        ];
        
        const wsOverview = createReportSheet({
            title: 'BÁO CÁO TỔNG QUAN ZYRO',
            headers: ['Chỉ số', 'Giá trị'],
            rows: overviewData.map((item) => [item['Chỉ số'], item['Giá trị']]),
            widths: [38, 28],
            rightAligned: [1],
        });
        XLSX.utils.book_append_sheet(wb, wsOverview, "Tổng Quan");

        // 2. SẢN PHẨM BÁN CHẠY
        if (topProducts.value?.length) {
            const wsTop = createReportSheet({
                title: 'SẢN PHẨM BÁN CHẠY',
                headers: ['Tên sản phẩm', 'Số lượng đã bán', 'Doanh thu'],
                rows: topProducts.value.map((p) => [p.product_name, formatNumber(p.total_sold), formatMoney(p.total_revenue)]),
                widths: [48, 20, 20],
                rightAligned: [1, 2],
            });
            XLSX.utils.book_append_sheet(wb, wsTop, "Sản Phẩm Bán Chạy");
        }
        // 2B. COMBO BÁN CHẠY (LOOKBOOKS)
        if (topCombos.value?.length) {
            const wsCombos = createReportSheet({
                title: 'COMBO LOOKBOOK BÁN CHẠY',
                headers: ['Tên Combo', 'Số lượng đã bán', 'Doanh thu'],
                rows: topCombos.value.map((c) => [c.lookbook_name, formatNumber(c.total_sold), formatMoney(c.total_revenue)]),
                widths: [48, 20, 20],
                rightAligned: [1, 2],
            });
            XLSX.utils.book_append_sheet(wb, wsCombos, "Combo Bán Chạy");
        }


        // 3. ĐƠN HÀNG GẦN ĐÂY
        if (recentOrders.value?.length) {
            const wsOrders = createReportSheet({
                title: 'ĐƠN HÀNG GẦN ĐÂY',
                headers: ['Mã ĐH', 'Khách hàng', 'Tổng tiền', 'Ngày đặt', 'Trạng thái'],
                rows: recentOrders.value.map((o) => [
                    o.order_code, 
                    o.customer_name || 'Khách lẻ', 
                    formatMoney(o.total_amount), 
                    new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(o.created_at)),
                    o.status === 'completed' ? 'Hoàn thành' : (o.status === 'pending' ? 'Chờ xử lý' : o.status)
                ]),
                widths: [16, 28, 20, 20, 20],
                rightAligned: [2],
            });
            XLSX.utils.book_append_sheet(wb, wsOrders, "Đơn Hàng Gần Đây");
        }

        // 4. BIỂU ĐỒ DOANH THU (CHART DATA)
        if (chartData.value?.length) {
            const wsChart = createReportSheet({
                title: 'CHI TIẾT DOANH THU THEO KỲ',
                headers: ['Thời gian', 'Doanh thu', 'Giá vốn', 'Lợi nhuận'],
                rows: chartData.value.map((c) => [c.date, formatMoney(c.revenue), formatMoney(c.cost), formatMoney(c.profit)]),
                widths: [20, 25, 25, 25],
                rightAligned: [1, 2, 3],
            });
            XLSX.utils.book_append_sheet(wb, wsChart, "Chi Tiết Doanh Thu");
        }

        const dateStr = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, `Bao_Cao_Zyro_${dateStr}.xlsx`);

        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Xuất Excel thành công!', showConfirmButton: false, timer: 3000 });
    } catch (err) {
        console.error("Lỗi xuất Excel:", err);
        Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tạo file Excel.' });
    } finally {
        isExporting.value = false;
    }
};

</script>

<template>
  <div class="dashboard-wrapper min-vh-100 px-3 py-3 px-xl-4 py-xl-4 bg-light-soft dark:bg-transparent">
    
    <!-- FIXED BUTTON EXPORT EXCEL -->
    <div class="position-fixed" style="bottom: 30px; right: 30px; z-index: 1050;">
        <button class="btn shadow-lg d-flex align-items-center justify-content-center transition-all hover-lift"
          type="button" @click.stop="showExportMenu = !showExportMenu"
          :disabled="isExporting"
          style="width: 60px; height: 60px; border-radius: 50%; padding: 0; background: linear-gradient(135deg, #10b981, #059669);"
          title="Xuất báo cáo Excel">
          <LoadingSpinner v-if="isExporting" class="text-white" style="width: 24px; height: 24px;" />
          <i v-else class="bi bi-file-earmark-arrow-down-fill fs-4 text-white"></i>
        </button>
        
        <!-- VUE CONTROLLED MENU (NO BOOTSTRAP JS NEEDED) -->
        <div v-show="showExportMenu" 
             class="dropdown-menu shadow border mb-3 p-3 show" 
             style="position: absolute; bottom: 100%; right: 0; margin-bottom: 10px; border-radius: 16px; min-width: 260px; border-color: rgba(0,0,0,0.08) !important; display: block; z-index: 1051; transform: none !important;"
             @click.stop>
          <div class="d-flex justify-content-between align-items-center mb-3 px-1">
             <h6 class="text-uppercase fw-bold text-muted font-size-xs mb-0 letter-spacing-1">Chọn kỳ xuất dữ liệu</h6>
             <button type="button" class="btn-close" style="font-size: 0.7rem;" @click="showExportMenu = false" aria-label="Close"></button>
          </div>
          <div class="d-flex flex-column gap-2">
            <button v-for="preset in periodPresets" :key="preset.key" type="button" 
                class="btn border-0 text-start d-flex align-items-center gap-3 py-2 px-3 fw-semibold rounded-3 transition-all hover-bg-light"
                @click="showExportMenu = false; exportWithPeriod(preset.key)">
              <div class="d-flex align-items-center justify-content-center rounded-circle" 
                   :class="preset.key === 'today' ? 'bg-primary-soft text-primary' : 
                           (preset.key === 'last_7_days' ? 'bg-info-soft text-info' : 
                           (preset.key === 'this_month' ? 'bg-brand-soft text-brand' : 
                           (preset.key === 'all' ? 'bg-success-soft text-success' : 'bg-light text-muted')))"
                   style="width: 32px; height: 32px;">
                   <i class="bi" :class="preset.key === 'today' ? 'bi-calendar-day' : (preset.key === 'all' ? 'bi-infinity' : 'bi-calendar')"></i>
              </div>
              {{ preset.label }}
            </button>
            <hr class="my-1">
            <button type="button" class="btn btn-brand-soft border-0 text-start d-flex align-items-center gap-3 py-2 px-3 fw-bold rounded-3 transition-all"
                @click="showExportMenu = false; exportWithPeriod('current')">
              <div class="d-flex align-items-center justify-content-center bg-brand text-white rounded-circle" style="width: 32px; height: 32px;">
                <i class="bi bi-funnel"></i>
              </div>
              Kỳ đang lọc (Mặc định)
            </button>
          </div>
        </div>
    </div>

    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
        <h1 class="logo-shimmer mb-3">ZYRO</h1>
        <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-else class="animation-fade-in">
        
        <!-- TOP 6 KPI CARDS (SORA Style) -->
        <div class="row row-cols-1 row-cols-md-3 row-cols-xl-6 g-3 g-xl-2 mb-4">
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Doanh thu kỳ lọc</p><h4 class="fw-bolder mb-0 text-dark stat-number text-truncate" :title="formatCurrency(stats.revenue)">{{ formatCompactCurrency(stats.revenue) }}</h4></div><div class="icon-circle bg-primary-soft text-primary flex-shrink-0" style="width: 38px; height: 38px;"><i class="bi bi-cash-stack fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs text-muted"><i class="bi bi-graph-up-arrow text-success me-1"></i> Doanh thu thực tế</div></div></div></div>
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Lợi nhuận</p><h4 class="fw-bolder mb-0 text-success stat-number text-truncate" :title="formatCurrency(stats.profit)">{{ formatCompactCurrency(stats.profit) }}</h4></div><div class="icon-circle bg-success-soft text-success flex-shrink-0" style="width: 38px; height: 38px;"><i class="bi bi-piggy-bank fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs text-muted"><i class="bi bi-info-circle me-1"></i> Doanh thu - Giá vốn</div></div></div></div>
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Đơn hoàn tất</p><h4 class="fw-bolder mb-0 text-dark stat-number text-truncate">{{ stats.orders }}</h4></div><div class="icon-circle bg-info-soft text-info flex-shrink-0" style="width: 38px; height: 38px;"><i class="bi bi-bag-check fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs text-muted">Giá trị TB: <span class="fw-bold text-dark ms-1">{{ formatCompactCurrency(stats.average_order_value) }}</span></div></div></div></div>
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Sản phẩm bán</p><h4 class="fw-bolder mb-0 text-dark stat-number text-truncate">{{ stats.products_sold || 0 }}</h4></div><div class="icon-circle bg-brand-soft text-brand flex-shrink-0" style="width: 38px; height: 38px; color: #6366f1; background: rgba(99, 102, 241, 0.1);"><i class="bi bi-box-seam fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs text-muted">Sản phẩm xuất kho trong kỳ</div></div></div></div>
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Khách mới</p><h4 class="fw-bolder mb-0 text-dark stat-number text-truncate">{{ stats.customers }}</h4></div><div class="icon-circle flex-shrink-0" style="width: 38px; height: 38px; color: #a855f7; background: rgba(168, 85, 247, 0.1);"><i class="bi bi-person-plus fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs text-muted">Tài khoản đăng ký mới</div></div></div></div>
            <div class="col"><div class="card custom-card h-100 border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-column justify-content-between"><div class="d-flex align-items-start justify-content-between mb-2"><div class="pe-2 min-w-0"><p class="text-muted fw-bold font-size-xs mb-1 text-uppercase letter-spacing-1 text-truncate">Cần xử lý</p><h4 class="fw-bolder mb-0 text-dark stat-number text-truncate">{{ stats.pending_orders }} <span class="font-size-xs fw-normal text-muted">đơn</span></h4></div><div class="icon-circle bg-warning-soft text-warning flex-shrink-0" style="width: 38px; height: 38px;"><i class="bi bi-clock-history fs-5"></i></div></div><div class="d-flex align-items-center mt-auto font-size-xs"><span v-if="stats.pending_orders > 0" class="text-warning fw-bold">Cần xử lý ngay</span><span v-else class="text-success fw-bold">Không có đơn tồn</span></div></div></div></div>
        </div>

        <div class="row g-3 g-xl-3 mb-4">
            <div class="col-lg-8 col-xl-9">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-3 px-xl-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">Thống kê doanh thu</h5>
                            <span class="text-muted font-size-xs mt-1">Biểu đồ tổng quan kinh doanh</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 dashboard-period-filter">
                            <button v-for="preset in periodPresets" :key="preset.key" @click="selectPeriod(preset.key)"
                                class="btn btn-sm rounded-pill px-3 fw-semibold d-flex align-items-center gap-1 transition-all"
                                :class="timeRange === preset.key ? 'btn-primary shadow-sm text-white' : 'btn-light border-light text-secondary hover-bg-light'" :disabled="isLoading">
                                <LoadingSpinner v-if="isLoading && timeRange === preset.key" class="text-white" style="width: 14px; height: 14px;" />
                                {{ preset.label }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3 p-xl-4 position-relative" style="min-height: 380px;">
                        <canvas ref="chartCanvas" class="w-100 h-100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4">
                        <h5 class="fw-bold mb-0 text-dark">Phương thức thanh toán</h5>
                        <span class="text-muted font-size-xs">Tỷ lệ thanh toán trong kỳ</span>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center p-3 p-xl-4">
                        <div style="height: 220px; width: 100%; max-width: 220px;" class="mb-4 position-relative">
                            <canvas ref="paymentChartCanvas"></canvas>
                        </div>
                        <div class="w-100 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2 font-size-sm border-bottom pb-2 border-light">
                                <span class="d-flex align-items-center gap-2 fw-medium text-dark"><span class="badge rounded-circle p-1" style="background-color: #005baa;">&nbsp;</span> VNPay</span>
                                <span class="fw-bold text-dark">{{ paymentStats.vnpay }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 font-size-sm border-bottom pb-2 border-light">
                                <span class="d-flex align-items-center gap-2 fw-medium text-dark"><span class="badge rounded-circle p-1" style="background-color: #a50064;">&nbsp;</span> MoMo</span>
                                <span class="fw-bold text-dark">{{ paymentStats.momo }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center font-size-sm border-light">
                                <span class="d-flex align-items-center gap-2 fw-medium text-dark"><span class="badge rounded-circle p-1" style="background-color: #10b981;">&nbsp;</span> COD</span>
                                <span class="fw-bold text-dark">{{ paymentStats.cod }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-xl-3 mb-4">
            <!-- DOANH THU THEO DANH MỤC -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4">
                        <h5 class="fw-bold mb-0 text-dark"><span class="badge rounded-pill bg-primary" style="width:8px;height:18px;margin-right:8px;vertical-align:middle;">&nbsp;</span>Doanh thu theo Danh mục</h5>
                    </div>
                    <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-center">
                        <div v-if="categoryRevenue.length === 0" class="text-center text-muted">Chưa có dữ liệu danh mục</div>
                        <div v-else style="height: 250px; width: 100%;" class="position-relative">
                            <canvas ref="categoryChartCanvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHÂN BỔ KHÁCH HÀNG (KHU VỰC) -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Phân bổ Khách hàng (Top 5 Khu vực)</h5>
                    </div>
                    <div class="card-body p-3 p-xl-4 custom-scrollbar" style="overflow-y: auto; max-height: 280px;">
                        <div v-if="topRegions.length === 0" class="text-center text-muted py-5">
                            Chưa có dữ liệu khu vực
                        </div>
                        <div v-else class="d-flex flex-column gap-2">
                            <div v-for="(region, idx) in topRegions" :key="idx" class="d-flex align-items-center rounded-3 p-3 shadow-sm border border-light transition-all table-row-hover bg-light-soft">
                                <div class="rank-badge fw-bolder shadow-sm flex-shrink-0 me-3" :class="getRankClass(idx)" style="width: 28px; height: 28px; font-size: 13px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                    {{ idx + 1 }}
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-1 text-dark fw-bold font-size-sm">{{ region.region_name }}</h6>
                                    <div class="font-size-xs text-muted"><i class="bi bi-box-seam me-1"></i>{{ region.total_orders }} đơn hàng</div>
                                </div>
                                <div class="text-end">
                                    <span class="text-success fw-bold font-size-sm">{{ formatCurrency(region.total_revenue) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-xl-3 mb-4">
            <!-- TOP SẢN PHẨM BÁN CHẠY -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0">Top Sản Phẩm Bán Chạy</h5>
                        <i class="bi bi-trophy-fill text-warning fs-4 opacity-50"></i>
                    </div>
                    <div class="card-body p-3 p-0 m-2 custom-scrollbar" style="overflow-y: auto; max-height: 380px;">
                        <div v-if="topProducts.length === 0" class="text-center py-5">
                            <p class="text-muted font-size-sm fw-semibold">Không có dữ liệu bán hàng kỳ này</p>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div v-for="(product, idx) in topProducts" :key="product.product_id" class="d-flex align-items-center rounded-3 p-2 shadow-sm border border-light transition-all table-row-hover position-relative overflow-hidden" :style="{ backgroundColor: getRankBgStyle(idx) }">
                                <div class="rank-badge fw-bolder shadow-sm flex-shrink-0 me-3 position-relative z-index-1" :class="getRankClass(idx)" style="width: 24px; height: 24px; font-size: 12px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">{{ idx + 1 }}</div>
                                <div class="product-img-box flex-shrink-0 shadow-sm me-3 border border-light bg-white rounded-3 overflow-hidden" style="width: 44px; height: 44px;">
                                    <img :src="getImageUrl(product.variant_image)" alt="Product" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-1 text-dark fw-bold font-size-sm text-truncate" :title="product.product_name">{{ product.product_name }}</h6>
                                    <div class="d-flex align-items-center gap-3 font-size-xs text-muted">
                                        <span>Bán: <strong class="text-dark">{{ product.total_sold }}</strong></span>
                                        <span class="text-success fw-bold">{{ formatCompactCurrency(product.total_revenue) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ĐƠN HÀNG MỚI NHẤT -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0">Đơn hàng mới nhất</h5>
                        <router-link :to="{name: 'admin-orders'}" class="btn btn-sm btn-light bg-light-soft border-0 rounded-pill px-3 fw-bold font-size-xs text-secondary hover-bg-light transition-all">Xem tất cả</router-link>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover custom-table align-middle m-0 font-size-sm">
                                <thead class="bg-light-soft text-muted font-size-xs text-uppercase letter-spacing-1">
                                    <tr>
                                        <th class="ps-4 py-3 rounded-top-start-3">Mã Đơn</th>
                                        <th class="py-3">Khách Hàng</th>
                                        <th class="py-3">Thời Gian</th>
                                        <th class="py-3 text-end">Tổng Tiền</th>
                                        <th class="pe-4 py-3 text-center rounded-top-end-3">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="recentOrders.length === 0">
                                        <td colspan="5" class="text-center py-5 text-muted font-size-sm fw-semibold">Chưa có giao dịch nào</td>
                                    </tr>
                                    <tr v-for="order in recentOrders" :key="order.id" class="table-row-hover cursor-pointer" @click="router.push({name: 'admin-orders-edit', params: {id: order.id}})">
                                        <td class="ps-4 py-3"><span class="text-brand fw-bold">{{ order.order_code }}</span></td>
                                        <td class="py-3"><div class="min-w-0"><p class="mb-0 fw-bold text-dark text-truncate" style="max-width: 150px;">{{ order.user ? order.user.full_name : 'Khách vãng lai' }}</p></div></td>
                                        <td class="py-3 text-muted font-size-xs fw-medium">{{ formatDate(order.created_at) }}</td>
                                        <td class="py-3 text-end fw-bold text-dark">{{ formatCurrency(order.total_amount) }}</td>
                                        <td class="pe-4 py-3 text-center"><div v-html="getStatusBadge(order.status)"></div></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-xl-3 mb-4">
            <!-- TOP KHÁCH HÀNG VIP -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0">Top Khách Hàng VIP</h5>
                        <i class="bi bi-person-hearts text-danger fs-4 opacity-50"></i>
                    </div>
                    <div class="card-body p-3 p-0 m-2 custom-scrollbar" style="overflow-y: auto; max-height: 400px;">
                        <div v-if="topBuyers.length === 0" class="text-center py-5"><p class="text-muted font-size-sm fw-semibold">Chưa có dữ liệu khách VIP</p></div>
                        <div class="d-flex flex-column gap-2">
                            <div v-for="(buyer, idx) in topBuyers" :key="buyer.user_id" class="d-flex align-items-center rounded-3 p-2 shadow-sm border border-light transition-all table-row-hover position-relative overflow-hidden" :style="{ backgroundColor: getRankBgStyle(idx) }">
                                <div class="rank-badge fw-bolder shadow-sm flex-shrink-0 me-2 position-relative z-index-1" :class="getRankClass(idx)" style="width: 24px; height: 24px; font-size: 12px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">{{ idx + 1 }}</div>
                                <!-- AVATAR WITH TIER FRAME -->
                                <div class="position-relative me-3 flex-shrink-0 z-index-1 avatar-wrapper" 
                                     :class="{ 'vip-glow': buyer.user?.tier && !['member', 'khởi đầu'].includes((buyer.user.tier.name || '').toLowerCase()) }"
                                     :style="{ '--tier-color': getTierColor(buyer.user?.tier?.name), width: '40px', height: '40px' }">
                                    
                                    <div v-if="buyer.user?.tier && !['member', 'khởi đầu'].includes((buyer.user.tier.name || '').toLowerCase())" class="crown-icon crown-sm">
                                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                        <path d="M235.25,83.56a16,16,0,0,0-18.77-5.18L174,94.24l-38.16-57.26a16,16,0,0,0-26.6,0L71.1,94.24,28.51,78.38a16,16,0,0,0-18.77,5.18,16.21,16.21,0,0,0-2.48,19.06L38,168.39A24,24,0,0,0,59.3,184H185.69a24,24,0,0,0,21.32-15.61l30.72-65.77A16.21,16.21,0,0,0,235.25,83.56Z"></path>
                                      </svg>
                                    </div>

                                    <div class="avatar-circle fw-bolder shadow-sm d-flex align-items-center justify-content-center bg-white" 
                                         :style="'border: 2px solid ' + getTierColor(buyer.user?.tier?.name) + ' !important; width: 100%; height: 100%; border-radius: 50%; overflow: hidden; position: relative; z-index: 2;'">
                                        <img v-if="buyer.user?.avatar_url" :src="getImageUrl(buyer.user.avatar_url)" class="w-100 h-100 object-fit-cover" />
                                        <span v-else :style="{ color: getTierColor(buyer.user?.tier?.name) }">{{ buyer.user?.full_name ? buyer.user.full_name.charAt(0).toUpperCase() : '?' }}</span>
                                    </div>
                                </div>

                                <div class="flex-grow-1 min-w-0 position-relative z-index-1">
                                    <div class="d-flex align-items-center mb-0">
                                        <p class="mb-0 fw-bold font-size-sm text-dark text-truncate me-1" :title="buyer.user ? buyer.user.full_name : ''">{{ buyer.user ? buyer.user.full_name : 'Unknown' }}</p>
                                        <i v-if="['male', 'Nam', 'nam'].includes(buyer.user?.gender)" class="bi bi-gender-male text-primary opacity-50 font-size-xs" title="Nam"></i>
                                        <i v-else-if="['female', 'Nữ', 'nữ'].includes(buyer.user?.gender)" class="bi bi-gender-female text-danger opacity-50 font-size-xs" title="Nữ"></i>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-size-xs text-brand fw-bold">{{ formatCompactCurrency(buyer.total_spent) }}</span>
                                        <span class="badge bg-light text-muted fw-normal px-2 py-1">{{ buyer.total_orders }} đơn</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SẮP HẾT HÀNG -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0"><i class="bi bi-box-seam text-warning me-2"></i>Theo dõi tồn kho</h5>
                        <span class="badge fw-bold rounded-pill" :class="stats.low_stock > 0 ? 'bg-danger-soft text-danger' : 'bg-primary-soft text-primary'">{{ stats.low_stock > 0 ? stats.low_stock + ' cảnh báo' : 'Ổn định' }}</span>
                    </div>
                    <div class="card-body p-0 custom-scrollbar" style="overflow-y: auto; max-height: 400px;">
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover custom-table align-middle m-0 font-size-sm">
                                <tbody>
                                    <tr v-if="lowStockList.length === 0"><td colspan="2" class="text-center py-5 text-muted font-size-sm fw-semibold">Chưa có sản phẩm nào</td></tr>
                                    <tr v-for="item in lowStockList" :key="item.id" class="table-row-hover">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product-img-box shadow-sm border bg-white rounded-3 overflow-hidden" style="width:40px; height:40px;">
                                                    <img :src="getImageUrl(item.product?.thumbnail_image)" class="w-100 h-100 object-fit-cover" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="mb-0 fw-bold text-dark text-truncate" style="max-width: 150px;">{{ item.product ? item.product.name : 'Unknown' }}</p>
                                                    <p class="mb-0 font-size-xs text-muted text-truncate" style="max-width: 150px;">SKU: {{ item.sku || 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <span class="fw-bold px-2 py-1 rounded-2" :class="item.stock_quantity <= 5 ? 'text-danger bg-danger-soft' : (item.stock_quantity <= 20 ? 'text-warning bg-warning-soft' : 'text-primary bg-primary-soft')">Tồn: {{ item.stock_quantity }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ĐÁNH GIÁ MỚI NHẤT -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2 px-3 px-xl-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0">Đánh giá mới nhất</h5>
                        <router-link :to="{name: 'admin-reviews'}" class="btn btn-sm btn-light bg-light-soft border-0 rounded-pill px-3 fw-bold font-size-xs text-secondary hover-bg-light transition-all">Xem tất cả</router-link>
                    </div>
                    <div class="card-body p-3 p-0 m-2 custom-scrollbar" style="overflow-y: auto; max-height: 400px;">
                        <div v-if="recentReviews.length === 0" class="text-center py-5"><p class="text-muted font-size-sm fw-semibold">Chưa có đánh giá nào</p></div>
                        <div class="d-flex flex-column gap-3">
                            <div v-for="review in recentReviews" :key="review.id" class="p-3 bg-light-soft rounded-4 border border-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img v-if="review.user && review.user.avatar_url" :src="getImageUrl(review.user.avatar_url)" class="rounded-circle object-fit-cover" width="30" height="30">
                                        <div v-else class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">{{ review.user ? review.user.full_name.charAt(0) : 'U' }}</div>
                                        <div><p class="mb-0 fw-bold text-dark font-size-sm">{{ review.user ? review.user.full_name : 'User' }}</p></div>
                                    </div>
                                    <div class="text-warning font-size-sm">
                                        <i class="bi bi-star-fill" v-for="n in review.rating" :key="n"></i><i class="bi bi-star text-muted opacity-25" v-for="n in (5-review.rating)" :key="n+5"></i>
                                    </div>
                                </div>
                                <p class="mb-1 text-muted font-size-sm text-truncate" :title="review.product ? review.product.name : ''">SP: <strong>{{ review.product ? review.product.name : '' }}</strong></p>
                                <p class="mb-0 font-size-sm text-dark fw-medium fst-italic">"{{ review.comment }}"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-xl-3">
            <!-- NHÓM KH CHỦ LỰC -->
            <div class="col-12 col-md-4">
                <div class="card custom-card h-100 border-0 shadow-sm rounded-4 transition-all" :style="{ background: customerInsights?.topGender?.gender?.toLowerCase() === 'nam' ? 'linear-gradient(to bottom right, rgba(13,202,240,0.05), var(--bs-card-bg))' : 'linear-gradient(to bottom right, rgba(165,0,100,0.05), var(--bs-card-bg))' }">
                    <div class="card-body p-4 d-flex flex-column position-relative overflow-hidden">
                        <div class="position-absolute" style="font-size: 8rem; right: -20px; bottom: -30px; pointer-events: none; z-index: 0; opacity: 0.06;" :class="customerInsights?.topGender?.gender?.toLowerCase() === 'nam' ? 'text-info' : (customerInsights?.topGender?.gender?.toLowerCase() === 'nữ' ? 'text-danger' : 'text-secondary')">
                            <i class="bi" :class="customerInsights?.topGender?.gender?.toLowerCase() === 'nam' ? 'bi-gender-male' : (customerInsights?.topGender?.gender?.toLowerCase() === 'nữ' ? 'bi-gender-female' : 'bi-people-fill')"></i>
                        </div>
                        <div class="z-index-1"><p class="fw-bold font-size-xs mb-0 text-uppercase letter-spacing-1 text-truncate" :class="customerInsights?.topGender?.gender?.toLowerCase() === 'nam' ? 'text-info' : 'text-danger'">Nhóm KH chủ lực</p></div>
                        <div class="z-index-1 my-auto py-4">
                            <h2 class="fw-bolder display-6 mb-2" style="letter-spacing: -1px;" :class="customerInsights?.topGender?.gender?.toLowerCase() === 'nam' ? 'text-info' : 'text-danger'">{{ customerInsights?.topGender?.gender || 'Chưa rõ' }}</h2>
                            <div class="d-inline-flex align-items-center px-2 py-1 rounded bg-white shadow-sm border border-light font-size-xs text-dark fw-bold"><i class="bi bi-person-check-fill text-success me-1"></i> Khách hàng mục tiêu</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BIỂU ĐỒ SỬ DỤNG MÃ GIẢM GIÁ -->
            <div class="col-12 col-md-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 custom-card">
                    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 px-3 px-xl-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2"><span class="badge rounded-pill" style="width:8px;height:18px;margin-right:8px;vertical-align:middle; background-color: #8b5cf6;">&nbsp;</span>Biểu đồ sử dụng mã giảm giá</h5>
                            <span class="text-muted font-size-xs mt-1">Hiển thị lịch sử sử dụng mã giảm giá qua các đơn hàng</span>
                        </div>
                    </div>
                    <div class="card-body p-3 p-xl-4 position-relative" style="min-height: 250px;">
                        <canvas ref="voucherChartCanvas" class="w-100 h-100"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
  </div>
</template>

<style scoped>
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.dashboard-wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.custom-card { background-color: var(--bs-card-bg); }
.icon-circle { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.stat-number { font-size: 1.4rem; letter-spacing: -0.5px; }
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1) !important; color: #0d6efd; }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1) !important; color: #198754; }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1) !important; color: #0dcaf0; }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107; }
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545; }
.bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1) !important; color: #6c757d; }
.bg-light-soft { background-color: #f8f9fa !important; }
.text-brand { color: #0d6efd !important; }
.font-size-xs { font-size: 0.75rem; }
.font-size-sm { font-size: 0.875rem; }
.letter-spacing-1 { letter-spacing: 0.5px; }
.min-w-0 { min-width: 0; }
.z-index-1 { z-index: 1; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.hover-primary:hover { color: var(--bs-primary) !important; }
.hover-bg-light:hover { background-color: rgba(0,0,0,0.05) !important; }
.table-row-hover:hover { background-color: rgba(0,0,0,0.015); }
.custom-table th { border-bottom: 1px solid rgba(0,0,0,0.05); }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.rank-1 { background: linear-gradient(135deg, #FFD700, #F79D00); color: #fff; }
.rank-2 { background: linear-gradient(135deg, #E0E0E0, #9E9E9E); color: #fff; }
.rank-3 { background: linear-gradient(135deg, #FFB75E, #ED8F03); color: #fff; }
.animation-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes shimmer { 0% { background-position: -200% center; } 100% { background-position: 200% center; } }
[data-bs-theme="dark"] .dashboard-wrapper { background-color: transparent !important; }
[data-bs-theme="dark"] .dashboard-wrapper .custom-card { background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.05) !important; }
[data-bs-theme="dark"] .dashboard-wrapper .bg-white { background-color: #1e293b !important; }
[data-bs-theme="dark"] .dashboard-wrapper .text-dark { color: #f8fafc !important; }
[data-bs-theme="dark"] .dashboard-wrapper .text-muted { color: #94a3b8 !important; }
[data-bs-theme="dark"] .dashboard-wrapper .bg-light-soft { background-color: rgba(15, 23, 42, 0.5) !important; }
[data-bs-theme="dark"] .dashboard-wrapper .border-light { border-color: rgba(255,255,255,0.05) !important; }
[data-bs-theme="dark"] .dashboard-wrapper .table-row-hover:hover { background-color: rgba(255,255,255,0.02) !important; }
[data-bs-theme="dark"] .dashboard-wrapper .custom-table th { border-bottom: 1px solid rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .dashboard-wrapper .btn-light { background-color: transparent !important; color: #94a3b8 !important; }
[data-bs-theme="dark"] .dashboard-wrapper .hover-bg-light:hover { background-color: rgba(255,255,255,0.1) !important; }

/* TIER AVATAR STYLES */
.avatar-wrapper {
  position: relative;
  border-radius: 50%;
  display: inline-flex;
}
.vip-glow::before {
  content: '';
  position: absolute;
  top: -2px; left: -2px; right: -2px; bottom: -2px;
  border-radius: 50%;
  z-index: 1;
  background: var(--tier-color);
  filter: blur(5px);
  animation: pulse-avatar-glow 2s infinite alternate;
}
@keyframes pulse-avatar-glow {
  0% { opacity: 0.5; filter: blur(3px); transform: scale(0.98); }
  100% { opacity: 1; filter: blur(6px); transform: scale(1.05); }
}
.crown-icon {
  position: absolute;
  z-index: 10;
  animation: floatCrown 1.5s infinite alternate ease-in-out;
}
.crown-sm {
  top: -6px;
  right: -4px;
}
.crown-sm svg {
  width: 16px;
  height: 16px;
  fill: #ffc107;
}
@keyframes floatCrown {
  0% { transform: translateY(0) rotate(-5deg); }
  100% { transform: translateY(-3px) rotate(5deg); }
}

</style>
