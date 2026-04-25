<template>
  <div class="info-page-wrapper pt-4 pb-5">
    <div class="zyro-container">
      
      <!-- BREADCRUMB -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small text-uppercase tracking-wider fw-semibold">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none zyro-link-hover">Trang chủ</router-link></li>
          <li class="breadcrumb-item active opacity-50" aria-current="page">Về ZYRO</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <InfoNavigation currentRoute="/about-us" />
        </div>

        <!-- CONTENT AREA -->
        <div class="col-lg-9">
          
          <div v-if="isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm w-100" style="min-height: 800px;">
            <div class="skeleton-line mb-4" style="width: 350px; height: 40px;"></div>
            <div class="skeleton-line mb-5" style="width: 100%; height: 400px; border-radius: 12px;"></div>
            <div class="skeleton-line mb-3" style="width: 100%; height: 20px;"></div>
            <div class="skeleton-line mb-3" style="width: 90%; height: 20px;"></div>
            <div class="skeleton-line mb-5" style="width: 95%; height: 20px;"></div>
            <div class="row g-4">
               <div class="col-6"><div class="skeleton-line h-100" style="min-height: 150px; border-radius: 12px;"></div></div>
               <div class="col-6"><div class="skeleton-line h-100" style="min-height: 150px; border-radius: 12px;"></div></div>
            </div>
          </div>

          <transition name="fade" mode="out-in">
            <div v-show="!isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm info-content-card w-100">
              <h1 class="display-6 fw-black text-uppercase tracking-tight mb-4" style="color: var(--color-c-dark);">Định Hình Phong Cách</h1>
              
              <div class="rounded-4 overflow-hidden mb-5 position-relative shadow-sm zyro-banner-container" style="background-color: var(--color-c-effect);">
                  <img v-if="bannerUrl" :src="bannerUrl" alt="Về ZYRO Brand" class="w-100 h-100 object-fit-cover transition-all hover-scale" />
                  
                  <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center">
                     <i class="bi bi-stars text-muted opacity-25" style="font-size: 5rem;"></i>
                  </div>
              </div>

              <section class="mb-5">
                <h4 class="fw-bold mb-3 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">01.</span> Tầm Nhìn
                </h4>
                <p class="text-muted lh-lg text-justify" style="font-size: 1.05rem;">
                  Được thành lập với hoài bão mang đến cho thế hệ trẻ Việt Nam những bộ trang phục không chỉ để mặc, mà còn để trải nghiệm. Chúng tôi tin rằng thời trang là ngôn ngữ không lời mạnh mẽ nhất để bạn kể câu chuyện của riêng mình giữa đám đông ồn ào. Sự phức tạp tinh vi nhất chính là sự tối giản. Tại ZYRO, chúng tôi loại bỏ đi những chi tiết thừa để tôn vinh trọn vẹn giá trị nguyên bản của người mặc.
                </p>
              </section>

              <section>
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2 section-title" style="color: var(--color-c-dark);">
                  <span class="font-serif italic fs-2 opacity-50" style="color: var(--color-c-hover);">02.</span> Triết Lý Thiết Kế
                </h4>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="p-4 rounded-4 border border-light-subtle h-100 bg-transparent card-hover text-center text-md-start">
                      <div class="icon-wrap d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 50px; height: 50px; background-color: var(--color-c-effect);">
                        <i class="bi bi-layers text-dark fs-4" style="color: var(--color-c-hover) !important;"></i>
                      </div>
                      <h6 class="fw-bold mb-2">Minimalism</h6>
                      <p class="text-muted mb-0 small lh-base">Tối giản trong đường nét, tập trung tối đa vào kỹ thuật cắt may và phom dáng (fitting) chuẩn mực.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-4 rounded-4 border border-light-subtle h-100 bg-transparent card-hover text-center text-md-start">
                      <div class="icon-wrap d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 50px; height: 50px; background-color: var(--color-c-effect);">
                        <i class="bi bi-feather text-dark fs-4" style="color: var(--color-c-hover) !important;"></i>
                      </div>
                      <h6 class="fw-bold mb-2">Comfort First</h6>
                      <p class="text-muted mb-0 small lh-base">Sử dụng nguồn vải cao cấp, thoáng khí, đảm bảo sự thoải mái tuyệt đối trong mọi chuyển động.</p>
                    </div>
                  </div>
                </div>
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
import axios from '@/utils/axios';
import InfoNavigation from '@/components/client/info/InfoNavigation.vue';

const isLoading = ref(true);
const bannerUrl = ref('');

const fetchBannerFromHome = async () => {
  try {
    const res = await axios.get('/client/home');
    if (res.data && res.data.success) {
      const homeData = res.data.data;
      if (homeData.most_loved && homeData.most_loved.banner) {
        bannerUrl.value = homeData.most_loved.banner.image;
      } 
      else if (homeData.hero_banners && homeData.hero_banners.length > 0) {
        bannerUrl.value = homeData.hero_banners[0].image_desktop;
      }
    }
  } catch (error) {
    console.error("Lỗi khi tải banner cho trang About Us:", error);
  } finally {
    setTimeout(() => { isLoading.value = false; }, 300);
  }
};

onMounted(() => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  fetchBannerFromHome();
});
</script>

<style scoped>
.info-page-wrapper { min-height: calc(100vh - 200px); }
.tracking-wider { letter-spacing: 0.05em; }
.tracking-widest { letter-spacing: 0.15em; }
.tracking-tight { letter-spacing: -0.02em; }
.fw-black { font-weight: 900; }
.font-serif { font-family: 'Georgia', serif; }
.italic { font-style: italic; }
.text-justify { text-align: justify; }

.zyro-link-hover { color: var(--color-c-dark); transition: color 0.3s ease; }
.zyro-link-hover:hover { color: var(--color-c-hover); }

.zyro-banner-container {
  height: 300px;
}
@media (min-width: 768px) {
  .zyro-banner-container { height: 400px; }
}

.hover-scale { transition: transform 0.8s ease; }
.zyro-banner-container:hover .hover-scale { transform: scale(1.05); }

.card-hover { transition: all 0.3s ease; }
.card-hover:hover { border-color: var(--color-c-hover) !important; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.card-hover:hover .icon-wrap { background-color: var(--color-c-dark) !important; }
.card-hover:hover .icon-wrap i { color: #ffffff !important; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease, transform 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
.skeleton-line {
  background: linear-gradient(90deg, #e2e5e7 25%, #f0f2f3 50%, #e2e5e7 75%);
  background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 4px;
}
@keyframes skeletonShimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

[data-bs-theme="dark"] .zyro-link-hover { color: #adb5bd; }
[data-bs-theme="dark"] .bg-white, [data-bs-theme="dark"] .info-content-card { background-color: #1e2125 !important; border-color: #2b3035 !important; }
[data-bs-theme="dark"] .skeleton-line { background: linear-gradient(90deg, #2b3035 25%, #373b3e 50%, #2b3035 75%); background-size: 200% 100%; }
[data-bs-theme="dark"] .icon-wrap { background-color: #2b3035 !important; }
</style>