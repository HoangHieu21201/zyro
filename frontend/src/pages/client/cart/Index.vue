<template>
  <!-- Padding-top 120px để tránh bị Header Fixed đè lên -->
  <div class="cart-page-wrapper pb-5 mb-5" style="padding-top: 120px;">
    
    <div class="zyro-container">
      
      <!-- BREADCRUMB ĐIỀU HƯỚNG GỌN GÀNG -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
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
        <h4 class="fw-bold text-dark dark:text-white mb-3">Giỏ hàng trống</h4>
        <p class="text-muted mb-4">Bạn chưa chọn sản phẩm nào vào giỏ hàng.</p>
        <router-link to="/category" class="btn btn-urban text-white px-5 py-3 fw-bold rounded-pill shadow-sm transition-all hover-transform">
          Tiếp Tục Mua Sắm
        </router-link>
      </div>

      <!-- TRẠNG THÁI CÓ SẢN PHẨM -->
      <div v-else class="row g-4 g-lg-5 animation-fade-in">
        
        <!-- ========================================== -->
        <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM               -->
        <!-- ========================================== -->
        <div class="col-lg-8">
          
          <!-- THANH TIẾN TRÌNH FREESHIP NẰM NGAY ĐẦU TIÊN ĐỂ HÚT MẮT -->
          <div class="p-3 rounded-4 mb-4 shadow-sm border border-urban border-opacity-25" style="background-color: rgba(84, 119, 146, 0.08);">
             <div v-if="remainingForFreeship > 0">
               <div class="d-flex justify-content-between small fw-bold mb-2 text-dark dark:text-gray-200">
                 <span>Mua thêm <span class="text-urban fs-6">{{ formatCurrency(remainingForFreeship) }}</span> để được</span>
                 <span class="text-urban fw-bolder text-uppercase tracking-widest"><i class="bi bi-truck me-1"></i> FREESHIP</span>
               </div>
               <div class="progress bg-white dark:bg-[#1a2533] shadow-sm" style="height: 10px;">
                 <div class="progress-bar bg-urban progress-bar-striped progress-bar-animated" :style="{ width: freeshipProgress + '%' }"></div>
               </div>
             </div>
             <div v-else class="text-urban fw-bold text-center d-flex align-items-center justify-content-center py-1">
               <i class="bi bi-check-circle-fill me-2 fs-4"></i> Tuyệt vời! Đơn hàng của bạn đã đủ điều kiện Miễn phí giao hàng.
             </div>
          </div>

          <div class="d-flex justify-content-between align-items-end mb-3">
             <h5 class="fw-bold text-dark dark:text-white m-0">Sản phẩm trong giỏ ({{ cartStore.totalQuantity }})</h5>
          </div>

          <div class="cart-items-list border-top dark:border-gray-700">
            <!-- TỪNG ITEM SẢN PHẨM -->
            <div v-for="item in cartStore.items" :key="'cartItem'+item.variant_id" 
                 class="cart-item position-relative py-4 border-bottom dark:border-gray-700 px-2 transition-all">
              
              <!-- SPINNER 3 CHẤM BÁO HIỆU ĐANG CẬP NHẬT SỐ LƯỢNG/XÓA NỔI LÊN TRÊN -->
              <div v-if="updatingItemId === item.variant_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader" style="z-index: 10;">
                 <span></span><span></span><span></span>
              </div>

              <!-- LỚP BỌC NỘI DUNG: Mờ đi và khóa click khi đang cập nhật -->
              <div class="transition-all w-100" :class="{'pe-none opacity-50': updatingItemId === item.variant_id}">
                  
                  <!-- NÚT XÓA BỌC VIỀN ĐỎ NẰM GÓC TRÊN BÊN PHẢI -->
                  <button class="btn-delete-circle d-flex align-items-center justify-content-center transition-all" @click="removeItem(item)" title="Xóa khỏi giỏ">
                    <i class="bi bi-x-lg"></i>
                  </button>

                  <div class="row align-items-center position-relative">
                    
                    <!-- 1. THÔNG TIN SẢN PHẨM -->
                    <div class="col-12 col-md-7 mb-3 mb-md-0 pe-md-4">
                      <div class="d-flex align-items-start gap-3">
                        <img :src="item.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" 
                             class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light flex-shrink-0" style="width: 90px; height: 120px;">
                        <div class="flex-grow-1">
                          <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2 pe-5">
                            <router-link :to="`/product/${item.product_slug || item.product_id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color">
                               {{ item.product_name }}
                            </router-link>
                          </h6>
                          
                          <!-- Thuộc tính Màu/Size -->
                          <div class="d-flex flex-wrap gap-2 mt-1 mb-2">
                            <span class="d-inline-flex align-items-center bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-2 py-1 rounded small shadow-sm fw-medium">
                              {{ item.attributes || 'Mặc định' }}
                            </span>
                          </div>
                          
                          <div class="fw-bold text-muted dark:text-gray-400" style="font-size: 0.95rem;">
                            {{ formatCurrency(item.current_price) }}
                          </div>

                          <!-- Cảnh báo hết hàng -->
                          <div v-if="item.stock_warning" class="text-danger small mt-1 fw-medium" style="font-size: 0.75rem;">
                             <i class="bi bi-exclamation-triangle"></i> Số lượng vượt quá tồn kho
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 2. SỐ LƯỢNG & 3. THÀNH TIỀN -->
                    <div class="col-12 col-md-5 d-flex justify-content-between justify-content-md-end align-items-center gap-md-4 position-relative">
                      
                      <!-- Ô ĐIỀU CHỈNH SỐ LƯỢNG -->
                      <div class="quantity-box border dark:border-gray-600 rounded-pill d-inline-flex bg-light dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 38px;">
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="decreaseQty(item)"><i class="bi bi-dash"></i></button>
                        <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none" style="width: 40px; font-size: 0.9rem;" :value="item.quantity" readonly>
                        <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="increaseQty(item)"><i class="bi bi-plus"></i></button>
                      </div>

                      <!-- TỔNG TIỀN THEO ITEM -->
                      <div class="fw-bold text-dark dark:text-white fs-5 text-end" style="min-width: 100px;">
                         {{ formatCurrency(item.current_price * item.quantity) }}
                      </div>
                      
                    </div>

                  </div>
              </div>
            </div>
          </div>
          <!-- Kết thúc danh sách -->

        </div>

        <!-- ========================================== -->
        <!-- CỘT PHẢI: TỔNG KẾT ĐƠN HÀNG                -->
        <!-- ========================================== -->
        <div class="col-lg-4">
          <div class="card border border-light-subtle shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top bg-light" style="top: 100px;">
            <h5 class="fw-bold text-dark dark:text-white mb-4 border-bottom dark:border-gray-700 pb-3 text-uppercase tracking-wide">Tổng Kết Đơn Hàng</h5>

            <!-- Chi tiết tính tiền -->
            <div class="d-flex justify-content-between mb-3 text-dark dark:text-gray-300">
              <span>Tạm tính ({{ cartStore.totalQuantity }} SP)</span>
              <span class="fw-semibold">{{ formatCurrency(cartStore.totalPrice) }}</span>
            </div>
            
            <div class="d-flex justify-content-between mb-4 text-dark dark:text-gray-300">
              <span>Phí giao hàng</span>
              <span class="fw-semibold" v-if="remainingForFreeship > 0">Tính ở bước thanh toán</span>
              <span class="fw-bold text-urban" v-else>Miễn phí</span>
            </div>

            <!-- Tổng tiền -->
            <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top border-secondary-subtle dark:border-gray-700">
              <span class="fw-bold text-uppercase fs-5 text-dark dark:text-white">Tổng Cộng</span>
              <span class="fw-bold text-danger fs-3">{{ formatCurrency(cartStore.totalPrice) }}</span>
            </div>
            
            <small class="text-muted fst-italic d-block mb-4 text-end">(Đã bao gồm VAT)</small>

            <button class="btn btn-urban btn-lg w-100 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform" 
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

