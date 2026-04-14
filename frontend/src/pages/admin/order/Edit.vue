<!-- File: frontend/src/pages/admin/order/Edit.vue -->
<template>
  <div class="order-edit-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải chi tiết hóa đơn...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-orders' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0 d-flex align-items-center flex-wrap gap-2">
              Đơn hàng <span class="text-urban font-monospace">#{{ order.order_code }}</span>
              <span class="badge border px-3 py-1.5 ms-2 fs-6 shadow-sm" :class="getOrderStatusClass(order.status)">
                <i class="bi me-1" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
              </span>
            </h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1"><i class="bi bi-calendar-event me-1"></i>Ngày đặt: {{ formatDateTime(order.created_at) }}</p>
          </div>
        </div>
      </div>

      <div class="row g-4">
        
        <!-- CỘT TRÁI: DANH SÁCH SP & TRACKING & TÀI CHÍNH -->
        <div class="col-xl-8 col-lg-7">
          
          <!-- Bảng Sản phẩm -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
            <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-bag-check-fill me-2"></i>Chi tiết sản phẩm mua ({{ order.items?.length || 0 }})</h6>
            
            <div class="table-responsive custom-scrollbar-x border rounded-3 dark:border-gray-700">
              <table class="table table-bordered mb-0 align-middle">
                <thead class="bg-light dark:bg-[#2b3035]">
                  <tr>
                    <th class="dark:text-gray-300 border-0" style="width: 50px;">Ảnh</th>
                    <th class="dark:text-gray-300 border-0">Sản phẩm</th>
                    <th class="dark:text-gray-300 border-0 text-center" style="width: 120px;">Đơn giá</th>
                    <th class="dark:text-gray-300 border-0 text-center" style="width: 80px;">SL</th>
                    <th class="dark:text-gray-300 border-0 text-end" style="width: 120px;">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="dark:border-gray-700">
                  <tr v-for="item in order.items" :key="item.id" class="dark:bg-[#1a2533]">
                    <td class="text-center">
                      <img :src="getImageUrl(item.product?.thumbnail_image)" class="rounded object-fit-cover border dark:border-gray-600 bg-white" style="width: 45px; height: 45px;" @error="handleImageError">
                    </td>
                    <td>
                      <div class="fw-bold text-dark dark:text-gray-200 small mb-1">{{ item.product_name }}</div>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;">Mã: {{ item.variant_sku }}</span>
                        <!-- Parse thuộc tính -->
                        <span v-for="(val, key) in parseAttributes(item.variant_attributes)" :key="key" class="badge bg-light text-dark dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 shadow-sm" style="font-size: 0.65rem;">
                          {{ key }}: {{ val }}
                        </span>
                      </div>
                    </td>
                    <td class="text-center text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(item.purchased_price) }}</td>
                    <td class="text-center fw-bold">x {{ item.quantity }}</td>
                    <td class="text-end text-danger fw-bold">{{ formatCurrency(item.total_price) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Tổng kết Tài chính -->
            <div class="row mt-4">
              <div class="col-md-6 offset-md-6 col-xl-5 offset-xl-7">
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Tổng tiền hàng:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.sub_total) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Phí vận chuyển:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.shipping_fee) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2 text-success">
                    <span class="small fw-semibold">Khuyến mãi / Voucher:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.discount_amount) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2 text-info" v-if="order.refunded_amount > 0">
                    <span class="small fw-semibold">Đã hoàn tiền:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.refunded_amount) }}</span>
                  </div>
                  <hr class="dark:border-gray-600 my-2">
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-uppercase fw-bold text-dark dark:text-white">TỔNG THU</span>
                    <span class="text-danger fw-bold fs-4">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tiến trình & Lịch sử -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 animation-fade-in">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom dark:border-gray-700 pb-2">
                <h6 class="fw-bold text-urban text-uppercase mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Tiến trình đơn hàng</h6>
                
                <!-- NÚT MỞ BẢN ĐỒ LIVE TRACKING -->
                <button type="button" class="btn btn-sm btn-urban text-white fw-bold rounded-pill shadow-sm d-flex align-items-center transition-all hover-transform" @click="openMapSimulation">
                   <i class="bi bi-map-fill me-2"></i> Xem Bản Đồ Live
                </button>
            </div>
            
            <div class="tracking-timeline position-relative">
               <!-- Cột Tracking Mô phỏng Text -->
               <div v-if="order.simulated_tracking && order.simulated_tracking.length > 0">
                  <div v-for="(track, index) in order.simulated_tracking" :key="index" class="timeline-item d-flex position-relative mb-4">
                    <div class="timeline-icon bg-urban text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0 z-1" style="width: 32px; height: 32px;">
                      <i class="bi" :class="getTrackingIcon(track.status)"></i>
                    </div>
                    <div class="timeline-content ms-3 ps-2 pb-2 border-bottom dark:border-gray-700 w-100">
                      <div class="d-flex justify-content-between flex-wrap gap-2 mb-1">
                        <strong class="text-dark dark:text-white" style="font-size: 0.95rem;">{{ track.location }}</strong>
                        <span class="badge bg-light dark:bg-[#2b3035] text-muted dark:text-gray-400 border">{{ track.time }}</span>
                      </div>
                      <p class="text-muted dark:text-gray-400 small mb-0">{{ track.description }}</p>
                    </div>
                  </div>
               </div>
               <div v-else class="text-center text-muted fst-italic">Không có dữ liệu tiến trình.</div>

               <!-- Đường thẳng nối timeline -->
               <div class="timeline-line position-absolute bg-secondary opacity-25" style="top: 30px; bottom: 30px; left: 15px; width: 2px; z-index: 0;"></div>
            </div>

            <!-- Lịch sử Admin cập nhật -->
            <div class="mt-4 pt-3 border-top dark:border-gray-700" v-if="order.histories && order.histories.length > 0">
               <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-clock-history me-1"></i>Nhật ký Hệ thống</h6>
               <ul class="list-group list-group-flush rounded-3 border dark:border-gray-700">
                  <li v-for="his in order.histories" :key="his.id" class="list-group-item bg-transparent dark:border-gray-700 p-3">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <div class="fw-bold text-dark dark:text-gray-200 small mb-1">
                           Chuyển: <span class="text-secondary text-decoration-line-through">{{ getOrderStatusLabel(his.old_status) }}</span> <i class="bi bi-arrow-right mx-1 text-muted"></i> <span class="text-urban">{{ getOrderStatusLabel(his.new_status) }}</span>
                        </div>
                        <div class="text-muted fst-italic" style="font-size: 0.8rem;">Note: "{{ his.note || 'Không có ghi chú' }}"</div>
                      </div>
                      <div class="text-end ms-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border mb-1"><i class="bi bi-person-badge"></i> {{ his.changer?.fullname || 'Hệ thống' }}</span>
                        <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ formatDateTime(his.created_at) }}</div>
                      </div>
                    </div>
                  </li>
               </ul>
            </div>
          </div>
        </div>

        <!-- CỘT PHẢI: KHÁCH HÀNG & THANH TOÁN & TRẠNG THÁI -->
        <div class="col-xl-4 col-lg-5">
           
           <!-- Thông tin Khách -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-person-lines-fill me-2"></i>Khách Hàng</h6>
              <div class="d-flex align-items-center mb-3">
                <img :src="getImageUrl(order.user?.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-3 border shadow-sm dark:border-gray-600" style="width: 50px; height: 50px;">
                <div class="overflow-hidden">
                  <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ order.user?.full_name || shippingInfoParsed?.name || 'Khách vãng lai' }}</h6>
                  <small class="text-muted dark:text-gray-400 d-block mt-1 font-monospace"><i class="bi bi-envelope me-1"></i>{{ order.user?.email || 'N/A' }}</small>
                </div>
              </div>
              <div class="bg-light dark:bg-[#212529] p-3 rounded-3 border dark:border-gray-700 small text-dark dark:text-gray-300">
                 <div class="mb-2"><i class="bi bi-telephone text-urban me-2 fw-bold"></i> SĐT: <strong>{{ shippingInfoParsed?.phone || 'N/A' }}</strong></div>
                 <div><i class="bi bi-geo-alt-fill text-urban me-2 fw-bold"></i> Đ/C: {{ shippingInfoParsed?.address }}, {{ shippingInfoParsed?.ward }}, {{ shippingInfoParsed?.district }}, {{ shippingInfoParsed?.city }}</div>
              </div>
           </div>

           <!-- Thanh toán & Giao nhận -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-truck me-2"></i>Giao dịch & Vận chuyển</h6>
              
              <div class="mb-3 d-flex justify-content-between align-items-center">
                 <span class="text-muted small fw-bold text-uppercase">Cổng thanh toán:</span>
                 <span class="badge bg-dark text-white shadow-sm border px-3 py-1.5"><i class="bi bi-wallet2 me-1"></i> {{ order.payment_method === 'cod' ? 'Tiền mặt (COD)' : (order.payment_method || 'N/A').toUpperCase() }}</span>
              </div>
              <div class="mb-3 d-flex justify-content-between align-items-center">
                 <span class="text-muted small fw-bold text-uppercase">Trạng thái T.Toán:</span>
                 <span class="badge border px-3 py-1.5 shadow-sm" :class="getPaymentStatusClass(order.payment_status)">{{ getPaymentStatusLabel(order.payment_status) }}</span>
              </div>

              <!-- FORM TÌM KIẾM KHO GỬI HÀNG THÔNG MINH -->
              <form @submit.prevent="onOriginCityChange">
                <div class="mb-3">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-1 d-flex align-items-center justify-content-between">
                    <span>Kho gửi hàng (Nơi xuất phát)</span>
                    <!-- Spinner Loading ngầm -->
                    <span v-if="isSavingShipping" class="spinner-border spinner-border-sm text-urban"></span>
                  </label>
                  <div class="position-relative dropdown-container" ref="provinceDropdownRef">
                    <input type="text"
                           class="form-control form-control-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 fw-semibold shadow-sm-hover"
                           v-model="provinceSearchQuery"
                           @focus="showProvinceDropdown = true"
                           @input="showProvinceDropdown = true"
                           placeholder="Tìm hoặc chọn Tỉnh/Thành phố...">
                    <i class="bi bi-chevron-down position-absolute top-50 end-0 translate-middle-y me-2 text-muted" style="pointer-events: none; font-size: 0.75rem;"></i>

                    <!-- ĐÃ FIX: Dropdown CSS Thuần tránh lỗi Dark Mode -->
                    <div v-if="showProvinceDropdown" class="custom-dropdown-menu shadow w-100 mt-1 position-absolute" style="z-index: 1050;">
                      <div v-if="filteredProvinces.length === 0" class="custom-dropdown-item text-muted small fst-italic px-3 py-2 border-0">Không tìm thấy</div>
                      <div v-else v-for="p in filteredProvinces" :key="p.code"
                          class="cursor-pointer small px-3 py-2 custom-dropdown-item"
                          :class="{'selected-dropdown-item': formShipping.origin_city === p.name}"
                          @click="selectProvince(p.name)">
                        {{ p.name }}
                      </div>
                    </div>
                  </div>
                  <small class="text-success fw-bold mt-1 d-block" style="font-size: 0.65rem;"><i class="bi bi-check-circle me-1"></i>Đã tự động ghi nhớ cho lần sau.</small>
                </div>

                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Mã vận đơn (Tracking Code)</label>
                <div class="input-group shadow-sm-hover mb-2">
                  <input type="text" class="form-control form-control-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 font-monospace text-uppercase" v-model="formShipping.tracking_number" placeholder="Nhập mã vận đơn...">
                  <button class="btn btn-sm btn-urban fw-bold" type="button" @click="onOriginCityChange" :disabled="isSavingShipping"><i class="bi bi-floppy-fill"></i></button>
                </div>
                <div class="d-flex gap-2">
                  <select class="form-select form-select-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 fw-semibold" v-model="formShipping.shipping_provider" @change="onOriginCityChange">
                    <option value="">-- Đơn vị vận chuyển --</option>
                    <option value="GHN">Giao Hàng Nhanh</option>
                    <option value="GHTK">Giao Hàng Tiết Kiệm</option>
                    <option value="VNPOST">VNPost</option>
                    <option value="VIETTEL">Viettel Post</option>
                    <option value="JNT">J&T Express</option>
                  </select>
                </div>
              </form>
           </div>

           <!-- FORM XỬ LÝ TRẠNG THÁI CHÍNH -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top" style="top: 20px;">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-toggles me-2"></i>Quyết định xử lý</h6>
              
              <!-- Cảnh báo đơn đã hoàn tất chu trình -->
              <div v-if="order.status === 'returned' && order.payment_status === 'paid' && (order.refunded_amount === null || order.refunded_amount == 0)" class="alert alert-warning border-0 p-3 rounded-3 shadow-sm mb-3 dark:bg-yellow-900/20 dark:text-yellow-200">
                 <i class="bi bi-arrow-return-left me-1 fs-5 align-middle"></i> <strong>YÊU CẦU HOÀN TRẢ!</strong><br>
                 <span class="small">Đơn hàng đã thanh toán nhưng khách đang yêu cầu hoàn tiền. Vui lòng chuyển sang trang <b>Quản lý Đơn Hoàn</b>.</span>
              </div>
              <div v-else-if="order.status === 'cancelled' || order.status === 'returned'" class="alert alert-danger border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-exclamation-triangle-fill me-1"></i> Đơn hàng này đã kết thúc ở trạng thái <strong>{{ getOrderStatusLabel(order.status) }}</strong>. Bạn không thể thay đổi tiến trình nữa.
              </div>
              
              <!-- ĐÃ FIX: Cho phép sửa khi đã Completed -->
              <div v-if="!['cancelled', 'returned'].includes(order.status)">
                <div v-if="order.status === 'completed'" class="alert alert-success border-0 p-3 rounded-3 shadow-sm mb-3">
                  <i class="bi bi-check-circle-fill me-1"></i> Đơn hàng đã giao thành công. Bạn vẫn có thể đối soát cập nhật trạng thái thanh toán bên dưới.
                </div>

                <!-- Form thay đổi trạng thái -->
                <form @submit.prevent="updateOrderStatus">
                  <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Đổi trạng thái đơn <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg fw-bold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" :class="getOrderStatusClass(formStatus.status)" v-model="formStatus.status" @change="onStatusChange">
                      <option v-for="st in getValidNextStatuses(order.status)" :key="st.value" :value="st.value" class="text-dark bg-white fw-bold">{{ st.label }}</option>
                    </select>
                    <small class="text-urban d-block mt-1 fst-italic"><i class="bi bi-info-circle me-1"></i>Hệ thống tự động ẩn các hướng đi không hợp lệ.</small>
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Đổi trạng thái Tiền <span class="text-danger">*</span></label>
                    <select class="form-select fw-semibold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="formStatus.payment_status">
                      <option value="unpaid">Chưa thanh toán</option>
                      <option value="paid">Đã thanh toán (Thu đủ)</option>
                      <option value="refunded" disabled>Đã hoàn tiền (Refunded)</option>
                    </select>
                  </div>

                  <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Lý do / Lời nhắn cho Khách</label>
                    
                    <div class="mb-2">
                      <div class="d-flex flex-wrap gap-1">
                        <span v-for="(note, idx) in quickNotes" :key="idx" 
                              class="badge bg-light text-dark border border-secondary-subtle cursor-pointer hover-urban-btn shadow-sm transition-all py-2"
                              @click="formStatus.note = note" title="Click để chọn nhanh">
                          {{ note }}
                        </span>
                      </div>
                    </div>

                    <textarea class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="formStatus.note" rows="3" placeholder="Gõ thủ công hoặc chọn các gợi ý bên trên..."></textarea>
                  </div>

                  <button type="submit" class="btn btn-urban btn-lg text-white w-100 fw-bold shadow-sm rounded-pill hover-transform" :disabled="isSavingStatus">
                    <span v-if="isSavingStatus" class="spinner-border spinner-border-sm me-2"></span> CHỐT TRẠNG THÁI
                  </button>
                </form>
              </div>
           </div>

        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL LIVE MAP TRACKING (Leaflet + OSRM)   -->
    <!-- ========================================== -->
    <div class="modal fade glass-modal" id="mapTrackingModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533] overflow-hidden">
          <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3">
            <div class="d-flex align-items-center">
              <div class="bg-urban text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm">
                <i class="bi bi-truck fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark dark:text-white mb-0">Mô phỏng Giao hàng Live</h5>
                <p class="text-muted small mb-0 font-monospace">Lộ trình: <span class="text-urban fw-bold">{{ mapData?.origin?.name }}</span> <i class="bi bi-arrow-right"></i> <span class="text-urban fw-bold">{{ mapData?.destination?.name }}</span></p>
              </div>
            </div>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close" @click="stopAnimation"></button>
          </div>
          <div class="modal-body p-0 position-relative map-container">
            <!-- Nơi render bản đồ Leaflet -->
            <div id="tracking-map" style="height: 60vh; width: 100%; background-color: #e5e5e5; z-index: 1;"></div>
            
            <!-- Overlay Loading -->
            <div v-if="isMapLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-75 dark:bg-[#1a2533] dark:bg-opacity-75" style="z-index: 10;">
                <div class="spinner-border text-urban mb-2" style="width: 3rem; height: 3rem;"></div>
                <div class="fw-bold text-urban">Đang kết nối vệ tinh...</div>
            </div>
          </div>
          <div class="modal-footer border-top-0 bg-light dark:bg-[#212529] p-3 justify-content-between align-items-center">
            <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Hành trình mô phỏng bằng công nghệ OSRM Routing.</div>
            <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal" @click="stopAnimation">Đóng bản đồ</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const route = useRoute();
