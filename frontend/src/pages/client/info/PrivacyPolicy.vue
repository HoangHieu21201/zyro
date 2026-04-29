<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách bảo mật</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN -->
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/privacy-policy" />
          </div>

          <!-- MAIN CONTENT -->
          <div class="col-lg-9">
            
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">CHÍNH SÁCH BẢO MẬT (PRIVACY POLICY)</h2>
              <p class="text-muted mt-2 font-sans-vn">ZYRO cam kết bảo vệ thông tin cá nhân của bạn một cách an toàn nhất.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 4" :key="i" class="shimmer rounded-3 w-100 mb-4" style="height: 120px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <p class="lh-lg mb-4">Chúng tôi hiểu rằng quyền riêng tư là vô cùng quan trọng. Chính sách này mô tả cách ZYRO thu thập, sử dụng và bảo mật thông tin khi bạn mua sắm tại hệ thống của chúng tôi.</p>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">1. Mục đích thu thập thông tin</h4>
                 <p class="lh-lg">Dữ liệu cá nhân (Họ tên, SĐT, Email, Địa chỉ) được ZYRO thu thập chỉ nhằm mục đích:</p>
                 <ul class="list-unstyled lh-lg mt-2 ms-3">
                    <li class="mb-2"><i class="bi bi-shield-check text-urban me-2"></i>Xử lý đơn đặt hàng và bàn giao cho Đơn vị vận chuyển (GHN, GHTK, ViettelPost...).</li>
                    <li class="mb-2"><i class="bi bi-shield-check text-urban me-2"></i>Cung cấp các dịch vụ chăm sóc khách hàng, hỗ trợ kỹ thuật và giải quyết khiếu nại.</li>
                    <li class="mb-2"><i class="bi bi-shield-check text-urban me-2"></i>Gửi thông tin cập nhật về đơn hàng, chương trình khuyến mãi (chỉ khi bạn đồng ý nhận email).</li>
                 </ul>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">2. Cam kết bảo mật dữ liệu</h4>
                 <p class="lh-lg">Bảo vệ thông tin cá nhân của khách hàng là ưu tiên số một tại ZYRO. Chúng tôi áp dụng các tiêu chuẩn mã hóa SSL/TLS cho mọi giao dịch. <strong>Tuyệt đối KHÔNG bán, trao đổi hay chia sẻ</strong> thông tin khách hàng cho bất kỳ bên thứ 3 nào khác với mục đích thương mại.</p>
                 <div class="alert alert-info bg-urban-effect border-0 text-dark dark:text-gray-200 mt-3 rounded-4 p-4 shadow-sm">
                    <i class="bi bi-info-circle-fill text-urban me-2"></i> Thông tin duy nhất được chia sẻ là Họ Tên, Địa chỉ và SĐT giao cho Đối tác vận chuyển để thực hiện việc giao hàng.
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">3. Quản lý thẻ thanh toán & Ví điện tử</h4>
                 <p class="lh-lg">ZYRO không trực tiếp lưu trữ thông tin số thẻ tín dụng hoặc tài khoản ngân hàng của bạn. Mọi giao dịch điện tử (MoMo, VNPay, Thẻ tín dụng) đều được xử lý thông qua hệ thống bảo mật chuẩn quốc tế PCI DSS của các Cổng thanh toán đối tác.</p>
               </div>

               <div class="policy-section">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">4. Xóa tài khoản và dữ liệu</h4>
                 <p class="lh-lg">Nếu bạn muốn xóa hoàn toàn tài khoản và dữ liệu mua hàng của mình khỏi hệ thống ZYRO, vui lòng liên hệ với chúng tôi qua email <strong>support@zyro.vn</strong>. Yêu cầu của bạn sẽ được xử lý trong vòng 48h làm việc.</p>
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
.bg-urban-effect { background-color: rgba(84, 119, 146, 0.05) !important; }

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