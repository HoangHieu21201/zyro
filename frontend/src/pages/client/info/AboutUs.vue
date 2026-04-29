<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">

    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">

        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang
                chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Về ZYRO</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN -->
          <div class="col-lg-3 d-none d-lg-block">
            <!-- ĐÃ FIX: Đổi từ /shipping-policy thành /about-us -->
            <InfoNavigation currentRoute="/about-us" />
          </div>

          <!-- MAIN CONTENT -->
          <div class="col-lg-9">

            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">VỀ
                CHÚNG TÔI</h2>
              <p class="text-muted mt-2 font-sans-vn">Câu chuyện thương hiệu và sứ mệnh của ZYRO.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
              <div class="shimmer rounded-4 w-100 mb-4" style="height: 300px;"></div>
              <div v-for="i in 3" :key="i" class="shimmer rounded-3 w-100 mb-3" style="height: 100px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">

              <div class="rounded-4 overflow-hidden mb-5 shadow-sm position-relative bg-urban-effect dark:bg-[#1a2533]"
                style="height: 350px;">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop"
                  class="w-100 h-100 object-fit-cover opacity-75">
                <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                  <h1 class="display-4 fw-bold text-white text-shadow-lg tracking-widest font-decor">Tự Tin Phong Cách
                  </h1>
                </div>
              </div>

              <div class="policy-section mb-5">
                <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">1. Câu chuyện thương hiệu</h4>
                <p class="lh-lg">ZYRO ra đời với niềm đam mê mãnh liệt dành cho thời trang và khao khát mang đến cho
                  khách hàng những sản phẩm chất lượng, thiết kế tinh tế với mức giá dễ tiếp cận. Chúng tôi tin rằng
                  thời trang không chỉ là quần áo, mà còn là ngôn ngữ không lời thể hiện cá tính và sự tự tin của mỗi
                  người.</p>
                <p class="lh-lg">Khởi đầu từ một cửa hàng nhỏ tại TP. Buôn Ma Thuột, ZYRO đang từng bước nỗ lực vươn
                  mình trở thành một trong những thương hiệu thời trang được yêu thích tại Việt Nam.</p>
              </div>

              <div class="policy-section mb-5">
                <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">2. Tầm nhìn & Sứ mệnh</h4>
                <div class="row g-4 mt-2">
                  <div class="col-md-6">
                    <div
                      class="p-4 bg-light dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 h-100 shadow-sm transition-all hover-transform">
                      <div class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="bi bi-eye-fill text-urban fs-5"></i>
                      </div>
                      <h5 class="fw-bold text-dark dark:text-white mb-2">Tầm nhìn</h5>
                      <p class="text-muted small lh-lg m-0">Trở thành nền tảng mua sắm thời trang trực tuyến hàng đầu,
                        nơi khách hàng có thể tìm thấy mọi thứ họ cần để hoàn thiện phong cách cá nhân.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div
                      class="p-4 bg-light dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 h-100 shadow-sm transition-all hover-transform">
                      <div class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="bi bi-rocket-takeoff-fill text-urban fs-5"></i>
                      </div>
                      <h5 class="fw-bold text-dark dark:text-white mb-2">Sứ mệnh</h5>
                      <p class="text-muted small lh-lg m-0">Không ngừng cải tiến chất lượng sản phẩm và dịch vụ, mang
                        lại trải nghiệm mua sắm mượt mà, tiện lợi và an tâm tuyệt đối cho khách hàng.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="policy-section mb-4">
                <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">3. Giá trị cốt lõi</h4>
                <ul class="list-unstyled lh-lg mt-3">
                  <li class="mb-3 d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-urban mt-1 me-3"></i>
                    <div><strong>Chất lượng hàng đầu:</strong> Khắt khe trong việc lựa chọn chất liệu và kiểm duyệt sản
                      phẩm trước khi đến tay khách hàng.</div>
                  </li>
                  <li class="mb-3 d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-urban mt-1 me-3"></i>
                    <div><strong>Tận tâm phục vụ:</strong> Đặt khách hàng làm trung tâm, luôn lắng nghe và hỗ trợ bằng
                      sự chân thành.</div>
                  </li>
                  <li class="mb-3 d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-urban mt-1 me-3"></i>
                    <div><strong>Đổi mới sáng tạo:</strong> Luôn cập nhật xu hướng mới nhất, áp dụng công nghệ để nâng
                      cao trải nghiệm người dùng.</div>
                  </li>
                  <li class="d-flex align-items-start">
                    <i class="bi bi-check-circle-fill text-urban mt-1 me-3"></i>
                    <div><strong>Trách nhiệm xã hội:</strong> Phát triển kinh doanh đi đôi với việc bảo vệ môi trường và
                      đóng góp cho cộng đồng.</div>
                  </li>
                </ul>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import InfoNavigation from '@/components/client/info/InfoNavigation.vue';

const isLoading = ref(true);

const simulateLoading = () => {
  setTimeout(() => {
    isLoading.value = false;
  }, 400);
};

onMounted(() => {
  window.scrollTo(0, 0);
  simulateLoading();
});
</script>

<style scoped>
.policy-page-wrapper {
  width: 100%;
}

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

.font-sans-vn {
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important;
}

.font-decor {
  font-family: 'Times New Roman', Times, serif;
  font-style: italic;
}

.text-shadow-lg {
  text-shadow: 2px 4px 10px rgba(0, 0, 0, 0.6);
}

.text-urban-dark {
  color: var(--color-c-dark, #213448) !important;
}

.text-urban {
  color: var(--color-c-hover, #547792) !important;
}

.bg-urban-effect {
  background-color: rgba(84, 119, 146, 0.05) !important;
}

.hover-urban:hover {
  color: var(--color-c-hover, #547792) !important;
}

.tracking-widest {
  letter-spacing: 2px;
}

.tracking-wide {
  letter-spacing: 1px;
}

.icon-box-sm {
  width: 45px;
  height: 45px;
  background-color: #ffffff;
  border: 1.5px solid rgba(84, 119, 146, 0.2);
}

html.dark .icon-box-sm {
  background-color: #2b3035;
  border-color: rgba(255, 255, 255, 0.1);
}

.hover-transform {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-transform:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

/* SKELETON CSS */
.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}

html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}

@keyframes placeholderShimmer {
  0% {
    background-position: -400px 0;
  }

  100% {
    background-position: 400px 0;
  }
}

.animation-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.transition-all {
  transition: all 0.3s ease;
}
</style>