<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Câu hỏi thường gặp</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN -->
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/Faq" />
          </div>

          <!-- MAIN CONTENT -->
          <div class="col-lg-9">
            
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">TRUNG TÂM TRỢ GIÚP (FAQ)</h2>
              <p class="text-muted mt-2 font-sans-vn">Tổng hợp các câu hỏi phổ biến nhất của khách hàng ZYRO.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 5" :key="i" class="shimmer rounded-3 w-100 mb-3" style="height: 70px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH (ACCORDION FAQ) -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <div class="accordion custom-accordion" id="accordionFaq">
                  
                  <!-- Câu 1 -->
                  <div class="accordion-item bg-transparent border-0 mb-3 rounded-4 shadow-sm overflow-hidden bg-white dark:bg-[#1a2533] border dark:border-gray-700">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button bg-transparent fw-bold text-dark dark:text-white shadow-none px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        1. Tôi có thể hủy đơn hàng sau khi đã đặt không?
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionFaq">
                      <div class="accordion-body text-muted dark:text-gray-400 px-4 pb-4 pt-1 lh-lg">
                        Được. Bạn có thể tự thao tác hủy đơn hàng trên website trong mục <strong>Đơn mua hàng</strong> MIỄN LÀ đơn hàng đó đang ở trạng thái <strong>Chờ xác nhận</strong>. Nếu đơn hàng đã chuyển sang Đang giao, vui lòng liên hệ CSKH để được hỗ trợ từ chối nhận hàng.
                      </div>
                    </div>
                  </div>

                  <!-- Câu 2 -->
                  <div class="accordion-item bg-transparent border-0 mb-3 rounded-4 shadow-sm overflow-hidden bg-white dark:bg-[#1a2533] border dark:border-gray-700">
                    <h2 class="accordion-header" id="headingTwo">
                      <button class="accordion-button collapsed bg-transparent fw-bold text-dark dark:text-white shadow-none px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        2. Làm sao để biết Size nào vừa với tôi?
                      </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFaq">
                      <div class="accordion-body text-muted dark:text-gray-400 px-4 pb-4 pt-1 lh-lg">
                        Bên trong mỗi trang chi tiết sản phẩm, ZYRO đều cung cấp <strong>Bảng Quy đổi Kích Cỡ (Size Guide)</strong> chi tiết theo chiều cao và cân nặng. Bạn cũng có thể nhắn tin trực tiếp cho ZYRO để được nhân viên tư vấn size chuẩn xác nhất.
                      </div>
                    </div>
                  </div>

                  <!-- Câu 3 -->
                  <div class="accordion-item bg-transparent border-0 mb-3 rounded-4 shadow-sm overflow-hidden bg-white dark:bg-[#1a2533] border dark:border-gray-700">
                    <h2 class="accordion-header" id="headingThree">
                      <button class="accordion-button collapsed bg-transparent fw-bold text-dark dark:text-white shadow-none px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        3. Thời gian hoàn tiền là bao lâu nếu tôi trả hàng?
                      </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFaq">
                      <div class="accordion-body text-muted dark:text-gray-400 px-4 pb-4 pt-1 lh-lg">
                        Ngay sau khi kho ZYRO nhận lại sản phẩm hoàn và kiểm tra hợp lệ, kế toán sẽ thực hiện lệnh hoàn tiền (Refund). Thời gian tiền về tài khoản ngân hàng thường từ <strong>24h - 48h làm việc</strong>. Đối với ví MoMo, thời gian xử lý từ <strong>1 - 3 ngày</strong>.
                      </div>
                    </div>
                  </div>

                  <!-- Câu 4 -->
                  <div class="accordion-item bg-transparent border-0 mb-3 rounded-4 shadow-sm overflow-hidden bg-white dark:bg-[#1a2533] border dark:border-gray-700">
                    <h2 class="accordion-header" id="headingFour">
                      <button class="accordion-button collapsed bg-transparent fw-bold text-dark dark:text-white shadow-none px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        4. Tôi có được kiểm tra hàng trước khi nhận không?
                      </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFaq">
                      <div class="accordion-body text-muted dark:text-gray-400 px-4 pb-4 pt-1 lh-lg">
                        <strong>CÓ.</strong> Nhằm đảm bảo quyền lợi, ZYRO cho phép bạn mở bưu phẩm để đồng kiểm (kiểm tra ngoại quan, màu sắc, số lượng) trước khi thanh toán cho Shipper. <i>Lưu ý: Không hỗ trợ mặc thử hoặc làm rách tem mác.</i>
                      </div>
                    </div>
                  </div>

               </div>

               <div class="mt-5 p-4 bg-urban-effect border border-urban border-opacity-25 rounded-4 text-center dark:bg-[#1a2533]">
                  <i class="bi bi-chat-heart text-urban fs-1 mb-3 d-block"></i>
                  <h6 class="fw-bold text-urban-dark dark:text-white mb-2">Bạn không tìm thấy câu trả lời?</h6>
                  <p class="text-muted small mb-4">Đội ngũ ZYRO luôn sẵn sàng hỗ trợ mọi thắc mắc của bạn.</p>
                  <router-link to="/contact" class="btn btn-urban rounded-pill px-4 fw-bold shadow-sm transition-all hover-transform">
                     Gửi Lời Nhắn Cho ZYRO
                  </router-link>
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
.btn-urban { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.2s ease; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important; }

/* TÙY CHỈNH ACCORDION */
.custom-accordion .accordion-button:not(.collapsed) {
   background-color: transparent !important;
   color: var(--color-c-hover, #547792) !important;
   box-shadow: none;
}
.custom-accordion .accordion-button::after {
   transition: transform 0.3s ease;
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
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.transition-all { transition: all 0.3s ease; }
</style>