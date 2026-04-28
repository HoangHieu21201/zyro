<template>
  <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533]">
        
        <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3 p-md-4">
          <h5 class="fw-bold text-dark dark:text-white mb-0 fs-6 fs-md-5 font-sans-vn">
            <i class="bi bi-receipt text-urban me-2"></i>Chi Tiết Đơn Hàng 
            <span v-if="selectedOrder" class="font-monospace text-urban ms-1">#{{ selectedOrder.order_code }}</span>
          </h5>
          <button type="button" class="btn-close dark:filter dark:invert" @click="closeModal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body p-3 p-md-4 custom-scrollbar-y bg-light dark:bg-[#121416]">
           
           <div v-if="isModalLoading" class="text-center py-5">
             <div class="spinner-border text-urban" role="status"></div>
             <div class="mt-2 text-muted font-sans-vn">Đang tải dữ liệu...</div>
           </div>

           <div v-else-if="selectedOrder">
             
              <!-- STEPPER -->
              <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700 mb-3">
                 <h6 class="fw-bold text-dark dark:text-white mb-3 font-sans-vn"><i class="bi bi-truck text-urban me-2"></i>Trạng thái đơn hàng</h6>
                 
                 <div v-if="selectedOrder.status === 'cancelled'" class="alert alert-danger mb-0 d-flex align-items-center font-sans-vn">
                    <i class="bi bi-x-circle-fill fs-4 me-3"></i> 
                    <div>
                      <strong>Đơn hàng đã bị hủy.</strong><br>
                      Lý do: {{ getCancelReason(selectedOrder) }}
                    </div>
                 </div>

                 <div v-else class="stepper-wrapper">
                    <div class="stepper-item" :class="{'completed': stepLevel >= 1}">
                      <div class="step-counter"><i class="bi bi-box-seam"></i></div>
                      <div class="step-name font-sans-vn">Đã đặt</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 2}">
                      <div class="step-counter"><i class="bi bi-clipboard-check"></i></div>
                      <div class="step-name font-sans-vn">Đã xác nhận</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 3}">
                      <div class="step-counter"><i class="bi bi-truck"></i></div>
                      <div class="step-name font-sans-vn">Đang giao</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 4}">
                      <div class="step-counter"><i class="bi bi-check-lg"></i></div>
                      <div class="step-name font-sans-vn">Thành công</div>
                    </div>
                 </div>
              </div>

              <div class="row g-3 mb-3">
                <!-- LIVE MAP DÙNG COMPONENT CHUNG -->
                <div class="col-12" v-if="stepLevel >= 3 && mapData">
                   <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700">
                      <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                         <h6 class="fw-bold text-dark dark:text-white m-0 font-sans-vn"><i class="bi bi-map text-urban me-2"></i>Hành trình đơn hàng</h6>
                         <span class="badge bg-urban font-sans-vn">{{ mapData.shipping_provider || 'Đơn vị Vận Chuyển' }}</span>
                      </div>
                      
                      <!-- GỌI COMPONENT TRACKING MAP Ở ĐÂY -->
                      <div class="rounded-3 border overflow-hidden" style="height: 350px;">
                          <TrackingMap :map-data="mapData" :status="selectedOrder.status" />
                      </div>

                      <div class="small text-muted mt-2 px-2 fst-italic text-end font-sans-vn"><i class="bi bi-info-circle me-1"></i>Mô phỏng đường đi bằng Mapbox Navigation.</div>
                   </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 bg-white dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 shadow-sm h-100 font-sans-vn">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Địa chỉ nhận hàng</h6>
                    <div class="fw-bold text-dark dark:text-gray-200 fs-5 mb-1">{{ selectedOrder.shipping_info?.name || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small mt-2"><i class="bi bi-telephone-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.phone || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small mt-2 lh-lg"><i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.address || 'Chưa cập nhật' }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 bg-white dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 shadow-sm h-100 font-sans-vn">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Thông tin thanh toán</h6>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Phương thức:</span>
                      <span class="fw-bold text-dark dark:text-white text-uppercase">{{ selectedOrder.payment_method || 'COD' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Tình trạng:</span>
                      <span class="badge border px-2 py-1 shadow-sm" :class="getPaymentStatusClass(selectedOrder.payment_status)">
                        {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
                      </span>
                    </div>
                    
                    <!-- ĐÃ CẬP NHẬT: LẤY TRỰC TIẾP TỪ CỘT DATABASE MỚI CỦA BẠN -->
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400 mt-4">
                      <span>Tạm tính ({{ selectedOrder.items?.length || 0 }} SP):</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.sub_total || (Number(selectedOrder.total_amount) - Number(selectedOrder.shipping_fee || 0) + Number(selectedOrder.discount_amount || 0))) }}</span>
                    </div>

                    <div v-if="selectedOrder.flash_sale_discount > 0" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Trợ giá Flash Sale:</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.flash_sale_discount) }}</span>
                    </div>
                    
                    <div v-if="selectedOrder.tier_discount > 0" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Ưu đãi Hạng (<span class="fw-bold">{{ selectedOrder.discount_details?.tier_name || 'Thành viên' }}</span>):</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.tier_discount) }}</span>
                    </div>

                    <div v-if="selectedOrder.voucher_discount > 0" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Giảm giá (Voucher):</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.voucher_discount) }}</span>
                    </div>

                    <!-- FALLBACK: Dự phòng cho các đơn hàng cũ gộp chung discount_amount -->
                    <div v-if="selectedOrder.discount_amount > 0 && !selectedOrder.tier_discount && !selectedOrder.voucher_discount" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Tổng Khuyến mãi:</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.discount_amount) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Phí vận chuyển:</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.shipping_fee || 0) }}</span>
                    </div>
                    
                    <hr class="dark:border-gray-600 my-2">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <span class="fw-bold text-dark dark:text-white text-uppercase">Tổng thanh toán:</span>
                      <span class="text-danger fw-bold fs-4">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ============================================== -->
              <!-- GIAO DIỆN SẢN PHẨM & COMBO                     -->
              <!-- ============================================== -->
              <div class="bg-white dark:bg-[#1a2533] p-0 rounded-4 shadow-sm border dark:border-gray-700 overflow-hidden">
                <div class="p-3 p-md-4 border-bottom dark:border-gray-700">
                   <h6 class="fw-bold text-dark dark:text-white m-0 font-sans-vn">Sản phẩm đã mua ({{ selectedOrder.items?.length || 0 }})</h6>
                </div>

                <div class="p-0">
                  <template v-for="(group, gIdx) in cartGroups(selectedOrder.items)" :key="'grp'+gIdx">

                    <!-- ===================================== -->
                    <!-- LOẠI 1: GÓI COMBO LOOKBOOK            -->
                    <!-- ===================================== -->
                    <div v-if="group.isLookbook" class="p-3 p-md-4" :class="{'border-top dark:border-gray-700': gIdx > 0}">
                        <div class="d-flex align-items-start gap-3">
                           <div class="position-relative flex-shrink-0" style="width: 80px; height: 100px;">
                             <img :src="group.lookbook_image" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
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
                                       <div class="text-muted small mb-1 font-sans-vn" style="font-size: 0.75rem;">Giá Set:</div>
                                       <div class="fw-bold text-danger fs-6 font-sans-vn">{{ formatCurrency(group.totalPrice) }}</div>
                                    </div>
                                    <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ group.comboQuantity }}</div>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- CHI TIẾT COMBO XỔ XUỐNG -->
                        <div v-show="isGroupExpanded(group.lookbook_id)" class="mt-4 pt-3 border-top border-dashed ps-2" @click.stop>
                           <div class="d-flex flex-column">
                              <div v-for="(item, idx) in group.items" :key="'cb_item_'+item.id" class="d-flex align-items-center gap-3 mb-3 last-no-border">
                                 <img :src="getImageUrl(item.variant_image)" style="width: 45px; height: 60px;" class="rounded-2 border dark:border-gray-600 object-fit-cover bg-light" @error="e => e.target.src='/client_placeholder.png'">
                                 <div class="flex-grow-1">
                                    <div class="fw-bold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn" style="font-size: 0.85rem;">{{ item.product_name }}</div>
                                    <div class="text-muted font-sans-vn d-flex justify-content-between pe-1 mt-1" style="font-size: 0.8rem;">
                                       <span>{{ parseAttributes(item.variant_attributes) }} <span class="mx-1">|</span> <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.purchased_price) }}</span></span>
                                       <span class="text-muted fw-bold">x{{ item.quantity }}</span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                    </div>

                    <!-- ===================================== -->
                    <!-- LOẠI 2: SẢN PHẨM LẺ BÌNH THƯỜNG       -->
                    <!-- ===================================== -->
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
                                     Phân loại: {{ parseAttributes(item.variant_attributes) }}
                                  </span>
                               </div>
                               <div class="col-4 col-sm-3 p-0 text-end d-flex flex-column justify-content-between">
                                  <div>
                                     <div class="text-muted small mb-1 font-sans-vn" style="font-size: 0.75rem;">Giá mua:</div>
                                     <div class="fw-bold text-dark dark:text-white fs-6 font-sans-vn">{{ formatCurrency(item.purchased_price) }}</div>
                                  </div>
                                  <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ item.quantity }}</div>
                               </div>
                            </div>
                          </div>
                       </div>
                    </template>

                  </template>
                </div>
              </div>

           </div>
        </div>
        
        <!-- FOOTER: NÚT THAO TÁC ĐỒNG BỘ THEO TRẠNG THÁI -->
        <div class="modal-footer border-top dark:border-gray-700 bg-white dark:bg-[#1a2533] p-3 justify-content-end rounded-bottom-4 z-index-2 position-relative shadow-sm-top">
          
          <!-- Trường hợp đơn Pending -->
          <button v-if="selectedOrder && selectedOrder.status === 'pending'" class="btn btn-outline-danger px-4 rounded-pill fw-bold font-sans-vn" @click="cancelOrder(selectedOrder)">Hủy đơn hàng</button>

          <!-- Trường hợp đã Yêu cầu Hoàn trả (return_status != null) -->
          <template v-if="selectedOrder && selectedOrder.return_status">
             <button class="btn bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-4 fw-bold font-sans-vn pe-none">
                <i class="bi bi-info-circle-fill me-1"></i> {{ getReturnStatusLabel(selectedOrder.return_status) }}
             </button>
             <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all font-sans-vn hover-urban-bg" @click="buyAgain(selectedOrder)">
                Mua lại
             </button>
          </template>
          
          <!-- Trường hợp Đã Giao Hàng & Chưa Yêu Cầu Hoàn -->
          <template v-else-if="selectedOrder && selectedOrder.status === 'completed'">
            <button class="btn btn-outline-danger rounded-pill px-4 fw-semibold transition-all font-sans-vn" @click="requestReturn(selectedOrder)">Hoàn trả / Đổi hàng</button>
            <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all font-sans-vn hover-urban-bg" @click="buyAgain(selectedOrder)">Mua lại</button>
            
            <button v-if="selectedOrder.is_reviewed" class="btn btn-outline-urban rounded-pill px-4 fw-bold shadow-sm transition-all font-sans-vn hover-urban-bg" @click="openReview(selectedOrder)">
               Xem lại đánh giá
            </button>
            <button v-else class="btn btn-urban rounded-pill px-4 fw-bold shadow-sm transition-all font-sans-vn" @click="openReview(selectedOrder)">
               Đánh giá
            </button>
          </template>

          <button type="button" class="btn btn-secondary px-4 px-md-5 rounded-pill fw-bold shadow-sm ms-2 font-sans-vn" @click="closeModal">Đóng</button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import Swal from 'sweetalert2';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import { useCartStore } from '@/stores/cartStore';
