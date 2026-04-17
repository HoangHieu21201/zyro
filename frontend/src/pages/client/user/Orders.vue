<template>
  <div class="user-orders-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Đơn mua hàng</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <div class="col-lg-9">
            <div class="row g-3 mb-4 animation-fade-in">
              <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10 border border-warning border-opacity-25 cursor-pointer hover-transform" @click="changeTab('pending')">
                  <div class="card-body p-2 p-md-3 p-xl-4 d-flex align-items-center justify-content-center justify-content-md-between flex-wrap text-center text-md-start">
                    <div>
                      <h6 class="fw-bold text-warning mb-1 text-uppercase text-truncate" style="font-size: 0.7rem; letter-spacing: 1px;">Chờ xử lý</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0 fs-4 fs-md-3">{{ orderStats.pending }} <span class="d-none d-md-inline fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="d-none d-md-flex bg-warning text-white rounded-circle align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-info bg-opacity-10 border border-info border-opacity-25 cursor-pointer hover-transform" @click="changeTab('shipping')">
                  <div class="card-body p-2 p-md-3 p-xl-4 d-flex align-items-center justify-content-center justify-content-md-between flex-wrap text-center text-md-start">
                    <div>
                      <h6 class="fw-bold text-info mb-1 text-uppercase text-truncate" style="font-size: 0.7rem; letter-spacing: 1px;">Đang giao</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0 fs-4 fs-md-3">{{ orderStats.shipping }} <span class="d-none d-md-inline fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="d-none d-md-flex bg-info text-white rounded-circle align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-truck fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-4 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10 border border-success border-opacity-25 cursor-pointer hover-transform" @click="changeTab('completed')">
                  <div class="card-body p-2 p-md-3 p-xl-4 d-flex align-items-center justify-content-center justify-content-md-between flex-wrap text-center text-md-start">
                    <div>
                      <h6 class="fw-bold text-success mb-1 text-uppercase text-truncate" style="font-size: 0.7rem; letter-spacing: 1px;">Hoàn thành</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0 fs-4 fs-md-3">{{ orderStats.completed }} <span class="d-none d-md-inline fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="d-none d-md-flex bg-success text-white rounded-circle align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-check-circle fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- BỘ LỌC TÌM KIẾM -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-3 p-md-4 mb-4 animation-fade-in">
              <div class="border-bottom dark:border-gray-700 pb-2 mb-3">
                <ul class="nav nav-underline flex-wrap justify-content-start" style="gap: 5px 20px; margin-bottom: -1px;">
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'all' }" href="#" @click.prevent="changeTab('all')">Tất cả</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'pending' }" href="#" @click.prevent="changeTab('pending')">Chờ xác nhận</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'confirmed' }" href="#" @click.prevent="changeTab('confirmed')">Đã xác nhận</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'shipping' }" href="#" @click.prevent="changeTab('shipping')">Đang giao</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'completed' }" href="#" @click.prevent="changeTab('completed')">Hoàn thành</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap text-danger" :class="{ 'active-tab': activeTab === 'cancelled' }" href="#" @click.prevent="changeTab('cancelled')">Đã hủy</a>
                  </li>
                </ul>
              </div>

              <div class="row g-2 g-md-3 align-items-center">
                <!-- Tìm kiếm -->
                <div class="col-12 col-lg-5">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="searchQuery" @input="debounceFetch" placeholder="Tên SP, Mã đơn...">
                  </div>
                </div>

                <!-- Từ ngày -->
                <div class="col-6 col-md-4 col-lg-2">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted px-2"><i class="bi bi-calendar"></i></span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white px-1" style="font-size: 0.85rem;" v-model="filterDateFrom" @change="debounceFetch" title="Từ ngày">
                  </div>
                </div>

                <!-- Đến ngày -->
                <div class="col-6 col-md-4 col-lg-2">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted px-2"><i class="bi bi-calendar-fill"></i></span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white px-1" style="font-size: 0.85rem;" v-model="filterDateTo" @change="debounceFetch" title="Đến ngày">
                  </div>
                </div>

                <!-- Lọc Sắp xếp -->
                <div class="col-8 col-md-4 col-lg-2">
                  <div class="d-flex align-items-center bg-light dark:bg-[#212529] rounded-pill border border-secondary-subtle dark:border-gray-600 px-2 overflow-hidden shadow-sm-hover h-100" style="min-height: 38px;">
                     <i class="bi bi-sort-down text-muted ms-1"></i>
                     <select class="form-select border-0 shadow-none fw-medium text-dark dark:text-gray-200 bg-transparent py-0" style="font-size: 0.85rem; cursor: pointer;" v-model="filterSort" @change="debounceFetch">
                       <option value="desc">Mới nhất</option>
                       <option value="asc">Cũ nhất</option>
                     </select>
                  </div>
                </div>

                <!-- Nút Clear -->
                <div class="col-4 col-md-12 col-lg-1 text-end text-lg-center">
                  <button v-if="hasFilters" class="btn btn-light text-danger fw-bold rounded-pill border shadow-sm w-100 p-0 d-flex align-items-center justify-content-center hover-danger" style="height: 38px;" title="Xóa lọc" @click="clearFilters">
                    <i class="bi bi-x-lg"></i> <span class="d-md-none ms-1 small">Xóa</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="animation-fade-in">
              
              <!-- ============================================== -->
              <!-- ĐÃ FIX: SKELETON LOADING THÔNG MINH CHO LIST   -->
              <!-- ============================================== -->
              <div v-if="isLoading" class="d-flex flex-column gap-4 pe-none">
                 <div v-for="i in 3" :key="'skel'+i" class="card border-light-subtle dark:border-gray-700 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden">
                    <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                       <div class="shimmer rounded mb-0" style="width: 120px; height: 18px;"></div>
                       <div class="shimmer rounded mb-0" style="width: 80px; height: 18px;"></div>
                    </div>
                    <div class="card-body p-0">
                       <div class="d-flex p-3 p-md-4 gap-3">
                          <div class="shimmer rounded-3" style="width: 80px; height: 80px; flex-shrink: 0;"></div>
                          <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                             <div class="d-flex justify-content-between">
                                <div class="shimmer rounded mb-2" style="width: 50%; height: 16px;"></div>
                                <div class="shimmer rounded" style="width: 80px; height: 16px;"></div>
                             </div>
                             <div class="d-flex justify-content-between align-items-end mt-2">
                                <div class="shimmer rounded" style="width: 100px; height: 14px;"></div>
                                <div class="shimmer rounded" style="width: 30px; height: 14px;"></div>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="card-footer bg-white dark:bg-[#1a2533] border-top dark:border-gray-700 p-3 px-md-4 d-flex justify-content-end align-items-center gap-2">
                       <div class="shimmer rounded me-auto" style="width: 120px; height: 18px;"></div>
                       <div class="shimmer rounded-pill" style="width: 90px; height: 35px;"></div>
                       <div class="shimmer rounded-pill" style="width: 90px; height: 35px;"></div>
                    </div>
                 </div>
              </div>

              <!-- Trống -->
              <div v-else-if="orders.length === 0" class="text-center py-5 my-4 bg-white dark:bg-[#1a2533] rounded-4 shadow-sm border border-light-subtle dark:border-gray-700">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-receipt text-muted opacity-50" style="font-size: 2.5rem;"></i>
                </div>
                <h6 class="fw-bold text-dark dark:text-white">Chưa có đơn hàng nào</h6>
                <p class="text-muted small">Bạn chưa có đơn hàng nào trong trạng thái này.</p>
                <router-link to="/category" class="btn btn-outline-urban rounded-pill px-4 fw-semibold mt-2">Bắt đầu mua sắm</router-link>
              </div>

              <!-- Lưới Card -->
              <div v-else class="d-flex flex-column gap-4">
                <div v-for="order in orders" :key="order.id" class="card border-light-subtle dark:border-gray-700 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden group hover-border-urban transition-all">
                  
                  <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="badge bg-urban text-white rounded-1 shadow-sm d-none d-sm-inline">ZYRO</span>
                      <span class="fw-bold font-monospace text-dark dark:text-gray-300 small border-sm-start ps-sm-2 dark:border-gray-600">#{{ order.order_code }}</span>
                      <span class="text-muted small ms-1"><i class="bi bi-clock me-1"></i>{{ formatDate(order.created_at) }}</span>
                    </div>
                    <span class="fw-bold text-uppercase d-flex align-items-center gap-1" :class="getOrderStatusColor(order.status)" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                      <i class="bi" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
                    </span>
                  </div>

                  <div class="card-body p-0 cursor-pointer" @click="openOrderDetail(order.id)">
                    <div v-for="(item, idx) in order.items" :key="item.id" 
                         class="d-flex p-3 p-md-4 gap-3 position-relative"
                         :class="{'border-top dark:border-gray-700': idx > 0}">
                      
                      <div class="position-absolute top-0 start-0 w-100 h-100 bg-c-effect opacity-25 dark:bg-[#121416] pe-none z-0"></div>

                      <div class="position-relative z-1" style="width: 80px; height: 80px; flex-shrink: 0;">
                        <img :src="getImageUrl(item.variant_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                      </div>
                      
                      <div class="flex-grow-1 position-relative z-1 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2" style="font-size: 0.95rem;">{{ item.product_name }}</h6>
                          <div class="text-end flex-shrink-0">
                             <div class="fw-bold text-dark dark:text-white">{{ formatCurrency(item.purchased_price) }}</div>
                          </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                           <span class="text-muted dark:text-gray-400 small">PL: {{ parseAttributes(item.variant_attributes) }}</span>
                           <span class="fw-semibold text-dark dark:text-gray-300">x{{ item.quantity }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer bg-white dark:bg-[#1a2533] border-top dark:border-gray-700 p-3 px-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="text-dark dark:text-gray-300 me-2">Thành tiền:</span>
                      <span class="fs-4 fw-bold text-urban">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                      <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-dark" @click="openOrderDetail(order.id)">
                         Chi Tiết
                      </button>
                      
                      <button v-if="order.status === 'pending'" class="btn btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-danger" @click.stop="cancelOrder(order)">
                         Hủy Đơn
                      </button>

                      <template v-if="order.status === 'completed'">
                        <button class="btn btn-outline-danger rounded-pill px-4 fw-semibold transition-all" @click.stop="requestReturn(order)">
                           Hoàn trả
                        </button>
                        <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all" @click.stop="buyAgain(order)">
                           Mua lại
                        </button>
                        <button v-if="order.is_reviewed" class="btn btn-outline-urban rounded-pill px-4 fw-bold shadow-sm transition-all" @click.stop="openReview(order)">
                           Xem lại đánh giá
                        </button>
                        <button v-else class="btn btn-urban rounded-pill px-4 fw-bold shadow-sm transition-all" @click.stop="openReview(order)">
                           Đánh giá
                        </button>
                      </template>
                    </div>
                  </div>
                  
                </div>
              </div>

              <!-- PHÂN TRANG -->
              <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-5">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" :class="{'disabled': pagination.current_page === 1}">
                    <a class="page-link shadow-none" href="#" @click.prevent="fetchOrders(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></a>
                  </li>
                  <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{'active': pagination.current_page === page}">
                    <a class="page-link shadow-none" href="#" @click.prevent="fetchOrders(page)">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{'disabled': pagination.current_page === pagination.last_page}">
                    <a class="page-link shadow-none" href="#" @click.prevent="fetchOrders(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></a>
                  </li>
                </ul>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- CÁC MODAL ĐƯỢC CHÈN -->
    <OrderDetailModal ref="orderDetailModalRef" @refresh="fetchOrders(pagination.current_page)" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import UserSidebar from '@/components/client/UserSidebar.vue';
import OrderDetailModal from './OrderDetailModal.vue';

// ĐÃ THÊM CART STORE ĐỂ MUA LẠI
import { useCartStore } from '@/stores/cartStore';

const router = useRouter();
const cartStore = useCartStore();
const orderDetailModalRef = ref(null);

const orders = ref([]);
const orderStats = ref({ pending: 0, shipping: 0, completed: 0 });
const pagination = ref({ current_page: 1, last_page: 1 });

const activeTab = ref('all');
const searchQuery = ref('');
const filterSort = ref('desc');
const filterDateFrom = ref('');
const filterDateTo = ref('');
const isLoading = ref(true);

let debounceTimer = null;
const debounceFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchOrders(1), 500);
};

