<!-- File: frontend/src/pages/client/user/Orders.vue -->
<template>
  <div class="user-orders-wrapper pb-5 mb-5">
    
    <!-- Đẩy nội dung xuống dưới Header -->
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Đơn mua hàng</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- ========================================== -->
          <!-- CỘT TRÁI: ĐÃ ĐƯỢC TÁCH RA THÀNH COMPONENT  -->
          <!-- ========================================== -->
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <!-- ========================================== -->
          <!-- CỘT PHẢI: QUẢN LÝ ĐƠN HÀNG                   -->
          <!-- ========================================== -->
          <div class="col-lg-9">
            
            <!-- 1. TOP ORDER: THỐNG KÊ NHANH -->
            <div class="row g-3 mb-4 animation-fade-in">
              <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10 border border-warning border-opacity-25 cursor-pointer hover-transform" @click="activeTab = 'pending'">
                  <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                      <h6 class="fw-bold text-warning mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Chờ xử lý</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0">{{ orderStats.pending }} <span class="fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-info bg-opacity-10 border border-info border-opacity-25 cursor-pointer hover-transform" @click="activeTab = 'shipping'">
                  <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                      <h6 class="fw-bold text-info mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Đang giao hàng</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0">{{ orderStats.shipping }} <span class="fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-truck fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10 border border-success border-opacity-25 cursor-pointer hover-transform" @click="activeTab = 'completed'">
                  <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                      <h6 class="fw-bold text-success mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Đánh giá</h6>
                      <h3 class="fw-bold text-dark dark:text-white mb-0">{{ orderStats.completed }} <span class="fs-6 text-muted fw-normal">đơn</span></h3>
                    </div>
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                      <i class="bi bi-star fs-5"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. LỌC THÔNG MINH (SMART FILTER) -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom dark:border-gray-700 pb-2 mb-3">
                <!-- TABS -->
                <ul class="nav nav-underline custom-scrollbar-x flex-nowrap" style="gap: 15px; margin-bottom: -1px;">
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'all' }" href="#" @click.prevent="activeTab = 'all'">Tất cả</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'pending' }" href="#" @click.prevent="activeTab = 'pending'">Chờ thanh toán</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'processing' }" href="#" @click.prevent="activeTab = 'processing'">Đang xử lý</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'shipping' }" href="#" @click.prevent="activeTab = 'shipping'">Đang giao</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'completed' }" href="#" @click.prevent="activeTab = 'completed'">Hoàn thành</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-0 fw-bold custom-tab text-nowrap text-danger" :class="{ 'active-tab': activeTab === 'cancelled' }" href="#" @click.prevent="activeTab = 'cancelled'">Đã hủy</a>
                  </li>
                </ul>
              </div>

              <div class="row g-3 align-items-center">
                <!-- TÌM KIẾM THEO NGÀY -->
                <div class="col-md-5">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-calendar-range"></i></span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="filterDateFrom" title="Từ ngày">
                    <span class="input-group-text bg-transparent border-0 text-muted">-</span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="filterDateTo" title="Đến ngày">
                  </div>
                </div>

                <!-- TÌM KIẾM TEXT -->
                <div class="col-md-5">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="searchQuery" placeholder="Tên SP, Mã đơn...">
                  </div>
                </div>

                <div class="col-md-2 text-end">
                  <button v-if="hasFilters" class="btn btn-sm btn-light text-danger fw-bold rounded-pill border shadow-sm w-100 py-2" @click="clearFilters">
                    <i class="bi bi-x-circle me-1"></i> Xóa lọc
                  </button>
                </div>
              </div>
            </div>

            <!-- 3. DANH SÁCH ĐƠN HÀNG -->
            <div class="animation-fade-in">
              
              <div v-if="filteredOrders.length === 0" class="text-center py-5 my-4 bg-white dark:bg-[#1a2533] rounded-4 shadow-sm border border-light-subtle dark:border-gray-700">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                  <i class="bi bi-receipt text-muted opacity-50" style="font-size: 2.5rem;"></i>
                </div>
                <h6 class="fw-bold text-dark dark:text-white">Chưa có đơn hàng nào</h6>
                <p class="text-muted small">Bạn chưa có đơn hàng nào trong trạng thái này.</p>
                <router-link to="/category" class="btn btn-outline-urban rounded-pill px-4 fw-semibold mt-2">Bắt đầu mua sắm</router-link>
              </div>

              <div v-else class="d-flex flex-column gap-4">
                <div v-for="order in filteredOrders" :key="order.id" class="card border-light-subtle dark:border-gray-700 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden group hover-border-urban transition-all">
                  
                  <!-- Card Header: Shop & Status -->
                  <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-urban text-white rounded-1 shadow-sm">ZYRO MALL</span>
                      <span class="fw-bold font-monospace text-dark dark:text-gray-300 small border-start ps-2 dark:border-gray-600">#{{ order.order_code }}</span>
                      <span class="text-muted small ms-2"><i class="bi bi-clock me-1"></i>{{ formatDate(order.created_at) }}</span>
                    </div>
                    <span class="fw-bold text-uppercase d-flex align-items-center gap-1" :class="getOrderStatusColor(order.status)" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                      <i class="bi" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
                    </span>
                  </div>

                  <!-- Card Body: Items List -->
                  <div class="card-body p-0">
                    <div v-for="(item, idx) in order.items" :key="item.id" 
                         class="d-flex p-3 p-md-4 gap-3 position-relative"
                         :class="{'border-top dark:border-gray-700': idx > 0}">
                      
                      <!-- Lớp mờ ngăn cách màu nhẹ (giống Shopee) -->
                      <div class="position-absolute top-0 start-0 w-100 h-100 bg-c-effect opacity-25 dark:bg-[#121416] pe-none z-0"></div>

                      <div class="position-relative z-1" style="width: 80px; height: 80px; flex-shrink: 0;">
                        <img :src="item.image" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white">
                      </div>
                      
                      <div class="flex-grow-1 position-relative z-1 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                          <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2" style="font-size: 0.95rem;">{{ item.name }}</h6>
                          <div class="text-end flex-shrink-0">
                             <div class="fw-bold text-dark dark:text-white">{{ formatCurrency(item.price) }}</div>
                             <div v-if="item.old_price" class="text-muted text-decoration-line-through small">{{ formatCurrency(item.old_price) }}</div>
                          </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                           <span class="text-muted dark:text-gray-400 small">Phân loại: {{ item.variant }}</span>
                           <span class="fw-semibold text-dark dark:text-gray-300">x{{ item.quantity }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Card Footer: Total & Actions -->
                  <div class="card-footer bg-white dark:bg-[#1a2533] border-top dark:border-gray-700 p-3 px-md-4">
                    <div class="d-flex justify-content-end align-items-center mb-3">
                      <span class="text-dark dark:text-gray-300 me-2">Thành tiền:</span>
                      <span class="fs-4 fw-bold text-urban">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                      <button v-if="['completed', 'cancelled'].includes(order.status)" class="btn btn-urban text-white rounded-pill px-4 fw-bold shadow-sm hover-transform">
                         Mua Lại
                      </button>
                      <button v-if="order.status === 'completed'" class="btn btn-outline-urban rounded-pill px-4 fw-bold hover-bg-urban">
                         Đánh Giá
                      </button>
                      <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-dark" @click="openOrderDetail(order)">
                         Xem Chi Tiết
                      </button>
                      <button v-if="order.status === 'pending'" class="btn btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-danger">
                         Hủy Đơn Hàng
                      </button>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL CHI TIẾT ĐƠN HÀNG (ORDER DETAIL)     -->
    <!-- ========================================== -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533]">
          <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-4">
            <h5 class="fw-bold text-dark dark:text-white mb-0">
              <i class="bi bi-receipt text-urban me-2"></i>Chi Tiết Đơn Hàng 
              <span v-if="selectedOrder" class="font-monospace text-urban ms-1">#{{ selectedOrder.order_code }}</span>
            </h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4 custom-scrollbar-y" style="max-height: 70vh; overflow-y: auto;" v-if="selectedOrder">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom dark:border-gray-700">
               <div>
                 <p class="text-muted small mb-1">Ngày đặt hàng: {{ formatDate(selectedOrder.created_at) }}</p>
                 <span class="badge border px-3 py-2 shadow-sm" :class="getPaymentStatusClass(selectedOrder.payment_status)">
                   <i class="bi bi-credit-card me-1"></i> {{ getPaymentStatusLabel(selectedOrder.payment_status) }} ({{ (selectedOrder.payment_method || 'COD').toUpperCase() }})
                 </span>
               </div>
               <div class="text-end">
                 <span class="fw-bold text-uppercase d-flex align-items-center gap-1 fs-6" :class="getOrderStatusColor(selectedOrder.status)">
                    <i class="bi" :class="getOrderStatusIcon(selectedOrder.status)"></i> {{ getOrderStatusLabel(selectedOrder.status) }}
                 </span>
               </div>
            </div>

            <div class="row g-4 mb-4">
               <div class="col-md-6">
                  <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 h-100">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Thông tin người nhận</h6>
                    <div class="fw-bold text-dark dark:text-gray-200">{{ selectedOrder.shipping_info?.name || currentUser.name }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-telephone-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.phone || '0987xxxxxx' }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.address || 'Không có địa chỉ' }}</div>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 h-100">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Thông tin vận chuyển</h6>
                    <div v-if="selectedOrder.shipping_provider">
                       <div class="fw-bold text-dark dark:text-gray-200">Đơn vị: {{ selectedOrder.shipping_provider }}</div>
                       <div class="text-muted small mt-1 font-monospace">Mã vận đơn: <span class="text-primary fw-bold">{{ selectedOrder.tracking_number }}</span></div>
                    </div>
                    <div v-else class="text-muted small fst-italic">Đơn hàng đang chờ xử lý vận chuyển.</div>
                  </div>
               </div>
            </div>

            <h6 class="fw-bold text-dark dark:text-white mb-3">Sản phẩm đã đặt</h6>
            <div class="table-responsive border rounded-3 dark:border-gray-700 mb-4">
              <table class="table table-borderless align-middle mb-0">
                <thead class="bg-light dark:bg-[#212529] border-bottom dark:border-gray-600">
                  <tr>
                    <th class="small text-muted text-uppercase py-3">Sản phẩm</th>
                    <th class="small text-muted text-uppercase py-3 text-center">Đơn giá</th>
                    <th class="small text-muted text-uppercase py-3 text-center">SL</th>
                    <th class="small text-muted text-uppercase py-3 text-end">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in selectedOrder.items" :key="idx" class="border-bottom dark:border-gray-700">
                    <td class="py-3">
                      <div class="d-flex align-items-center gap-3">
                        <img :src="item.image" class="rounded object-fit-cover border dark:border-gray-600" style="width: 50px; height: 50px;">
                        <div>
                          <div class="fw-bold text-dark dark:text-gray-200 small">{{ item.name }}</div>
                          <div class="text-muted small mt-1">Phân loại: {{ item.variant }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center fw-bold text-dark dark:text-gray-300 small">{{ formatCurrency(item.price) }}</td>
                    <td class="text-center fw-bold text-dark dark:text-gray-300 small">{{ item.quantity }}</td>
                    <td class="text-end fw-bold text-urban small">{{ formatCurrency(item.price * item.quantity) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="row">
               <div class="col-md-6 offset-md-6">
                  <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3">
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Tạm tính:</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.total_amount - (selectedOrder.shipping_fee || 0) + (selectedOrder.discount_amount || 0)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Phí vận chuyển:</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.shipping_fee || 0) }}</span>
                    </div>
                    <div v-if="selectedOrder.discount_amount > 0" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Giảm giá/Voucher:</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.discount_amount) }}</span>
                    </div>
                    <hr class="dark:border-gray-600 my-2">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <span class="fw-bold text-dark dark:text-white text-uppercase">Tổng cộng:</span>
                      <span class="text-danger fw-bold fs-4">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                    </div>
                  </div>
               </div>
            </div>

          </div>
          <div class="modal-footer border-top dark:border-gray-700 bg-light dark:bg-[#212529] p-3 justify-content-end">
            <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import UserSidebar from '@/components/client/UserSidebar.vue';

