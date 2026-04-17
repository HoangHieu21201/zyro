<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="isOpen" class="minicart-backdrop" @click="$emit('close')"></div>
    </transition>

    <transition name="slide-right">
      <div v-if="isOpen" class="minicart-panel bg-white dark:bg-[#1a2533] shadow-lg d-flex flex-column">
        
        <!-- HEADER -->
        <div class="p-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center bg-light dark:bg-[#212529]">
          <h5 class="fw-bold m-0 text-dark dark:text-white d-flex align-items-center text-uppercase tracking-wide">
            <i class="bi bi-bag-check-fill text-dark me-2"></i> Giỏ hàng 
            <span class="badge bg-dark ms-2 rounded-pill" style="font-size: 0.75rem;">{{ cartStore.totalQuantity }}</span>
          </h5>
          <button class="btn-close dark:filter-invert" @click="$emit('close')"></button>
        </div>

        <!-- THANH TIẾN TRÌNH FREESHIP -->
        <div class="p-3 border-bottom border-secondary-subtle dark:border-gray-700 bg-light">
           <div v-if="remainingForFreeship > 0">
             <div class="d-flex justify-content-between small fw-bold mb-2 text-dark dark:text-gray-200">
               <span>Mua thêm <span class="text-dark fs-6">{{ formatCurrency(remainingForFreeship) }}</span> để được</span>
               <span class="text-urban fw-bolder tracking-widest"><i class="bi bi-truck me-1"></i> FREESHIP</span>
             </div>
             <div class="progress bg-white border shadow-sm" style="height: 8px;">
               <div class="progress-bar bg-urban progress-bar-striped progress-bar-animated" :style="{ width: freeshipProgress + '%' }"></div>
             </div>
           </div>
           <div v-else class="text-urban fw-bold small text-center d-flex align-items-center justify-content-center">
             <i class="bi bi-check-circle-fill me-2 fs-5"></i> Tuyệt vời! Bạn đã được Miễn phí giao hàng.
           </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="flex-grow-1 overflow-auto custom-scrollbar-y p-3 relative">
           
           <!-- 1. HIỆU ỨNG SKELETON: Chỉ hiện khi Load lần đầu tiên -->
           <div v-if="cartStore.isLoading && cartStore.items.length === 0" class="d-flex flex-column gap-4 w-100">
             <div v-for="i in 3" :key="'skel'+i" class="d-flex gap-3 pb-3 border-bottom dark:border-gray-700">
               <div class="shimmer rounded-3 border dark:border-gray-600" style="width: 85px; height: 110px;"></div>
               <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                 <div>
                   <div class="shimmer rounded-2 mb-2 w-100" style="height: 16px;"></div>
                   <div class="shimmer rounded-2 mb-3 w-50" style="height: 14px;"></div>
                 </div>
                 <div class="d-flex justify-content-between align-items-end mt-auto pt-2">
                   <div class="shimmer rounded-2" style="width: 90px; height: 30px;"></div>
                   <div class="shimmer rounded-2" style="width: 80px; height: 24px;"></div>
                 </div>
               </div>
             </div>
           </div>

           <!-- 2. Giỏ hàng trống thực sự -->
           <div v-else-if="cartStore.items.length === 0" class="text-center py-5 text-muted mt-5">
             <i class="bi bi-bag-x fs-1 d-block mb-3 opacity-50"></i>
             <p class="fw-medium">Giỏ hàng của bạn đang trống.</p>
             <button class="btn btn-dark text-white rounded-pill px-4 py-2 mt-2 fw-bold shadow-sm" @click="$emit('close')">
               Tiếp tục mua sắm
             </button>
           </div>

           <!-- 3. Có sản phẩm -->
           <div v-else class="d-flex flex-column gap-4">
             <div v-for="(item, index) in cartStore.items" :key="'cartItem'+index" 
                  class="d-flex gap-3 pb-3 border-bottom dark:border-gray-700 position-relative transition-all"
                  :class="{'pe-none': updatingItemId === item.variant_id}"> <!-- ĐÃ BỎ LÀM MỜ (opacity-50), CHỈ GIỮ PE-NONE KHÓA CLICK -->
               
               <!-- Ảnh SP -->
               <img :src="item.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" 
                    class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light" style="width: 85px; height: 110px;">
               
               <!-- Thông tin SP -->
               <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                 <div>
                   <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2" style="font-size: 0.9rem;" :title="item.product_name">
                     <router-link :to="`/product/${item.product_slug || item.product_id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color" @click="$emit('close')">
                       {{ item.product_name }}
                     </router-link>
                   </h6>
                   
                   <!-- Thuộc tính (Màu / Size) -->
                   <div class="d-flex flex-wrap gap-2 mt-1">
                     <span class="d-inline-flex align-items-center bg-light text-secondary border px-2 py-1 rounded small fw-medium" style="font-size: 0.75rem;">
                       {{ item.attributes || 'Mặc định' }}
                     </span>
                   </div>
                   
                   <!-- Cảnh báo hết hàng -->
                   <div v-if="item.stock_warning" class="text-danger small mt-1 fw-medium" style="font-size: 0.7rem;">
                     <i class="bi bi-exclamation-triangle"></i> Vượt quá tồn kho
                   </div>
                 </div>
                 
                 <!-- HÀNG BOTTOM: CHỈNH SỐ LƯỢNG - SPINNER - GIÁ - XÓA -->
                 <div class="d-flex justify-content-between align-items-center mt-auto pt-2 position-relative">
                   
                   <!-- CHỈNH SỐ LƯỢNG -->
                   <div class="quantity-box border dark:border-gray-600 rounded-2 d-flex bg-white dark:bg-[#212529] shadow-sm" style="width: 90px; height: 30px;">
                      <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="decreaseQty(item)"><i class="bi bi-dash"></i></button>
                      <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white" style="font-size: 0.85rem;" :value="item.quantity" readonly>
                      <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="increaseQty(item)"><i class="bi bi-plus"></i></button>
                   </div>
                   
                   <!-- ĐÃ THAY ĐỔI: SPINNER 3 DẤU CHẤM (BOUNCING DOTS) NẰM GIỮA -->
                   <!-- Dùng position-absolute để không làm xô lệch khoảng cách của Giá khi hiện ra -->
                   <div v-if="updatingItemId === item.variant_id" class="position-absolute start-50 translate-middle-x bouncing-loader">
                     <span></span><span></span><span></span>
                   </div>

                   <!-- CỤM GIÁ & XÓA -->
                   <div class="d-flex align-items-center gap-2">
                     <div class="fw-bold text-dark dark:text-white" style="font-size: 1.05rem;">
                       {{ formatCurrency(item.current_price) }}
                     </div>
                     <button class="btn btn-link text-muted p-0 hover-danger transition-color d-flex align-items-center ms-1" style="text-decoration: none;" @click="removeItem(item)" title="Xóa khỏi giỏ">
                        <i class="bi bi-trash3 fs-5"></i>
                     </button>
                   </div>

                 </div>
               </div>

             </div>
           </div>
        </div>

        <!-- FOOTER (TỔNG TIỀN & CTA) -->
        <div class="p-4 bg-light dark:bg-[#1a2533] border-top border-secondary-subtle dark:border-gray-700 shadow-sm-top z-1" v-if="cartStore.items.length > 0">
           <div class="d-flex justify-content-between align-items-center mb-3">
             <span class="text-muted fw-bold text-uppercase small tracking-wide">Tổng tạm tính:</span>
             <span class="fw-bold text-danger fs-4">{{ formatCurrency(cartStore.totalPrice) }}</span>
           </div>
           <div class="d-flex flex-column gap-2">
             <router-link to="/checkout" class="btn btn-dark py-3 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform fs-6" 
                          :class="{'pe-none opacity-50': updatingItemId !== null || cartStore.isLoading}" @click="$emit('close')">
               Thanh toán <i class="bi bi-arrow-right ms-1"></i>
             </router-link>
             
             <router-link to="/cart" class="btn btn-link text-dark dark:text-gray-300 py-2 fw-bold text-uppercase tracking-wide text-decoration-none hover-urban" 
                          :class="{'pe-none opacity-50': updatingItemId !== null || cartStore.isLoading}" @click="$emit('close')" style="font-size: 0.85rem;">
               <i class="bi bi-cart2 me-1"></i> Xem chi tiết giỏ hàng
             </router-link>
           </div>
        </div>

      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useCartStore } from '@/stores/cartStore';

