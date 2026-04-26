<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="isOpen" class="minicart-backdrop" @click="$emit('close')"></div>
    </transition>

    <transition name="slide-right">
      <div v-if="isOpen" class="minicart-panel bg-white dark:bg-[#1a2533] shadow-lg d-flex flex-column">
        
        <!-- HEADER -->
        <div class="p-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center bg-light dark:bg-[#212529]">
          <h5 class="fw-bold m-0 text-dark dark:text-white d-flex align-items-center text-uppercase tracking-wide font-sans-vn">
            <i class="bi bi-bag-check-fill text-dark me-2"></i> Giỏ hàng 
            <span class="badge bg-dark ms-2 rounded-pill" style="font-size: 0.75rem;">{{ cartStore.totalQuantity }}</span>
          </h5>
          <button class="btn-close dark:filter-invert" @click="$emit('close')"></button>
        </div>

        <!-- THANH TIẾN TRÌNH FREESHIP -->
        <div class="p-3 border-bottom border-secondary-subtle dark:border-gray-700 bg-light">
           <div v-if="remainingForFreeship > 0">
             <div class="d-flex justify-content-between small fw-bold mb-2 text-dark dark:text-gray-200 font-sans-vn">
               <span>Mua thêm <span class="text-dark fs-6">{{ formatCurrency(remainingForFreeship) }}</span> để được</span>
               <span class="text-urban fw-bolder tracking-widest"><i class="bi bi-truck me-1"></i> FREESHIP</span>
             </div>
             <div class="progress bg-white border shadow-sm" style="height: 8px;">
               <div class="progress-bar bg-urban progress-bar-striped progress-bar-animated" :style="{ width: freeshipProgress + '%' }"></div>
             </div>
           </div>
           <div v-else class="text-urban fw-bold small text-center d-flex align-items-center justify-content-center font-sans-vn">
             <i class="bi bi-check-circle-fill me-2 fs-5"></i> Tuyệt vời! Bạn đã được Miễn phí giao hàng.
           </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="flex-grow-1 overflow-auto custom-scrollbar-y p-3 relative">
           
           <!-- 1. HIỆU ỨNG SKELETON -->
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

           <!-- 2. Giỏ hàng trống -->
           <div v-else-if="cartStore.items.length === 0" class="text-center py-5 text-muted mt-5">
             <i class="bi bi-bag-x fs-1 d-block mb-3 opacity-50"></i>
             <p class="fw-medium font-sans-vn">Giỏ hàng của bạn đang trống.</p>
             <button class="btn btn-dark text-white rounded-pill px-4 py-2 mt-2 fw-bold shadow-sm font-sans-vn" @click="$emit('close')">
               Tiếp tục mua sắm
             </button>
           </div>

           <!-- 3. Có sản phẩm -->
           <div v-else class="d-flex flex-column gap-3">
             <div v-for="(group, gIdx) in cartGroups" :key="'grp'+gIdx">

               <!-- ===================================== -->
               <!-- LOẠI 1: GÓI COMBO LOOKBOOK (CÓ ẢNH & NHẬP SỐ) -->
               <!-- ===================================== -->
               <div v-if="group.isLookbook" class="bg-urban-effect dark:bg-[#1a2533] p-3 rounded-4 border border-secondary border-opacity-25 shadow-sm position-relative transition-all"
                    :class="{'pe-none opacity-75': updatingItemId === 'combo_' + group.lookbook_id}">
                  
                  <div class="d-flex align-items-start gap-3">
                    
                    <!-- Ảnh đại diện Combo -->
                    <img :src="group.lookbook_image || group.items[0]?.image || '/client_placeholder.png'" 
                         @error="e => e.target.src='/client_placeholder.png'" 
                         class="rounded-3 object-fit-cover shadow-sm border border-secondary border-opacity-25 dark:border-gray-600 flex-shrink-0 cursor-pointer" 
                         style="width: 75px; height: 95px;" 
                         @click="toggleGroup(group.lookbook_id)"
                         :alt="group.lookbook_name">
                         
                    <!-- Cột thông tin Combo -->
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="cursor-pointer" @click="toggleGroup(group.lookbook_id)">
                          <span class="badge bg-urban text-white rounded-pill px-2 py-1 shadow-sm font-sans-vn d-inline-block mb-1" style="font-size: 0.7rem;">
                            <i class="bi bi-magic me-1"></i> {{ group.lookbook_name }}
                          </span>
                          <div class="small text-muted font-sans-vn fw-bold">{{ group.items.length }} món đồ</div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-1 mt-1">
                          <i class="bi fs-5 text-muted transition-all cursor-pointer hover-urban" :class="expandedGroups.includes(group.lookbook_id) ? 'bi-chevron-up' : 'bi-chevron-down'" @click="toggleGroup(group.lookbook_id)"></i>
                          <button class="btn btn-link text-muted p-0 ms-1 hover-danger transition-color z-index-3" 
                                  @click="removeCombo(group)" title="Xóa toàn bộ Combo">
                             <i class="bi bi-trash3 fs-5"></i>
                          </button>
                        </div>
                      </div>

                      <!-- Giá & Số lượng (Cho phép gõ số) -->
                      <div class="d-flex justify-content-between align-items-center bg-white dark:bg-[#212529] p-2 rounded-3 border border-light-subtle dark:border-gray-700 shadow-sm mt-3">
                         <div class="fw-bold text-danger font-sans-vn ps-1" style="font-size: 1.05rem;">
                           {{ formatCurrency(group.totalPrice) }}
                         </div>
                         <div class="quantity-box border border-light-subtle dark:border-gray-600 rounded-2 d-flex bg-light dark:bg-[#121416]" style="width: 90px; height: 32px;">
                            <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click.stop="decreaseComboQty(group)"><i class="bi bi-dash"></i></button>
                            <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white no-spinners" 
                                   style="font-size: 0.85rem;" 
                                   :value="group.comboQuantity" 
                                   @change="onManualComboQtyChange(group, $event.target.value)">
                            <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click.stop="increaseComboQty(group)"><i class="bi bi-plus"></i></button>
                         </div>
                      </div>
                    </div>
                  </div>

                  <!-- Dropdown Các món bên trong Combo (Read-only) ĐÃ BỎ VIỀN XANH -->
                  <div v-show="expandedGroups.includes(group.lookbook_id)" class="combo-items-dropdown mt-3 pt-3 border-top border-secondary border-opacity-25">
                    <div class="d-flex flex-column gap-3">
                      <div v-for="item in group.items" :key="'lbItem'+item.variant_id" class="d-flex gap-3 align-items-center">
                         <img :src="item.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" 
                              class="rounded-2 border border-light-subtle dark:border-gray-600 object-fit-cover shadow-sm bg-white" style="width: 45px; height: 60px;">
                         
                         <div class="flex-grow-1">
                           <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-1 font-sans-vn" style="font-size: 0.85rem;">
                             {{ item.product_name }}
                           </h6>
                           <div class="d-flex justify-content-between align-items-center">
                             <span class="text-secondary dark:text-gray-400 font-sans-vn" style="font-size: 0.75rem;">
                               <!-- ĐÃ CẬP NHẬT: Định dạng MÀU/SIZE | GIÁ TIỀN -->
                               {{ item.attributes || 'Mặc định' }} <span class="mx-1">|</span> <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.current_price) }}</span>
                             </span>
                             <span class="text-muted fw-semibold" style="font-size: 0.75rem;">x{{ item.quantity }}</span>
                           </div>
                         </div>
                      </div>
                    </div>
                  </div>

                  <!-- Loader khi đang cập nhật Combo -->
                  <div v-if="updatingItemId === 'combo_' + group.lookbook_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader bg-white dark:bg-gray-800 p-2 rounded-pill shadow">
                    <span></span><span></span><span></span>
                  </div>
               </div>

               <!-- ===================================== -->
               <!-- LOẠI 2: SẢN PHẨM LẺ (CŨNG CHO NHẬP SỐ) -->
               <!-- ===================================== -->
               <div v-else class="d-flex flex-column gap-3">
                 <div v-for="item in group.items" :key="'nmItem'+item.variant_id"
                      class="d-flex gap-3 pb-3 border-bottom border-light-subtle dark:border-gray-700 position-relative transition-all"
                      :class="{'pe-none opacity-50': updatingItemId === item.variant_id}">
                    
                    <img :src="item.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" 
                         class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light" style="width: 85px; height: 110px;">
                    
                    <div class="flex-grow-1 d-flex flex-column justify-content-between py-1">
                      <div>
                        <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 line-clamp-2 font-sans-vn" style="font-size: 0.9rem;" :title="item.product_name">
                          <router-link :to="`/product/${item.product_slug || item.product_id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color" @click="$emit('close')">
                            {{ item.product_name }}
                          </router-link>
                        </h6>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                          <span class="d-inline-flex align-items-center bg-light dark:bg-gray-700 text-secondary dark:text-gray-300 border dark:border-gray-600 px-2 py-1 rounded fw-medium font-sans-vn" style="font-size: 0.75rem;">
                            {{ item.attributes || 'Mặc định' }}
                          </span>
                        </div>
                        <div v-if="item.stock_warning" class="text-danger mt-1 fw-medium font-sans-vn" style="font-size: 0.7rem;">
                          <i class="bi bi-exclamation-triangle"></i> Vượt quá tồn kho
                        </div>
                      </div>
                      
                      <div class="d-flex justify-content-between align-items-center mt-auto pt-2 position-relative">
                        
                        <!-- Số lượng Nhập thủ công -->
                        <div class="quantity-box border dark:border-gray-600 rounded-2 d-flex bg-white dark:bg-[#212529] shadow-sm" style="width: 90px; height: 30px;">
                           <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="decreaseQty(item)"><i class="bi bi-dash"></i></button>
                           <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white no-spinners" 
                                  style="font-size: 0.85rem;" 
                                  :value="item.quantity" 
                                  @change="onManualQtyChange(item, $event.target.value)">
                           <button class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="increaseQty(item)"><i class="bi bi-plus"></i></button>
                        </div>
                        
                        <div v-if="updatingItemId === item.variant_id" class="position-absolute start-50 translate-middle-x bouncing-loader">
                          <span></span><span></span><span></span>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                          <div class="fw-bold text-dark dark:text-white font-sans-vn" style="font-size: 1.05rem;">
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
           </div>
        </div>

        <!-- FOOTER -->
        <div class="p-4 bg-light dark:bg-[#1a2533] border-top border-secondary-subtle dark:border-gray-700 shadow-sm-top z-1" v-if="cartStore.items.length > 0">
           <div class="d-flex justify-content-between align-items-center mb-3">
             <span class="text-muted fw-bold text-uppercase small tracking-wide font-sans-vn">Tổng tạm tính:</span>
             <span class="fw-bold text-danger fs-4 font-sans-vn">{{ formatCurrency(cartStore.totalPrice) }}</span>
           </div>
           <div class="d-flex flex-column gap-2">
             <router-link to="/checkout" class="btn btn-dark py-3 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform fs-6 font-sans-vn" 
                          :class="{'pe-none opacity-50': updatingItemId !== null || cartStore.isLoading}" @click="$emit('close')">
               Thanh toán <i class="bi bi-arrow-right ms-1"></i>
             </router-link>
             
             <router-link to="/cart" class="btn btn-link text-dark dark:text-gray-300 py-2 fw-bold text-uppercase tracking-wide text-decoration-none hover-urban font-sans-vn" 
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