const currentUser = ref({
  name: 'Alex Nguyễn',
  avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop'
});

const activeTab = ref('all');
const searchQuery = ref('');

// BỘ LỌC NGÀY
const filterDateFrom = ref('');
const filterDateTo = ref('');

// CHI TIẾT ĐƠN HÀNG
const selectedOrder = ref(null);
let orderDetailModalInstance = null;

// MOCK DATA: Đơn hàng thực tế
const orders = ref([
  {
    id: 1,
    order_code: 'ZYRO-2604A1B2',
    status: 'pending',
    payment_status: 'unpaid',
    payment_method: 'cod',
    created_at: '2026-04-08T09:00:00Z',
    total_amount: 859000,
    shipping_fee: 30000,
    discount_amount: 0,
    shipping_info: { name: 'Alex Nguyễn', phone: '0987654321', address: '123 Đường ABC, Quận 1, TP HCM' },
    items: [
      { id: 101, name: 'Áo Sơ Mi Nam Cộc Tay Cafe Túi Ngực Chống Nhăn Cao Cấp', variant: 'Xám sáng, Size L', price: 469000, old_price: 650000, quantity: 1, image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=200&auto=format&fit=crop' },
      { id: 102, name: 'Quần Kaki Nam Dáng Ôm', variant: 'Đen, Size 32', price: 360000, old_price: null, quantity: 1, image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=200&auto=format&fit=crop' }
    ]
  },
  {
    id: 2,
    order_code: 'ZYRO-2403X9Y8',
    status: 'shipping',
    payment_status: 'paid',
    payment_method: 'vnpay',
    created_at: '2026-03-24T14:30:00Z',
    total_amount: 199000,
    shipping_fee: 0,
    discount_amount: 50000,
    shipping_provider: 'Giao Hàng Nhanh',
    tracking_number: 'GHN123456789',
    shipping_info: { name: 'Alex Nguyễn', phone: '0987654321', address: '123 Đường ABC, Quận 1, TP HCM' },
    items: [
      { id: 204, name: 'Áo Thun Typography Nữ Mùa Hè', variant: 'Trắng, Size S', price: 249000, old_price: 398000, quantity: 1, image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=200&auto=format&fit=crop' }
    ]
  },
  {
    id: 3,
    order_code: 'ZYRO-1502K7M6',
    status: 'completed',
    payment_status: 'paid',
    payment_method: 'card',
    created_at: '2026-02-15T10:15:00Z',
    total_amount: 1250000,
    shipping_fee: 0,
    discount_amount: 0,
    shipping_provider: 'Giao Hàng Tiết Kiệm',
    tracking_number: 'GHTK987654321',
    shipping_info: { name: 'Alex Nguyễn', phone: '0987654321', address: '123 Đường ABC, Quận 1, TP HCM' },
    items: [
      { id: 301, name: 'Giày Thể Thao Nam Chạy Bộ Nhẹ Tênh', variant: 'Xanh Navy, Size 42', price: 1250000, old_price: 1500000, quantity: 1, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=200&auto=format&fit=crop' }
    ]
  },
  {
    id: 4,
    order_code: 'ZYRO-0101Q5W4',
    status: 'cancelled',
    payment_status: 'unpaid',
    payment_method: 'cod',
    created_at: '2026-01-01T08:00:00Z',
    total_amount: 450000,
    shipping_fee: 30000,
    discount_amount: 0,
    shipping_info: { name: 'Alex Nguyễn', phone: '0987654321', address: '123 Đường ABC, Quận 1, TP HCM' },
    items: [
      { id: 401, name: 'Áo Khoác Gió Nữ Thể Thao', variant: 'Xanh Mint, Size M', price: 420000, old_price: 650000, quantity: 1, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=200&auto=format&fit=crop' }
    ]
  }
]);

// MỞ MODAL XEM CHI TIẾT
const openOrderDetail = (order) => {
  selectedOrder.value = order;
  if (!orderDetailModalInstance) {
    orderDetailModalInstance = new window.bootstrap.Modal(document.getElementById('orderDetailModal'));
  }
  orderDetailModalInstance.show();
};

onBeforeUnmount(() => {
  if (orderDetailModalInstance) orderDetailModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

// COMPUTED STATS
const orderStats = computed(() => {
  return {
    pending: orders.value.filter(o => o.status === 'pending').length,
    shipping: orders.value.filter(o => o.status === 'shipping').length,
    completed: orders.value.filter(o => o.status === 'completed').length,
  };
});

// KIỂM TRA XEM CÓ BỘ LỌC ĐANG CHẠY KHÔNG
const hasFilters = computed(() => {
  return filterDateFrom.value !== '' || filterDateTo.value !== '' || searchQuery.value !== '';
});

const clearFilters = () => {
  filterDateFrom.value = '';
  filterDateTo.value = '';
  searchQuery.value = '';
};

// COMPUTED LỌC ĐƠN HÀNG (CÓ LỌC NGÀY THÁNG)
const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Lọc theo Tab Trạng thái
  if (activeTab.value !== 'all') {
    result = result.filter(o => o.status === activeTab.value);
  }

  // 2. Lọc theo Search (Mã đơn hoặc Tên SP)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(o => {
      if (o.order_code.toLowerCase().includes(q)) return true;
      return o.items.some(item => item.name.toLowerCase().includes(q));
    });
  }

  // 3. Lọc theo Ngày tháng (Từ ngày - Đến ngày)
  if (filterDateFrom.value) {
    const fromDate = new Date(filterDateFrom.value).setHours(0,0,0,0);
    result = result.filter(o => new Date(o.created_at).getTime() >= fromDate);
  }
  
  if (filterDateTo.value) {
    const toDate = new Date(filterDateTo.value).setHours(23,59,59,999);
    result = result.filter(o => new Date(o.created_at).getTime() <= toDate);
  }

  return result;
});

// HELPERS
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);

const formatDate = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')}`;
};

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ thanh toán', 'processing': 'Đang xử lý', 'shipping': 'Đang giao hàng', 'completed': 'Giao hàng thành công', 'cancelled': 'Đã hủy' };
  return map[status] || status;
};

