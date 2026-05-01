<template>
  <!-- Padding-top 120px để tránh bị Header Fixed đè lên -->
  <div class="cart-page-wrapper pb-5 mb-5" style="padding-top: 100px;">
    
    <div class="zyro-container">
      
      <!-- BREADCRUMB ĐIỀU HƯỚNG GỌN GÀNG -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
          <li class="breadcrumb-item active text-dark dark:text-gray-300" aria-current="page">Giỏ hàng của bạn</li>
        </ol>
      </nav>

      <!-- SKELETON CHỜ TẢI DỮ LIỆU -->
      <div v-if="cartStore.isLoading && cartStore.items.length === 0" class="row g-5">
         <div class="col-lg-8">
            <div class="shimmer rounded-4 w-100 mb-4" style="height: 80px;"></div>
            <div v-for="i in 3" :key="'sk'+i" class="shimmer rounded-4 w-100 mb-3" style="height: 150px;"></div>
         </div>
         <div class="col-lg-4">
            <div class="shimmer rounded-4 w-100" style="height: 300px;"></div>
         </div>
      </div>

      <!-- TRẠNG THÁI GIỎ HÀNG TRỐNG -->
      <div v-else-if="cartStore.items.length === 0" class="text-center py-5 my-5 animation-fade-in">
        <div class="bg-light dark:bg-[#1a2533] rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 120px; height: 120px;">
          <i class="bi bi-bag-x text-muted opacity-50" style="font-size: 4rem;"></i>
        </div>
        <h4 class="fw-bold text-dark dark:text-white mb-3 font-sans-vn">Giỏ hàng trống</h4>
        <p class="text-muted mb-4 font-sans-vn">Bạn chưa chọn sản phẩm nào vào giỏ hàng.</p>
        <router-link to="/category" class="btn btn-urban text-white px-5 py-3 fw-bold rounded-pill shadow-sm transition-all hover-transform font-sans-vn">
          Tiếp Tục Mua Sắm
        </router-link>
      </div>

      <!-- TRẠNG THÁI CÓ SẢN PHẨM -->
      <div v-else class="row g-4 g-lg-5 animation-fade-in">
        
        <!-- ========================================== -->
        <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM (GOM NHÓM)    -->
        <!-- ========================================== -->
        <div class="col-lg-8">
          
          <!-- THANH TIẾN TRÌNH FREESHIP -->
          <div class="p-3 rounded-4 mb-4 shadow-sm border border-urban border-opacity-25" style="background-color: rgba(84, 119, 146, 0.08);">
             <div v-if="remainingForFreeship > 0">
               <div class="d-flex justify-content-between small fw-bold mb-2 text-dark dark:text-gray-200 font-sans-vn">
                 <span>Mua thêm <span class="text-urban fs-6">{{ formatCurrency(remainingForFreeship) }}</span> để được</span>
                 <span class="text-urban fw-bolder text-uppercase tracking-widest"><i class="bi bi-truck me-1"></i> FREESHIP</span>
               </div>
               <div class="progress bg-white dark:bg-[#1a2533] shadow-sm" style="height: 10px;">
                 <div class="progress-bar bg-urban progress-bar-striped progress-bar-animated" :style="{ width: freeshipProgress + '%' }"></div>
               </div>
             </div>
             <div v-else class="text-urban fw-bold text-center d-flex align-items-center justify-content-center py-1 font-sans-vn">
               <i class="bi bi-check-circle-fill me-2 fs-4"></i> Tuyệt vời! Đơn hàng của bạn đã đủ điều kiện Miễn phí giao hàng.
             </div>
          </div>

          <div class="d-flex justify-content-between align-items-end mb-3">
             <h5 class="fw-bold text-dark dark:text-white m-0 font-sans-vn">Sản phẩm trong giỏ ({{ cartStore.totalQuantity }})</h5>
          </div>

          <div class="cart-items-list border-top dark:border-gray-700 pt-2">
            
            <template v-for="(group, gIdx) in cartGroups" :key="'grp'+gIdx">

              <!-- ===================================== -->
              <!-- LOẠI 1: GÓI COMBO LOOKBOOK            -->
              <!-- ===================================== -->
              <div v-if="group.isLookbook" 
                   class="cart-item position-relative p-3 p-md-4 transition-all bg-urban-effect dark:bg-[#1a2533] rounded-4 mb-4 mt-2 shadow-sm border border-secondary border-opacity-25"
                   :class="{'pe-none opacity-75': updatingItemId === 'combo_' + group.lookbook_id}">
                  
                  <button class="btn-delete-circle transition-all shadow-sm" @click="removeCombo(group)" title="Xóa toàn bộ Combo">
                    <i class="bi bi-x-lg"></i>
                  </button>

                  <!-- SPINNER -->
                  <div v-if="updatingItemId === 'combo_' + group.lookbook_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader" style="z-index: 10;">
                     <span></span><span></span><span></span>
                  </div>

                  <div class="row align-items-start position-relative">
                    <!-- 1. THÔNG TIN COMBO -->
                    <div class="col-12 col-md-7 mb-3 mb-md-0 pe-md-5">
                      <div class="d-flex align-items-start gap-3">
                        <img :src="group.lookbook_image || group.items[0]?.image || '/client_placeholder.png'" 
                             @error="e => e.target.src='/client_placeholder.png'" 
                             class="rounded-3 border border-secondary border-opacity-25 dark:border-gray-600 object-fit-cover shadow-sm bg-light flex-shrink-0 cursor-pointer" 
                             style="width: 100px; height: 130px;"
                             @click="toggleGroup(group.lookbook_id)">
                        
                        <div class="flex-grow-1 pt-1">
                          <div class="cursor-pointer" @click="toggleGroup(group.lookbook_id)">
                             <span class="badge bg-urban text-white rounded-pill px-3 py-1 shadow-sm font-sans-vn d-inline-block mb-2">
                               <i class="bi bi-magic me-1"></i> {{ group.lookbook_name }}
                             </span>
                             <div class="fw-bold text-dark dark:text-gray-200 mb-1 font-sans-vn fs-6">
                               Combo {{ group.items.length }} món đồ
                             </div>
                             <div class="text-muted small d-flex align-items-center gap-1 hover-urban transition-color mt-2 font-sans-vn fw-medium">
                               <i class="bi" :class="expandedGroups.includes(group.lookbook_id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                               {{ expandedGroups.includes(group.lookbook_id) ? 'Thu gọn chi tiết' : 'Xem chi tiết các món' }}
                             </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 2. SỐ LƯỢNG & TỔNG TIỀN COMBO -->
                    <div class="col-12 col-md-5 d-flex justify-content-between justify-content-md-end align-items-center gap-md-4 position-relative mt-2 mt-md-0 pt-md-4">
                      <div class="quantity-box border dark:border-gray-600 rounded-pill d-inline-flex bg-white dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 38px;">
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="decreaseComboQty(group)"><i class="bi bi-dash"></i></button>
                        <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none no-spinners font-sans-vn" 
                               style="width: 40px; font-size: 0.95rem;" 
                               :value="group.comboQuantity" 
                               @change="onManualComboQtyChange(group, $event.target.value)">
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="increaseComboQty(group)"><i class="bi bi-plus"></i></button>
                      </div>

                      <div class="fw-bold text-danger fs-4 text-end font-sans-vn" style="min-width: 100px;">
                         {{ formatCurrency(group.totalPrice) }}
                      </div>
                    </div>
                  </div>

                  <!-- DROPDOWN CHI TIẾT CÁC MÓN BÊN TRONG -->
                  <div v-show="expandedGroups.includes(group.lookbook_id)" class="combo-details mt-4 pt-3 border-top border-secondary border-opacity-25 ps-md-3">
                     <div class="d-flex flex-column gap-3">
                        <div v-for="item in group.items" :key="'cb_item_'+item.variant_id" class="d-flex align-items-center gap-3 bg-white dark:bg-[#212529] p-2 rounded-3 border border-light-subtle dark:border-gray-700 shadow-sm">
                           <img :src="item.image || '/client_placeholder.png'" style="width: 55px; height: 75px;" class="rounded-2 border object-fit-cover shadow-sm bg-white">
                           <div class="flex-grow-1">
                              <h6 class="fw-semibold text-dark dark:text-gray-200 mb-1 line-clamp-1 font-sans-vn" style="font-size: 0.9rem;">
                                <!-- ĐÃ FIX: CHỈ LẤY PRODUCT_ID ĐỂ TẠO LINK CHUẨN XÁC -->
                                <router-link :to="`/product/${item.product_id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color">
                                  {{ item.product_name }}
                                </router-link>
                              </h6>
                              <div class="text-secondary dark:text-gray-400 font-sans-vn" style="font-size: 0.85rem;">
                                 {{ item.attributes || 'Mặc định' }} <span class="mx-1">|</span> <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.current_price) }}</span>
                              </div>
                           </div>
                           <div class="text-muted small fw-bold pe-3">x{{ item.quantity }}</div>
                        </div>
                     </div>
                  </div>

              </div>

              <!-- ===================================== -->
              <!-- LOẠI 2: SẢN PHẨM LẺ BÌNH THƯỜNG       -->
              <!-- ===================================== -->
              <template v-else>
                <div v-for="item in group.items" :key="'cartItem'+item.variant_id" 
                     class="cart-item position-relative p-3 p-md-4 border-bottom dark:border-gray-700 transition-all"
                     :class="{'pe-none opacity-50': updatingItemId === item.variant_id}">
                  
                  <div v-if="updatingItemId === item.variant_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader" style="z-index: 10;">
                     <span></span><span></span><span></span>
                  </div>

                  <button class="btn-delete-circle transition-all shadow-sm" @click="removeItem(item)" title="Xóa khỏi giỏ">
                    <i class="bi bi-x-lg"></i>
                  </button>

                  <div class="row align-items-center position-relative">
                    <div class="col-12 col-md-7 mb-3 mb-md-0 pe-md-5">
                      <div class="d-flex align-items-start gap-3">
                        <img :src="item.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" 
                             class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light flex-shrink-0" style="width: 90px; height: 120px;">
                        <div class="flex-grow-1 pt-1">
                          <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2 pe-3 font-sans-vn">
                            <!-- ĐÃ FIX: CHỈ LẤY PRODUCT_ID ĐỂ TẠO LINK CHUẨN XÁC -->
                            <router-link :to="`/product/${item.product_id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color">
                               {{ item.product_name }}
                            </router-link>
                          </h6>
                          
                          <div class="d-flex flex-wrap gap-2 mt-1 mb-2">
                            <span class="d-inline-flex align-items-center bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-2 py-1 rounded small shadow-sm fw-medium font-sans-vn">
                              {{ item.attributes || 'Mặc định' }}
                            </span>
                          </div>
                          
                          <div class="fw-bold text-muted dark:text-gray-400 font-sans-vn" style="font-size: 0.95rem;">
                            {{ formatCurrency(item.current_price) }}
                          </div>

                          <div v-if="item.stock_warning" class="text-danger small mt-2 fw-medium font-sans-vn" style="font-size: 0.75rem;">
                             <i class="bi bi-exclamation-triangle"></i> Số lượng vượt quá tồn kho
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-5 d-flex justify-content-between justify-content-md-end align-items-center gap-md-4 position-relative">
                      <div class="quantity-box border dark:border-gray-600 rounded-pill d-inline-flex bg-light dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 38px;">
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="decreaseQty(item)"><i class="bi bi-dash"></i></button>
                        <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none no-spinners font-sans-vn" 
                               style="width: 40px; font-size: 0.95rem;" 
                               :value="item.quantity" 
                               @change="onManualQtyChange(item, $event.target.value)">
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="increaseQty(item)"><i class="bi bi-plus"></i></button>
                      </div>

                      <div class="fw-bold text-dark dark:text-white fs-5 text-end font-sans-vn" style="min-width: 100px;">
                         {{ formatCurrency(item.current_price * item.quantity) }}
                      </div>
                    </div>
                  </div>
                </div>
              </template>

            </template>
          </div>
          <!-- Kết thúc danh sách -->

        </div>

        <!-- ========================================== -->
        <!-- CỘT PHẢI: TỔNG KẾT ĐƠN HÀNG                -->
        <!-- ========================================== -->
        <div class="col-lg-4">
          <div class="card border border-light-subtle shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top bg-light" style="top: 100px;">
            <h5 class="fw-bold text-dark dark:text-white mb-4 border-bottom dark:border-gray-700 pb-3 text-uppercase tracking-wide font-sans-vn">Tổng Kết Đơn Hàng</h5>

            <!-- Chi tiết tính tiền -->
            <div class="d-flex justify-content-between mb-3 text-dark dark:text-gray-300 font-sans-vn">
              <span>Tạm tính ({{ cartStore.totalQuantity }} SP)</span>
              <span class="fw-semibold">{{ formatCurrency(cartStore.totalPrice) }}</span>
            </div>
            
            <div class="d-flex justify-content-between mb-4 text-dark dark:text-gray-300 font-sans-vn">
              <span>Phí giao hàng</span>
              <span class="fw-semibold" v-if="remainingForFreeship > 0">Tính ở bước thanh toán</span>
              <span class="fw-bold text-urban" v-else>Miễn phí</span>
            </div>

            <!-- Tổng tiền -->
            <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top border-secondary-subtle dark:border-gray-700 font-sans-vn">
              <span class="fw-bold text-uppercase fs-5 text-dark dark:text-white">Tổng Cộng</span>
              <span class="fw-bold text-danger fs-3">{{ formatCurrency(cartStore.totalPrice) }}</span>
            </div>
            
            <small class="text-muted fst-italic d-block mb-4 text-end font-sans-vn">(Đã bao gồm VAT)</small>

            <button class="btn btn-urban btn-lg w-100 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform font-sans-vn" 
                    :class="{'pe-none opacity-50': updatingItemId !== null || cartStore.isLoading}" 
                    @click="proceedToCheckout">
              Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
            </button>

            <!-- Trust Badges -->
            <div class="mt-4 pt-3 border-top border-secondary-subtle dark:border-gray-700 d-flex justify-content-center gap-3 opacity-75">
              <i class="bi bi-shield-check fs-4 text-dark dark:text-gray-300" title="Thanh toán an toàn 100%"></i>
              <i class="bi bi-arrow-return-left fs-4 text-dark dark:text-gray-300" title="Đổi trả dễ dàng"></i>
              <i class="bi bi-credit-card-fill fs-4 text-dark dark:text-gray-300" title="Đa dạng phương thức"></i>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cartStore';

