<template>
  <div class="return-edit-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải hồ sơ hoàn trả...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-returns' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0 d-flex align-items-center flex-wrap gap-2 font-sans-vn">
              Xử lý Đơn <span class="text-urban font-monospace">#{{ order.order_code }}</span>
              <span class="badge border px-3 py-1.5 ms-2 fs-6 shadow-sm" :class="getReturnStatusClass(order.return_status)">
                {{ getReturnStatusLabel(order.return_status) }}
              </span>
            </h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1 font-sans-vn"><i class="bi bi-calendar-event me-1"></i>Yêu cầu tạo lúc: {{ formatDateTime(order.updated_at) }}</p>
          </div>
        </div>
      </div>

      <!-- THÔNG TIN YÊU CẦU HOÀN TRẢ TỪ KHÁCH HÀNG -->
      <div class="alert border-0 shadow-sm rounded-4 mb-4 d-flex gap-3 align-items-start dark:bg-[#1a2533]" style="background-color: #fff5f5;">
         <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1 shadow-sm" style="width: 45px; height: 45px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
         </div>
         <div class="w-100 font-sans-vn">
             <h5 class="fw-bold mb-2 text-danger">Chi Tiết Yêu Cầu Từ Khách Hàng</h5>
             <div class="bg-white dark:bg-[#121416] p-3 rounded-3 border border-danger border-opacity-10 mb-2">
                <p class="mb-0 fs-6 text-dark dark:text-gray-200 fw-bold">{{ getReturnReason(order) }}</p>
             </div>
             <div class="small text-dark dark:text-gray-400">
                <i class="bi bi-info-circle-fill me-1 text-danger"></i> <b>Lưu ý nghiệp vụ:</b> ZYRO xử lý hoàn trả trên toàn bộ đơn. Kế toán cần đối soát sản phẩm thực tế nhận lại trước khi bấm "Xác nhận đã Bank tiền".
             </div>
         </div>
      </div>

      <div class="row g-4">
        <!-- CỘT TRÁI: SẢN PHẨM & TÀI CHÍNH GỐC -->
        <div class="col-xl-8 col-lg-7">
          
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
            <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2 font-sans-vn"><i class="bi bi-bag-check-fill me-2"></i>Sản phẩm thuộc đơn ({{ order.items?.length || 0 }})</h6>
            
            <div class="bg-white dark:bg-[#212529] rounded-3 border dark:border-gray-700 overflow-hidden opacity-75 pe-none">
               <template v-for="(group, gIdx) in cartGroups(order.items)" :key="'grp'+gIdx">

                  <!-- 1. HIỂN THỊ GÓI COMBO -->
                  <div v-if="group.isLookbook" class="p-3 p-md-4" :class="{'border-top dark:border-gray-700': gIdx > 0}">
                     <div class="d-flex align-items-start gap-3">
                        <div class="position-relative flex-shrink-0" style="width: 80px; height: 100px;">
                          <img :src="getImageUrl(group.lookbook_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                        </div>
                        
                        <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 py-1">
                           <div class="row w-100 m-0">
                              <div class="col-8 col-sm-9 p-0 pe-2">
                                 <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2" style="font-size: 1rem; line-height: 1.3;">
                                    {{ group.lookbook_name }}
                                 </h6>
                                 <span class="d-inline-block bg-light dark:bg-[#2b3035] text-muted dark:text-gray-400 border dark:border-gray-600 px-2 py-1 rounded-2 fw-medium mb-2 font-sans-vn" style="font-size: 0.75rem;">
                                    <i class="bi bi-magic me-1"></i> Combo Set {{ group.items.length }} món
                                 </span>
                                 <div class="mt-1">
                                    <span class="text-urban small fw-bold cursor-pointer hover-text-dark transition-color font-sans-vn" @click.stop="toggleGroup(group.lookbook_id)">
                                       {{ isGroupExpanded(group.lookbook_id) ? 'Thu gọn chi tiết' : 'Xem chi tiết combo' }} 
                                       <i class="bi ms-1" :class="isGroupExpanded(group.lookbook_id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    </span>
                                 </div>
                              </div>
                              
                              <div class="col-4 col-sm-3 p-0 text-end d-flex flex-column justify-content-between">
                                 <div>
                                    <div class="text-muted small mb-1 font-sans-vn" style="font-size: 0.75rem;">Giá thanh toán:</div>
                                    <div class="fw-bold text-danger fs-6 font-sans-vn">{{ formatCurrency(group.totalPrice) }}</div>
                                 </div>
                                 <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ group.comboQuantity }}</div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- CHI TIẾT CÁC MÓN BÊN TRONG COMBO (Xổ xuống) -->
                     <div v-show="isGroupExpanded(group.lookbook_id)" class="mt-4 pt-3 border-top border-dashed ps-2" @click.stop>
                        <div class="d-flex flex-column">
                           <div v-for="(item, idx) in group.items" :key="'cb_item_'+item.id" class="d-flex align-items-center gap-3 mb-3 last-no-border">
                              <img :src="getImageUrl(item.variant_image)" style="width: 45px; height: 60px;" class="rounded-2 border dark:border-gray-600 object-fit-cover bg-light" @error="e => e.target.src='/client_placeholder.png'">
                              <div class="flex-grow-1">
                                 <div class="fw-bold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn" style="font-size: 0.85rem;">{{ item.product_name }}</div>
                                 <div class="text-muted d-flex justify-content-between pe-1 mt-1 font-sans-vn" style="font-size: 0.8rem;">
                                    <span>
                                       Mã: <span class="text-dark dark:text-gray-300">{{ item.variant_sku }}</span> <span class="mx-2 opacity-50">|</span> 
                                       PL: <span class="text-dark dark:text-gray-300">{{ parseAttributes(item.variant_attributes) }}</span> <span class="mx-2 opacity-50">|</span> 
                                       <span class="fw-semibold text-dark dark:text-gray-300">{{ formatCurrency(item.purchased_price) }}</span>
                                    </span>
                                    <span class="text-muted fw-bold">x{{ item.quantity }}</span>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- 2. SẢN PHẨM MUA LẺ -->
                  <template v-else>
                     <div v-for="(item, idx) in group.items" :key="item.id" 
                          class="d-flex p-3 p-md-4 gap-3 position-relative"
                          :class="{'border-top dark:border-gray-700': gIdx > 0 || idx > 0}">
                        
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-c-effect opacity-25 dark:bg-[#121416] pe-none z-0"></div>

                        <div class="position-relative z-1" style="width: 80px; height: 100px; flex-shrink: 0;">
                          <img :src="getImageUrl(item.variant_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                        </div>
                        
                        <div class="flex-grow-1 position-relative z-1 d-flex flex-column justify-content-between py-1">
                          <div class="row w-100 m-0">
                             <div class="col-8 col-sm-9 p-0 pe-2">
                                <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2 font-sans-vn" style="font-size: 0.95rem; line-height: 1.3;">{{ item.product_name }}</h6>
                                <span class="d-inline-block bg-light dark:bg-[#2b3035] text-muted dark:text-gray-400 border dark:border-gray-600 px-2 py-1 rounded-2 fw-medium font-sans-vn" style="font-size: 0.75rem;">
                                   Mã: {{ item.variant_sku }} | Phân loại: {{ parseAttributes(item.variant_attributes) }}
                                </span>
                             </div>
                             <div class="col-4 col-sm-3 p-0 text-end d-flex flex-column justify-content-between">
                                <div>
                                   <div class="text-muted small font-sans-vn" style="font-size: 0.75rem;">Giá thanh toán:</div>
                                   <div class="fw-bold text-danger fs-6 font-sans-vn">{{ formatCurrency(item.purchased_price) }}</div>
                                </div>
                                <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ item.quantity }}</div>
                             </div>
                          </div>
                        </div>
                     </div>
                  </template>
               </template>
            </div>

            <!-- Tổng kết Tài chính -->
            <div class="row mt-4">
              <div class="col-md-6 offset-md-6 col-xl-5 offset-xl-7">
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 font-sans-vn">
                  <div class="d-flex justify-content-between mb-2 text-success">
                    <span class="small fw-semibold">Khuyến mãi đã áp:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.discount_amount) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Khách đã thanh toán:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                  <hr class="dark:border-gray-600 my-2">
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-uppercase fw-bold text-dark dark:text-white">GIÁ TRỊ HOÀN TỐI ĐA</span>
                    <span class="text-danger fw-bold fs-4">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Lịch sử Xử lý & BẢN ĐỒ -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 animation-fade-in">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom dark:border-gray-700 pb-2">
                <h6 class="fw-bold text-muted small text-uppercase mb-0 font-sans-vn"><i class="bi bi-clock-history me-1"></i>Lịch sử Xử lý & Giao vận</h6>
                
                <!-- NÚT MỞ BẢN ĐỒ LIVE TRACKING ĐÃ FIX -->
                <button type="button" class="btn btn-sm btn-urban text-white fw-bold rounded-pill shadow-sm d-flex align-items-center transition-all hover-transform font-sans-vn px-3" @click="openMapSimulation">
                   <i class="bi bi-map-fill me-2"></i> Xem Lộ Trình Thu Hồi
                </button>
            </div>
            
            <ul class="list-group list-group-flush rounded-3 border dark:border-gray-700 custom-scrollbar-y mb-4" style="max-height: 250px; overflow-y: auto;">
               <li v-for="his in order.histories" :key="his.id" class="list-group-item bg-transparent dark:border-gray-700 p-3">
                 <div class="d-flex justify-content-between align-items-start">
                   <div>
                     <div class="fw-bold text-dark dark:text-gray-200 small mb-1 font-sans-vn">
                        Cập nhật: <span class="text-secondary text-decoration-line-through">{{ getOrderStatusLabel(his.old_status) }}</span> <i class="bi bi-arrow-right mx-1 text-muted"></i> <span class="text-urban">{{ getOrderStatusLabel(his.new_status) }}</span>
                     </div>
                     <div class="text-muted fst-italic font-sans-vn" style="font-size: 0.8rem;">Note: "{{ his.note || 'Không có ghi chú' }}"</div>
                   </div>
                   <div class="text-end ms-2">
                     <span class="badge bg-secondary bg-opacity-10 text-secondary border mb-1 font-sans-vn"><i class="bi bi-person-badge"></i> {{ his.changer?.fullname || 'Khách/Hệ thống' }}</span>
                     <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ formatDateTime(his.created_at) }}</div>
                   </div>
                 </div>
               </li>
            </ul>
          </div>
        </div>

        <!-- CỘT PHẢI: QUYẾT ĐỊNH KẾ TOÁN (FORM PHẢN HỒI NHANH) -->
        <div class="col-xl-4 col-lg-5">
           
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2 font-sans-vn"><i class="bi bi-person-lines-fill me-2"></i>Tài khoản yêu cầu</h6>
              <div class="d-flex align-items-center">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center me-3 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 45px; height: 45px;">
                   <i class="bi bi-person-fill text-muted fs-4"></i>
                </div>
                <div class="overflow-hidden">
                  <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate font-sans-vn">{{ order.user?.full_name || shippingInfoParsed?.name || 'Khách vãng lai' }}</h6>
                  <small class="text-muted dark:text-gray-400 d-block mt-1 font-monospace"><i class="bi bi-telephone me-1"></i>{{ shippingInfoParsed?.phone || 'N/A' }}</small>
                </div>
              </div>
           </div>

           <!-- FORM XỬ LÝ HOÀN TIỀN -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top font-sans-vn" style="top: 20px;">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-cash-stack me-2"></i>Quyết định Kế toán</h6>
              
              <div v-if="order.return_status === 'approved'" class="alert alert-success border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-check-circle-fill me-1"></i> Đơn hàng này đã được <strong>Hoàn tiền thành công</strong>. Luồng xử lý RMA đã khép lại.
              </div>
              <div v-else-if="order.return_status === 'rejected'" class="alert alert-danger border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-x-circle-fill me-1"></i> Yêu cầu này đã bị <strong>Từ chối hoàn trả</strong>. 
              </div>

              <!-- Form thay đổi trạng thái -->
              <form @submit.prevent="submitRefund" v-else>
                <div class="mb-4">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Hành động <span class="text-danger">*</span></label>
                  <select class="form-select form-select-lg fw-bold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="refundForm.action" :class="getRefundActionClass(refundForm.action)" @change="onActionChange">
                    <option value="propose">Đề xuất (Đang thỏa thuận)</option>
                    <option value="reject">Từ chối hoàn trả</option>
                    <option value="refunded">Xác nhận đã Bank tiền (Chốt sổ)</option>
                  </select>
                </div>

                <div class="mb-4" v-if="refundForm.action !== 'reject'">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Số tiền hoàn (VNĐ) <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input type="number" class="form-control form-control-lg fw-bold text-danger dark:bg-[#212529] dark:text-white border-0" v-model.number="refundForm.refund_amount" min="0" :max="order.total_amount" required>
                    <span class="input-group-text bg-white dark:bg-[#212529] border-0 text-muted">₫</span>
                  </div>
                  <small class="text-danger fw-bold d-block mt-1">{{ formatCurrency(refundForm.refund_amount) }}</small>
                </div>

                <!-- ======================================================== -->
                <!-- CHECKLIST PHẢN HỒI NHANH (MẶC ĐỊNH CHỌN) -->
                <!-- ======================================================== -->
                <div class="mb-4">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Chọn nhanh nội dung phản hồi</label>
                  <div class="d-flex flex-column gap-2 mb-3">
                     <div v-for="(note, idx) in currentTemplates" :key="idx" class="form-check custom-checklist-item p-0">
                        <input class="form-check-input d-none" type="checkbox" :id="'note-'+idx" :value="note" v-model="selectedNotes">
                        <label class="form-check-label w-100 p-2 rounded-3 border transition-all cursor-pointer small fw-medium font-sans-vn" 
                               :for="'note-'+idx" 
                               :class="selectedNotes.includes(note) ? 'bg-urban text-white border-urban shadow-sm' : 'bg-light dark:bg-[#212529] text-muted dark:text-gray-400 border-light-subtle dark:border-gray-700 hover-bg-effect'">
                           <i class="bi me-2" :class="selectedNotes.includes(note) ? 'bi-check-circle-fill' : 'bi-circle'"></i>
                           {{ note }}
                        </label>
                     </div>
                     
                     <div class="form-check custom-checklist-item p-0">
                        <input class="form-check-input d-none" type="checkbox" id="note-custom" v-model="isCustomNote">
                        <label class="form-check-label w-100 p-2 rounded-3 border transition-all cursor-pointer small fw-bold font-sans-vn" 
                               for="note-custom"
                               :class="isCustomNote ? 'bg-dark text-white border-dark shadow-sm' : 'bg-light dark:bg-[#212529] text-muted dark:text-gray-400 border-light-subtle dark:border-gray-700 hover-bg-effect'">
                           <i class="bi me-2" :class="isCustomNote ? 'bi-pencil-square' : 'bi-plus-circle'"></i>
                           Nội dung khác / Ghi chú tay...
                        </label>
                     </div>
                  </div>

                  <transition name="slide-fade">
                    <div v-if="isCustomNote" class="mt-2">
                       <label class="form-label small fw-bold text-muted text-uppercase mb-1">Mô tả chi tiết bổ sung</label>
                       <textarea class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                                 v-model="customTextNote" rows="3" 
                                 placeholder="Nhập thông tin chi tiết dành riêng cho khách hàng này..."></textarea>
                    </div>
                  </transition>
                </div>

                <button type="submit" class="btn btn-urban btn-lg text-white w-100 fw-bold shadow-sm rounded-pill transition-all hover-transform" :disabled="isSavingRefund">
                  <span v-if="isSavingRefund" class="spinner-border spinner-border-sm me-2"></span> CHỐT PHIẾU RMA
                </button>
              </form>
           </div>
        </div>
      </div>
    </div>

    <!-- MODAL LIVE MAP TRACKING (ĐÃ ĐẢO NGƯỢC HƯỚNG RMA) -->
    <div class="modal fade glass-modal" id="mapTrackingModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533] overflow-hidden">
          <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3">
            <div class="d-flex align-items-center font-sans-vn">
              <div class="bg-urban text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm">
                <i class="bi bi-truck fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark dark:text-white mb-0">Lộ Trình Thu Hồi Giao Vận (RMA)</h5>
                <p class="text-muted small mb-0 font-monospace">Lộ trình: Khách gửi <span class="text-urban fw-bold">{{ mapData?.origin?.name }}</span> <i class="bi bi-arrow-right"></i> Về Kho <span class="text-urban fw-bold text-success">{{ mapData?.destination?.name }}</span></p>
              </div>
            </div>
            <button type="button" class="btn-close dark:filter-invert" data-bs-dismiss="modal" aria-label="Close" @click="closeMapSimulation"></button>
          </div>
          
          <div class="modal-body p-0 position-relative" style="height: 60vh;">
             <!-- GỌI COMPONENT TRACKING TỪ CANVAS -->
             <TrackingMap v-if="isMapOpen && mapData" :map-data="mapData" :status="order.status" />
          </div>
          
          <div class="modal-footer border-top-0 bg-light dark:bg-[#212529] p-3 justify-content-between align-items-center font-sans-vn">
            <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Hành trình mô phỏng dựa trên dữ liệu Mapbox Navigation.</div>
            <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal" @click="closeMapSimulation">Đóng bản đồ</button>
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
import TrackingMap from '@/components/shared/TrackingMap.vue';

