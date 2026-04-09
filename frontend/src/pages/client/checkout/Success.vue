<!-- File: frontend/src/pages/client/checkout/Success.vue -->
<template>
  <div class="checkout-success-wrapper pb-5 mb-5">
    <!-- Đẩy nội dung xuống dưới Header (Khoảng 90px) -->
    <div class="pt-5 mt-4">
      <div class="zyro-container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
        
        <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 text-center animation-fade-up" style="max-width: 600px; width: 100%;">
          
          <!-- Icon Thành Công -->
          <div class="mb-4 position-relative mx-auto" style="width: 100px; height: 100px;">
            <div class="icon-pulse position-absolute top-50 start-50 translate-middle rounded-circle bg-success opacity-25"></div>
            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center position-relative shadow-sm border border-success border-opacity-25" style="width: 100px; height: 100px; z-index: 2;">
              <i class="bi bi-check-lg" style="font-size: 3.5rem;"></i>
            </div>
          </div>

          <h2 class="fw-bold text-c-dark dark:text-white mb-3 text-uppercase tracking-wide">Đặt Hàng Thành Công!</h2>
          <p class="text-muted dark:text-gray-400 mb-4 px-md-3">
            Cảm ơn bạn đã tin tưởng và mua sắm tại <strong class="text-c-dark dark:text-white">ZYRO</strong>. Đơn hàng của bạn đang được xử lý và sẽ sớm được giao đến bạn.
          </p>

          <!-- Tóm tắt nhanh -->
          <div class="bg-light dark:bg-[#212529] p-4 rounded-4 border dark:border-gray-700 mb-4 text-start shadow-sm mx-auto w-100">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Mã đơn hàng:</span>
              <span class="fw-bold text-c-dark dark:text-white font-monospace">{{ orderCode }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Phương thức thanh toán:</span>
              <span class="fw-semibold text-c-dark dark:text-gray-300">{{ paymentMethod }}</span>
            </div>
            <div class="d-flex justify-content-between pt-2 mt-2 border-top dark:border-gray-600">
              <span class="text-muted small fw-bold text-uppercase">Tổng thanh toán:</span>
              <span class="fw-bold text-danger fs-5">{{ formatCurrency(totalAmount) }}</span>
            </div>
          </div>

          <!-- Điều hướng -->
          <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-2">
            <router-link to="/user/orders" class="btn btn-outline-secondary dark:text-gray-300 dark:border-gray-600 dark:bg-[#2b3035] rounded-pill px-4 py-2.5 fw-bold transition-all hover-c-dark flex-grow-1">
              <i class="bi bi-receipt me-1"></i> Xem Đơn Hàng
            </router-link>
            <router-link to="/" class="btn btn-c-dark text-white rounded-pill px-4 py-2.5 fw-bold shadow-sm transition-all hover-transform flex-grow-1">
              Tiếp Tục Mua Sắm <i class="bi bi-arrow-right ms-1"></i>
            </router-link>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// Có thể lấy các giá trị này từ query URL hoặc Pinia/Vuex Store sau khi thanh toán xong
const orderCode = ref(route.query.order_code || 'ZYRO-' + Math.floor(Math.random() * 1000000));
const paymentMethod = ref(route.query.method === 'vnpay' ? 'Thanh toán qua VNPAY' : 'Thanh toán khi nhận hàng (COD)');
const totalAmount = ref(route.query.amount || 1066000); // Mock data tạm thời

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<style scoped>
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

.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }

.btn-c-dark { background-color: var(--color-c-dark); color: white; border: none; transition: 0.2s ease; }
.btn-c-dark:hover { background-color: var(--color-c-hover); color: white; }
.hover-c-dark:hover { border-color: var(--color-c-dark) !important; color: var(--color-c-dark) !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }

.tracking-wide { letter-spacing: 1px; }

/* Animation Icon Nhịp đập */
.icon-pulse {
  width: 100px;
  height: 100px;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% { transform: translate(-50%, -50%) scale(0.95); opacity: 0.5; }
  50% { transform: translate(-50%, -50%) scale(1.3); opacity: 0; }
  100% { transform: translate(-50%, -50%) scale(0.95); opacity: 0; }
}

.animation-fade-up { animation: fadeUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>