<template>
  <div class="checkout-failed-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
        
        <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 text-center animation-shake" style="max-width: 550px; width: 100%;">
          
          <div class="mb-4 mx-auto bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-danger border-opacity-25" style="width: 100px; height: 100px;">
            <i class="bi bi-x-lg" style="font-size: 3.5rem;"></i>
          </div>

          <h2 class="fw-bold text-danger mb-3 text-uppercase tracking-wide">Thanh Toán Thất Bại!</h2>
          <p class="text-muted dark:text-gray-400 mb-4">
            Rất tiếc, giao dịch của bạn không thể hoàn tất do lỗi kết nối hoặc thao tác bị hủy. Đơn hàng <strong v-if="orderCode" class="text-dark dark:text-white">#{{ orderCode }}</strong> chưa được thanh toán thành công.
          </p>

          <div class="bg-light dark:bg-[#212529] p-3 rounded-4 border dark:border-gray-700 mb-4 text-start shadow-sm mx-auto w-100">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-info-circle-fill text-warning mt-1"></i>
              <small class="text-muted dark:text-gray-300">
                <strong>Gợi ý:</strong> Vui lòng kiểm tra lại số dư tài khoản, hạn mức thanh toán trong ngày, hoặc thử chọn phương thức "Thanh toán khi nhận hàng (COD)" ở bước tiếp theo để đảm bảo mua được sản phẩm.
              </small>
            </div>
          </div>

          <div class="d-flex flex-column gap-2 mt-2">
            <router-link to="/checkout" class="btn btn-c-dark text-white rounded-pill py-3 fw-bold shadow-sm transition-all hover-transform w-100 text-uppercase tracking-wide fs-6">
              <i class="bi bi-arrow-repeat me-1"></i> Thử Lại Thanh Toán
            </router-link>
            <router-link to="/cart" class="btn btn-link text-muted dark:text-gray-400 text-decoration-none fw-semibold transition-all hover-text-dark mt-2">
              Quay lại Giỏ hàng
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
const orderCode = ref(route.query.order || '');

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

.btn-c-dark { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.2s ease; }
.btn-c-dark:hover { background-color: var(--color-c-hover, #547792); color: white; }

.hover-text-dark:hover { color: var(--color-c-dark) !important; text-decoration: underline !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }

.tracking-wide { letter-spacing: 1px; }

.animation-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>