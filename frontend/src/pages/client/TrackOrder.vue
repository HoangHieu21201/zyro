<template>
  <div class="track-order-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Tra cứu đơn hàng</li>
          </ol>
        </nav>

        <div class="row justify-content-center">
          <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in overflow-hidden">
              
              <div class="bg-light dark:bg-[#212529] p-4 p-md-5 border-bottom dark:border-gray-700 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white dark:bg-[#1a2533] rounded-circle shadow-sm mb-3" style="width: 70px; height: 70px;">
                  <i class="bi bi-box-seam text-c-dark dark:text-white fs-1"></i>
                </div>
                <h3 class="fw-bold text-c-dark dark:text-white mb-2 text-uppercase tracking-widest">Tra Cứu Đơn Hàng</h3>
                <p class="text-muted small mb-0 px-md-4">Kiểm tra tình trạng vận chuyển đơn hàng của bạn nhanh chóng chỉ với mã đơn và số điện thoại đặt hàng.</p>
              </div>

              <div class="p-4 p-md-5">
                <form @submit.prevent="handleTrack" v-if="!orderResult" autocomplete="off">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted text-uppercase">Mã đơn hàng <span class="text-danger">*</span></label>
                      <input type="text" class="form-control custom-input text-uppercase font-monospace" v-model="form.orderCode" required placeholder="VD: ZYRO-123456">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted text-uppercase">Số điện thoại <span class="text-danger">*</span></label>
                      <input type="tel" class="form-control custom-input font-monospace" v-model="form.phone" required placeholder="Nhập SĐT đặt hàng">
                    </div>
                    <div class="col-12 mt-4 text-center">
                      <button type="submit" class="btn btn-c-dark btn-lg px-5 rounded-pill fw-bold text-uppercase tracking-wide shadow-sm hover-transform" :disabled="isSearching">
                        <span v-if="isSearching" class="spinner-border spinner-border-sm me-2"></span>
                        <template v-else><i class="bi bi-search me-2"></i> Tra cứu ngay</template>
                      </button>
                    </div>
                  </div>
                </form>

                <div v-else class="animation-fade-up">
                  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom dark:border-gray-700">
                    <div>
                      <h5 class="fw-bold text-c-dark dark:text-white mb-1">Mã đơn: <span class="text-urban font-monospace">{{ orderResult.code }}</span></h5>
                      <span class="text-muted small">Ngày đặt: {{ orderResult.date }}</span>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold" @click="orderResult = null">
                      <i class="bi bi-arrow-repeat me-1"></i> Tra cứu đơn khác
                    </button>
                  </div>

                  <div class="position-relative tracking-timeline my-5 px-3">
                    <div class="progress position-absolute top-50 start-0 translate-middle-y w-100" style="height: 4px; z-index: 1;">
                      <div class="progress-bar bg-urban" :style="{ width: progressWidth }"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between position-relative z-2">
                      <div class="step-item text-center" :class="{ 'active': currentStep >= 1 }">
                        <div class="step-icon mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-2 border-white">
                          <i class="bi bi-receipt"></i>
                        </div>
                        <div class="small fw-bold step-text">Đã đặt hàng</div>
                      </div>
                      <div class="step-item text-center" :class="{ 'active': currentStep >= 2 }">
                        <div class="step-icon mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-2 border-white">
                          <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="small fw-bold step-text">Đang chuẩn bị</div>
                      </div>
                      <div class="step-item text-center" :class="{ 'active': currentStep >= 3 }">
                        <div class="step-icon mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-2 border-white">
                          <i class="bi bi-truck"></i>
                        </div>
                        <div class="small fw-bold step-text">Đang giao hàng</div>
                      </div>
                      <div class="step-item text-center" :class="{ 'active': currentStep >= 4 }">
                        <div class="step-icon mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-2 border-white">
                          <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="small fw-bold step-text">Thành công</div>
                      </div>
                    </div>
                  </div>

                  <div class="bg-c-effect dark:bg-[#212529] p-4 rounded-4 mt-4">
                    <h6 class="fw-bold text-c-dark dark:text-white mb-3"><i class="bi bi-info-circle text-urban me-2"></i>Cập nhật mới nhất</h6>
                    <ul class="list-unstyled mb-0 position-relative log-list">
                      <li v-for="(log, idx) in orderResult.logs" :key="idx" class="position-relative ps-4 mb-3 pb-3" :class="{'border-bottom dark:border-gray-700': idx !== orderResult.logs.length - 1}">
                        <div class="log-bullet position-absolute bg-urban rounded-circle" style="width: 10px; height: 10px; left: 0; top: 5px;"></div>
                        <div class="fw-bold text-dark dark:text-gray-200 small">{{ log.status }}</div>
                        <div class="text-muted small mt-1">{{ log.time }} - {{ log.location }}</div>
                      </li>
                    </ul>
                  </div>

                </div>

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
import Swal from 'sweetalert2';

