<template>
  <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">
    <div class="pt-5 mt-4">
      <div class="zyro-container pt-3">
        <nav aria-label="breadcrumb" class="mb-4 mt-2">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Chính sách Đổi trả</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3 d-none d-lg-block">
            <InfoNavigation currentRoute="/return-policy" />
          </div>

          <div class="col-lg-9">
            <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
              <h2 class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">ĐỔI TRẢ & HOÀN TIỀN</h2>
              <p class="text-muted mt-2 font-sans-vn">Cam kết mang lại sự an tâm tuyệt đối khi mua sắm thời trang tại ZYRO.</p>
            </div>

            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 4" :key="i" class="shimmer rounded-4 w-100 mb-4" style="height: 120px;"></div>
            </div>

            <div v-else class="policy-content font-sans-vn animation-fade-in text-dark dark:text-gray-300">
               <p class="lh-lg mb-4 text-muted dark:text-gray-400">Với mong muốn khách hàng luôn có trải nghiệm hài lòng nhất, ZYRO hỗ trợ đổi size, đổi mẫu hoặc hoàn tiền tận tình theo các điều kiện cực kỳ minh bạch dưới đây.</p>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-box-seam text-urban me-2"></i> 1. Điều kiện áp dụng</h4>
                 <div class="bg-urban-effect dark:bg-[#1a2533] p-4 rounded-4 border border-urban border-opacity-10 mb-3">
                   <ul class="list-unstyled lh-lg mb-0 text-muted dark:text-gray-300">
                      <li class="mb-3 d-flex align-items-start">
                         <i class="bi bi-clock-history text-primary fs-4 me-3" style="margin-top: 4px;"></i>
                         <div><strong>Thời gian:</strong> Trong vòng <strong>07 ngày</strong> kể từ ngày hệ thống cập nhật giao hàng thành công.</div>
                      </li>
                      <li class="mb-3 d-flex align-items-start">
                         <i class="bi bi-tags-fill text-success fs-4 me-3" style="margin-top: 4px;"></i>
                         <div><strong>Tình trạng:</strong> Sản phẩm phải còn <strong>nguyên tem mác, chưa qua sử dụng, chưa giặt ủi</strong> và không bị dính bẩn hay hư hỏng do tác động từ khách hàng.</div>
                      </li>
                      <li class="mb-0 d-flex align-items-start">
                         <i class="bi bi-camera-video-fill text-danger fs-4 me-3" style="margin-top: 4px;"></i>
                         <div><strong>Bảo vệ quyền lợi:</strong> Quý khách vui lòng cung cấp video quay rõ cảnh khui kiện hàng để được hỗ trợ nhanh nhất.</div>
                      </li>
                   </ul>
                 </div>
                 
                 <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 rounded-4 p-3 d-flex align-items-center">
                   <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3"></i>
                   <span class="small text-danger"><strong>Lưu ý:</strong> Đồ lót, Phụ kiện, và Hàng Sale (Giảm giá > 50%) KHÔNG áp dụng chính sách đổi trả, trừ lỗi do nhà sản xuất.</span>
                 </div>
               </div>

               <div class="policy-section mb-5">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-4 tracking-wide"><i class="bi bi-arrow-repeat text-urban me-2"></i> 2. Quy trình thực hiện</h4>
                 <div class="row g-3">
                   <div class="col-md-4">
                     <div class="p-4 text-center rounded-4 border border-gray-200 dark:border-gray-700 h-100 hover-transform bg-white dark:bg-[#212529]">
                       <div class="text-white rounded-circle d-inline-flex justify-content-center align-items-center fw-bold fs-4 mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0d6efd, #0dcaf0); box-shadow: 0 4px 10px rgba(13,110,253,0.2);">1</div>
                       <h6 class="fw-bold mb-2">Gửi Yêu Cầu</h6>
                       <p class="small text-muted mb-0">Vào <strong>"Đơn mua hàng"</strong> <i class="bi bi-caret-right-fill text-primary mx-1"></i> Chọn <strong>"Đổi/Trả hàng"</strong>, ghi rõ lý do hoặc liên hệ Zalo CSKH.</p>
                     </div>
                   </div>
                   <div class="col-md-4">
                     <div class="p-4 text-center rounded-4 border border-gray-200 dark:border-gray-700 h-100 hover-transform bg-white dark:bg-[#212529]">
                       <div class="text-white rounded-circle d-inline-flex justify-content-center align-items-center fw-bold fs-4 mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #198754, #20c997); box-shadow: 0 4px 10px rgba(25,135,84,0.2);">2</div>
                       <h6 class="fw-bold mb-2">Kiểm Duyệt</h6>
                       <p class="small text-muted mb-0">Bộ phận CSKH sẽ gọi xác nhận, hướng dẫn đóng gói và gửi shipper tới lấy hàng.</p>
                     </div>
                   </div>
                   <div class="col-md-4">
                     <div class="p-4 text-center rounded-4 border border-gray-200 dark:border-gray-700 h-100 hover-transform bg-white dark:bg-[#212529]">
                       <div class="text-white rounded-circle d-inline-flex justify-content-center align-items-center fw-bold fs-4 mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #fd7e14, #ffc107); box-shadow: 0 4px 10px rgba(253,126,20,0.2);">3</div>
                       <h6 class="fw-bold mb-2">Đổi Mới / Hoàn Tiền</h6>
                       <p class="small text-muted mb-0">ZYRO nhận hàng, kiểm tra và lập tức giao sản phẩm mới hoặc chuyển khoản hoàn tiền cho bạn.</p>
                     </div>
                   </div>
                 </div>
               </div>

               <div class="policy-section mb-4">
                 <h4 class="fw-bold text-urban-dark dark:text-white mb-3 tracking-wide"><i class="bi bi-wallet2 text-urban me-2"></i> 3. Chi phí đổi trả</h4>
                 <ul class="list-unstyled lh-lg text-muted dark:text-gray-400">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-urban me-2"></i><strong>Lỗi từ ZYRO (sai màu, sai size, hàng lỗi):</strong> ZYRO chịu 100% chi phí vận chuyển 2 chiều.</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-urban me-2"></i><strong>Khách hàng muốn đổi size/mẫu:</strong> Khách hàng vui lòng thanh toán phí vận chuyển 1 chiều.</li>
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