const route = useRoute();
const router = useRouter();
const orderId = route.params.id;

const isPageLoading = ref(true);
const isSavingRefund = ref(false);

const order = ref({});
const shippingInfoParsed = ref({});

const refundForm = ref({ action: 'propose', refund_amount: 0, refund_note: '' });

// ====== LOGIC QUICK NOTES DẠNG CHECKLIST ======
const selectedNotes = ref([]);
const isCustomNote = ref(false);
const customTextNote = ref('');

const quickTemplates = {
  propose: [
    'Sản phẩm đã về kho, đang trong quá trình đối soát lỗi sản xuất.',
    'ZYRO đề xuất hỗ trợ đổi trả sản phẩm mới do lỗi từ nhà cung cấp.',
    'Vui lòng cung cấp thêm hình ảnh khui hàng rõ nét để Kế toán phê duyệt.',
    'Đề xuất hoàn lại 80% giá trị do sản phẩm đã bị bóc tem mác.'
  ],
  reject: [
    'Sản phẩm bị bẩn/hỏng do tác động vật lý từ khách hàng, không đủ điều kiện hoàn.',
    'Thời gian yêu cầu hoàn trả đã quá hạn quy định (7 ngày kể từ khi nhận hàng).',
    'Sản phẩm gửi về không phải là hàng của ZYRO hoặc không trùng mã vận đơn.',
    'Lý do đổi trả không phù hợp với chính sách bảo hành hiện tại.'
  ],
  refunded: [
    'Đã thực hiện chuyển khoản hoàn tiền thành công qua số tài khoản khách hàng.',
    'Giao dịch hoàn trả đã hoàn tất. Cảm ơn quý khách đã tin tưởng ZYRO.',
    'Đã tất toán phí đơn hàng vào ví voucher của tài khoản khách hàng.'
  ]
};

