<!-- File: frontend/src/pages/client/cart/Index.vue -->
<template>
  <div class="cart-page-wrapper pb-5 mb-5">
    
    <!-- Đẩy nội dung xuống dưới Header -->
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">Giỏ hàng của bạn</li>
          </ol>
        </nav>

        <div class="d-flex align-items-end justify-content-between mb-4 pb-3 border-bottom dark:border-gray-700">
          <h2 class="fw-bold text-dark dark:text-white m-0 text-uppercase tracking-widest" style="letter-spacing: 2px;">Giỏ hàng</h2>
          <span class="text-muted fw-semibold">{{ cartItems.length }} sản phẩm</span>
        </div>

        <div v-if="cartItems.length === 0" class="text-center py-5 my-5 animation-fade-in">
          <div class="bg-light dark:bg-[#1a2533] rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 120px; height: 120px;">
            <i class="bi bi-bag-x text-muted opacity-50" style="font-size: 4rem;"></i>
          </div>
          <h4 class="fw-bold text-dark dark:text-white mb-3">Giỏ hàng trống</h4>
          <p class="text-muted mb-4">Bạn chưa chọn sản phẩm nào vào giỏ hàng.</p>
          <router-link to="/category" class="btn btn-urban text-white px-5 py-3 fw-bold rounded-pill shadow-sm transition-all hover-transform">
            Tiếp Tục Mua Sắm
          </router-link>
        </div>

        <div v-else class="row g-5 animation-fade-in">
          <!-- ========================================== -->
          <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM               -->
          <!-- ========================================== -->
          <div class="col-lg-8">
            
            <!-- THANH TIẾN TRÌNH FREESHIP (ĐÃ FIX MÀU PASTEL SANG TRỌNG) -->
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

            <!-- BẢNG SẢN PHẨM -->
            <div class="table-responsive">
              <table class="table table-borderless align-middle mb-0">
                <thead class="border-bottom dark:border-gray-700">
                  <tr>
                    <th class="text-uppercase text-muted small fw-bold pb-3" style="width: 50%;">Sản phẩm</th>
                    <th class="text-uppercase text-muted small fw-bold pb-3 text-center" style="width: 15%;">Đơn giá</th>
                    <th class="text-uppercase text-muted small fw-bold pb-3 text-center" style="width: 20%;">Số lượng</th>
                    <th class="text-uppercase text-muted small fw-bold pb-3 text-end" style="width: 15%;">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in cartItems" :key="item.id" class="border-bottom dark:border-gray-700">
                    <td class="py-4">
                      <div class="d-flex align-items-center gap-3">
                        <!-- ĐÃ FIX: Gỡ bỏ nút X màu đỏ đè lên ảnh -->
                        <div class="position-relative">
                          <img :src="item.image" class="rounded-3 border dark:border-gray-600 object-fit-cover shadow-sm bg-light" style="width: 90px; height: 120px;">
                        </div>
                        <div>
                          <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2">
                            <router-link :to="`/product/${item.id}`" class="text-decoration-none text-dark dark:text-gray-200 hover-text-urban transition-color">{{ item.name }}</router-link>
                          </h6>
                          <!-- ĐÃ FIX: Biến thể có thêm dấu chấm màu sắc -->
                          <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="d-inline-flex align-items-center bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-2 py-1 rounded small shadow-sm fw-medium">
                              <span class="rounded-circle me-2 border dark:border-gray-500" :style="{ width: '12px', height: '12px', backgroundColor: item.hex }"></span>
                              {{ item.color }}
                            </span>
                            <span class="d-inline-flex align-items-center bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-2 py-1 rounded small shadow-sm fw-medium">
                              Size: {{ item.size }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="py-4 text-center">
                      <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.price) }}</span>
                    </td>
                    <td class="py-4 text-center">
                      <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                        <div class="quantity-box border dark:border-gray-600 rounded-pill d-inline-flex bg-light dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 38px;">
                          <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="item.quantity > 1 ? item.quantity-- : null"><i class="bi bi-dash"></i></button>
                          <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none" style="width: 40px; font-size: 0.9rem;" v-model="item.quantity" readonly>
                          <button class="btn p-0 border-0 text-dark dark:text-gray-300 d-flex align-items-center justify-content-center hover-urban transition-color" style="width: 35px;" @click="item.quantity++"><i class="bi bi-plus"></i></button>
                        </div>
                        <!-- Nút Xóa (Thùng rác) tinh tế -->
                        <button class="btn btn-link text-muted small p-0 text-decoration-none hover-danger transition-color" style="font-size: 0.8rem;" @click="removeItem(index)">
                          <i class="bi bi-trash3 me-1"></i> Xóa
                        </button>
                      </div>
                    </td>
                    <td class="py-4 text-end">
                      <span class="fw-bold text-dark fs-6">{{ formatCurrency(item.price * item.quantity) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- TÙY CHỌN XUẤT HÓA ĐƠN VAT -->
            <div class="mt-4 pt-3 border-top dark:border-gray-700">
              <div class="form-check d-flex align-items-center mb-3 p-0">
                <input class="form-check-input ms-0 me-2 cursor-pointer fs-5" type="checkbox" id="requireVAT" v-model="requireVAT">
                <label class="form-check-label fw-bold text-dark dark:text-gray-200 cursor-pointer" for="requireVAT">
                  Yêu cầu xuất hóa đơn đỏ (VAT) cho Công ty
                </label>
              </div>

              <!-- Form nhập thông tin VAT (Mở ra khi check) -->
              <transition name="slide-fade">
                <div v-if="requireVAT" class="bg-light dark:bg-[#212529] p-4 rounded-4 border border-light-subtle dark:border-gray-700 mt-2 shadow-sm">
                  <div class="row g-3">
                    <div class="col-md-12">
                      <label class="form-label small fw-bold text-muted">Tên công ty <span class="text-danger">*</span></label>
                      <input type="text" class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-0 shadow-sm-hover" v-model="vatInfo.company_name" placeholder="Nhập tên công ty">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Mã số thuế <span class="text-danger">*</span></label>
                      <input type="text" class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-0 shadow-sm-hover" v-model="vatInfo.tax_code" placeholder="Nhập mã số thuế">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Email nhận hóa đơn</label>
                      <input type="email" class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-0 shadow-sm-hover" v-model="vatInfo.email" placeholder="Email kế toán (Tùy chọn)">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label small fw-bold text-muted">Địa chỉ công ty <span class="text-danger">*</span></label>
                      <input type="text" class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-0 shadow-sm-hover" v-model="vatInfo.address" placeholder="Nhập địa chỉ theo ĐKKD">
                    </div>
                  </div>
                </div>
              </transition>
            </div>
          </div>

          <!-- ========================================== -->
          <!-- CỘT PHẢI: TỔNG KẾT ĐƠN HÀNG (SUMMARY)      -->
          <!-- ========================================== -->
          <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top bg-light" style="top: 100px;">
              <h5 class="fw-bold text-dark dark:text-white mb-4 border-bottom dark:border-gray-700 pb-3 text-uppercase tracking-wide">Tổng Kết Đơn Hàng</h5>

              <!-- Mã Giảm Giá -->
              <div class="mb-4">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">Mã ưu đãi / Khuyến mãi</label>
                <div class="input-group shadow-sm-hover border border-secondary-subtle rounded-3 overflow-hidden bg-white">
                  <span class="input-group-text bg-white dark:bg-[#212529] border-0 text-muted"><i class="bi bi-ticket-perforated"></i></span>
                  <input type="text" class="form-control bg-white dark:bg-[#212529] border-0 dark:text-white font-monospace text-uppercase shadow-none" v-model="promoCode" placeholder="Nhập mã ZYRO...">
                  <button class="btn btn-dark fw-bold px-4 rounded-0" type="button" @click="applyPromo">Áp dụng</button>
                </div>
              </div>

              <!-- Chi tiết tính tiền -->
              <div class="d-flex justify-content-between mb-3 text-dark dark:text-gray-300">
                <span>Tạm tính ({{ totalItems }} SP)</span>
                <span class="fw-semibold">{{ formatCurrency(cartTotal) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-3 text-success" v-if="discountAmount > 0">
                <span>Giảm giá (Voucher)</span>
                <span class="fw-bold">- {{ formatCurrency(discountAmount) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-4 text-dark dark:text-gray-300">
                <span>Phí giao hàng</span>
                <span class="fw-semibold" v-if="remainingForFreeship > 0">Tính ở bước thanh toán</span>
                <span class="fw-bold text-urban" v-else>Miễn phí</span>
              </div>

              <!-- Tổng tiền -->
              <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top border-secondary-subtle dark:border-gray-700">
                <span class="fw-bold text-uppercase fs-5 text-dark dark:text-white">Tổng Cộng</span>
                <span class="fw-bold text-dark fs-3">{{ formatCurrency(finalTotal) }}</span>
              </div>
              
              <small class="text-muted fst-italic d-block mb-4 text-end">(Đã bao gồm VAT)</small>

              <!-- ĐÃ FIX: Nút Checkout đổi sang màu Dark (Đen) sang trọng -->
              <button class="btn btn-dark btn-lg w-100 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg hover-transform" @click="proceedToCheckout">
                Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
              </button>

              <!-- Trust Badges -->
              <div class="mt-4 pt-3 border-top border-secondary-subtle dark:border-gray-700 d-flex justify-content-center gap-3 opacity-75">
                <i class="bi bi-shield-check fs-4 text-dark" title="Thanh toán an toàn 100%"></i>
                <i class="bi bi-arrow-return-left fs-4 text-dark" title="Đổi trả dễ dàng"></i>
                <i class="bi bi-credit-card-fill fs-4 text-dark" title="Đa dạng phương thức"></i>
              </div>
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
import Swal from 'sweetalert2';

const router = useRouter();

// Mock Data Giỏ hàng (Đã bổ sung mã Hex màu sắc để làm Color dot)
const cartItems = ref([
  { id: 101, name: 'Sơ Mi Nam Cộc Tay Cafe Túi Ngực Chống Nhăn', price: 469000, quantity: 1, color: 'Xám sáng', hex: '#bdc3c7', size: 'M', image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=300&auto=format&fit=crop' },
  { id: 204, name: 'Áo Thun Typography Nữ Mùa Hè', price: 199000, quantity: 2, color: 'Đen nhám', hex: '#2c3e50', size: 'S', image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=300&auto=format&fit=crop' },
]);

// Config Freeship
const FREESHIP_THRESHOLD = 1000000;

// VAT Logic
const requireVAT = ref(false);
const vatInfo = ref({ company_name: '', tax_code: '', email: '', address: '' });

// Promo Logic
const promoCode = ref('');
const discountAmount = ref(0);

// Computed Totals
const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const finalTotal = computed(() => {
  const total = cartTotal.value - discountAmount.value;
  return total > 0 ? total : 0;
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
  Swal.fire({
    title: 'Bỏ sản phẩm này?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#212529',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý',
    cancelButtonText: 'Giữ lại'
  }).then((result) => {
    if (result.isConfirmed) {
      cartItems.value.splice(index, 1);
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã xóa khỏi giỏ hàng', showConfirmButton: false, timer: 1500 });
    }
  });
};

const applyPromo = () => {
  if (!promoCode.value) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Vui lòng nhập mã', showConfirmButton: false, timer: 2000 });
    return;
  }
  if (promoCode.value.toUpperCase() === 'ZYRO304') {
    discountAmount.value = 50000;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Áp dụng mã thành công! Giảm 50.000đ', showConfirmButton: false, timer: 2000 });
  } else {
    discountAmount.value = 0;
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Mã không hợp lệ hoặc đã hết hạn', showConfirmButton: false, timer: 2000 });
  }
};

const proceedToCheckout = () => {
  if (requireVAT.value) {
    if (!vatInfo.value.company_name || !vatInfo.value.tax_code || !vatInfo.value.address) {
      Swal.fire('Thiếu thông tin', 'Vui lòng điền đầy đủ các trường bắt buộc trong form Xuất hóa đơn VAT.', 'warning');
      return;
    }
  }
  // Gắn loading giả lập
  Swal.fire({ title: 'Đang chuyển hướng...', text: 'Vui lòng đợi giây lát', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
  setTimeout(() => {
    Swal.close();
    router.push('/checkout'); // Chuyển sang trang Checkout
  }, 800);
};

onMounted(() => {
  window.scrollTo(0, 0);
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
  .zyro-container {
    padding-left: 0;
    padding-right: 0;
  }
}

/* TIỆN ÍCH & ANIMATION */
.hover-text-dark:hover { color: #000 !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }

.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.hover-danger:hover { color: #dc3545 !important; }
.transition-color { transition: color 0.2s ease; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }

.cursor-pointer { cursor: pointer; }
.sticky-top { transition: all 0.3s ease; }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* Slide fade cho Form VAT */
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }
</style>