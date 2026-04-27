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
          <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN -->
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/shipping-policy" />
          </div>

          <!-- MAIN CONTENT -->
          <div class="col-lg-9">
            
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">ĐIỀU KHOẢN DỊCH VỤ</h2>
              <p class="text-muted mt-2 font-sans-vn">Vui lòng đọc kỹ các điều khoản trước khi sử dụng dịch vụ tại ZYRO.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 5" :key="i" class="shimmer rounded-3 w-100 mb-4" style="height: 100px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <p class="lh-lg mb-4">Chào mừng bạn đến với ZYRO. Khi truy cập và sử dụng website của chúng tôi, bạn đồng ý tuân thủ các điều khoản và điều kiện dưới đây.</p>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">1. Chấp nhận điều khoản</h4>
                 <p class="lh-lg">Bằng việc đăng ký tài khoản hoặc mua sắm trên ZYRO, bạn xác nhận rằng bạn đã đọc, hiểu và đồng ý bị ràng buộc bởi các Điều khoản Dịch vụ này. ZYRO có quyền thay đổi, chỉnh sửa, thêm hoặc lược bỏ bất kỳ phần nào trong Quy định chung này vào bất cứ lúc nào. Các thay đổi có hiệu lực ngay khi được đăng trên trang web mà không cần thông báo trước.</p>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">2. Quyền và Trách nhiệm của khách hàng</h4>
                 <ul class="list-unstyled lh-lg ms-3">
                    <li class="mb-2"><i class="bi bi-chevron-right text-urban me-2"></i>Cung cấp thông tin chính xác, đầy đủ khi đăng ký tài khoản và mua hàng.</li>
                    <li class="mb-2"><i class="bi bi-chevron-right text-urban me-2"></i>Bảo mật thông tin tài khoản và mật khẩu của mình.</li>
                    <li class="mb-2"><i class="bi bi-chevron-right text-urban me-2"></i>Không sử dụng website vào mục đích bất hợp pháp, lừa đảo, hoặc phá hoại hệ thống.</li>
                 </ul>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">3. Quy định về giá cả & Sản phẩm</h4>
                 <p class="lh-lg">Mọi thông tin về giá sản phẩm trên ZYRO đều được niêm yết bằng Việt Nam Đồng (VNĐ) và đã bao gồm Thuế Giá Trị Gia Tăng (VAT). Chúng tôi luôn nỗ lực đảm bảo thông tin sản phẩm (hình ảnh, mô tả, giá cả) chính xác nhất có thể. Tuy nhiên, trong một số trường hợp hiếm hoi xảy ra sai sót hệ thống, ZYRO có quyền từ chối hoặc hủy các đơn hàng bị ảnh hưởng.</p>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">4. Sở hữu trí tuệ</h4>
                 <p class="lh-lg">Tất cả nội dung trên trang web này bao gồm văn bản, thiết kế, đồ họa, logo, biểu tượng, hình ảnh, âm thanh và phần mềm đều thuộc bản quyền của ZYRO hoặc các nhà cung cấp nội dung của ZYRO. Nghiêm cấm mọi hành vi sao chép, tái tạo, phân phối hoặc sử dụng cho mục đích thương mại mà không có sự cho phép bằng văn bản từ chúng tôi.</p>
               </div>

               <div class="policy-section">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">5. Giải quyết tranh chấp</h4>
                 <p class="lh-lg">Bất kỳ tranh cãi, khiếu nại hoặc tranh chấp phát sinh từ hoặc liên quan đến giao dịch tại ZYRO hoặc các Quy định và Điều kiện này đều sẽ được giải quyết bằng hình thức thương lượng, hòa giải, trọng tài và/hoặc Tòa án theo Luật bảo vệ Người tiêu dùng Việt Nam.</p>
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
.policy-page-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

.text-urban-dark { color: var(--color-c-dark, #213448) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }

.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

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
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.transition-all { transition: all 0.3s ease; }
</style>