// Gọi Component Swal dùng chung của ZYRO
import { ZyroSwal } from '@/components/client/ZyroSwal';

const router = useRouter();
const cartStore = useCartStore();

// Track item đang thao tác
const updatingItemId = ref(null);
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

// THUẬT TOÁN GOM NHÓM ĐÃ ĐƯỢC CHUẨN HÓA
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

// ========================================================
// ACTIONS COMBO (BULK)
// ========================================================
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

const removeCombo = (group) => {
  ZyroSwal.confirmDelete(group.lookbook_name).then(async (result) => {
    if (result.isConfirmed) {
      updatingItemId.value = 'combo_' + group.lookbook_id;
      try {
        for (const item of group.items) {
          await cartStore.removeItem(item.item_id, item.variant_id);
        }
        ZyroSwal.toastSuccess('Đã xóa bộ sưu tập khỏi giỏ');
      } finally {
        updatingItemId.value = null;
      }
    }
  });
};

// ========================================================
// ACTIONS ITEM LẺ
// ========================================================
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

const removeItem = (item) => {
  ZyroSwal.confirmDelete(item.product_name).then(async (result) => {
    if (result.isConfirmed) {
      updatingItemId.value = item.variant_id;
      try {
        await cartStore.removeItem(item.item_id, item.variant_id);
        ZyroSwal.toastSuccess('Đã xóa khỏi giỏ hàng');
      } finally {
        updatingItemId.value = null;
      }
    }
  });
};

