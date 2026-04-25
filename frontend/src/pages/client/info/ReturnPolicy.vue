<!-- frontend/src/pages/client/info/ReturnPolicy.vue -->
<template>
  <div class="info-page-wrapper pt-4 pb-5">
    <div class="zyro-container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small text-uppercase tracking-wider fw-semibold">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none zyro-link-hover">Trang chủ</router-link></li>
          <li class="breadcrumb-item active opacity-50" aria-current="page">Đổi trả</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <InfoNavigation currentRoute="/return-policy" />
        </div>

        <div class="col-lg-9">
          
          <div v-if="isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm w-100" style="min-height: 800px;">
            <div class="skeleton-line mb-4" style="width: 350px; height: 40px;"></div>
            <div class="skeleton-line mb-4" style="width: 100%; height: 100px; border-radius: 12px;"></div>
            <div class="skeleton-line mb-3" style="width: 100%; height: 20px;"></div>
            <div class="skeleton-line mb-3" style="width: 90%; height: 20px;"></div>
            <div class="row g-4 mt-2">
               <div class="col-4"><div class="skeleton-line h-100" style="min-height: 150px; border-radius: 12px;"></div></div>
               <div class="col-4"><div class="skeleton-line h-100" style="min-height: 150px; border-radius: 12px;"></div></div>
               <div class="col-4"><div class="skeleton-line h-100" style="min-height: 150px; border-radius: 12px;"></div></div>
            </div>
          </div>

          <transition name="fade" mode="out-in">
            <div v-show="!isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm info-content-card w-100">
              <h1 class="display-6 fw-black text-uppercase tracking-tight mb-4" style="color: var(--color-c-dark);">Đổi Trả Dễ Dàng</h1>
              
              <div class="alert bg-transparent border-0 rounded-4 mb-5 p-4 d-flex align-items-center gap-3" style="background-color: var(--color-c-effect) !important;">
                <i class="bi bi-arrow-repeat fs-1" style="color: var(--color-c-hover);"></i>
                <div>
                  <h6 class="fw-bold mb-1" style="color: var(--color-c-dark);">Hỗ trợ 07 Ngày</h6>
                  <p class="mb-0 text-muted small">Kể từ thời điểm quý khách nhận hàng thành công từ đơn vị vận chuyển.</p>
                </div>
              </div>

              <section class="mb-5">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">01.</span> Điều Kiện Áp Dụng
                </h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="p-3 border border-light-subtle rounded-3 bg-transparent h-100">
                      <i class="bi bi-check-circle text-success mb-2 fs-5"></i>
                      <p class="text-muted small mb-0 lh-lg">Sản phẩm nguyên tem, nguyên mác, chưa qua sử dụng, chưa giặt ủi và không bám mùi lạ.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 border border-light-subtle rounded-3 bg-transparent h-100">
                      <i class="bi bi-x-circle text-danger mb-2 fs-5"></i>
                      <p class="text-muted small mb-0 lh-lg">Không áp dụng cho hàng phụ kiện (tất, đồ lót) hoặc hàng giảm giá đặc biệt Flash Sale > 50%.</p>
                    </div>
                  </div>
                </div>
              </section>

              <section class="mb-5 pb-3">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">02.</span> Quy trình đổi hàng
                </h4>
                
                <div class="row g-4">
                  <div class="col-md-4">
                    <div class="policy-card p-4 rounded-4 h-100 border border-light-subtle transition-all text-center bg-transparent">
                      <div class="display-3 font-serif italic opacity-25 mb-3" style="color: var(--color-c-hover);">1</div>
                      <h6 class="fw-bold mb-3">Tạo yêu cầu</h6>
                      <p class="text-muted small lh-base">Tạo yêu cầu trả hàng trong mục "Lịch sử đơn hàng" hoặc gọi Hotline 1900 9999 để được hỗ trợ.</p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="policy-card p-4 rounded-4 h-100 border border-light-subtle transition-all text-center bg-transparent">
                      <div class="display-3 font-serif italic opacity-25 mb-3" style="color: var(--color-c-hover);">2</div>
                      <h6 class="fw-bold mb-3">Đóng gói gửi về</h6>
                      <p class="text-muted small lh-base">Đóng gói cẩn thận. Ghi rõ mã đơn hàng và SĐT bên ngoài gói hàng gửi về kho ZYRO.</p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="policy-card p-4 rounded-4 h-100 border border-light-subtle transition-all text-center bg-transparent">
                      <div class="display-3 font-serif italic opacity-25 mb-3" style="color: var(--color-c-hover);">3</div>
                      <h6 class="fw-bold mb-3">Chờ xác nhận</h6>
                      <p class="text-muted small lh-base">Sau 2-3 ngày xử lý kiểm định, kho sẽ gửi sản phẩm mới (hoặc hoàn tiền) lại cho bạn.</p>
                    </div>
                  </div>
                </div>
              </section>

              <section>
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">03.</span> Chi Phí
                </h4>
                <ul class="text-muted lh-lg mb-0 ps-3">
                  <li class="mb-2"><strong>Lỗi do ZYRO:</strong> Chúng tôi sẽ điều phối shipper lấy hàng tại nhà và chịu 100% cước phí đổi/trả.</li>
                  <li class="mb-2"><strong>Khách đổi ý (Size/Mẫu):</strong> Quý khách thanh toán phí gửi hàng về kho. ZYRO miễn phí gửi sản phẩm mới đi.</li>
                </ul>
              </section>

            </div>
          </transition>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import InfoNavigation from '@/components/client/info/InfoNavigation.vue';

const isLoading = ref(true);

onMounted(() => { 
  window.scrollTo({ top: 0, behavior: 'smooth' }); 
  setTimeout(() => { isLoading.value = false; }, 300); 
});
</script>

<style scoped>
.info-page-wrapper { min-height: calc(100vh - 200px); }
.tracking-wider { letter-spacing: 0.05em; }
.tracking-tight { letter-spacing: -0.02em; }
.fw-black { font-weight: 900; }
.font-serif { font-family: 'Georgia', serif; }
.italic { font-style: italic; }
.zyro-link-hover { color: var(--color-c-dark); transition: color 0.3s ease; }
.zyro-link-hover:hover { color: var(--color-c-hover); }

.policy-card:hover {
  border-color: var(--color-c-hover) !important;
  box-shadow: 0 10px 25px rgba(33, 52, 72, 0.05); transform: translateY(-5px);
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
.skeleton-line { background: linear-gradient(90deg, #e2e5e7 25%, #f0f2f3 50%, #e2e5e7 75%); background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 4px; }
@keyframes skeletonShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

[data-bs-theme="dark"] .zyro-link-hover { color: #adb5bd; }
[data-bs-theme="dark"] .bg-white, [data-bs-theme="dark"] .info-content-card { background-color: #1e2125 !important; border-color: #2b3035 !important; }
[data-bs-theme="dark"] .skeleton-line { background: linear-gradient(90deg, #2b3035 25%, #373b3e 50%, #2b3035 75%); background-size: 200% 100%; }
[data-bs-theme="dark"] .alert { background-color: #2b3035 !important; }
</style>