const form = ref({ orderCode: '', phone: '' });
const isSearching = ref(false);
const orderResult = ref(null);

// Giả lập Dữ liệu trả về
const mockData = {
  code: 'ZYRO-123456',
  date: '08/04/2026',
  step: 3, // Đang giao
  logs: [
    { status: 'Đơn hàng đang được giao đến bạn', time: '08:30 - 10/04/2026', location: 'Bưu cục Quận 1, TP HCM' },
    { status: 'Đơn hàng đã xuất kho', time: '15:00 - 09/04/2026', location: 'Kho tổng ZYRO Hà Nội' },
    { status: 'Đang đóng gói sản phẩm', time: '10:00 - 09/04/2026', location: 'Kho tổng ZYRO Hà Nội' },
    { status: 'Đặt hàng thành công', time: '09:15 - 08/04/2026', location: 'Hệ thống ZYRO' }
  ]
};

const currentStep = computed(() => orderResult.value ? orderResult.value.step : 1);
const progressWidth = computed(() => {
  if (currentStep.value === 1) return '0%';
  if (currentStep.value === 2) return '33%';
  if (currentStep.value === 3) return '66%';
  return '100%';
});

const handleTrack = () => {
  isSearching.value = true;
  setTimeout(() => {
    isSearching.value = false;
    // Tùy theo logic API thực tế, nếu sai thì báo lỗi
    if (form.value.orderCode.includes('ZYRO')) {
      orderResult.value = mockData;
    } else {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Không tìm thấy đơn hàng!', showConfirmButton: false, timer: 2000 });
    }
  }, 1000);
};

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.track-order-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover) !important; }
.bg-urban { background-color: var(--color-c-hover) !important; }
.bg-c-effect { background-color: var(--color-c-effect); }

.btn-c-dark { background-color: var(--color-c-dark); color: white; border: none; transition: 0.2s; }
.btn-c-dark:hover { background-color: var(--color-c-hover); color: white; }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

/* INPUT CHUẨN MƯỢT */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  padding: 0.75rem 1.25rem; 
  font-size: 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  box-shadow: none !important; 
}
html.dark .custom-input { background-color: #1a2533; border-color: #373b3e; color: white; }
.custom-input:focus, .custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; 
}
html.dark .custom-input:focus { background-color: #212529; box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important; }

/* TIMELINE CSS */
.step-item { width: 80px; }
.step-icon { width: 45px; height: 45px; background-color: #e9ecef; color: #adb5bd; transition: all 0.3s ease; font-size: 1.2rem; }
html.dark .step-icon { background-color: #373b3e; border-color: #212529 !important; }
.step-text { color: #adb5bd; transition: color 0.3s ease; }

.step-item.active .step-icon { background-color: var(--color-c-hover); color: white; border-color: white !important; box-shadow: 0 0 0 4px rgba(84, 119, 146, 0.2) !important; }
.step-item.active .step-text { color: var(--color-c-dark); }
html.dark .step-item.active .step-text { color: white; }

.log-list::before {
  content: ''; position: absolute; top: 10px; bottom: 20px; left: 4px;
  width: 2px; background-color: rgba(84, 119, 146, 0.2);
}

.animation-fade-in { animation: fadeIn 0.5s ease-in-out; }
.animation-fade-up { animation: fadeUp 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>