const router = useRouter();
const orderId = route.params.id;

const isPageLoading = ref(true);
const isSavingShipping = ref(false);
const isSavingStatus = ref(false);

const order = ref({});
const shippingInfoParsed = ref({});
const provinces = ref([]); 

// CUSTOM DROPDOWN
const showProvinceDropdown = ref(false);
const provinceSearchQuery = ref('');
const provinceDropdownRef = ref(null);

const formShipping = ref({ tracking_number: '', shipping_provider: '', origin_city: '' });
const formStatus = ref({ status: '', payment_status: '', note: '' });

// ====== LIVE MAP TRACKING VARIABLES ======
const isMapLoading = ref(false);
const mapData = ref(null);
let leafletMap = null;
let routingLine = null;
let truckMarker = null;
let animationFrameId = null;

const quickNotes = computed(() => {
  const s = formStatus.value.status;
  if (s === 'confirmed' || s === 'processing') return ['Đơn hàng đã được xác nhận và đang đóng gói.', 'Đang điều phối hàng từ kho tổng.'];
  if (s === 'shipping') return ['Đã bàn giao cho đơn vị vận chuyển.', 'Hàng đang trên đường đến bưu cục phát.'];
  if (s === 'completed') return ['Đã giao thành công. Cảm ơn quý khách!', 'Khách đã nhận và kiểm tra hàng hóa.'];
  if (s === 'cancelled') return ['Không liên lạc được với khách hàng.', 'Khách hàng yêu cầu hủy đơn.', 'Hết hàng trong kho, mong quý khách thông cảm.'];
  if (s === 'returned') return ['Khách từ chối nhận hàng.', 'Sai thông tin địa chỉ/SĐT.', 'Hàng bị lỗi trong quá trình vận chuyển.'];
  return ['Vui lòng kiểm tra lại thông tin.', 'Cập nhật hệ thống nội bộ.'];
});

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const handleImageError = (e) => { e.target.src = defaultAvatar; };

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const parseAttributes = (jsonStr) => {
  if(!jsonStr) return {};
  if(typeof jsonStr === 'object') return jsonStr;
  try { return JSON.parse(jsonStr); } catch(e) { return {}; }
};