const proceedToCheckout = () => {
  ZyroSwal.showLoading('Đang xử lý');
  setTimeout(() => {
    ZyroSwal.close();
    router.push('/checkout'); 
  }, 800);
};

onMounted(() => {
  window.scrollTo(0, 0);
  if (cartStore.items.length === 0) {
     cartStore.fetchDBCart();
  }
});
</script>

<style scoped>
.cart-page-wrapper { width: 100%; }

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

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.font-decor { font-family: 'Times New Roman', Times, serif; font-style: italic; }

.hover-text-dark:hover { color: #000 !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792) !important; color: white; border: none; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448) !important; color: white; }
.bg-urban-effect { background-color: var(--color-c-effect, #ebf1f5) !important; }

.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.transition-color { transition: color 0.2s ease; }
.cursor-pointer { cursor: pointer; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important; }

.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.sticky-top { transition: all 0.3s ease; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.btn-delete-circle {
  position: absolute;
  top: 16px; 
  right: 16px;
  width: 32px;
  height: 32px;
  background-color: #ffffff;
  border: 1.5px solid #dc3545;
  color: #dc3545;
  border-radius: 50%;
  padding: 0;
  z-index: 5;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-delete-circle i { font-size: 0.85rem; font-weight: bold; pointer-events: none; }
.btn-delete-circle:hover { 
  background-color: #dc3545 !important; 
  color: #ffffff !important; 
  transform: scale(1.1); 
  box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2); 
}
html.dark .btn-delete-circle { 
  background-color: #212529; 
  border-color: #ef4444; 
  color: #ef4444; 
}
html.dark .btn-delete-circle:hover { 
  background-color: #ef4444 !important; 
  color: #ffffff !important; 
}

/* ẨN NÚT MŨI TÊN TRONG Ô INPUT NUMBER */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.no-spinners {
  -moz-appearance: textfield;
}

/* HIỆU ỨNG 3 DẤU CHẤM */
.bouncing-loader {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  z-index: 10; 
}
.bouncing-loader span {
  display: block;
  width: 8px;
  height: 8px;
  background-color: var(--color-c-hover, #547792);
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}
html.dark .bouncing-loader span { background-color: #94B4C1; }
.bouncing-loader span:nth-child(1) { animation-delay: -0.32s; }
.bouncing-loader span:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.3); }
}

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