// Track item đang thao tác để hiện spinner cục bộ
const updatingItemId = ref(null);

// Config Freeship
const FREESHIP_THRESHOLD = 1000000;

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

// ========================================================
// ACTIONS GỌI PINIA STORE
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

const removeItem = (item) => {
  // Sử dụng Swal custom của Zyro
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

/* TIỆN ÍCH & ANIMATION */
.hover-text-dark:hover { color: #000 !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792) !important; color: white; border: none; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448) !important; color: white; }

.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.transition-color { transition: color 0.2s ease; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important; }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.sticky-top { transition: all 0.3s ease; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* NÚT XÓA TRÒN VIỀN ĐỎ NẰM GÓC PHẢI */
.btn-delete-circle {
  position: absolute;
  top: 1.5rem; 
  right: 0.5rem;
  width: 32px;
  height: 32px;
  border: 1.5px solid #dc3545;
  color: #dc3545;
  background: transparent;
  border-radius: 50%;
  padding: 0;
  z-index: 2;
  cursor: pointer;
}
.btn-delete-circle i { font-size: 0.85rem; font-weight: bold; }
.btn-delete-circle:hover { background: #dc3545; color: #fff; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2); }
html.dark .btn-delete-circle { border-color: #ef4444; color: #ef4444; }
html.dark .btn-delete-circle:hover { background: #ef4444; color: #fff; }

/* CSS HIỆU ỨNG 3 DẤU CHẤM (BOUNCING DOTS LOADER) NỔI BẬT LÊN TRÊN */
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