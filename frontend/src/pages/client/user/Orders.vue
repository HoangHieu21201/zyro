<template>
  <div class="user-orders-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
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
            
            <!-- KHỐI THỐNG KÊ TỔNG ĐƠN HÀNG VÀ TỔNG CHI TIÊU -->
            <div class="row g-3 mb-4 animation-fade-in">
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-[#1a2533] overflow-hidden position-relative p-4 border border-light-subtle dark:border-gray-700">
                  <div class="position-absolute top-50 translate-middle-y end-0 pe-4 opacity-10 text-muted">
                    <i class="bi bi-bag-check-fill" style="font-size: 5rem;"></i>
                  </div>
                  <div class="position-relative z-index-2">
                    <h6 class="fw-bold text-muted mb-2 text-uppercase font-sans-vn" style="letter-spacing: 1px; font-size: 0.8rem;">Tổng số đơn hàng</h6>
                    <h2 class="fw-bold text-dark dark:text-white mb-0 font-sans-vn d-flex align-items-baseline gap-2" style="font-size: 2.2rem;">
                      {{ orderStats.total_orders || 0 }} <span class="fs-6 text-muted fw-normal">Đơn</span>
                    </h2>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-urban text-white overflow-hidden position-relative p-4">
                  <div class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-10" style="clip-path: polygon(0 0, 100% 0, 100% 30%, 0% 100%);"></div>
                  
                  <div class="position-absolute top-50 translate-middle-y end-0 pe-4 opacity-25">
                    <i class="bi bi-wallet2" style="font-size: 5rem;"></i>
                  </div>
                  <div class="position-relative z-index-2">
                    <h6 class="fw-bold text-white-50 mb-2 text-uppercase font-sans-vn" style="letter-spacing: 1px; font-size: 0.8rem;">Tổng chi tiêu</h6>
                    <h2 class="fw-bold text-white mb-0 font-sans-vn d-flex align-items-baseline gap-2" style="font-size: 2.2rem;">
                      {{ formatCurrency(orderStats.total_spent || 0) }}
                    </h2>
                  </div>
                </div>
              </div>
            </div>

            <!-- BỘ LỌC TÌM KIẾM -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-3 p-md-4 mb-4 animation-fade-in font-sans-vn">
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
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap" :class="{ 'active-tab': activeTab === 'completed' }" href="#" @click.prevent="changeTab('completed')">Thành công</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap text-danger" :class="{ 'active-tab': activeTab === 'cancelled' }" href="#" @click.prevent="changeTab('cancelled')">Đã hủy</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link py-2 px-1 fw-bold custom-tab text-nowrap text-secondary" :class="{ 'active-tab': activeTab === 'returned' }" href="#" @click.prevent="changeTab('returned')">Hoàn trả / Đổi hàng</a>
                  </li>
                </ul>
              </div>

              <div class="row g-2 g-md-3 align-items-center">
                <div class="col-12 col-lg-5">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="searchQuery" @input="debounceFetch" placeholder="Tên SP, Mã đơn...">
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted px-2"><i class="bi bi-calendar"></i></span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white px-1" style="font-size: 0.85rem;" v-model="filterDateFrom" @change="debounceFetch" title="Từ ngày">
                  </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                  <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted px-2"><i class="bi bi-calendar-fill"></i></span>
                    <input type="date" class="form-control border-0 bg-transparent shadow-none dark:text-white px-1" style="font-size: 0.85rem;" v-model="filterDateTo" @change="debounceFetch" title="Đến ngày">
                  </div>
                </div>

                <div class="col-8 col-md-4 col-lg-2">
                  <div class="d-flex align-items-center bg-light dark:bg-[#212529] rounded-pill border border-secondary-subtle dark:border-gray-600 px-2 overflow-hidden shadow-sm-hover h-100" style="min-height: 38px;">
                     <i class="bi bi-sort-down text-muted ms-1"></i>
                     <select class="form-select border-0 shadow-none fw-medium text-dark dark:text-gray-200 bg-transparent py-0" style="font-size: 0.85rem; cursor: pointer;" v-model="filterSort" @change="debounceFetch">
                       <option value="desc">Mới nhất</option>
                       <option value="asc">Cũ nhất</option>
                     </select>
                  </div>
                </div>

                <div class="col-4 col-md-12 col-lg-1 text-end text-lg-center">
                  <button v-if="hasFilters" class="btn btn-light text-danger fw-bold rounded-pill border shadow-sm w-100 p-0 d-flex align-items-center justify-content-center hover-danger" style="height: 38px;" title="Xóa lọc" @click="clearFilters">
                    <i class="bi bi-x-lg"></i> <span class="d-md-none ms-1 small">Xóa</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="animation-fade-in">
              
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
                <h6 class="fw-bold text-dark dark:text-white font-sans-vn">Chưa có đơn hàng nào</h6>
                <p class="text-muted small font-sans-vn">Bạn chưa có đơn hàng nào trong trạng thái này.</p>
                <router-link to="/category" class="btn btn-outline-urban rounded-pill px-4 fw-semibold mt-2 font-sans-vn">Bắt đầu mua sắm</router-link>
              </div>

              <!-- Lưới Card Đơn hàng -->
              <div v-else class="d-flex flex-column gap-4">
                <div v-for="order in orders" :key="order.id" class="card border-light-subtle dark:border-gray-700 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden group hover-border-urban transition-all">
                  
                  <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="badge bg-urban text-white rounded-1 shadow-sm d-none d-sm-inline">ZYRO</span>
                      <span class="fw-bold font-monospace text-dark dark:text-gray-300 small border-sm-start ps-sm-2 dark:border-gray-600">#{{ order.order_code }}</span>
                      <span class="text-muted small ms-1 font-sans-vn"><i class="bi bi-clock me-1"></i>{{ formatDate(order.created_at) }}</span>
                    </div>
                    
                    <span v-if="order.return_status" class="fw-bold text-uppercase d-flex align-items-center gap-1 font-sans-vn" :class="getReturnLabelColor(order.return_status)" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                      <i class="bi bi-arrow-return-left"></i> Yêu cầu đổi trả
                    </span>
                    <span v-else class="fw-bold text-uppercase d-flex align-items-center gap-1 font-sans-vn" :class="getOrderStatusColor(order.status)" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                      <i class="bi" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
                    </span>
                  </div>

                  <div class="card-body p-0 cursor-pointer" @click="openOrderDetail(order.id)">
                    <template v-for="(group, gIdx) in cartGroups(order.items)" :key="'grp'+gIdx">

                      <!-- GÓI COMBO LOOKBOOK -->
                      <div v-if="group.isLookbook" class="p-3 p-md-4 border-bottom dark:border-gray-700">
                          <div class="d-flex align-items-start gap-3">
                             <div class="position-relative flex-shrink-0" style="width: 80px; height: 100px;">
                               <img :src="group.lookbook_image" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                             </div>
                             
                             <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                   <div>
                                      <span class="badge bg-secondary text-white rounded-pill px-2 py-1 shadow-sm font-sans-vn mb-1" style="font-size: 0.65rem;">
                                         <i class="bi bi-magic me-1"></i> {{ group.lookbook_name }}
                                      </span>
                                      <h6 class="fw-bold text-dark dark:text-gray-200 mb-0 font-sans-vn" style="font-size: 0.95rem;">Combo {{ group.items.length }} món đồ</h6>
                                   </div>
                                   <div class="text-end flex-shrink-0">
                                      <div class="fw-bold text-danger font-sans-vn">{{ formatCurrency(group.totalPrice) }}</div>
                                   </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                   <div class="text-muted small d-flex align-items-center gap-1 cursor-pointer hover-urban transition-color font-sans-vn fw-medium" @click.stop="toggleGroup(order.id, group.lookbook_id)">
                                       <i class="bi" :class="isGroupExpanded(order.id, group.lookbook_id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                       {{ isGroupExpanded(order.id, group.lookbook_id) ? 'Thu gọn chi tiết' : 'Xem chi tiết các món' }}
                                   </div>
                                   <span class="fw-semibold text-dark dark:text-gray-300 font-sans-vn">SL: {{ group.comboQuantity }}</span>
                                </div>
                             </div>
                          </div>

                          <div v-show="isGroupExpanded(order.id, group.lookbook_id)" class="combo-details mt-3 pt-3 border-top border-light-subtle dark:border-gray-700 ps-2" @click.stop>
                             <div class="d-flex flex-column gap-2">
                                <div v-for="item in group.items" :key="'cb_item_'+item.id" class="d-flex align-items-center gap-3 bg-white dark:bg-[#1a2533] p-2 rounded-3 border border-light-subtle dark:border-gray-700">
                                   <img :src="getImageUrl(item.variant_image)" style="width: 40px; height: 50px;" class="rounded-2 border dark:border-gray-600 object-fit-cover shadow-sm bg-light" @error="e => e.target.src='/client_placeholder.png'">
                                   <div class="flex-grow-1">
                                      <div class="fw-semibold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn" style="font-size: 0.85rem;">{{ item.product_name }}</div>
                                      <div class="text-secondary dark:text-gray-400 font-sans-vn d-flex justify-content-between pe-2 mt-1" style="font-size: 0.75rem;">
                                         <span>{{ parseAttributes(item.variant_attributes) }} <span class="mx-1">|</span> <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.purchased_price) }}</span></span>
                                         <span class="fw-bold">x{{ item.quantity }}</span>
                                      </div>
                                   </div>
                                </div>
                             </div>
                          </div>
                      </div>

                      <!-- SẢN PHẨM LẺ -->
                      <template v-else>
                         <div v-for="(item, idx) in group.items" :key="item.id" 
                              class="d-flex p-3 p-md-4 gap-3 position-relative border-bottom dark:border-gray-700">
                            
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-c-effect opacity-25 dark:bg-[#121416] pe-none z-0"></div>

                            <div class="position-relative z-1" style="width: 80px; height: 100px; flex-shrink: 0;">
                              <img :src="getImageUrl(item.variant_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                            </div>
                            
                            <div class="flex-grow-1 position-relative z-1 d-flex flex-column justify-content-between">
                              <div class="d-flex justify-content-between align-items-start gap-3">
                                <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2 font-sans-vn" style="font-size: 0.95rem;">{{ item.product_name }}</h6>
                                <div class="text-end flex-shrink-0">
                                   <div class="fw-bold text-dark dark:text-white font-sans-vn">{{ formatCurrency(item.purchased_price) }}</div>
                                </div>
                              </div>
                              <div class="d-flex justify-content-between align-items-end mt-2">
                                 <span class="text-muted dark:text-gray-400 small font-sans-vn">PL: {{ parseAttributes(item.variant_attributes) }}</span>
                                 <span class="fw-semibold text-dark dark:text-gray-300 font-sans-vn">x{{ item.quantity }}</span>
                              </div>
                            </div>
                         </div>
                      </template>

                    </template>
                  </div>

                  <div class="card-footer bg-white dark:bg-[#1a2533] border-top dark:border-gray-700 p-3 px-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="text-dark dark:text-gray-300 me-2 font-sans-vn">Thành tiền:</span>
                      <span class="fs-4 fw-bold text-urban font-sans-vn">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                      <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-dark font-sans-vn" @click="openOrderDetail(order.id)">
                         Chi Tiết
                      </button>
                      
                      <button v-if="order.status === 'pending'" class="btn btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-pill px-4 fw-semibold transition-all hover-danger font-sans-vn" @click.stop="cancelOrder(order)">
                         Hủy Đơn
                      </button>

                      <!-- NẾU ĐÃ LÀ ĐƠN YÊU CẦU HOÀN TRẢ -->
                      <template v-if="order.return_status">
                         <button class="btn bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-4 fw-bold font-sans-vn pe-none">
                            <i class="bi bi-info-circle-fill me-1"></i> {{ getReturnStatusLabel(order.return_status) }}
                         </button>
                         <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all font-sans-vn hover-urban-bg" @click.stop="buyAgain(order)">
                            Mua lại
                         </button>
                      </template>
                      
                      <!-- NẾU CHƯA CÓ YÊU CẦU HOÀN TRẢ NÀO VÀ ĐÃ GIAO XONG -->
                      <template v-else-if="order.status === 'completed'">
                        <button class="btn btn-outline-danger rounded-pill px-4 fw-semibold transition-all font-sans-vn" @click.stop="requestReturn(order)">
                           Hoàn trả / Đổi hàng
                        </button>
                        <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all font-sans-vn" @click.stop="buyAgain(order)">
                           Mua lại
                        </button>
                        <button v-if="order.is_reviewed" class="btn btn-outline-urban rounded-pill px-4 fw-bold shadow-sm transition-all font-sans-vn" @click.stop="openReview(order)">
                           Xem đánh giá
                        </button>
                        <button v-else class="btn btn-urban rounded-pill px-4 fw-bold shadow-sm transition-all font-sans-vn" @click.stop="openReview(order)">
                           Đánh giá
                        </button>
                      </template>

                    </div>
                  </div>
                  
                </div>
              </div>

              <!-- PHÂN TRANG -->
              <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-5">
                <ul class="pagination pagination-sm mb-0 shadow-sm">
                  <li class="page-item" :class="{'disabled': pagination.current_page === 1}">
                    <a class="page-link shadow-none dark:bg-[#212529] dark:border-gray-600 text-urban" href="#" @click.prevent="fetchOrders(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></a>
                  </li>
                  <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{'active': pagination.current_page === page}">
                    <a class="page-link shadow-none dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'dark:bg-[#212529] dark:text-gray-300'" href="#" @click.prevent="fetchOrders(page)">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{'disabled': pagination.current_page === pagination.last_page}">
                    <a class="page-link shadow-none dark:bg-[#212529] dark:border-gray-600 text-urban" href="#" @click.prevent="fetchOrders(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></a>
                  </li>
                </ul>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- MODAL -->
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
import Swal from 'sweetalert2';

import { useCartStore } from '@/stores/cartStore';

const router = useRouter();
const cartStore = useCartStore();
const orderDetailModalRef = ref(null);

const orders = ref([]);
const orderStats = ref({ total_orders: 0, total_spent: 0 }); // ĐÃ FIX THỐNG KÊ
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
    
    const minDelay = new Promise(resolve => setTimeout(resolve, 600)); 
    const apiCall = api.get('/client/user/orders', { params });

    const [res] = await Promise.all([apiCall, minDelay]);

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

const expandedGroups = ref([]);

const groupOrderItems = (items) => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };

  items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        let lbName = 'Combo Phong Cách';
        let lbImage = '/client_placeholder.png';
        
        if (item.lookbook) {
           lbName = item.lookbook.name;
           lbImage = item.lookbook.main_image ? getImageUrl(item.lookbook.main_image) : '/client_placeholder.png';
        } else if (item.variant_image) {
           lbImage = getImageUrl(item.variant_image);
        }

        group = { 
          isLookbook: true, 
          lookbook_id: item.lookbook_id, 
          lookbook_name: lbName,
          lookbook_image: lbImage,
          items: [],
          comboQuantity: item.quantity, 
          totalPrice: 0 
        };
        result.push(group);
      }
      group.items.push(item);
      group.totalPrice += (item.purchased_price * item.quantity);
    } else {
      normalGroup.items.push(item);
    }
  });

  if (normalGroup.items.length > 0) {
    result.push(normalGroup);
  }

  return result;
};

