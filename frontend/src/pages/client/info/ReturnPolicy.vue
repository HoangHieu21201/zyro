<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách Đổi trả & Hoàn tiền</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN -->
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/return-policy" />
          </div>

          <!-- MAIN CONTENT -->
          <div class="col-lg-9">
            
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">CHÍNH SÁCH ĐỔI TRẢ (RMA)</h2>
              <p class="text-muted mt-2 font-sans-vn">Cam kết mang lại sự an tâm tuyệt đối khi mua sắm tại ZYRO.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 4" :key="i" class="shimmer rounded-3 w-100 mb-4" style="height: 150px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <p class="lh-lg mb-4">Với mong muốn khách hàng có được trải nghiệm hài lòng nhất, ZYRO hỗ trợ khách hàng đổi trả sản phẩm hoặc hoàn tiền theo các điều kiện và quy định dưới đây.</p>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">1. Điều kiện áp dụng đổi trả</h4>
                 <p class="lh-lg">Khách hàng được hỗ trợ đổi size, đổi mẫu hoặc trả hàng hoàn tiền nếu thỏa mãn TẤT CẢ các điều kiện sau:</p>
                 <ul class="list-unstyled lh-lg mt-3 bg-urban-effect dark:bg-[#1a2533] p-4 rounded-4">
                    <li class="mb-2 d-flex align-items-start">
                       <i class="bi bi-asterisk text-urban mt-1 me-3"></i>
                       <div>Thời gian: Trong vòng <strong>07 ngày</strong> kể từ ngày hệ thống cập nhật giao hàng thành công.</div>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                       <i class="bi bi-asterisk text-urban mt-1 me-3"></i>
                       <div>Tình trạng sản phẩm: Sản phẩm phải còn <strong>nguyên tem mác, nguyên tag, chưa qua sử dụng, chưa giặt ủi</strong> và không bị dính bẩn hay hư hỏng do tác động từ phía khách hàng.</div>
                    </li>
                    <li class="d-flex align-items-start">
                       <i class="bi bi-asterisk text-urban mt-1 me-3"></i>
                       <div>Giấy tờ: Yêu cầu cung cấp hóa đơn mua hàng (hoặc thông tin số điện thoại đặt hàng) và video quay rõ cảnh khui kiện hàng.</div>
                    </li>
                 </ul>
                 <div class="alert border border-danger border-opacity-25 bg-white dark:bg-[#212529] mt-3 small">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> <strong>Lưu ý:</strong> Các sản phẩm thuộc danh mục: Đồ lót, Phụ kiện (tất, mũ, vòng cổ), Sản phẩm xả kho (Flash Sale > 50%) sẽ KHÔNG được áp dụng chính sách đổi trả, trừ trường hợp lỗi do nhà sản xuất.
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">2. Chi phí đổi trả</h4>
                 <ul class="list-unstyled lh-lg">
                    <li class="mb-3 d-flex align-items-start">
                       <i class="bi bi-dot text-muted fs-3 lh-1 me-1"></i>
                       <div><strong>Lỗi từ phía ZYRO:</strong> (Giao sai mẫu, sai màu, sai size, hàng bị lỗi/rách). ZYRO sẽ chịu 100% chi phí vận chuyển 2 chiều để đổi sản phẩm mới hoặc hoàn tiền cho bạn.</div>
                    </li>
                    <li class="d-flex align-items-start">
                       <i class="bi bi-dot text-muted fs-3 lh-1 me-1"></i>
                       <div><strong>Lý do từ phía khách hàng:</strong> (Không vừa size, đổi ý muốn sang mẫu khác). Khách hàng sẽ thanh toán chi phí vận chuyển để gửi hàng về kho ZYRO và phí vận chuyển đơn hàng mới (nếu có).</div>
                    </li>
                 </ul>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">3. Quy trình thực hiện Đổi/Trả</h4>
                 <div class="row g-4 mt-2">
                    <div class="col-md-4">
                       <div class="p-3 border dark:border-gray-700 rounded-4 text-center h-100 shadow-sm bg-white dark:bg-[#1a2533]">
                          <div class="bg-urban text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 fs-5 fw-bold" style="width: 40px; height: 40px;">1</div>
                          <h6 class="fw-bold text-urban-dark dark:text-white">Gửi Yêu Cầu</h6>
                          <p class="small text-muted mb-0">Truy cập "Đơn mua hàng" -> Chọn "Hoàn trả/Đổi hàng" và ghi rõ lý do.</p>
                       </div>
                    </div>
                    <div class="col-md-4">
                       <div class="p-3 border dark:border-gray-700 rounded-4 text-center h-100 shadow-sm bg-white dark:bg-[#1a2533]">
                          <div class="bg-urban text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 fs-5 fw-bold" style="width: 40px; height: 40px;">2</div>
                          <h6 class="fw-bold text-urban-dark dark:text-white">Kiểm Duyệt</h6>
                          <p class="small text-muted mb-0">Bộ phận CSKH sẽ liên hệ xác nhận và hướng dẫn bạn đóng gói gửi hàng về kho.</p>
                       </div>
                    </div>
                    <div class="col-md-4">
                       <div class="p-3 border dark:border-gray-700 rounded-4 text-center h-100 shadow-sm bg-white dark:bg-[#1a2533]">
                          <div class="bg-urban text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 fs-5 fw-bold" style="width: 40px; height: 40px;">3</div>
                          <h6 class="fw-bold text-urban-dark dark:text-white">Xử lý RMA</h6>
                          <p class="small text-muted mb-0">Sau khi nhận và kiểm tra hàng, ZYRO sẽ tiến hành chuyển khoản hoàn tiền hoặc gửi SP mới.</p>
                       </div>
                    </div>
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">4. Chính sách hoàn tiền (Refund)</h4>
                 <p class="lh-lg">Nếu quý khách yêu cầu trả hàng và hoàn tiền, thời gian xử lý sẽ phụ thuộc vào phương thức quý khách đã thanh toán:</p>
                 <ul class="lh-lg">
                    <li><strong>Thanh toán COD (Tiền mặt):</strong> Hoàn tiền qua Chuyển khoản Ngân hàng nội địa trong vòng <strong>24h - 48h</strong> làm việc sau khi kho xác nhận nhận lại hàng.</li>
                    <li><strong>Thanh toán qua MoMo:</strong> Hoàn tiền trực tiếp vào tài khoản MoMo của quý khách trong vòng <strong>1 - 3 ngày</strong> làm việc.</li>
                 </ul>
                 <p class="lh-lg fst-italic text-muted">Số tiền hoàn lại sẽ trừ đi phí vận chuyển ban đầu (nếu đơn hàng bị lỗi không xuất phát từ phía ZYRO).</p>
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
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
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