<!-- File: frontend/src/components/client/MiniCartDrawer.vue -->
<template>
  <Teleport to="body">
    <!-- Lớp phủ tối màn hình -->
    <transition name="fade">
      <div v-if="isOpen" class="minicart-backdrop" @click="$emit('close')"></div>
    </transition>

    <!-- Khung trượt từ bên phải sang -->
    <transition name="slide-right">
      <div v-if="isOpen" class="minicart-panel bg-white dark:bg-[#1a2533] shadow-lg d-flex flex-column">
        
        <!-- HEADER -->
        <div class="p-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center bg-light dark:bg-[#212529]">
          <h5 class="fw-bold m-0 text-dark dark:text-white d-flex align-items-center text-uppercase tracking-wide">
            <i class="bi bi-bag-check-fill text-dark me-2"></i> Giỏ hàng 
            <span class="badge bg-dark ms-2 rounded-pill" style="font-size: 0.75rem;">{{ cartItems.length }}</span>
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

        <!-- DANH SÁCH SẢN PHẨM (BODY) -->
        <div class="flex-grow-1 overflow-auto custom-scrollbar-y p-3">
           <div v-if="cartItems.length === 0" class="text-center py-5 text-muted mt-5">
             <i class="bi bi-bag-x fs-1 d-block mb-3 opacity-50"></i>
             <p class="fw-medium">Giỏ hàng của bạn đang trống.</p>
             <button class="btn btn-dark text-white rounded-pill px-4 py-2 mt-2 fw-bold shadow-sm" @click="$emit('close')">
               Tiếp tục mua sắm
             </button>
           </div>

           <div v-else class="d-flex flex-column gap-4">
             <div v-for="(item, index) in cartItems" :key="item.id" class="d-flex gap-3 position-relative pb-3 border-bottom dark:border-gray-700">
               <!-- Ảnh SP -->
               <img :src="item.image" class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light" style="width: 85px; height: 110px;">
               
               <!-- Thông tin SP -->
               <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                 <div>
                   <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2" style="font-size: 0.9rem;" :title="item.name">
                     <router-link :to="`/product/${item.id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color">{{ item.name }}</router-link>
                   </h6>
                   <!-- Cập nhật hiển thị màu/size sang trọng -->
                   <div class="d-flex flex-wrap gap-2 mt-2">
                     <span class="d-inline-flex align-items-center bg-light text-secondary border px-2 py-1 rounded small fw-medium" style="font-size: 0.75rem;">
                       <span class="rounded-circle me-1 border" :style="{ width: '10px', height: '10px', backgroundColor: item.hex }"></span>
                       {{ item.color }}
                     </span>
                     <span class="d-inline-flex align-items-center bg-light text-secondary border px-2 py-1 rounded small fw-medium" style="font-size: 0.75rem;">
                       Size: {{ item.size }}
                     </span>
                   </div>
                 </div>
                 
                 <!-- Chỉnh số lượng & Giá -->
                 <div class="d-flex justify-content-between align-items-center mt-auto">
                   <div class="quantity-box border dark:border-gray-600 rounded-2 d-flex bg-white dark:bg-[#212529] shadow-sm" style="width: 90px; height: 30px;">
                      <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="item.quantity > 1 ? item.quantity-- : null"><i class="bi bi-dash"></i></button>
                      <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white" style="font-size: 0.85rem;" v-model="item.quantity" readonly>
                      <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="item.quantity++"><i class="bi bi-plus"></i></button>
                   </div>
                   <div class="fw-bold text-dark" style="font-size: 1rem;">{{ formatCurrency(item.price) }}</div>
                 </div>
               </div>

               <!-- Nút Xóa (Gỡ bỏ nút tròn đỏ lỗi thời, chuyển sang nút xóa mảnh) -->
               <button class="btn btn-link position-absolute bottom-0 end-0 text-muted p-0 hover-danger transition-color pb-3" style="font-size: 0.8rem; text-decoration: none;" @click="removeItem(index)" title="Xóa">
                  <i class="bi bi-trash3"></i>
               </button>
             </div>
           </div>
        </div>

        <!-- FOOTER (TỔNG TIỀN & CTA) -->
        <div class="p-4 bg-light dark:bg-[#1a2533] border-top border-secondary-subtle dark:border-gray-700 shadow-sm-top z-1" v-if="cartItems.length > 0">
           <div class="d-flex justify-content-between align-items-center mb-3">
             <span class="text-muted fw-bold text-uppercase small tracking-wide">Tổng tạm tính:</span>
             <span class="fw-bold text-dark fs-4">{{ formatCurrency(cartTotal) }}</span>
           </div>
           <div class="d-flex flex-column gap-2">
             <!-- Nút chốt Sale đen nhám sang trọng -->
             <router-link to="/checkout" class="btn btn-dark py-3 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform fs-6" @click="$emit('close')">
               Thanh toán <i class="bi bi-arrow-right ms-1"></i>
             </router-link>
             
             <!-- Nút phụ Xem giỏ hàng -->
             <router-link to="/cart" class="btn btn-link text-dark dark:text-gray-300 py-2 fw-bold text-uppercase tracking-wide text-decoration-none hover-urban" @click="$emit('close')" style="font-size: 0.85rem;">
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

const props = defineProps({
  isOpen: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

// Mock Data Giỏ hàng (Đã cập nhật mã Hex màu)
const cartItems = ref([
  { id: 101, name: 'Sơ Mi Nam Cộc Tay Cafe Túi Ngực Chống Nhăn', price: 469000, quantity: 1, color: 'Xám sáng', hex: '#bdc3c7', size: 'M', image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=200&auto=format&fit=crop' },
  { id: 204, name: 'Áo Thun Typography Nữ', price: 199000, quantity: 2, color: 'Đen nhám', hex: '#2c3e50', size: 'S', image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=200&auto=format&fit=crop' },
]);

// Cấu hình Freeship
const FREESHIP_THRESHOLD = 1000000; // 1 Triệu

const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const remainingForFreeship = computed(() => {
  const diff = FREESHIP_THRESHOLD - cartTotal.value;
  return diff > 0 ? diff : 0;
});

const freeshipProgress = computed(() => {
  const percent = (cartTotal.value / FREESHIP_THRESHOLD) * 100;
  return percent > 100 ? 100 : percent;
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

const removeItem = (index) => {
  cartItems.value.splice(index, 1);
};

// Khóa cuộn trang khi mở Drawer
watch(() => props.isOpen, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});
</script>

<style scoped>
/* Lớp phủ tối mờ */
.minicart-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1070;
}

/* Khung trượt từ bên phải */
.minicart-panel {
  position: fixed;
  top: 0; right: 0;
  width: 100%; max-width: 400px;
  height: 100vh;
  z-index: 1080;
  border-left: 1px solid rgba(0,0,0,0.1);
}
html.dark .minicart-panel { border-left-color: rgba(255,255,255,0.05); }

/* Animation Trượt */
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Utils */
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

/* Scrollbar */
.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>