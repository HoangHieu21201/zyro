<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách vận chuyển</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/shipping-policy" />
          </div>

          <div class="col-lg-9">
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">GIAO HÀNG & VẬN CHUYỂN</h2>
              <p class="text-muted mt-2 font-sans-vn">Nhanh chóng, an toàn và hỗ trợ đồng kiểm tận nơi.</p>
            </div>

            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 4" :key="i" class="shimmer rounded-4 w-100 mb-4" style="height: 120px;"></div>
            </div>

            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               
               <div class="alert border-0 bg-urban-effect dark:bg-[#1a2533] rounded-4 p-4 d-flex align-items-center gap-4 mb-5 shadow-sm">
                  <div class="bg-white dark:bg-[#212529] rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 shadow" style="width: 60px; height: 60px;">
                     <i class="bi bi-truck text-primary fs-3"></i>
                  </div>
                  <div>
                     <h5 class="fw-bold text-urban-dark dark:text-white mb-2">Freeship Toàn Quốc!</h5>
                     <p class="text-muted small mb-0 lh-lg">Miễn phí giao hàng tận nhà cho mọi đơn hàng thời trang có giá trị thanh toán cuối cùng từ <strong>1.000.000 VNĐ</strong> trở lên.</p>
                  </div>
               </div>

               <div class="row g-4 mb-5">
                 <div class="col-lg-6">
                   <div class="policy-section h-100">
                     <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-cash-coin text-warning me-2"></i> 1. Phí vận chuyển</h4>
                     <div class="bg-gray-50 dark:bg-[#1a2533] rounded-4 p-4 border border-gray-200 dark:border-gray-700 h-100">
                       <ul class="list-unstyled mb-0">
                          <li class="d-flex justify-content-between border-bottom dark:border-gray-600 pb-3 mb-3">
                             <span class="text-muted dark:text-gray-400">Nội thành Buôn Ma Thuột</span>
                             <strong class="text-urban-dark dark:text-white">15.000 VNĐ</strong>
                          </li>
                          <li class="d-flex justify-content-between border-bottom dark:border-gray-600 pb-3 mb-3">
                             <span class="text-muted dark:text-gray-400">Khu vực Miền Nam / Trung</span>
                             <strong class="text-urban-dark dark:text-white">25.000 VNĐ</strong>
                          </li>
                          <li class="d-flex justify-content-between">
                             <span class="text-muted dark:text-gray-400">Khu vực Miền Bắc & Xã/Huyện</span>
                             <strong class="text-urban-dark dark:text-white">35.000 VNĐ</strong>
                          </li>
                       </ul>
                     </div>
                   </div>
                 </div>

                 <div class="col-lg-6">
                   <div class="policy-section h-100">
                     <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-calendar2-check text-info me-2"></i> 2. Thời gian giao hàng</h4>
                     <div class="bg-gray-50 dark:bg-[#1a2533] rounded-4 p-4 border border-gray-200 dark:border-gray-700 h-100">
                       <ul class="list-unstyled mb-0">
                          <li class="d-flex justify-content-between border-bottom dark:border-gray-600 pb-3 mb-3">
                             <span class="text-muted dark:text-gray-400">Nội thành Buôn Ma Thuột</span>
                             <strong class="text-urban-dark dark:text-white">1 - 2 ngày</strong>
                          </li>
                          <li class="d-flex justify-content-between border-bottom dark:border-gray-600 pb-3 mb-3">
                             <span class="text-muted dark:text-gray-400">Khu vực Miền Nam / Trung</span>
                             <strong class="text-urban-dark dark:text-white">2 - 3 ngày</strong>
                          </li>
                          <li class="d-flex justify-content-between">
                             <span class="text-muted dark:text-gray-400">Khu vực Miền Bắc & Xã/Huyện</span>
                             <strong class="text-urban-dark dark:text-white">3 - 5 ngày</strong>
                          </li>
                       </ul>
                     </div>
                   </div>
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-box2-heart text-danger me-2"></i> 3. Đặc quyền Đồng Kiểm</h4>
                 <p class="lh-lg text-muted dark:text-gray-400 mb-3">Nhằm đảm bảo quyền lợi tuyệt đối cho khách hàng, ZYRO áp dụng chính sách <strong>Được kiểm tra hàng trước khi thanh toán</strong>.</p>
                 
                 <div class="p-4 rounded-4 bg-white dark:bg-[#1a2533] border border-gray-200 dark:border-gray-700">
                   <ul class="list-unstyled lh-lg mb-0 text-muted dark:text-gray-400">
                      <li class="mb-3 d-flex align-items-start">
                         <i class="bi bi-check-circle-fill text-success fs-5 me-3 mt-1"></i>
                         <div>Quý khách vui lòng mở gói hàng, kiểm tra chất liệu, kiểu dáng và số lượng đối chiếu với hóa đơn mua hàng.</div>
                      </li>
                      <li class="mb-3 d-flex align-items-start">
                         <i class="bi bi-exclamation-circle-fill text-warning fs-5 me-3 mt-1"></i>
                         <div><strong>Vui lòng KHÔNG:</strong> mặc thử, làm bẩn hoặc cắt mác (tag) sản phẩm trong quá trình kiểm tra với Shipper.</div>
                      </li>
                      <li class="mb-0 d-flex align-items-start">
                         <i class="bi bi-x-circle-fill text-danger fs-5 me-3 mt-1"></i>
                         <div>Nếu sản phẩm không đúng yêu cầu hoặc bị lỗi/dơ, quý khách <strong>có quyền từ chối nhận hàng</strong> và không phải trả bất kỳ chi phí nào.</div>
                      </li>
                   </ul>
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