const currentTemplates = computed(() => quickTemplates[refundForm.value.action] || []);

const onActionChange = () => {
    selectedNotes.value = currentTemplates.value.length > 0 ? [currentTemplates.value[0]] : [];
    isCustomNote.value = false;
    customTextNote.value = '';
};

const mapData = ref(null);
const isMapOpen = ref(false);

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

const getReturnReason = (orderObj) => {
  if (!orderObj || !orderObj.histories) return 'Khách hàng yêu cầu đổi trả.';
  const req = orderObj.histories.find(h => h.note && h.note.includes('KHÁCH HÀNG YÊU CẦU HOÀN TRẢ'));
  if (req) return req.note.replace('KHÁCH HÀNG YÊU CẦU HOÀN TRẢ:', '').trim();
  return 'Lý do: Không rõ (Khách hàng không nhập chi tiết).';
};

const expandedGroups = ref([]);

const groupOrderItems = (items) => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };
  if (!items) return result;

  items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        let lbName = item.lookbook ? item.lookbook.name : 'Combo / Set Đồ';
        let lbImage = item.lookbook ? item.lookbook.main_image : item.variant_image;

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

  if (normalGroup.items.length > 0) result.push(normalGroup);
  return result;
};

const cartGroups = (items) => groupOrderItems(items);
const toggleGroup = (lookbookId) => {
  if (expandedGroups.value.includes(lookbookId)) {
    expandedGroups.value = expandedGroups.value.filter(k => k !== lookbookId);
  } else {
    expandedGroups.value.push(lookbookId);
  }
};
const isGroupExpanded = (lookbookId) => expandedGroups.value.includes(lookbookId);