const hasFilters = computed(() => filterDateFrom.value !== '' || filterDateTo.value !== '' || searchQuery.value !== '' || filterSort.value !== 'desc');

const clearFilters = () => {
  filterDateFrom.value = ''; filterDateTo.value = ''; searchQuery.value = ''; filterSort.value = 'desc';
  fetchOrders(1);
};

const changeTab = (tab) => {
  activeTab.value = tab;
  fetchOrders(1);
};

const fetchOrders = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = {
      page,
      status: activeTab.value,
      search: searchQuery.value,
      date_from: filterDateFrom.value,
      date_to: filterDateTo.value,
      sort: filterSort.value 
    };
    
    const res = await api.get('/client/user/orders', { params });
    if (res.data.success) {
      orders.value = res.data.data.data;
      pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page
      };
      if (res.data.stats) {
        orderStats.value = res.data.stats;
      }
    }
  } catch (error) {
    ZyroSwal.toastError('Không tải được danh sách đơn hàng');
  } finally {
    isLoading.value = false;
  }
};

const openOrderDetail = (id) => {
  if (orderDetailModalRef.value) {
     orderDetailModalRef.value.openModal(id);
  }
};

const cancelOrder = (order) => {
  if (order.status !== 'pending') {
     ZyroSwal.toastError('Chỉ có thể hủy đơn chờ xác nhận');
     return;
  }

  ZyroSwal.confirmCancelOrder().then(async (result) => {
    if (result.isConfirmed) {
       try {
         ZyroSwal.showLoading('Đang xử lý...');
         await api.post(`/client/user/orders/${order.id}/cancel`, { reason: result.value });
         ZyroSwal.close();
         ZyroSwal.toastSuccess('Hủy đơn hàng thành công');
         fetchOrders(pagination.value.current_page);
       } catch (err) {
         ZyroSwal.close();
         ZyroSwal.toastError(err.response?.data?.message || 'Có lỗi xảy ra khi hủy');
       }
    }
  });
};