import TrackingMap from '@/components/shared/TrackingMap.vue';

const emit = defineEmits(['refresh']);
const router = useRouter();
const cartStore = useCartStore();

const selectedOrder = ref(null);
const isModalLoading = ref(false);
let modalInstance = null;

const mapData = ref(null);

const stepLevel = computed(() => {
  if (!selectedOrder.value) return 0;
  const s = selectedOrder.value.status;
  if (s === 'pending') return 1;
  if (s === 'confirmed' || s === 'processing') return 2;
  if (s === 'shipping') return 3;
  if (s === 'completed' || s === 'returned') return 4;
  return 0;
});

const closeModal = () => {
  if (document.activeElement) document.activeElement.blur();
  if (modalInstance) modalInstance.hide();
};

const openModal = async (id) => {
  selectedOrder.value = null;
  mapData.value = null;
  if (!modalInstance) {
    modalInstance = new window.bootstrap.Modal(document.getElementById('orderDetailModal'));
  }
  modalInstance.show();
  
  isModalLoading.value = true;
  expandedGroups.value = [];
  
  try {
    const res = await api.get(`/client/user/orders/${id}`);
    if (res.data.success) {
       selectedOrder.value = res.data.data;
       if (typeof selectedOrder.value.shipping_info === 'string') {
          selectedOrder.value.shipping_info = JSON.parse(selectedOrder.value.shipping_info);
       }
       // Parse Json discount_details
       if (typeof selectedOrder.value.discount_details === 'string') {
          try {
             selectedOrder.value.discount_details = JSON.parse(selectedOrder.value.discount_details);
          } catch(e) {
             selectedOrder.value.discount_details = {};
          }
       }
       if (stepLevel.value >= 3) {
          prepareMapData();
       }
    }
  } catch (err) {
    ZyroSwal.toastError('Lỗi tải chi tiết đơn hàng');
    closeModal();
  } finally {
    isModalLoading.value = false;
  }
};