// UI Helpers
const getOrderStatusClass = (status) => {
  const map = { 'pending': 'bg-warning text-dark border-warning', 'confirmed': 'bg-info text-white border-info', 'processing': 'bg-primary text-white border-primary', 'shipping': 'bg-primary text-white border-primary', 'completed': 'bg-success text-white border-success', 'cancelled': 'bg-danger text-white border-danger', 'returned': 'bg-secondary text-white border-secondary' };
  return map[status] || 'bg-light text-secondary border-secondary';
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'completed': 'Thành công', 'cancelled': 'Đã hủy', 'returned': 'Đã hoàn trả' };
  return map[status] || status;
};

const getOrderStatusIcon = (status) => {
  const map = { 'pending': 'bi-hourglass-split', 'confirmed': 'bi-check2-circle', 'processing': 'bi-box-seam', 'shipping': 'bi-truck', 'completed': 'bi-check-circle-fill', 'cancelled': 'bi-x-circle-fill', 'returned': 'bi-arrow-return-left' };
  return map[status] || 'bi-record-circle';
};

const getTrackingIcon = (status) => {
  const map = { 'pending': 'bi-cart-check', 'confirmed': 'bi-box-seam', 'shipping': 'bi-truck', 'completed': 'bi-house-heart', 'cancelled': 'bi-x-circle', 'returned': 'bi-arrow-return-left' };
  return map[status] || 'bi-geo-alt';
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

const getValidNextStatuses = (currentStatus) => {
  const all = [
    { value: 'pending', label: 'Chờ xác nhận' }, { value: 'confirmed', label: 'Đã xác nhận' },
    { value: 'processing', label: 'Đang xử lý/chuẩn bị' }, { value: 'shipping', label: 'Bắt đầu Giao hàng' },
    { value: 'completed', label: 'Giao Thành công' }, { value: 'cancelled', label: 'Khách Hủy đơn' },
    { value: 'returned', label: 'Giao thất bại (Hoàn trả)' }
  ];
  const rules = { 'pending': ['pending', 'confirmed', 'cancelled'], 'confirmed': ['confirmed', 'processing', 'shipping', 'cancelled'], 'processing': ['processing', 'shipping', 'cancelled'], 'shipping': ['shipping', 'completed', 'returned'], 'completed': ['completed'], 'cancelled': ['cancelled'], 'returned': ['returned'] };
  return all.filter(s => (rules[currentStatus] || [currentStatus]).includes(s.value));
};

const removeAccents = (str) => {
  if (!str) return '';
  return str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
};

const filteredProvinces = computed(() => {
  if (!provinceSearchQuery.value) return provinces.value;
  const queryWords = removeAccents(provinceSearchQuery.value).split(' ').filter(Boolean);
  return provinces.value.filter(p => queryWords.every(word => removeAccents(p.name).includes(word)));
});

const fetchProvinces = async () => {
  try { const res = await axios.get('https://provinces.open-api.vn/api/p/'); provinces.value = res.data; } 
  catch (err) { console.error(err); }
};

// ĐÃ FIX 2: Thêm cờ isSilent để khi chọn địa chỉ lưu ngầm thì không bị nháy Shimmer toàn trang
const fetchData = async (isSilent = false) => {
  if (!isSilent) isPageLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}`, { headers: getHeaders() });
    order.value = res.data.data;
    shippingInfoParsed.value = typeof order.value.shipping_info === 'string' ? JSON.parse(order.value.shipping_info) : order.value.shipping_info;

    formShipping.value.tracking_number = order.value.tracking_number || '';
    formShipping.value.shipping_provider = order.value.shipping_provider || '';
    formShipping.value.origin_city = shippingInfoParsed.value?.origin_city || localStorage.getItem('default_origin_city') || 'Thành phố Hà Nội';
    
    // Đồng bộ lại text ô search
    provinceSearchQuery.value = formShipping.value.origin_city;

    formStatus.value.status = order.value.status;
    formStatus.value.payment_status = order.value.payment_status;
    formStatus.value.note = '';
  } catch (e) {
    if (!isSilent) {
       Swal.fire('Lỗi', 'Không tìm thấy đơn hàng', 'error');
       router.push({ name: 'admin-orders' });
    }
  } finally { 
    if (!isSilent) isPageLoading.value = false; 
  }
};

const selectProvince = (name) => {
  formShipping.value.origin_city = name;
  provinceSearchQuery.value = name;
  showProvinceDropdown.value = false;
  onOriginCityChange();
};

const onOriginCityChange = () => {
  if (formShipping.value.origin_city) localStorage.setItem('default_origin_city', formShipping.value.origin_city);
  updateShippingInfo(); 
};

const updateShippingInfo = async () => {
  isSavingShipping.value = true;
  const payload = { ...formShipping.value, shipping_info: { ...shippingInfoParsed.value, origin_city: formShipping.value.origin_city } };
  try {
    await axios.put(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}`, payload, { headers: getHeaders() });
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu cấu hình Vận đơn', showConfirmButton: false, timer: 1500 });
    await fetchData(true); // Chỉ cập nhật ngầm, không nháy trang
  } catch(e) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Lỗi lưu thông tin', showConfirmButton: false, timer: 1500 });
  } finally { isSavingShipping.value = false; }
};

