<!-- File: frontend/src/pages/client/checkout/Index.vue -->
<template>
  <div class="checkout-page-wrapper pb-5 mb-5">
    
    <!-- Đẩy nội dung xuống dưới Header (Khoảng 90px) -->
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/cart" class="text-decoration-none text-muted hover-text-dark">Giỏ hàng</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Thanh toán</li>
          </ol>
        </nav>

        <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
          <h2 class="fw-bold text-c-dark dark:text-white m-0 text-uppercase tracking-widest" style="letter-spacing: 2px;">Thanh toán an toàn</h2>
        </div>

        <form @submit.prevent="placeOrder" autocomplete="off">
          <div class="row g-5">
            
            <!-- ========================================== -->
            <!-- CỘT TRÁI: THÔNG TIN GIAO HÀNG & THANH TOÁN -->
            <!-- ========================================== -->
            <div class="col-lg-7">
              
              <!-- 1. THÔNG TIN LIÊN HỆ & GIAO HÀNG -->
              <div class="mb-5">
                <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center">
                  <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">1</span> 
                  Thông tin giao hàng
                </h5>
                
                <div class="row g-3">
                  <div class="col-md-12">
                    <label class="form-label small fw-bold text-muted text-uppercase">Họ và tên người nhận <span class="text-danger">*</span></label>
                    <input type="text" class="form-control custom-input" v-model="form.fullname" required placeholder="Nhập họ và tên">
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control custom-input" v-model="form.phone" required placeholder="Nhập số điện thoại">
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted text-uppercase">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control custom-input" v-model="form.email" required placeholder="Nhập địa chỉ email">
                  </div>

                  <!-- ĐỊA CHỈ DROPDOWN (Đã sửa placeholder đẹp và gỡ form-select-lg) -->
                  <div class="col-md-4 mt-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                    <select class="form-select custom-input fw-semibold" :class="{'placeholder-active': !addressHelper.province}" v-model="addressHelper.province" @change="onProvinceChange" required>
                      <option value="" disabled selected hidden>Chọn Tỉnh/Thành</option>
                      <option v-for="p in provinces" :key="p.code" :value="p.name">{{ p.name }}</option>
                    </select>
                  </div>
                  <div class="col-md-4 mt-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Quận/Huyện <span class="text-danger">*</span></label>
                    <select class="form-select custom-input fw-semibold" :class="{'placeholder-active': !addressHelper.district}" v-model="addressHelper.district" @change="onDistrictChange" :disabled="!addressHelper.province" required>
                      <option value="" disabled selected hidden>Chọn Quận/Huyện</option>
                      <option v-for="d in districts" :key="d.code" :value="d.name">{{ d.name }}</option>
                    </select>
                  </div>
                  <div class="col-md-4 mt-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Phường/Xã <span class="text-danger">*</span></label>
                    <select class="form-select custom-input fw-semibold" :class="{'placeholder-active': !addressHelper.ward}" v-model="addressHelper.ward" :disabled="!addressHelper.district" required>
                      <option value="" disabled selected hidden>Chọn Phường/Xã</option>
                      <option v-for="w in wards" :key="w.code" :value="w.name">{{ w.name }}</option>
                    </select>
                  </div>
                  
                  <div class="col-12 mt-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                    <input type="text" class="form-control custom-input" v-model="addressHelper.detail" required placeholder="Nhập số nhà, tên đường">
                  </div>

                  <div class="col-12 mt-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú đơn hàng</label>
                    <textarea class="form-control custom-input" rows="2" v-model="form.note" placeholder="Nhập ghi chú (nếu có)"></textarea>
                  </div>
                </div>
              </div>

              <!-- 2. PHƯƠNG THỨC VẬN CHUYỂN -->
              <div class="mb-5">
                <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center">
                  <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">2</span> 
                  Phương thức vận chuyển
                </h5>
                
                <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card active-card" for="shipStandard">
                  <div class="card-body p-3 d-flex align-items-center">
                    <div class="form-check m-0 d-flex align-items-center w-100">
                      <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="shippingMethod" id="shipStandard" checked>
                      <div class="w-100 cursor-pointer d-flex justify-content-between align-items-center">
                        <div>
                          <div class="fw-bold text-c-dark dark:text-white fs-6">Giao hàng tiêu chuẩn</div>
                          <div class="small text-muted dark:text-gray-400">Thời gian giao hàng từ 2-4 ngày làm việc.</div>
                        </div>
                        <div class="fw-bold text-c-hover fs-5" v-if="shippingFee === 0">Miễn phí</div>
                        <div class="fw-bold text-c-dark dark:text-white fs-5" v-else>{{ formatCurrency(shippingFee) }}</div>
                      </div>
                    </div>
                  </div>
                </label>
              </div>

              <!-- 3. PHƯƠNG THỨC THANH TOÁN -->
              <div class="mb-5">
                <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center">
                  <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">3</span> 
                  Phương thức thanh toán
                </h5>
                
                <div class="d-flex flex-column gap-3">
                  <!-- COD -->
                  <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card" :class="form.payment_method === 'cod' ? 'active-card' : 'inactive-card'">
                    <div class="card-body p-3 d-flex align-items-center">
                      <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="paymentMethod" value="cod" v-model="form.payment_method">
                      <div class="d-flex align-items-center gap-3 w-100">
                        <div class="icon-box rounded p-2 d-flex align-items-center justify-content-center">
                          <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-c-dark dark:text-white fs-6">Thanh toán khi nhận hàng (COD)</div>
                          <div class="small text-muted dark:text-gray-400">Thanh toán bằng tiền mặt khi shipper giao hàng tới.</div>
                        </div>
                      </div>
                    </div>
                  </label>

                  <!-- VNPAY -->
                  <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card" :class="form.payment_method === 'vnpay' ? 'active-card' : 'inactive-card'">
                    <div class="card-body p-3 d-flex align-items-center">
                      <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="paymentMethod" value="vnpay" v-model="form.payment_method">
                      <div class="d-flex align-items-center gap-3 w-100">
                        <div class="icon-box rounded p-2 d-flex align-items-center justify-content-center">
                          <i class="bi bi-qr-code fs-3"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-c-dark dark:text-white fs-6">Thanh toán qua VNPAY</div>
                          <div class="small text-muted dark:text-gray-400">Quét mã QR qua ứng dụng ngân hàng hoặc ví điện tử.</div>
                        </div>
                      </div>
                    </div>
                  </label>
                  
                  <!-- Thẻ Tín Dụng -->
                  <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card" :class="form.payment_method === 'card' ? 'active-card' : 'inactive-card'">
                    <div class="card-body p-3 d-flex align-items-center">
                      <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="paymentMethod" value="card" v-model="form.payment_method">
                      <div class="d-flex align-items-center gap-3 w-100">
                        <div class="icon-box rounded p-2 d-flex align-items-center justify-content-center">
                          <i class="bi bi-credit-card-2-front fs-3"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-c-dark dark:text-white fs-6">Thẻ tín dụng / Ghi nợ</div>
                          <div class="small text-muted dark:text-gray-400">Hỗ trợ Visa, Mastercard, JCB.</div>
                        </div>
                      </div>
                    </div>
                  </label>
                </div>
              </div>

              <!-- 4. YÊU CẦU XUẤT HÓA ĐƠN VAT -->
              <div class="mb-5">
                <div class="form-check d-flex align-items-center mb-3 p-0">
                  <input class="form-check-input ms-0 me-2 custom-radio fs-5" type="checkbox" id="requireVAT" v-model="requireVAT">
                  <label class="form-check-label fw-bold text-c-dark dark:text-gray-200 cursor-pointer" for="requireVAT">
                    Yêu cầu xuất hóa đơn đỏ (VAT) cho Công ty
                  </label>
                </div>

                <transition name="slide-fade">
                  <div v-if="requireVAT" class="p-4 rounded-4 vat-box mt-2 shadow-sm">
                    <div class="row g-3">
                      <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Tên công ty <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="vatInfo.company_name" placeholder="Nhập tên công ty">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Mã số thuế <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="vatInfo.tax_code" placeholder="Nhập mã số thuế">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Email nhận hóa đơn</label>
                        <input type="email" class="form-control custom-input" v-model="vatInfo.email" placeholder="Nhập email nhận hóa đơn">
                      </div>
                      <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Địa chỉ công ty <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="vatInfo.address" placeholder="Nhập địa chỉ công ty">
                      </div>
                    </div>
                  </div>
                </transition>
              </div>

            </div>

            <!-- ========================================== -->
            <!-- CỘT PHẢI: TỔNG KẾT ĐƠN HÀNG (STICKY)       -->
            <!-- ========================================== -->
            <div class="col-lg-5">
              <div class="card shadow-sm rounded-4 summary-box sticky-top p-4 p-md-5" style="top: 100px;">
                <h5 class="fw-bold text-c-dark dark:text-white mb-4 border-bottom border-light-subtle dark:border-gray-700 pb-3 text-uppercase tracking-wide">Tổng Kết Đơn Hàng</h5>

                <!-- DANH SÁCH SẢN PHẨM TRONG GIỎ -->
                <div class="custom-scrollbar-y pe-2 mb-4" style="max-height: 350px; overflow-y: auto;">
                  <div v-for="(item, index) in cartItems" :key="item.id" class="d-flex gap-3 mb-3 pb-3 border-bottom border-light-subtle dark:border-gray-700 position-relative">
                    <div class="position-relative">
                      <img :src="item.image" class="rounded-3 border border-light-subtle dark:border-gray-600 object-fit-cover bg-white" style="width: 65px; height: 85px;">
                      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-c-dark text-white border border-white">{{ item.quantity }}</span>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="fw-bold text-c-dark dark:text-gray-200 mb-1" style="font-size: 0.85rem;">{{ item.name }}</h6>
                      <div class="text-muted small mb-2 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                        <span class="d-inline-flex align-items-center"><span class="rounded-circle me-1 border" :style="{ width: '8px', height: '8px', backgroundColor: item.hex }"></span>{{ item.color }}</span>
                        <span>|</span>
                        <span>Size: {{ item.size }}</span>
                      </div>
                      <div class="fw-bold text-c-dark dark:text-gray-300">{{ formatCurrency(item.price) }}</div>
                    </div>
                  </div>
                </div>

                <!-- MÃ GIẢM GIÁ -->
                <div class="mb-4">
                  <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">Mã ưu đãi / Khuyến mãi</label>
                  <div class="input-group custom-input-group rounded-3 overflow-hidden bg-white dark:bg-[#212529]">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-ticket-perforated"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 dark:text-white font-monospace text-uppercase shadow-none" v-model="promoCode" placeholder="Nhập mã ZYRO...">
                    <button class="btn btn-c-dark fw-bold px-4 rounded-0" type="button" @click="applyPromo">Áp dụng</button>
                  </div>
                </div>

                <!-- CHI TIẾT TÍNH TIỀN -->
                <div class="d-flex justify-content-between mb-2 text-c-dark dark:text-gray-300 small">
                  <span>Tạm tính ({{ totalItems }} SP)</span>
                  <span class="fw-semibold">{{ formatCurrency(cartTotal) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success small" v-if="discountAmount > 0">
                  <span>Giảm giá (Voucher)</span>
                  <span class="fw-bold">- {{ formatCurrency(discountAmount) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-4 text-c-dark dark:text-gray-300 small">
                  <span>Phí vận chuyển</span>
                  <span class="fw-bold text-c-hover" v-if="shippingFee === 0">Miễn phí</span>
                  <span class="fw-bold" v-else>{{ formatCurrency(shippingFee) }}</span>
                </div>

                <!-- TỔNG CỘNG -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light-subtle dark:border-gray-700 mb-4">
                  <span class="fw-bold text-uppercase fs-5 text-c-dark dark:text-white">Tổng Cộng</span>
                  <div class="text-end">
                    <span class="fw-bold text-danger fs-3 d-block lh-1 mb-1">{{ formatCurrency(finalTotal) }}</span>
                    <small class="text-muted fst-italic" style="font-size: 0.7rem;">(Đã bao gồm VAT)</small>
                  </div>
                </div>

                <!-- NÚT CHỐT ĐƠN CHUẨN MÀU THƯƠNG HIỆU -->
                <button type="submit" class="btn btn-c-dark btn-lg w-100 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg mt-2" :disabled="isProcessing">
                  <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
                  <template v-else>ĐẶT HÀNG <i class="bi bi-bag-check ms-2"></i></template>
                </button>

                <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.7rem;">
                  Bằng việc bấm Đặt hàng, bạn đồng ý với <router-link to="#" class="text-c-dark dark:text-gray-300 text-decoration-underline">Điều khoản sử dụng</router-link> của ZYRO.
                </p>

                <!-- Trust Badges -->
                <div class="mt-4 pt-3 border-top border-light-subtle dark:border-gray-700 d-flex justify-content-center gap-3 opacity-75">
                  <i class="bi bi-shield-check fs-4 text-c-dark dark:text-gray-400" title="Thanh toán an toàn 100%"></i>
                  <i class="bi bi-arrow-return-left fs-4 text-c-dark dark:text-gray-400" title="Đổi trả dễ dàng"></i>
                  <i class="bi bi-credit-card-fill fs-4 text-c-dark dark:text-gray-400" title="Đa dạng phương thức"></i>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const isProcessing = ref(false);

// Form data
const form = ref({
  fullname: '',
  phone: '',
  email: '',
  note: '',
  payment_method: 'cod'
});

// Mock Data Giỏ hàng
const cartItems = ref([
  { id: 101, name: 'Sơ Mi Nam Cộc Tay Cafe Túi Ngực Chống Nhăn', price: 469000, quantity: 1, color: 'Xám sáng', hex: '#bdc3c7', size: 'M', image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=200&auto=format&fit=crop' },
  { id: 204, name: 'Áo Thun Typography Nữ Mùa Hè', price: 199000, quantity: 2, color: 'Đen nhám', hex: '#2c3e50', size: 'S', image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=200&auto=format&fit=crop' },
]);

// Cấu hình tính toán
const FREESHIP_THRESHOLD = 1000000;
const SHIPPING_COST = 30000;

const promoCode = ref('');
const discountAmount = ref(0);

// VAT Logic
const requireVAT = ref(false);
const vatInfo = ref({ company_name: '', tax_code: '', email: '', address: '' });

// Address Dropdown Logic
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

// Gọi API lấy tỉnh thành
const fetchProvinces = async () => {
  try {
    const res = await axios.get('https://provinces.open-api.vn/api/p/');
    provinces.value = res.data;
  } catch (err) { console.error("Lỗi lấy Tỉnh thành"); }
};

const onProvinceChange = async () => {
  addressHelper.district = ''; addressHelper.ward = ''; districts.value = []; wards.value = [];
  const p = provinces.value.find(i => i.name === addressHelper.province);
  if (p) {
    const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`);
    districts.value = res.data.districts;
  }
};

const onDistrictChange = async () => {
  addressHelper.ward = ''; wards.value = [];
  const d = districts.value.find(i => i.name === addressHelper.district);
  if (d) {
    const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`);
    wards.value = res.data.wards;
  }
};

// Computed
const totalItems = computed(() => cartItems.value.reduce((total, item) => total + item.quantity, 0));
const cartTotal = computed(() => cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0));
const shippingFee = computed(() => cartTotal.value >= FREESHIP_THRESHOLD ? 0 : SHIPPING_COST);
const finalTotal = computed(() => {
  const total = cartTotal.value + shippingFee.value - discountAmount.value;
  return total > 0 ? total : 0;
});

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const applyPromo = () => {
  if (!promoCode.value) return;
  if (promoCode.value.toUpperCase() === 'ZYRO304') {
    discountAmount.value = 50000;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Áp dụng mã thành công!', showConfirmButton: false, timer: 2000 });
  } else {
    discountAmount.value = 0;
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Mã không hợp lệ', showConfirmButton: false, timer: 2000 });
  }
};

const placeOrder = () => {
  if (!addressHelper.province || !addressHelper.district || !addressHelper.ward || !addressHelper.detail) {
    Swal.fire('Thiếu thông tin', 'Vui lòng chọn đầy đủ địa chỉ giao hàng.', 'warning');
    return;
  }

  isProcessing.value = true;
  
  // Giả lập thời gian xử lý và chuyển sang trang Success
  setTimeout(() => {
    isProcessing.value = false;
    router.push('/checkout/success'); 
  }, 1500);
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProvinces();
});
</script>

<style scoped>
.checkout-page-wrapper { width: 100%; }

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

/* =======================================================
   MÀU SẮC ĐỒNG BỘ TỪ APP.VUE
======================================================== */
.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-c-hover { color: var(--color-c-hover) !important; }
.bg-c-dark { background-color: var(--color-c-dark) !important; color: white; }
.btn-c-dark { background-color: var(--color-c-dark); color: white; border: none; transition: 0.2s ease; }
.btn-c-dark:hover { background-color: var(--color-c-hover); color: white; }

.step-number {
  background-color: var(--color-c-dark);
  color: white;
}

/* =======================================================
   KHẮC PHỤC TRIỆT ĐỂ LỖI NHÚN NHẢY & GIAO DIỆN "THÔ"
======================================================== */
/* Input chung */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); /* Viền mỏng thanh lịch */
  color: var(--color-c-dark);
  padding: 0.65rem 1rem; /* Kích thước cân đối, không to như form-control-lg */
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important; /* Cấm Bootstrap can thiệp bóng */
}
html.dark .custom-input {
  background-color: #1a2533;
  border-color: #373b3e;
  color: white;
}
.custom-input:focus, .custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  outline: none;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; /* Glow nhẹ nhàng */
}
html.dark .custom-input:focus {
  background-color: #212529;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important;
}