const requestReturn = (order) => {
  Swal.fire({
      title: 'Yêu cầu hoàn trả',
      input: 'textarea',
      inputPlaceholder: 'Nhập lý do hoàn trả của bạn (Hàng lỗi, sai mẫu, v.v...)',
      showCancelButton: true,
      confirmButtonText: 'Gửi yêu cầu',
      cancelButtonText: 'Đóng',
      confirmButtonColor: '#dc3545',
      inputValidator: (value) => {
        return new Promise((resolve) => {
          if (value.trim()) resolve();
          else resolve('Vui lòng nhập lý do hoàn trả!');
        });
      },
      customClass: { popup: 'rounded-4 dark:bg-[#1a2533]' }
  }).then(async (result) => {
      if (result.isConfirmed) {
          try {
            ZyroSwal.showLoading('Đang gửi yêu cầu...');
            const res = await api.post(`/client/user/orders/${order.id}/return`, { reason: result.value });
            ZyroSwal.close();
            ZyroSwal.toastSuccess(res.data.message);
          } catch (err) {
            ZyroSwal.close();
            ZyroSwal.toastError(err.response?.data?.message || 'Có lỗi xảy ra');
          }
      }
  });
};

const buyAgain = async (order) => {
  try {
      ZyroSwal.showLoading('Đang thêm vào giỏ...');
      const res = await api.post(`/client/user/orders/${order.id}/buy-again`);
      await cartStore.fetchDBCart();
      ZyroSwal.close();
      ZyroSwal.toastSuccess(res.data.message);
      router.push('/cart');
  } catch (error) {
      ZyroSwal.close();
      ZyroSwal.toastError(error.response?.data?.message || 'Không thể mua lại sản phẩm');
  }
};

