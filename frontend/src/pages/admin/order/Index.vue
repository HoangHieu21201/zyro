<template>
  <div class="order-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu đơn hàng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản Lý Đơn Hàng</h3>
          <p class="text-muted dark:text-gray-400 small mb-0 mt-1 d-none d-md-block">Theo dõi và xử lý tiến trình giao dịch của khách hàng.</p>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <!-- TABS TRẠNG THÁI ĐƠN HÀNG -->
      <div class="mb-4 overflow-auto custom-scrollbar-x">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-nowrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-collection me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ counts.all || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Chờ xác nhận
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'pending'}">{{ counts.pending || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'confirmed' }" @click.prevent="switchTab('confirmed')">
              <i class="bi bi-box-seam me-2 text-info"></i> Đã xác nhận / Xử lý
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'confirmed'}">{{ counts.confirmed || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'shipping' }" @click.prevent="switchTab('shipping')">
              <i class="bi bi-truck me-2 text-primary"></i> Đang giao
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'shipping'}">{{ counts.shipping || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'completed' }" @click.prevent="switchTab('completed')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Thành công
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'completed'}">{{ counts.completed || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'cancelled' }" @click.prevent="switchTab('cancelled')">
              <i class="bi bi-x-circle-fill me-2 text-danger"></i> Đã hủy
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'cancelled', 'bg-danger text-white border-danger': activeTab !== 'cancelled'}">{{ counts.cancelled || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- BỘ LỌC AUTO-FILTER -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] animation-fade-in position-relative">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm -->
            <div class="col-xl-3 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Mã đơn, SĐT hoặc Tên khách...">
            </div>
            
            <div class="col-xl-2 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-credit-card me-1"></i>Thanh toán</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 fw-semibold" 
                      v-model="filters.payment_status" @change="applyFilters">
                <option value="">Tất cả</option>
                <option value="unpaid">Chưa thanh toán</option>
                <option value="paid">Đã thanh toán (Paid)</option>
                <option value="refunded">Đã hoàn tiền (Refunded)</option>
              </select>
            </div>

            <div class="col-xl-4 col-md-8">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-calendar-range me-1"></i>Thời gian đặt hàng</label>
              <div class="input-group input-group-sm shadow-sm-hover" style="height: 38px;">
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_from" @change="applyFilters">
                <span class="input-group-text border-secondary-subtle dark:border-gray-700 bg-white dark:bg-[#1a2533]">-</span>
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_to" @change="applyFilters">
              </div>
            </div>

            <div class="col-xl-3 col-md-4 text-end">
               <div class="d-flex justify-content-end gap-2 align-items-center h-100">
                 <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban me-2" title="Đang tải dữ liệu..."></span>
                 <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all w-100" @click="resetFilters" v-if="hasActiveFilters">
                   <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                 </button>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BẢNG DỮ LIỆU ĐƠN HÀNG -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all position-relative">
        <div v-if="isLoading && !isFirstLoad" class="position-absolute top-0 start-0 w-100 h-100 rounded-4 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.6;">
           <div class="spinner-border text-urban" style="width: 3rem; height: 3rem;"></div>
        </div>

        <div class="card-body p-0">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 15%;">Mã Đơn</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-end" style="width: 15%;">Tổng thu</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Thanh toán</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="orders.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có đơn hàng nào.
                  </td>
                </tr>
                <tr v-else v-for="order in orders" :key="order.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': order.deleted_at}">
                  <td class="px-4 py-3 font-monospace fw-bold text-urban">
                    <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="text-decoration-none text-urban hover-text-danger transition-all">
                      #{{ order.order_code }}
                    </router-link>
                    <div class="text-muted small fw-normal mt-1" style="font-size: 0.7rem;">{{ formatDateTime(order.created_at) }}</div>
                  </td>
                  
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center me-3 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 40px; height: 40px;">
                         <i class="bi bi-person-fill text-muted fs-5"></i>
                      </div>
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ getCustomerName(order) }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace">{{ getCustomerPhone(order) }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4 text-end">
                    <div class="fw-bold text-danger fs-6">{{ formatCurrency(order.total_amount) }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-bag-check me-1"></i>{{ order.items_count || 0 }} SP</div>
                  </td>

                  <td class="px-4 text-center">
                    <span class="badge border px-3 py-2 shadow-sm w-100" :class="getPaymentStatusClass(order.payment_status)">
                      {{ getPaymentStatusLabel(order.payment_status) }}
                    </span>
                    <div class="text-muted mt-1 font-monospace" style="font-size: 0.65rem;">VNPAY/COD</div>
                  </td>

                  <td class="px-4 text-center">
                    <span class="badge border px-3 py-2 shadow-sm w-100 text-start" :class="getOrderStatusClass(order.status)">
                      <i class="bi me-1" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
                    </span>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="btn btn-sm btn-urban text-white shadow-sm border fw-bold px-3">
                        Chi tiết <i class="bi bi-arrow-right"></i>
                      </router-link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="orders.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="order in orders" :key="order.id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]" :class="{'opacity-75': order.deleted_at}">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-3 border-bottom dark:border-gray-700 pb-2">
                     <div>
                       <div class="fw-bold font-monospace text-urban">#{{ order.order_code }}</div>
                       <small class="text-muted">{{ formatDateTime(order.created_at) }}</small>
                     </div>
                     <span class="badge border" :class="getOrderStatusClass(order.status)">{{ getOrderStatusLabel(order.status) }}</span>
                  </div>

                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light dark:bg-[#1a2533] rounded-circle d-flex align-items-center justify-content-center me-2 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 35px; height: 35px;">
                       <i class="bi bi-person-fill text-muted"></i>
                    </div>
                    <div class="overflow-hidden w-100">
                      <div class="fw-bold dark:text-gray-200 text-truncate" style="font-size: 0.9rem;">{{ getCustomerName(order) }}</div>
                      <small class="text-muted dark:text-gray-400 font-monospace">{{ getCustomerPhone(order) }}</small>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-end pt-2 border-top dark:border-gray-700 mt-2 mb-3">
                    <div>
                      <span class="text-muted small d-block mb-1">Tổng cộng ({{ order.items_count }} SP)</span>
                      <span class="badge border px-2 py-1" :class="getPaymentStatusClass(order.payment_status)">{{ getPaymentStatusLabel(order.payment_status) }}</span>
                    </div>
                    <div class="text-danger fw-bold fs-5">
                      {{ formatCurrency(order.total_amount) }}
                    </div>
                  </div>

                  <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="btn btn-urban text-white shadow-sm fw-bold btn-sm w-100 py-2">
                    XỬ LÝ ĐƠN HÀNG
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="pagination.last_page > 1">
        <span class="text-muted dark:text-gray-400 small">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button></li>
            
            <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }">
              <button class="page-link dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="changePage(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();

const orders = ref([]);
const counts = ref({});
const systemModules = ref([]);
const currentPageLevel = ref(null);

const isLoading = ref(false);
const isFirstLoad = ref(true); 

// Backend Pagination & Filters
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filters = ref({
  search: '',
  status: '', 
  payment_status: '',
  date_from: '',
  date_to: ''
});

// Trạng thái được chọn ở Tabs
const activeTab = ref('all'); 
let searchTimeout = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary bg-opacity-10 text-primary', 5: 'bg-success bg-opacity-10 text-success' };
  return map[level] || 'bg-secondary';
};