const getReturnStatusClass = (status) => {
    if (status === 'approved') return 'bg-success bg-opacity-10 text-success border-success';
    if (status === 'pending') return 'bg-warning bg-opacity-10 text-warning border-warning';
    if (status === 'proposing') return 'bg-info bg-opacity-10 text-info border-info';
    if (status === 'rejected') return 'bg-danger bg-opacity-10 text-danger border-danger';
    return 'bg-secondary bg-opacity-10 text-secondary border-secondary';
};

const getReturnStatusLabel = (status) => {
    if (status === 'approved') return 'Đã hoàn tiền';
    if (status === 'pending') return 'Chờ Kế toán xử lý';
    if (status === 'proposing') return 'Đang thỏa thuận';
    if (status === 'rejected') return 'Đã từ chối hoàn';
    return 'Đang xử lý';
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'completed': 'Thành công', 'cancelled': 'Đã hủy', 'returned': 'Đã hoàn trả/Từ chối nhận' };
  return map[status] || status;
};

const getRefundActionClass = (action) => {
  if (action === 'propose') return 'text-info';
  if (action === 'reject') return 'text-danger';
  return 'text-success';
};

const getCoordinatesForCity = (cityName) => {
  if(!cityName) return [21.0285, 105.8542]; 
  const city = cityName.toLowerCase();
  const coordsMap = {
    'hà nội': [21.0285, 105.8542], 'hồ chí minh': [10.8231, 106.6297], 'đà nẵng': [16.0471, 108.2068],
    'hải phòng': [20.8449, 106.6881], 'cần thơ': [10.0452, 105.7469], 'đắk lắk': [12.6667, 108.0383]
  };
  for (const key in coordsMap) { if (city.includes(key)) return coordsMap[key]; }
  return [21.0285, 105.8542];
};