const openReview = (order) => {
  router.push(`/user/review?order_id=${order.id}`);
};

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
const formatDate = (dateString) => {
  if(!dateString) return '';
  return new Date(dateString).toLocaleDateString('vi-VN');
};
const parseAttributes = (jsonStr) => {
  if(!jsonStr) return 'Mặc định';
  if(Array.isArray(jsonStr)) return jsonStr.join(' - ');
  if(typeof jsonStr === 'string') {
     try { 
         const parsed = JSON.parse(jsonStr);
         return Array.isArray(parsed) ? parsed.join(' - ') : parsed;
     } catch(e) { return jsonStr; }
  }
  return 'Mặc định';
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'completed': 'Thành công', 'cancelled': 'Đã hủy', 'returned': 'Hoàn trả' };
  return map[status] || status;
};

const getOrderStatusColor = (status) => {
  const map = { 'pending': 'text-warning', 'confirmed': 'text-info', 'processing': 'text-primary', 'shipping': 'text-primary', 'completed': 'text-success', 'cancelled': 'text-danger', 'returned': 'text-secondary' };
  return map[status] || 'text-secondary';
};

const getOrderStatusIcon = (status) => {
  const map = { 'pending': 'bi-clock-history', 'confirmed': 'bi-check-all', 'processing': 'bi-box-seam', 'shipping': 'bi-truck', 'completed': 'bi-check-circle-fill', 'cancelled': 'bi-x-circle-fill', 'returned': 'bi-arrow-return-left' };
  return map[status] || 'bi-info-circle';
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchOrders(1);
});
</script>

<style scoped>
.user-orders-wrapper { width: 100%; padding: 28px;}

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }

.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }

.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.btn-outline-danger { transition: 0.2s; }
.btn-outline-danger:hover { background-color: #dc3545; color: white; }

.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }

/* TABS TÙY CHỈNH CHỐNG VỠ RESPONSIVE */
.custom-tab { color: #6c757d; border-bottom: 2px solid transparent; transition: all 0.3s; padding-bottom: 12px !important; font-size: 0.95rem; }
.custom-tab:hover { color: var(--color-c-hover, #547792); }
.custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; border-bottom-color: var(--color-c-hover, #547792) !important; }

/* UTILS */
.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important; }

.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; }
.hover-dark:hover { background-color: #212529 !important; color: white !important; border-color: #212529 !important; }
.hover-danger:hover { background-color: #dc3545 !important; color: white !important; border-color: #dc3545 !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus { box-shadow: none !important; }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.cursor-pointer { cursor: pointer; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* SKELETON CSS */
.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

</style>