/* Fix riêng cho Select (Dropdown) */
select.custom-input {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23547792' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 16px 12px;
  padding-right: 2.5rem;
}
html.dark select.custom-input {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

/* Màu chữ cho Placeholder của Select */
select.custom-input.placeholder-active {
  color: #6c757d; 
  font-weight: 400 !important;
}
html.dark select.custom-input.placeholder-active { color: #adb5bd; }

/* Nút Radio Card (Thanh toán / Vận chuyển) */
.payment-method-card {
  border: 1.5px solid var(--color-c-effect); 
  background-color: #ffffff;
  transition: all 0.2s ease-in-out;
}
html.dark .payment-method-card {
  border-color: #373b3e;
  background-color: transparent;
}
.payment-method-card:hover {
  border-color: var(--color-c-light);
}

.active-card {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect) !important; 
}
html.dark .active-card {
  border-color: var(--color-c-light) !important;
  background-color: rgba(148, 180, 193, 0.1) !important;
}

.icon-box {
  width: 45px; 
  height: 45px;
  background-color: #ffffff;
  color: var(--color-c-hover);
  border: 1.5px solid var(--color-c-effect);
  transition: all 0.2s ease;
}
html.dark .icon-box {
  background-color: #212529;
  border-color: #373b3e !important;
}
.active-card .icon-box {
  color: var(--color-c-dark);
  border-color: var(--color-c-hover) !important;
}

/* Custom Radio Button cho tiệp màu */
.custom-radio {
  cursor: pointer;
  border-color: var(--color-c-light);
}
.custom-radio:checked {
  background-color: var(--color-c-hover);
  border-color: var(--color-c-hover);
}

/* Input Group (Mã giảm giá) */
.custom-input-group {
  border: 1.5px solid var(--color-c-light);
  transition: all 0.2s ease-in-out;
}
.custom-input-group:focus-within {
  border-color: var(--color-c-hover);
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2);
}
html.dark .custom-input-group { border-color: #373b3e; }
html.dark .custom-input-group:focus-within { border-color: var(--color-c-light); box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1); }

/* Box Tổng kết & VAT */
.summary-box, .vat-box {
  background-color: var(--color-c-effect);
  border: 1.5px solid transparent;
}
html.dark .summary-box, html.dark .vat-box {
  background-color: #1a2533;
  border-color: #373b3e;
}

/* Tiện ích */
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }
.cursor-pointer { cursor: pointer; }
.sticky-top { transition: all 0.3s ease; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* Slide fade cho Form VAT */
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }
</style>