const openMapSimulation = () => {
  const modalEl = document.getElementById('mapTrackingModal');
  const modal = new window.bootstrap.Modal(modalEl);
  
  const info = shippingInfoParsed.value || {};
  // ĐÃ FIX: Lấy Kho xuất phát từ LocalStorage để đồng bộ với tính năng cập nhật Vận đơn, hết hard-code.
  const warehouseCity = info.origin_city || localStorage.getItem('default_origin_city') || 'Hà Nội';
  const customerCity = info.city || 'Hồ Chí Minh';
  
  // ĐÃ ĐẢO CHIỀU TỌA ĐỘ: Đơn Hoàn Trả (RMA) đi từ Khách Hàng (Origin) về lại Kho (Destination)
  mapData.value = {
     origin: { name: customerCity, coords: getCoordinatesForCity(customerCity) },
     destination: { name: warehouseCity, coords: getCoordinatesForCity(warehouseCity) }
  };
  
  isMapOpen.value = true;
  modal.show();
  
  modalEl.addEventListener('hidden.bs.modal', () => { isMapOpen.value = false; }, { once: true });
};

const closeMapSimulation = () => { isMapOpen.value = false; };

const fetchData = async () => {
  isPageLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}`, { headers: getHeaders() });
    order.value = res.data.data;
    shippingInfoParsed.value = typeof order.value.shipping_info === 'string' ? JSON.parse(order.value.shipping_info) : order.value.shipping_info;
    
    refundForm.value.action = 'propose';
    refundForm.value.refund_amount = order.value.refunded_amount !== null ? order.value.refunded_amount : order.value.total_amount;
    
    selectedNotes.value = currentTemplates.value.length > 0 ? [currentTemplates.value[0]] : [];
  } catch (e) {
    Swal.fire('Lỗi', 'Không tìm thấy hồ sơ RMA', 'error');
    router.push({ name: 'admin-returns' });
  } finally { isPageLoading.value = false; }
};

const submitRefund = async () => {
  let finalNote = selectedNotes.value.join(' | ');
  if (isCustomNote.value && customTextNote.value.trim()) {
      finalNote = finalNote ? `${finalNote}. Ghi chú thêm: ${customTextNote.value}` : customTextNote.value;
  }

  if (!finalNote && refundForm.value.action !== 'refunded') {
      Swal.fire('Chú ý', 'Vui lòng chọn hoặc nhập nội dung phản hồi cho khách hàng.', 'warning');
      return;
  }

  const payload = {
      ...refundForm.value,
      refund_note: finalNote
  };

  if (payload.action === 'refunded') {
      const confirm = await Swal.fire({
          title: 'Xác nhận Chốt sổ RMA?',
          text: `Hệ thống sẽ ghi nhận bạn đã chuyển khoản ${formatCurrency(payload.refund_amount)} thành công cho khách hàng.`,
          icon: 'warning', showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Đã hoàn tiền'
      });
      if(!confirm.isConfirmed) return;
  }

  isSavingRefund.value = true;
  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}/refund`, payload, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã cập nhật quyết định và gửi thông báo tới khách hàng.', timer: 2000, showConfirmButton: false });
    await fetchData();
  } catch(e) {
    Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xử lý Kế toán', 'error');
  } finally { isSavingRefund.value = false; }
};

onMounted(() => { fetchData(); });
onBeforeUnmount(() => {
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = ''; document.body.style = '';
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-1px); }

.hover-bg-effect:hover { background-color: rgba(84, 119, 146, 0.05); }
.cursor-pointer { cursor: pointer; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important; }

/* CSS Checklist */
.custom-checklist-item label { border: 1px solid transparent; transition: 0.2s; }
.custom-checklist-item label:hover { border-color: var(--color-c-hover); }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.last-no-border:last-child { border-bottom: none !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }

.glass-modal { backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4); }
</style>