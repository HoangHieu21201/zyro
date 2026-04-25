<template>
  <div class="info-page-wrapper pt-4 pb-5">
    <div class="zyro-container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small text-uppercase tracking-wider fw-semibold">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none zyro-link-hover">Trang chủ</router-link></li>
          <li class="breadcrumb-item active opacity-50" aria-current="page">FAQ</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <InfoNavigation currentRoute="/faq" />
        </div>

        <div class="col-lg-9">
          
          <transition name="fade" mode="out-in">
            
            <div v-if="isLoading" class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm w-100" style="min-height: 800px;">
              <div class="skeleton-line mb-4" style="width: 250px; height: 35px;"></div>
              <div class="skeleton-line mb-4" style="width: 100%; height: 250px; border-radius: 12px;"></div>
              <div class="skeleton-line mb-3" style="width: 100%; height: 60px;"></div>
              <div class="skeleton-line mb-3" style="width: 100%; height: 60px;"></div>
              <div class="skeleton-line mb-3" style="width: 100%; height: 60px;"></div>
              <div class="skeleton-line mb-3" style="width: 100%; height: 60px;"></div>
            </div>

            <div v-else class="bg-white rounded-4 border border-light-subtle p-4 p-md-5 shadow-sm info-content-card w-100">
              <h1 class="display-6 fw-black text-uppercase tracking-tight mb-4" style="color: var(--color-c-dark);">Câu hỏi thường gặp</h1>
              
              <div class="rounded-4 overflow-hidden mb-5 position-relative shadow-sm zyro-banner-container" style="background-color: var(--color-c-effect); height: 250px;">
                  <img v-if="bannerUrl" :src="bannerUrl" alt="FAQ ZYRO" class="w-100 h-100 object-fit-cover transition-all hover-scale" />
                  <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center">
                     <i class="bi bi-question-circle text-muted opacity-25" style="font-size: 5rem;"></i>
                  </div>
              </div>

              <div class="accordion zyro-accordion">
                
                <!-- FAQ Item 1 -->
                <div class="accordion-item border-0 mb-3 bg-transparent rounded-4 overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold px-4 py-3 shadow-none" 
                            :class="{ collapsed: activeTab !== 1 }" 
                            @click="toggleTab(1)" 
                            type="button">
                      1. Thời gian giao hàng mất bao lâu?
                    </button>
                  </h2>
                  <div class="zyro-faq-wrapper" :class="{ 'is-open': activeTab === 1 }">
                    <div class="zyro-faq-inner">
                      <div class="accordion-body px-4 pt-0 pb-4 text-muted lh-lg">
                        Tùy thuộc vào khu vực của bạn, thời gian giao hàng tiêu chuẩn dao động từ 1-5 ngày làm việc. Nếu bạn ở nội thành Hà Nội hoặc TP.HCM, ZYRO có cung cấp dịch vụ giao hỏa tốc trong vòng 2-4 tiếng.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="accordion-item border-0 mb-3 bg-transparent rounded-4 overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold px-4 py-3 shadow-none" 
                            :class="{ collapsed: activeTab !== 2 }" 
                            @click="toggleTab(2)" 
                            type="button">
                      2. Tôi có thể kiểm tra hàng trước khi thanh toán không?
                    </button>
                  </h2>
                  <div class="zyro-faq-wrapper" :class="{ 'is-open': activeTab === 2 }">
                    <div class="zyro-faq-inner">
                      <div class="accordion-body px-4 pt-0 pb-4 text-muted lh-lg">
                        Hoàn toàn có thể. ZYRO áp dụng chính sách "Đồng kiểm" - quý khách được quyền mở gói hàng kiểm tra ngoại quan (màu sắc, số lượng, mẫu mã) cùng shipper trước khi thanh toán. Tuy nhiên, quý khách vui lòng không mặc thử sản phẩm.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="accordion-item border-0 mb-3 bg-transparent rounded-4 overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold px-4 py-3 shadow-none" 
                            :class="{ collapsed: activeTab !== 3 }" 
                            @click="toggleTab(3)" 
                            type="button">
                      3. Làm thế nào để đổi size nếu không vừa?
                    </button>
                  </h2>
                  <div class="zyro-faq-wrapper" :class="{ 'is-open': activeTab === 3 }">
                    <div class="zyro-faq-inner">
                      <div class="accordion-body px-4 pt-0 pb-4 text-muted lh-lg">
                        Bạn có thể yêu cầu đổi size trong vòng 07 ngày kể từ khi nhận hàng. Hãy truy cập mục "Lịch sử đơn hàng" trong tài khoản của bạn để gửi yêu cầu, hoặc liên hệ Hotline 1900 9999. Khách hàng vui lòng chịu phí ship gửi hàng về kho ZYRO, chúng tôi sẽ miễn phí ship gửi lại.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="accordion-item border-0 mb-3 bg-transparent rounded-4 overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold px-4 py-3 shadow-none" 
                            :class="{ collapsed: activeTab !== 4 }" 
                            @click="toggleTab(4)" 
                            type="button">
                      4. Mua hàng lỗi thì phải làm sao?
                    </button>
                  </h2>
                  <div class="zyro-faq-wrapper" :class="{ 'is-open': activeTab === 4 }">
                    <div class="zyro-faq-inner">
                      <div class="accordion-body px-4 pt-0 pb-4 text-muted lh-lg">
                        Trong trường hợp hy hữu nhận phải hàng lỗi từ nhà sản xuất, ZYRO thành thật xin lỗi bạn. Chúng tôi sẽ chịu 100% chi phí vận chuyển 2 chiều để đổi trả sản phẩm mới cho bạn trong thời gian sớm nhất.
                      </div>
                    </div>
                  </div>
                </div>

              </div>
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