// UI Helpers: Badges & Labels
const getOrderStatusClass = (status) => {
  const map = {
    'pending': 'bg-warning bg-opacity-10 text-warning border-warning',
    'confirmed': 'bg-info bg-opacity-10 text-info border-info',
    'processing': 'bg-primary bg-opacity-10 text-primary border-primary',
    'shipping': 'bg-primary bg-opacity-10 text-primary border-primary',
    'completed': 'bg-success bg-opacity-10 text-success border-success',
    'cancelled': 'bg-danger bg-opacity-10 text-danger border-danger',
    'returned': 'bg-secondary bg-opacity-10 text-secondary border-secondary',
    'refunded': 'bg-dark bg-opacity-10 text-dark border-dark'
  };
  return map[status] || 'bg-light text-secondary border-secondary';
};

const getOrderStatusLabel = (status) => {
  const map = {
    'pending': 'Chờ xác nhận',
    'confirmed': 'Đã xác nhận',
    'processing': 'Đang chuẩn bị',
    'shipping': 'Đang giao',
    'completed': 'Thành công',
    'cancelled': 'Đã hủy',
    'returned': 'Đã hoàn trả',
    'refunded': 'Đã hoàn tiền'
  };
  return map[status] || status;
};

const getOrderStatusIcon = (status) => {
  const map = {
    'pending': 'bi-hourglass-split',
    'confirmed': 'bi-check2-circle',
    'processing': 'bi-box-seam',
    'shipping': 'bi-truck',
    'completed': 'bi-check-circle-fill',
    'cancelled': 'bi-x-circle-fill',
    'returned': 'bi-arrow-return-left',
    'refunded': 'bi-cash-coin'
  };
  return map[status] || 'bi-record-circle';
};