// ĐÃ FIX LỖI 1: Bổ sung Auto gạt nút Thanh toán khi hoàn tất đơn COD
const onStatusChange = () => {
  if (formStatus.value.status === 'completed' && formStatus.value.payment_status === 'unpaid') {
      formStatus.value.payment_status = 'paid';
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Tự động chuyển: Đã thanh toán', showConfirmButton: false, timer: 3000 });
  }
};

const updateOrderStatus = async () => {
  if (['cancelled', 'returned'].includes(formStatus.value.status) && formStatus.value.status !== order.value.status) {
      const confirm = await Swal.fire({ title: 'Cảnh báo Bảo mật Kho', text: `Kho hàng của các sản phẩm sẽ tự động được cộng lại. Bạn chắc chắn chứ?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý' });
      if(!confirm.isConfirmed) return;
  }
  if (formStatus.value.status === 'shipping' && order.value.status !== 'shipping') {
      const confirmShip = await Swal.fire({ title: 'Xác nhận Bắt đầu Giao Hàng?', html: `Xuất kho từ:<br><b class="text-urban fs-5">${formShipping.value.origin_city || 'Chưa xác định'}</b><br>Đúng chưa sếp?`, icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover)', confirmButtonText: 'Giao hàng!' });
      if(!confirmShip.isConfirmed) return;
  }

  isSavingStatus.value = true;
  try {
    await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}/status`, formStatus.value, { headers: getHeaders() });
    
    // ĐÃ THÊM: Auto bật Map mô phỏng khi chốt đơn Giao thành công
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật trạng thái đơn hàng & Email đã được gửi!', timer: 2000, showConfirmButton: false }).then(() => {
        if (formStatus.value.status === 'completed') {
            openMapSimulation();
        }
    });

    await fetchData(true); // Load ngầm không nháy trang
  } catch(e) {
    Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xử lý trạng thái', 'error');
    formStatus.value.status = order.value.status;
  } finally { isSavingStatus.value = false; }
};