// Track id đang cập nhật
const updatingItemId = ref(null);
// Track Accordion Combo nào đang mở
const expandedGroups = ref([]);

const FREESHIP_THRESHOLD = 1000000;

const remainingForFreeship = computed(() => {
  const diff = FREESHIP_THRESHOLD - cartStore.totalPrice;
  return diff > 0 ? diff : 0;
});

const freeshipProgress = computed(() => {
  const percent = (cartStore.totalPrice / FREESHIP_THRESHOLD) * 100;
  return percent > 100 ? 100 : percent;
});

// THUẬT TOÁN GOM NHÓM ĐÃ NÂNG CẤP LẤY ẢNH & TÊN TỪ API
const cartGroups = computed(() => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };

  cartStore.items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        group = { 
          isLookbook: true, 
          lookbook_id: item.lookbook_id, 
          lookbook_name: item.lookbook_name || 'Combo Phong Cách',
          lookbook_image: item.lookbook_image,
          items: [],
          comboQuantity: item.quantity, 
          totalPrice: 0 
        };
        result.push(group);
      }
      group.items.push(item);
      group.totalPrice += (item.current_price * item.quantity);
    } else {
      normalGroup.items.push(item);
    }
  });

  if (normalGroup.items.length > 0) {
    result.push(normalGroup);
  }

  return result;
});