const cartGroups = (items) => groupOrderItems(items);

const toggleGroup = (orderId, lookbookId) => {
  const key = `${orderId}_${lookbookId}`;
  if (expandedGroups.value.includes(key)) {
    expandedGroups.value = expandedGroups.value.filter(k => k !== key);
  } else {
    expandedGroups.value.push(key);
  }
};

const isGroupExpanded = (orderId, lookbookId) => expandedGroups.value.includes(`${orderId}_${lookbookId}`);

const openOrderDetail = (id) => {
  if (orderDetailModalRef.value) {
     orderDetailModalRef.value.openModal(id);
  }
};

// ========================================================
// HỦY ĐƠN: SỬ DỤNG CHECKLIST HTML CUSTOM
// ========================================================
const cancelOrder = (order) => {
  if (order.status !== 'pending') {
     ZyroSwal.toastError('Chỉ có thể hủy đơn chờ xác nhận');
     return;
  }

  Swal.fire({
      title: 'Hủy Đơn Hàng',
      html: `
        <style>
          .swal-checklist-label { border: 1px solid rgba(84,119,146,0.3); border-radius: 0.5rem; padding: 0.6rem 0.8rem; display: flex; align-items: center; cursor: pointer; transition: 0.2s; margin-bottom: 0.5rem; }
          .swal-checklist-label:hover { background-color: rgba(84,119,146,0.05); }
          .swal-checklist-label input[type="radio"] { cursor: pointer; width: 1.1rem; height: 1.1rem; accent-color: #dc3545; margin-top: 0; margin-right: 0.6rem; flex-shrink: 0; }
          .dark .swal-checklist-label { border-color: rgba(255,255,255,0.2); }
          .dark .swal-checklist-label:hover { background-color: rgba(255,255,255,0.05); }
        </style>
        <div class="text-start font-sans-vn mt-2">
          <label class="form-label small fw-bold text-muted text-uppercase mb-2">Lý do hủy đơn <span class="text-danger">*</span></label>
          <div class="d-flex flex-column mb-3">
             <label class="swal-checklist-label">
                <input type="radio" name="cancel_reason" value="Muốn thay đổi địa chỉ giao hàng" checked>
                <span class="small fw-medium text-dark dark:text-gray-200">Muốn thay đổi địa chỉ giao hàng</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="cancel_reason" value="Muốn thay đổi sản phẩm / Mã giảm giá">
                <span class="small fw-medium text-dark dark:text-gray-200">Muốn thay đổi sản phẩm / Mã giảm giá</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="cancel_reason" value="Đổi ý, không muốn mua nữa">
                <span class="small fw-medium text-dark dark:text-gray-200">Đổi ý, không muốn mua nữa</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="cancel_reason" value="Tìm thấy giá rẻ hơn ở nơi khác">
                <span class="small fw-medium text-dark dark:text-gray-200">Tìm thấy giá rẻ hơn ở nơi khác</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="cancel_reason" value="Lý do khác">
                <span class="small fw-medium text-dark dark:text-gray-200">Lý do khác...</span>
             </label>
          </div>
          <label class="form-label small fw-bold text-muted text-uppercase mb-2">Chi tiết thêm (nếu có)</label>
          <textarea id="swal-cancel-detail" class="form-control shadow-none bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" rows="2" placeholder="Nhập thêm chi tiết..."></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Xác nhận Hủy',
      cancelButtonText: 'Đóng',
      confirmButtonColor: '#dc3545',
      preConfirm: () => {
        const reason = document.querySelector('input[name="cancel_reason"]:checked')?.value;
        const detail = document.getElementById('swal-cancel-detail').value.trim();
        if (reason === 'Lý do khác' && !detail) {
          Swal.showValidationMessage('Vui lòng nhập chi tiết lý do hủy đơn của bạn!');
          return false;
        }
        return detail ? `${reason} - Chi tiết: ${detail}` : reason;
      },
      customClass: { popup: 'rounded-4 dark:bg-[#1a2533]' }
  }).then(async (result) => {
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

// ========================================================
// YÊU CẦU HOÀN TRẢ: SỬ DỤNG CHECKLIST HTML CUSTOM
// ========================================================
const requestReturn = (order) => {
  Swal.fire({
      title: 'Hỗ trợ Hoàn trả / Đổi hàng',
      html: `
        <style>
          .swal-checklist-label { border: 1px solid rgba(84,119,146,0.3); border-radius: 0.5rem; padding: 0.6rem 0.8rem; display: flex; align-items: center; cursor: pointer; transition: 0.2s; margin-bottom: 0.5rem; }
          .swal-checklist-label:hover { background-color: rgba(84,119,146,0.05); }
          .swal-checklist-label input[type="radio"] { cursor: pointer; width: 1.1rem; height: 1.1rem; accent-color: #dc3545; margin-top: 0; margin-right: 0.6rem; flex-shrink: 0; }
          .dark .swal-checklist-label { border-color: rgba(255,255,255,0.2); }
          .dark .swal-checklist-label:hover { background-color: rgba(255,255,255,0.05); }
        </style>
        <div class="text-start font-sans-vn mt-2">
          <div class="alert alert-warning small border-0 py-2 px-3 mb-3 dark:bg-yellow-900/30">
             <i class="bi bi-info-circle-fill me-1"></i> Lưu ý: ZYRO hỗ trợ giải quyết yêu cầu trên toàn bộ đơn hàng.
          </div>

          <!-- Câu hỏi 1: Nhu Cầu -->
          <label class="form-label small fw-bold text-muted text-uppercase mb-2">Nhu cầu của bạn <span class="text-danger">*</span></label>
          <div class="d-flex flex-column mb-3">
             <label class="swal-checklist-label">
                <input type="radio" name="return_type" value="Hoàn tiền trả hàng" checked>
                <span class="small fw-bold text-dark dark:text-white">Hoàn tiền trả hàng</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="return_type" value="Đổi hàng (Lỗi/Size)">
                <span class="small fw-bold text-dark dark:text-white">Đổi hàng (Đổi Size / Sản phẩm lỗi)</span>
             </label>
          </div>

          <!-- Câu hỏi 2: Lý Do -->
          <label class="form-label small fw-bold text-muted text-uppercase mb-2">Lý do chi tiết <span class="text-danger">*</span></label>
          <div class="d-flex flex-column mb-3">
            <label class="swal-checklist-label">
                <input type="radio" name="return_reason" value="Sản phẩm bị lỗi sản xuất / Hư hỏng" checked>
                <span class="small fw-medium text-dark dark:text-gray-200">Sản phẩm bị lỗi sản xuất / Hư hỏng</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="return_reason" value="Giao sai mẫu / Không đúng mô tả">
                <span class="small fw-medium text-dark dark:text-gray-200">Giao sai mẫu / Không đúng mô tả</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="return_reason" value="Không vừa Size / Cần đổi Size khác">
                <span class="small fw-medium text-dark dark:text-gray-200">Không vừa Size / Cần đổi Size khác</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="return_reason" value="Đổi ý / Yêu cầu hoàn tiền">
                <span class="small fw-medium text-dark dark:text-gray-200">Đổi ý / Yêu cầu hoàn tiền</span>
             </label>
             <label class="swal-checklist-label">
                <input type="radio" name="return_reason" value="Lý do khác">
                <span class="small fw-medium text-dark dark:text-gray-200">Lý do khác...</span>
             </label>
          </div>
          
          <!-- Chi tiết Mô tả -->
          <label class="form-label small fw-bold text-muted text-uppercase mb-2">Mô tả thêm chi tiết</label>
          <textarea id="swal-return-detail" class="form-control shadow-none bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" rows="3" placeholder="Nhập tình trạng cụ thể (VD: Size S áo chật quá...) để ZYRO hỗ trợ nhanh nhất..."></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Gửi yêu cầu',
      cancelButtonText: 'Đóng',
      confirmButtonColor: '#dc3545',
      preConfirm: () => {
        const type = document.querySelector('input[name="return_type"]:checked')?.value;
        const reason = document.querySelector('input[name="return_reason"]:checked')?.value;
        const detail = document.getElementById('swal-return-detail').value.trim();
        
        if (reason === 'Lý do khác' && !detail) {
          Swal.showValidationMessage('Vui lòng nhập mô tả chi tiết lý do của bạn!');
          return false;
        }
        
        // Nối chuỗi để lưu vào DB một cách chi tiết và rõ ràng nhất
        let finalReason = `[Yêu cầu: ${type}] - Lý do: ${reason}`;
        if (detail) {
            finalReason += ` - Chi tiết: ${detail}`;
        }
        return finalReason;
      },
      customClass: { popup: 'rounded-4 dark:bg-[#1a2533]' }
  }).then(async (result) => {
      if (result.isConfirmed) {
          try {
            ZyroSwal.showLoading('Đang gửi yêu cầu...');
            const res = await api.post(`/client/user/orders/${order.id}/return`, { reason: result.value });
            ZyroSwal.close();
            ZyroSwal.toastSuccess(res.data.message);
            fetchOrders(pagination.value.current_page);
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

const getReturnLabelColor = (status) => {
  const map = { 'pending': 'text-warning', 'proposing': 'text-info', 'approved': 'text-success', 'rejected': 'text-danger' };
  return map[status] || 'text-secondary';
};

const getReturnStatusLabel = (status) => {
  const map = { 'pending': 'Đang xử lý hoàn trả', 'proposing': 'Đang thỏa thuận hoàn trả', 'approved': 'Đã hoàn tiền', 'rejected': 'Từ chối hoàn trả' };
  return map[status] || 'Đang xử lý hoàn trả';
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
.hover-urban-bg:hover { background-color: var(--color-c-hover, #547792); color: white; border-color: var(--color-c-hover, #547792); }

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
.form-control:focus, .form-select:focus { box-shadow: none !important; border-color: var(--color-c-hover, #547792) !important; }

.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.cursor-pointer { cursor: pointer; }

.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; }
html.dark .border-dashed { border-color: #373b3e !important; }
.last-no-border:last-child { margin-bottom: 0 !important; padding-bottom: 0 !important; border: none !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

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