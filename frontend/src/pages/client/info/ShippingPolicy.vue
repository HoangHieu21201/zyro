<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách vận chuyển</li>
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
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">CHÍNH SÁCH VẬN CHUYỂN</h2>
              <p class="text-muted mt-2 font-sans-vn">Thông tin về phí giao hàng và thời gian nhận hàng tại ZYRO.</p>
            </div>

            <!-- SKELETON LOADER -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 4" :key="i" class="shimmer rounded-3 w-100 mb-4" style="height: 120px;"></div>
            </div>

            <!-- NỘI DUNG CHÍNH -->
            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <div class="alert border-0 bg-urban-effect dark:bg-[#1a2533] rounded-4 p-4 d-flex align-items-start gap-3 mb-5 shadow-sm">
                  <div class="bg-white dark:bg-[#212529] rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px;">
                     <i class="bi bi-truck text-urban fs-4"></i>
                  </div>
                  <div>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-1">Miễn phí giao hàng toàn quốc!</h5>
                     <p class="text-muted small mb-0 lh-lg">Áp dụng cho mọi đơn hàng có giá trị thanh toán cuối cùng từ <strong>1.000.000 VNĐ</strong> trở lên.</p>
                  </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">1. Phí vận chuyển (Shipping Fee)</h4>
                 <ul class="list-unstyled lh-lg mt-3">
                    <li class="mb-3 d-flex align-items-start">
                       <i class="bi bi-chevron-right text-urban mt-1 me-2"></i>
                       <div><strong>Đơn hàng dưới 1.000.000đ:</strong> Phí vận chuyển đồng giá <strong class="text-danger">30.000đ</strong> áp dụng trên toàn quốc.</div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                       <i class="bi bi-chevron-right text-urban mt-1 me-2"></i>
                       <div><strong>Đơn hàng từ 1.000.000đ trở lên:</strong> <strong class="text-success">Miễn phí vận chuyển (Freeship)</strong>.</div>
                    </li>
                 </ul>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">2. Thời gian giao hàng dự kiến</h4>
                 <div class="table-responsive">
                    <table class="table table-bordered border-light-subtle dark:border-gray-700 mt-2 text-dark dark:text-gray-300">
                       <thead class="bg-light dark:bg-[#1a2533]">
                          <tr>
                             <th class="py-3">Khu vực nhận hàng</th>
                             <th class="py-3">Thời gian dự kiến</th>
                          </tr>
                       </thead>
                       <tbody>
                          <tr>
                             <td class="py-3">Nội thành TP. Buôn Ma Thuột</td>
                             <td class="py-3 fw-medium text-urban">1 - 2 ngày làm việc</td>
                          </tr>
                          <tr>
                             <td class="py-3">Khu vực Miền Nam / Miền Trung</td>
                             <td class="py-3 fw-medium">2 - 3 ngày làm việc</td>
                          </tr>
                          <tr>
                             <td class="py-3">Khu vực Miền Bắc & Các Huyện/Xã xa</td>
                             <td class="py-3 fw-medium">3 - 5 ngày làm việc</td>
                          </tr>
                       </tbody>
                    </table>
                 </div>
                 <p class="text-muted small mt-2 fst-italic">* Thời gian giao hàng có thể kéo dài hơn dự kiến trong các dịp Lễ, Tết hoặc điều kiện thời tiết khắc nghiệt.</p>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">3. Quy định kiểm tra hàng</h4>
                 <p class="lh-lg">Nhằm đảm bảo quyền lợi cho khách hàng, ZYRO áp dụng chính sách <strong>Được kiểm tra hàng trước khi thanh toán (Đồng kiểm)</strong>.</p>
                 <ul class="list-unstyled lh-lg mt-2">
                    <li class="mb-2 d-flex align-items-start">
                       <i class="bi bi-check2 text-urban mt-1 me-2 fs-5"></i>
                       <div>Quý khách vui lòng mở gói hàng và kiểm tra đối chiếu mẫu mã, màu sắc, số lượng với hóa đơn.</div>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                       <i class="bi bi-check2 text-urban mt-1 me-2 fs-5"></i>
                       <div><strong>Lưu ý:</strong> Vui lòng KHÔNG mặc thử, KHÔNG làm bẩn hoặc cắt mác sản phẩm trong quá trình kiểm tra.</div>
                    </li>
                    <li class="d-flex align-items-start">
                       <i class="bi bi-x text-danger mt-1 me-2 fs-4"></i>
                       <div>Nếu sản phẩm không đúng yêu cầu hoặc bị lỗi/dơ, quý khách có quyền từ chối nhận hàng và không phải trả bất kỳ chi phí nào.</div>
                    </li>
                 </ul>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide">4. Theo dõi hành trình đơn hàng</h4>
                 <p class="lh-lg">Ngay sau khi đơn hàng được giao cho đơn vị vận chuyển, hệ thống sẽ gửi Email xác nhận kèm Mã vận đơn (Tracking Code). Quý khách có thể theo dõi hành trình gói hàng bằng 2 cách:</p>
                 <ol class="lh-lg">
                    <li>Truy cập mục <strong>"Tài khoản > Đơn mua hàng"</strong> trên website ZYRO.</li>
                    <li>Sử dụng tính năng <strong>"Tra cứu đơn hàng"</strong> ngoài trang chủ.</li>
                 </ol>
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