<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách bảo mật</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/privacy-policy" />
          </div>

          <div class="col-lg-9">
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">CHÍNH SÁCH BẢO MẬT</h2>
              <p class="text-muted mt-2 font-sans-vn">ZYRO cam kết bảo vệ an toàn tuyệt đối thông tin cá nhân của bạn.</p>
            </div>

            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 3" :key="i" class="shimmer rounded-4 w-100 mb-4" style="height: 120px;"></div>
            </div>

            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <p class="lh-lg mb-5 text-muted dark:text-gray-400">Sự riêng tư của khách hàng là ưu tiên số một tại ZYRO. Chúng tôi cam kết tuyệt đối không trao đổi hay mua bán thông tin cá nhân của bạn cho bất kỳ bên thứ ba nào nhằm mục đích thương mại.</p>

               <div class="row g-4 mb-5">
                 <div class="col-md-6">
                   <div class="policy-section h-100 p-4 rounded-4 bg-gray-50 dark:bg-[#1a2533] border border-gray-200 dark:border-gray-700 transition-all hover-transform">
                     <i class="bi bi-file-earmark-person-fill text-primary fs-2 mb-3 d-block"></i>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-3">1. Mục đích thu thập</h5>
                     <p class="lh-lg text-muted dark:text-gray-400 mb-0 small">ZYRO thu thập thông tin cá nhân (Tên, Email, Số điện thoại, Địa chỉ) để xử lý đơn hàng, liên hệ giao hàng và gửi các thông báo về các chương trình ưu đãi đặc biệt dành riêng cho bạn.</p>
                   </div>
                 </div>
                 
                 <div class="col-md-6">
                   <div class="policy-section h-100 p-4 rounded-4 bg-gray-50 dark:bg-[#1a2533] border border-gray-200 dark:border-gray-700 transition-all hover-transform">
                     <i class="bi bi-shield-lock-fill text-success fs-2 mb-3 d-block"></i>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-3">2. Bảo mật tuyệt đối</h5>
                     <p class="lh-lg text-muted dark:text-gray-400 mb-0 small">Hệ thống thanh toán của ZYRO được mã hóa bảo mật hoàn toàn. Thông tin cá nhân của bạn được lưu trữ an toàn trên máy chủ của chúng tôi và chỉ được chia sẻ với đối tác vận chuyển để giao hàng.</p>
                   </div>
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-cookie text-warning me-2"></i> 3. Sử dụng Cookie</h4>
                 <div class="bg-urban-effect dark:bg-[#1a2533] p-4 rounded-4 border border-urban border-opacity-10">
                   <p class="lh-lg text-muted dark:text-gray-400 mb-0">Website sử dụng cookie để ghi nhớ giỏ hàng, thông tin đăng nhập và cải thiện trải nghiệm mua sắm của bạn. Bạn hoàn toàn có quyền tắt cookie trong trình duyệt, nhưng điều này có thể làm giảm trải nghiệm cá nhân hóa của bạn tại ZYRO.</p>
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