const getPaymentStatusClass = (status) => {
  if (status === 'paid') return 'bg-success bg-opacity-10 text-success border-success';
  if (status === 'refunded') return 'bg-dark text-white border-dark';
  return 'bg-warning bg-opacity-10 text-warning border-warning';
};

const getPaymentStatusLabel = (status) => {
  if (status === 'paid') return 'Đã thanh toán';
  if (status === 'refunded') return 'Đã hoàn tiền';
  return 'Chưa thanh toán';
};

// Utils JSON Extract
const getCustomerName = (order) => {
  if (order.user) return order.user.full_name;
  try {
     const info = typeof order.shipping_info === 'string' ? JSON.parse(order.shipping_info) : order.shipping_info;
     return info.name || 'Khách vãng lai';
  } catch(e) { return 'Khách vãng lai'; }
};

const getCustomerPhone = (order) => {
  try {
     const info = typeof order.shipping_info === 'string' ? JSON.parse(order.shipping_info) : order.shipping_info;
     return info.phone || 'N/A';
  } catch(e) { return 'N/A'; }
};

// Lọc & Phân trang
const hasActiveFilters = computed(() => {
  return filters.value.search !== '' || filters.value.payment_status !== '' || filters.value.date_from !== '' || filters.value.date_to !== '';
});

const resetFilters = () => {
  filters.value = { search: '', status: '', payment_status: '', date_from: '', date_to: '' };
  activeTab.value = 'all';
  applyFilters();
};

const applyFilters = () => {
  pagination.value.current_page = 1;
  fetchData();
};

const onSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { applyFilters(); }, 500); 
};

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'all') {
      filters.value.status = '';
  } else if (tab === 'cancelled') {
      // Vì hủy và trả gộp chung tab
      filters.value.status = 'cancelled,returned'; 
  } else if (tab === 'confirmed') {
      filters.value.status = 'confirmed,processing'; 
  } else {
      filters.value.status = tab;
  }
  applyFilters();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    fetchData();
  }
};

// ================= FETCH DATA API =================
const fetchData = async (isSilent = false) => {
  if (!isSilent) isLoading.value = true;
  
  try {
    if (isFirstLoad.value) {
        const resModules = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
        systemModules.value = resModules.data.data;
        const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_orders'));
        if (currentModule) currentPageLevel.value = currentModule.required_level;
    }

    const params = new URLSearchParams();
    params.append('page', pagination.value.current_page);
    
    // Nếu status bị gộp (VD: cancelled,returned), gọi API custom hoặc tách mảng (Phụ thuộc BE, ở đây BE của ta dùng "=" nên ta sửa lại)
    // Để giữ nguyên BE ta tạm bỏ logic mảng, lấy tạm cái đầu tiên. Sếp có thể tự nâng cấp BE để nhận IN()
    if(filters.value.status) {
        const firstStatus = filters.value.status.split(',')[0];
        params.append('status', firstStatus); 
    }

    if(filters.value.payment_status) params.append('payment_status', filters.value.payment_status);
    if(filters.value.search) params.append('search', filters.value.search);
    if(filters.value.date_from) params.append('date_from', filters.value.date_from);
    if(filters.value.date_to) params.append('date_to', filters.value.date_to);

    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders?${params.toString()}`, { headers: getHeaders() });
    
    orders.value = res.data.data.data || [];
    counts.value = res.data.counts || {};
    pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        total: res.data.data.total
    };
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.orders').listen('.OrderEvent', () => { fetchData(true); });
  }
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.orders'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.hover-text-danger:hover { color: #dc3545 !important; }

/* TABS STYLING */
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.hover-danger:hover { color: #dc3545 !important; border-color: #dc3545 !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style> 