const props = defineProps({
  isOpen: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);
const cartStore = useCartStore();

// Track id của sản phẩm đang được thay đổi số lượng hoặc xóa
const updatingItemId = ref(null);

// Cấu hình Freeship
const FREESHIP_THRESHOLD = 1000000; // 1 Triệu

const remainingForFreeship = computed(() => {
  const diff = FREESHIP_THRESHOLD - cartStore.totalPrice;
  return diff > 0 ? diff : 0;
});

const freeshipProgress = computed(() => {
  const percent = (cartStore.totalPrice / FREESHIP_THRESHOLD) * 100;
  return percent > 100 ? 100 : percent;
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

// Actions tương tác
const increaseQty = async (item) => {
  const maxStock = item.current_stock !== undefined ? item.current_stock : 50; 
  if (item.quantity >= maxStock) return;
  
  updatingItemId.value = item.variant_id; // Bật spinner ở item này
  try {
    await cartStore.updateQuantity(item.item_id, item.variant_id, item.quantity + 1);
  } finally {
    updatingItemId.value = null; // Tắt spinner
  }
};

const decreaseQty = async (item) => {
  if (item.quantity > 1) {
    updatingItemId.value = item.variant_id;
    try {
      await cartStore.updateQuantity(item.item_id, item.variant_id, item.quantity - 1);
    } finally {
      updatingItemId.value = null;
    }
  }
};

const removeItem = async (item) => {
  updatingItemId.value = item.variant_id;
  try {
    await cartStore.removeItem(item.item_id, item.variant_id);
  } finally {
    updatingItemId.value = null;
  }
};

// Khóa cuộn trang khi mở Drawer
watch(() => props.isOpen, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});
</script>

<style scoped>
.minicart-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1070;
}

.minicart-panel {
  position: fixed;
  top: 0; right: 0;
  width: 100%; max-width: 420px;
  height: 100vh;
  z-index: 1080;
  border-left: 1px solid rgba(0,0,0,0.1);
}
html.dark .minicart-panel { border-left-color: rgba(255,255,255,0.05); }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.hover-danger:hover { color: #dc3545 !important; }
.transition-color { transition: color 0.2s ease; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; }

.tracking-wide { letter-spacing: 1px; }
.tracking-widest { letter-spacing: 2px; }
.shadow-sm-top { box-shadow: 0 -4px 15px rgba(0,0,0,0.03); }

.line-clamp-2 {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;  
  overflow: hidden; text-overflow: ellipsis; line-height: 1.4;
}

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* ======================================================
   CSS MỚI: HIỆU ỨNG 3 DẤU CHẤM (BOUNCING DOTS LOADER)
====================================================== */
.bouncing-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  z-index: 5;
}
.bouncing-loader span {
  display: block;
  width: 6px;
  height: 6px;
  background-color: var(--color-c-hover, #547792); /* Chấm màu theo chủ đề web */
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}
html.dark .bouncing-loader span {
  background-color: #94B4C1; /* Sáng hơn một chút ở chế độ ban đêm */
}
.bouncing-loader span:nth-child(1) { animation-delay: -0.32s; }
.bouncing-loader span:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.2); }
}

/* ======================================================
   SKELETON CSS (Màu xám nhấp nháy cho hiệu ứng chờ tải thẻ rỗng)
====================================================== */
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

.z-index-3 { z-index: 3; }
.transition-all { transition: all 0.3s ease; }
</style>