const activeTab = ref(1);

const toggleTab = (tabIndex) => {
  activeTab.value = activeTab.value === tabIndex ? null : tabIndex;
};

const fetchBannerFromHome = async () => {
  try {
    const res = await axios.get('/client/home');
    if (res.data && res.data.success) {
      const homeData = res.data.data;
      if (homeData.hero_banners && homeData.hero_banners.length > 0) {
        bannerUrl.value = homeData.hero_banners[homeData.hero_banners.length - 1].image_desktop;
      }
    }
  } catch (error) {
    console.error("Lỗi khi tải banner cho trang FAQ:", error);
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
.tracking-tight { letter-spacing: -0.02em; }
.fw-black { font-weight: 900; }

.zyro-link-hover { color: var(--color-c-dark); transition: color 0.3s ease; }
.zyro-link-hover:hover { color: var(--color-c-hover); }

.hover-scale { transition: transform 0.8s ease; }
.zyro-banner-container:hover .hover-scale { transform: scale(1.05); }

.zyro-faq-wrapper {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.35s ease-in-out;
}
.zyro-faq-wrapper.is-open {
  grid-template-rows: 1fr;
}
.zyro-faq-inner {
  overflow: hidden;
}

.zyro-accordion .accordion-item {
  border: 1px solid rgba(148, 180, 193, 0.3) !important;
  transition: all 0.3s ease;
}
.zyro-accordion .accordion-item:hover {
  border-color: var(--color-c-hover) !important;
}
.zyro-accordion .accordion-button {
  background-color: transparent !important;
  color: var(--color-c-dark);
  font-size: 1.05rem;
}
.zyro-accordion .accordion-button:focus {
  border-color: transparent;
  box-shadow: none;
}
.zyro-accordion .accordion-button:not(.collapsed) {
  color: var(--color-c-hover);
  background-color: var(--color-c-effect) !important;
}

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
[data-bs-theme="dark"] .accordion-item { border-color: #373b3e !important; }
[data-bs-theme="dark"] .accordion-button { color: #f8f9fa; }
[data-bs-theme="dark"] .accordion-button:not(.collapsed) { background-color: #2b3035 !important; color: #fff; }
</style>