defineExpose({ openModal });

// ========================================================
// THUẬT TOÁN GOM NHÓM ĐỒNG BỘ 
// ========================================================
const expandedGroups = ref([]);

const groupOrderItems = (items) => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };

  if (!items) return result;

  items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        let lbName = 'Combo / Set Đồ';
        let lbImage = null;
        
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

const toggleGroup = (lookbookId) => {
  if (expandedGroups.value.includes(lookbookId)) {
    expandedGroups.value = expandedGroups.value.filter(k => k !== lookbookId);
  } else {
    expandedGroups.value.push(lookbookId);
  }
};

const isGroupExpanded = (lookbookId) => expandedGroups.value.includes(lookbookId);

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
         
         emit('refresh');
         closeModal();
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
            
            emit('refresh');
            closeModal();
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
      closeModal();
      router.push('/cart');
  } catch (error) {
      ZyroSwal.close();
      ZyroSwal.toastError(error.response?.data?.message || 'Không thể mua lại sản phẩm');
  }
};

const openReview = (order) => {
  closeModal();
  router.push(`/user/review?order_id=${order.id}`);
};

// ĐÃ CẬP NHẬT: Tọa độ bao quát Việt Nam
const getCoordinatesForCity = (cityName) => {
  if(!cityName) return [21.0285, 105.8542]; 
  const city = cityName.toLowerCase();
  const coordsMap = {
    'hà nội': [21.0285, 105.8542],
    'hồ chí minh': [10.8231, 106.6297],
    'đà nẵng': [16.0471, 108.2068],
    'hải phòng': [20.8449, 106.6881],
    'cần thơ': [10.0452, 105.7469],
    'đắk lắk': [12.6667, 108.0383],
    'lâm đồng': [11.9404, 108.4583],
    'nghệ an': [18.6733, 105.6813],
    'thanh hóa': [19.2973, 105.2974],
    'bình dương': [11.1667, 106.6667],
    'đồng nai': [10.9333, 107.1833],
    'quảng ninh': [21.0000, 107.3333],
    'thừa thiên huế': [16.4637, 107.5909],
    'khánh hòa': [12.2388, 109.1967]
  };
  for (const key in coordsMap) {
    if (city.includes(key)) return coordsMap[key];
  }
  return [21.0285, 105.8542];
};

