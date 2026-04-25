<!-- frontend/src/pages/client/info/ShippingPolicy.vue -->
<template>
  <div class="info-page-wrapper pt-4 pb-5">
    <div class="zyro-container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small text-uppercase tracking-wider fw-semibold">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none zyro-link-hover">Trang chủ</router-link></li>
          <li class="breadcrumb-item active opacity-50" aria-current="page">Vận chuyển</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <InfoNavigation currentRoute="/shipping-policy" />
        </div>

        <!-- CONTENT AREA -->
        <div class="col-lg-9">
          
          <div v-if="isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm w-100" style="min-height: 800px;">
            <div class="skeleton-line mb-4" style="width: 350px; height: 40px;"></div>
            <div class="skeleton-line mb-4" style="width: 100%; height: 100px; border-radius: 12px;"></div>
            <div class="skeleton-line mb-3" style="width: 100%; height: 20px;"></div>
            <div class="skeleton-line mb-3" style="width: 90%; height: 20px;"></div>
          </div>

          <transition name="fade" mode="out-in">
            <div v-show="!isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm info-content-card w-100">
              <h1 class="display-6 fw-black text-uppercase tracking-tight mb-4" style="color: var(--color-c-dark);">Vận chuyển & Giao nhận</h1>
              
              <div class="alert bg-transparent border-0 rounded-4 mb-5 p-4 d-flex align-items-center gap-3" style="background-color: var(--color-c-effect) !important;">
                <i class="bi bi-truck fs-1" style="color: var(--color-c-hover);"></i>
                <div>
                  <h6 class="fw-bold mb-1" style="color: var(--color-c-dark);">Miễn phí Vận chuyển</h6>
                  <p class="mb-0 text-muted small">Áp dụng giao hàng tiêu chuẩn 0Đ toàn quốc cho đơn hàng từ 500.000 VNĐ.</p>
                </div>
              </div>

              <section class="mb-5">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">01.</span> Thời Gian
                </h4>
                <div class="table-responsive rounded-4 border border-light-subtle">
                  <table class="table mb-0 align-middle zyro-custom-table bg-transparent">
                    <thead>
                      <tr class="text-uppercase text-muted small">
                        <th class="py-3 px-4 border-0">Khu vực</th>
                        <th class="py-3 px-4 border-0 text-end">Thời gian dự kiến</th>
                      </tr>
                    </thead>
                    <tbody class="border-top border-light-subtle">
                      <tr>
                        <td class="py-3 px-4 fw-medium">Hà Nội & TP. HCM (Tiêu chuẩn)</td>
                        <td class="py-3 px-4 text-end text-muted">1 - 2 ngày</td>
                      </tr>
                      <tr>
                        <td class="py-3 px-4 fw-medium">Hà Nội & TP. HCM (Hỏa tốc)</td>
                        <td class="py-3 px-4 text-end text-muted">2 - 4 tiếng</td>
                      </tr>
                      <tr>
                        <td class="py-3 px-4 fw-medium">Các tỉnh thành khác</td>
                        <td class="py-3 px-4 text-end text-muted">3 - 5 ngày</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </section>

              <section>
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">02.</span> Chi Phí & Đồng Kiểm
                </h4>
                <ul class="text-muted lh-lg mb-0 ps-3">
                  <li class="mb-2">Đơn hàng dưới 500.000đ: Phí vận chuyển đồng giá <strong>30.000đ</strong>.</li>
                  <li class="mb-2">Giao hỏa tốc: Tính theo biểu phí Grab/Ahamove thực tế.</li>
                  <li><strong>Quyền lợi đồng kiểm:</strong> Khách hàng được mở bọc xem ngoại quan sản phẩm cùng shipper. Yêu cầu không mặc thử, làm rách tem mác.</li>
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

.zyro-custom-table th { background-color: transparent !important; }
.zyro-custom-table tbody tr { background-color: transparent; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
.skeleton-line { background: linear-gradient(90deg, #e2e5e7 25%, #f0f2f3 50%, #e2e5e7 75%); background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 4px; }
@keyframes skeletonShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

[data-bs-theme="dark"] .zyro-link-hover { color: #adb5bd; }
[data-bs-theme="dark"] .bg-white, [data-bs-theme="dark"] .info-content-card { background-color: #1e2125 !important; border-color: #2b3035 !important; }
[data-bs-theme="dark"] .skeleton-line { background: linear-gradient(90deg, #2b3035 25%, #373b3e 50%, #2b3035 75%); background-size: 200% 100%; }
[data-bs-theme="dark"] .alert { background-color: #2b3035 !important; }
</style>