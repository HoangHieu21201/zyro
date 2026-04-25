<!-- frontend/src/pages/client/info/TermsOfService.vue -->
<template>
  <div class="info-page-wrapper pt-4 pb-5">
    <div class="zyro-container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small text-uppercase tracking-wider fw-semibold">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none zyro-link-hover">Trang chủ</router-link></li>
          <li class="breadcrumb-item active opacity-50" aria-current="page">Điều khoản</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <InfoNavigation currentRoute="/terms-of-service" />
        </div>

        <div class="col-lg-9">
          
          <div v-if="isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm w-100" style="min-height: 800px;">
            <div class="skeleton-line mb-4" style="width: 350px; height: 40px;"></div>
            <div class="skeleton-line mb-3" style="width: 100%; height: 20px;"></div>
            <div class="skeleton-line mb-3" style="width: 100%; height: 20px;"></div>
            <div class="skeleton-line mb-3" style="width: 90%; height: 20px;"></div>
          </div>

          <transition name="fade" mode="out-in">
            <div v-show="!isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm info-content-card w-100">
              <h1 class="display-6 fw-black text-uppercase tracking-tight mb-5" style="color: var(--color-c-dark);">Điều Khoản Dịch Vụ</h1>

              <section class="mb-5">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">01.</span> Thỏa Thuận Sử Dụng
                </h4>
                <p class="text-muted lh-lg text-justify mb-0">
                  Khi truy cập website ZYRO STUDIOS, quý khách đã ngầm định đồng ý với các thỏa thuận sau đây. Chúng tôi có quyền sửa đổi, bổ sung điều khoản mà không cần báo trước. Khách hàng sử dụng dịch vụ mua sắm tại web phải đảm bảo trên 18 tuổi hoặc có sự giám sát của người bảo hộ pháp lý.
                </p>
              </section>

              <section class="mb-5">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">02.</span> Bản Quyền (Copyright)
                </h4>
                <div class="p-4 rounded-3 border border-light-subtle bg-transparent text-muted lh-lg text-justify">
                  Mọi quyền sở hữu trí tuệ, bao gồm mã nguồn, logo, hình ảnh Lookbook, video và văn bản đều thuộc sở hữu hợp pháp của ZYRO. Nghiêm cấm sao chép, phân phối hoặc khai thác thương mại đối với bất kỳ nội dung nào nếu không được ZYRO ủy quyền bằng văn bản.
                </div>
              </section>

              <section>
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">03.</span> Thanh Toán An Toàn
                </h4>
                <p class="text-muted lh-lg text-justify mb-0">
                  Chúng tôi cung cấp phương thức thanh toán trả sau (COD) và thanh toán trước (Banking, VNPay, ví điện tử). Tất cả giao dịch điện tử sẽ được mã hóa an toàn. Trong trường hợp có rủi ro về mạng lỗi, ZYRO sẽ phối hợp với ngân hàng đối tác để giải quyết và hoàn tiền chính xác cho khách hàng.
                </p>
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
.text-justify { text-align: justify; }

.zyro-link-hover { color: var(--color-c-dark); transition: color 0.3s ease; }
.zyro-link-hover:hover { color: var(--color-c-hover); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
.skeleton-line { background: linear-gradient(90deg, #e2e5e7 25%, #f0f2f3 50%, #e2e5e7 75%); background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 4px; }
@keyframes skeletonShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

[data-bs-theme="dark"] .zyro-link-hover { color: #adb5bd; }
[data-bs-theme="dark"] .bg-white, [data-bs-theme="dark"] .info-content-card { background-color: #1e2125 !important; border-color: #2b3035 !important; }
[data-bs-theme="dark"] .skeleton-line { background: linear-gradient(90deg, #2b3035 25%, #373b3e 50%, #2b3035 75%); background-size: 200% 100%; }
</style>