const toggleGroup = (id) => {
  if (expandedGroups.value.includes(id)) {
    expandedGroups.value = expandedGroups.value.filter(gId => gId !== id);
  } else {
    expandedGroups.value.push(id);
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

// =====================================
// ACTIONS DÀNH RIÊNG CHO COMBO (BULK ACTION)
// =====================================
const increaseComboQty = async (group) => {
  const newQty = group.comboQuantity + 1;
  if (newQty > 50) return;
  
  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, newQty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const decreaseComboQty = async (group) => {
  if (group.comboQuantity <= 1) return;
  const newQty = group.comboQuantity - 1;
  
  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, newQty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const onManualComboQtyChange = async (group, newQty) => {
  let qty = parseInt(newQty);
  if (isNaN(qty) || qty < 1) qty = 1;
  if (qty > 50) qty = 50;

  if (qty === group.comboQuantity) {
     const input = document.activeElement;
     if (input && input.tagName === 'INPUT') input.value = qty;
     return; 
  }

  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, qty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const removeCombo = async (group) => {
  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.removeItem(item.item_id, item.variant_id);
    }
  } finally {
    updatingItemId.value = null;
  }
};

// =====================================
// ACTIONS CHO SẢN PHẨM LẺ
// =====================================
const increaseQty = async (item) => {
  const maxStock = item.current_stock !== undefined ? item.current_stock : 50; 
  if (item.quantity >= maxStock) return;
  
  updatingItemId.value = item.variant_id;
  try {
    await cartStore.updateQuantity(item.item_id, item.variant_id, item.quantity + 1);
  } finally {
    updatingItemId.value = null; 
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

const onManualQtyChange = async (item, newQty) => {
  let qty = parseInt(newQty);
  if (isNaN(qty) || qty < 1) qty = 1;
  
  const maxStock = item.current_stock !== undefined ? item.current_stock : 50;
  const limit = Math.min(50, maxStock);
  if (qty > limit) qty = limit;

  if (qty === item.quantity) {
     const input = document.activeElement;
     if (input && input.tagName === 'INPUT') input.value = qty;
     return;
  }

  updatingItemId.value = item.variant_id;
  try {
    await cartStore.updateQuantity(item.item_id, item.variant_id, qty);
  } finally {
    updatingItemId.value = null;
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

watch(() => props.isOpen, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});
</script>

<style scoped>
.minicart-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.5); z-index: 1070; }
.minicart-panel { position: fixed; top: 0; right: 0; width: 100%; max-width: 420px; height: 100vh; z-index: 1080; border-left: 1px solid rgba(0,0,0,0.1); }
html.dark .minicart-panel { border-left-color: rgba(255,255,255,0.05); }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.bg-urban-effect { background-color: var(--color-c-effect, #ebf1f5) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.hover-danger:hover { color: #dc3545 !important; }
.transition-color { transition: color 0.2s ease; }
.cursor-pointer { cursor: pointer; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important; }

.tracking-wide { letter-spacing: 1px; }
.tracking-widest { letter-spacing: 2px; }
.shadow-sm-top { box-shadow: 0 -4px 15px rgba(0,0,0,0.03); }

.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* CHỐNG HIỆN 2 MŨI TÊN TĂNG GIẢM TRONG Ô INPUT NUMBER */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.no-spinners {
  -moz-appearance: textfield;
}

.bouncing-loader { display: flex; align-items: center; justify-content: center; gap: 4px; z-index: 5; }
.bouncing-loader span { display: block; width: 6px; height: 6px; background-color: var(--color-c-hover, #547792); border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; }
html.dark .bouncing-loader span { background-color: #94B4C1; }
.bouncing-loader span:nth-child(1) { animation-delay: -0.32s; }
.bouncing-loader span:nth-child(2) { animation-delay: -0.16s; }
@keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1.2); } }

.shimmer { background: #f6f7f8; background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%); background-repeat: no-repeat; background-size: 800px 100%; animation: placeholderShimmer 1.5s infinite linear; }
html.dark .shimmer { background: #2b3035; background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%); }
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.z-index-3 { z-index: 3; }
.transition-all { transition: all 0.3s ease; }
</style>