const getOrderStatusColor = (status) => {
  const map = { 'pending': 'text-warning', 'processing': 'text-info', 'shipping': 'text-primary', 'completed': 'text-success', 'cancelled': 'text-danger' };
  return map[status] || 'text-secondary';
};

const getOrderStatusIcon = (status) => {
  const map = { 'pending': 'bi-clock-history', 'processing': 'bi-box-seam', 'shipping': 'bi-truck', 'completed': 'bi-check-circle-fill', 'cancelled': 'bi-x-circle-fill' };
  return map[status] || 'bi-info-circle';
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

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<style scoped>
.user-orders-wrapper { width: 100%; }

/* KHUNG CHUẨN ZYRO CONTAINER: 1310px */
.zyro-container {
  width: 100%;
  max-width: 1310px;
  margin: 0 auto;
  padding-left: 20px;
  padding-right: 20px;
}
@media (min-width: 1400px) {
  .zyro-container { padding-left: 0; padding-right: 0; }
}

/* =======================================================
   MÀU SẮC ĐỒNG BỘ TỪ APP.VUE
======================================================== */
.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }

.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }

.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }

/* TABS TÙY CHỈNH */
.custom-tab { color: #6c757d; border-bottom: 2px solid transparent; transition: all 0.3s; }
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

/* Scrollbar */
.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>