const prepareMapData = async () => {
  if (!selectedOrder.value || !selectedOrder.value.shipping_info) return;
  const info = selectedOrder.value.shipping_info;
  
  const originName = info.origin_city || 'Hà Nội';
  const destName = info.city || 'Hồ Chí Minh';
  
  // NẠP DỮ LIỆU MAP ĐỂ CHUYỂN VÀO COMPONENT DÙNG CHUNG
  mapData.value = {
     origin: { name: originName, coords: getCoordinatesForCity(originName) },
     destination: { name: destName, coords: getCoordinatesForCity(destName) },
     shipping_provider: selectedOrder.value.shipping_provider
  };
};

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
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

const getCancelReason = (order) => {
  if (!order.histories) return 'Khách hàng hủy đơn';
  const h = order.histories.find(x => x.status === 'cancelled');
  return h ? h.note : 'Khách hàng hủy đơn';
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

const getReturnStatusLabel = (status) => {
  const map = { 'pending': 'Đang xử lý hoàn trả', 'proposing': 'Đang thỏa thuận hoàn trả', 'approved': 'Đã hoàn tiền', 'rejected': 'Từ chối hoàn trả' };
  return map[status] || 'Đang xử lý hoàn trả';
};

onBeforeUnmount(() => {
  if (modalInstance) modalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.btn-outline-danger { transition: 0.2s; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.hover-urban-bg:hover { background-color: var(--color-c-hover, #547792); color: white; border-color: var(--color-c-hover, #547792); }
.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.transition-color { transition: color 0.2s ease; }

.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* STEPPER TRACKING UI */
.tracking-stepper-container { position: relative; margin-bottom: 20px; }
.stepper-wrapper { display: flex; justify-content: space-between; position: relative; width: 100%; padding-bottom: 10px; }
.stepper-wrapper::before { content: ''; position: absolute; top: 15px; left: 10%; right: 10%; height: 3px; background-color: #e9ecef; z-index: 1; border-radius: 5px; }
html.dark .stepper-wrapper::before { background-color: #373b3e; }
.stepper-item { position: relative; display: flex; flex-direction: column; align-items: center; flex: 1; z-index: 2; }
.step-counter { width: 32px; height: 32px; border-radius: 50%; background-color: #e9ecef; color: #adb5bd; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; transition: all 0.3s ease; border: 3px solid white; }
html.dark .step-counter { background-color: #373b3e; border-color: #1a2533; }
.step-name { font-size: 0.85rem; font-weight: 600; color: #6c757d; text-align: center; }

.stepper-item.completed .step-counter { background-color: var(--color-c-hover, #009981); color: white; box-shadow: 0 0 0 4px rgba(0, 153, 129, 0.2); }
.stepper-item.completed .step-name { color: var(--color-c-hover, #009981); }
.stepper-item.completed + .stepper-item.completed::after { content: ''; position: absolute; top: 15px; right: 50%; width: 100%; height: 3px; background-color: var(--color-c-hover, #009981); z-index: -1; }
.stepper-item:first-child::after { display: none; }

.tracking-item:last-child .tracking-line { display: none !important; }
.z-index-2 { z-index: 2; }

/* UTILS */
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.cursor-pointer { cursor: pointer; }
.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; }
html.dark .border-dashed { border-color: #373b3e !important; }
.last-no-border:last-child { margin-bottom: 0 !important; padding-bottom: 0 !important; border: none !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
</style>