const handleClickOutside = (event) => {
  if (provinceDropdownRef.value && !provinceDropdownRef.value.contains(event.target)) {
    showProvinceDropdown.value = false;
    if (provinceSearchQuery.value !== formShipping.value.origin_city) provinceSearchQuery.value = formShipping.value.origin_city;
  }
};

// ==========================================
// LOGIC BẢN ĐỒ LIVE TRACKING (Leaflet + OSRM)
// ==========================================
const loadLeafletScript = () => {
  return new Promise((resolve) => {
    if (window.L) return resolve();
    const link = document.createElement('link');
    link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => resolve();
    document.head.appendChild(script);
  });
};

const stopAnimation = () => {
   if(animationFrameId) cancelAnimationFrame(animationFrameId);
};

const openMapSimulation = async () => {
  const modalEl = document.getElementById('mapTrackingModal');
  const modal = new window.bootstrap.Modal(modalEl);
  modal.show();

  isMapLoading.value = true;

  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}/simulation`, { headers: getHeaders() });
    mapData.value = res.data.data;
    
    const p1 = [mapData.value.origin.lat, mapData.value.origin.lng];
    const p2 = [mapData.value.destination.lat, mapData.value.destination.lng];

    await loadLeafletScript();
    
    setTimeout(async () => {
      if (!leafletMap) {
        leafletMap = window.L.map('tracking-map').setView(p1, 6);
        window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(leafletMap);
      } else {
        if (routingLine) leafletMap.removeLayer(routingLine);
        if (truckMarker) leafletMap.removeLayer(truckMarker);
      }

      leafletMap.invalidateSize(); 

      const iconA = window.L.divIcon({ html: '<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:30px; height:30px; font-weight:bold; border:2px solid white;">A</div>', className: ''});
      const iconB = window.L.divIcon({ html: '<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:30px; height:30px; font-weight:bold; border:2px solid white;">B</div>', className: ''});
      
      window.L.marker(p1, {icon: iconA}).bindPopup('<b>Kho Xuất Phát:</b><br>' + mapData.value.origin.name).addTo(leafletMap);
      window.L.marker(p2, {icon: iconB}).bindPopup('<b>Nơi Nhận Hàng:</b><br>' + mapData.value.destination.name).addTo(leafletMap);

      const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${p1[1]},${p1[0]};${p2[1]},${p2[0]}?overview=full&geometries=geojson`;
      let routeCoords = [];
      try {
         const osrmRes = await axios.get(osrmUrl);
         routeCoords = osrmRes.data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
      } catch (err) {
         routeCoords = [p1, p2];
      }

      routingLine = window.L.polyline(routeCoords, { color: '#009981', weight: 4, opacity: 0.8, dashArray: '10, 10' }).addTo(leafletMap);
      leafletMap.fitBounds(routingLine.getBounds(), { padding: [50, 50] });

      const truckHtml = `<div class="bg-danger text-white rounded shadow-lg d-flex align-items-center justify-content-center border border-2 border-white" style="width:35px; height:35px;"><i class="bi bi-truck fs-5"></i></div>`;
      const truckDivIcon = window.L.divIcon({ html: truckHtml, className: '', iconSize: [35, 35], iconAnchor: [17, 17] });
      
      // ĐÃ FIX LỖI 2: Logic tốc độ chạy xe (Anim) thông minh hơn, Completed cho chạy xé gió tới đích luôn
      const totalPoints = routeCoords.length;
      let currentIndex = 0; // Luôn bắt đầu từ 0 để sếp xem từ đầu hành trình
      let speed = 0;

      if (mapData.value.status === 'completed' || mapData.value.status === 'returned') {
          speed = Math.ceil(totalPoints / 120) || 3; // Tăng tốc tua nhanh quá khứ
      } else if (mapData.value.status === 'shipping') {
          currentIndex = Math.floor(totalPoints * 0.1); // Đang đi giữa chừng
          speed = Math.ceil(totalPoints / 400) || 1; // Chạy rề rề
      }

      truckMarker = window.L.marker(routeCoords[currentIndex], {icon: truckDivIcon}).addTo(leafletMap);

      isMapLoading.value = false;

      if (speed > 0 && routeCoords.length > 2) {
          const animate = () => {
             if (currentIndex < totalPoints - 1) {
                 currentIndex += speed;
                 if(currentIndex >= totalPoints) currentIndex = totalPoints - 1;
                 truckMarker.setLatLng(routeCoords[currentIndex]);
                 animationFrameId = requestAnimationFrame(animate);
             } else {
                 // Nếu đang shipping mà đi hết map thì quay lại khúc giữa
                 if (mapData.value.status === 'shipping') {
                     currentIndex = Math.floor(totalPoints * 0.1);
                     animationFrameId = requestAnimationFrame(animate);
                 }
             }
          };
          animate();
      }

    }, 300);

  } catch(e) {
    isMapLoading.value = false;
    Swal.fire('Lỗi', 'Không thể khởi tạo Bản đồ Live: ' + e.message, 'error');
  }
};

