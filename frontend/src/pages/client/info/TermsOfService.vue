<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Điều khoản dịch vụ</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/terms-of-service" />
          </div>

          <div class="col-lg-9">
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">ĐIỀU KHOẢN DỊCH VỤ</h2>
              <p class="text-muted mt-2 font-sans-vn">Vui lòng đọc kỹ các điều khoản trước khi mua sắm và sử dụng dịch vụ tại ZYRO.</p>
            </div>

            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 3" :key="i" class="shimmer rounded-4 w-100 mb-4" style="height: 160px;"></div>
            </div>

            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               <p class="lh-lg mb-4 text-muted dark:text-gray-400">Chào mừng bạn đến với ZYRO. Bằng việc truy cập và mua sắm tại website của chúng tôi, bạn đồng ý tuân thủ các điều khoản và quy định dưới đây. ZYRO có quyền thay đổi, chỉnh sửa các điều khoản này bất cứ lúc nào mà không cần thông báo trước.</p>

               <div class="row g-4 mb-5">
                 <div class="col-md-6">
                   <div class="p-4 rounded-4 bg-urban-effect dark:bg-[#1a2533] h-100 transition-all hover-transform border border-urban border-opacity-10">
                     <i class="bi bi-person-check-fill text-primary fs-2 mb-3 d-block"></i>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-3">1. Trách nhiệm khách hàng</h5>
                     <ul class="list-unstyled lh-lg mb-0 text-muted dark:text-gray-400 small">
                        <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> Cung cấp thông tin giao hàng chính xác.</li>
                        <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> Bảo mật thông tin tài khoản cá nhân.</li>
                        <li class="mb-0 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> Mua sắm văn minh, không gian lận.</li>
                     </ul>
                   </div>
                 </div>
                 <div class="col-md-6">
                   <div class="p-4 rounded-4 bg-urban-effect dark:bg-[#1a2533] h-100 transition-all hover-transform border border-urban border-opacity-10">
                     <i class="bi bi-tags-fill text-warning fs-2 mb-3 d-block"></i>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-3">2. Giá cả & Sản phẩm</h5>
                     <ul class="list-unstyled lh-lg mb-0 text-muted dark:text-gray-400 small">
                        <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> Giá niêm yết bằng VNĐ, đã gồm VAT.</li>
                        <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> Hình ảnh cam kết sát thực tế nhất.</li>
                        <li class="mb-0 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> ZYRO có quyền hủy đơn nếu xảy ra sự cố.</li>
                     </ul>
                   </div>
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide d-flex align-items-center"><i class="bi bi-shield-lock text-success me-2"></i> 3. Bản quyền & Sở hữu trí tuệ</h4>
                 <p class="lh-lg text-muted dark:text-gray-400">Tất cả hình ảnh lookbook, thiết kế sản phẩm, logo và nội dung trên website này đều thuộc bản quyền của ZYRO. Nghiêm cấm mọi hành vi sao chép, tái tạo, phân phối hoặc sử dụng cho mục đích thương mại mà không có sự cho phép bằng văn bản từ chúng tôi.</p>
               </div>
               
               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide d-flex align-items-center"><i class="bi bi-envelope-paper text-info me-2"></i> 4. Giải quyết tranh chấp</h4>
                 <p class="lh-lg text-muted dark:text-gray-400">Mọi tranh chấp phát sinh từ việc mua bán hoặc sử dụng dịch vụ tại ZYRO sẽ được ưu tiên giải quyết thông qua thương lượng và hòa giải trên tinh thần tôn trọng lợi ích của khách hàng. ZYRO luôn lắng nghe và đặt quyền lợi của bạn lên hàng đầu.</p>
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