onMounted(() => {
  fetchProvinces();
  fetchData();
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  stopAnimation();
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-1px); }

.hover-urban-btn:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }
.cursor-pointer { cursor: pointer; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

/* FIX HOÀN TOÀN LỖI DROPDOWN BỊ TỆP MÀU DARK MODE BẰNG CSS THUẦN */
.custom-dropdown-menu { 
  display: block; max-height: 250px; overflow-y: auto; border-radius: 8px; padding: 0.5rem 0; 
  background-color: #fff; border: 1px solid #dee2e6;
}
.custom-dropdown-item { color: #212529; transition: background-color 0.2s ease, color 0.2s ease; border: none; }
.custom-dropdown-item:hover { background-color: var(--color-c-effect); }

html.dark .custom-dropdown-menu { background-color: #212529; border-color: #373b3e; }
html.dark .custom-dropdown-item { color: #f8f9fa; }
html.dark .custom-dropdown-item:hover { background-color: #2b3035; color: #fff; }

.selected-dropdown-item { background-color: rgba(84, 119, 146, 0.15) !important; color: var(--color-c-hover, #547792) !important; font-weight: bold; }
html.dark .selected-dropdown-item { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; }

/* Timeline CSS */
.tracking-timeline .timeline-item:last-child .timeline-content { border-bottom: none !important; }

/* Map CSS & AUTO DARK MODE */
.glass-modal { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4); }
html.dark #tracking-map { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>