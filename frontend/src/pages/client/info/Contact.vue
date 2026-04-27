<template>
   <div class="policy-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] transition-all">

      <div class="pt-5 mt-4">
         <div class="zyro-container pt-3">

            <!-- BREADCRUMB -->
            <nav aria-label="breadcrumb" class="mb-4 mt-2">
               <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
                  <li class="breadcrumb-item"><router-link to="/"
                        class="text-decoration-none text-muted hover-urban">Trang chủ</router-link></li>
                  <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">Liên hệ với
                     ZYRO</li>
               </ol>
            </nav>

            <div class="row g-4 g-lg-5">
               <!-- SIDEBAR: ĐIỀU HƯỚNG THÔNG TIN (ĐÃ FIX PROP) -->
               <div class="col-lg-3 d-none d-lg-block">
                  <InfoNavigation currentRoute="/contact" />
               </div>

               <!-- MAIN CONTENT -->
               <div class="col-lg-9">

                  <div class="mb-4 pb-2 border-bottom dark:border-gray-700">
                     <h2
                        class="fw-bold text-urban-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn">
                        LIÊN HỆ CHÚNG TÔI</h2>
                     <p class="text-muted mt-2 font-sans-vn">Đội ngũ ZYRO luôn sẵn sàng lắng nghe và hỗ trợ bạn.</p>
                  </div>

                  <!-- SKELETON LOADER -->
                  <div v-if="isLoading" class="row g-4 pe-none">
                     <div class="col-md-5">
                        <div class="shimmer rounded-4 w-100 mb-4" style="height: 200px;"></div>
                        <div class="shimmer rounded-4 w-100" style="height: 300px;"></div>
                     </div>
                     <div class="col-md-7">
                        <div class="shimmer rounded-4 w-100 h-100" style="min-height: 525px;"></div>
                     </div>
                  </div>

                  <!-- NỘI DUNG CHÍNH (MAP & FORM) -->
                  <div v-else class="row g-4 animation-fade-in policy-content">

                     <!-- CỘT TRÁI: THÔNG TIN & BẢN ĐỒ -->
                     <div class="col-md-5 d-flex flex-column gap-4">
                        <!-- Thông tin liên hệ -->
                        <div
                           class="bg-urban-effect dark:bg-[#1a2533] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 font-sans-vn h-100">
                           <h5 class="fw-bold text-urban-dark dark:text-white mb-4"><i
                                 class="bi bi-headset text-urban me-2"></i>Trụ sở ZYRO</h5>

                           <div class="d-flex align-items-start mb-3">
                              <div
                                 class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                 <i class="bi bi-geo-alt-fill text-urban"></i>
                              </div>
                              <div>
                                 <div
                                    class="fw-bold text-dark dark:text-gray-200 small text-uppercase tracking-wide mb-1">
                                    Địa chỉ</div>
                                 <div class="text-muted small lh-base">Số 123 Đường Nguyễn Tất Thành, TP. Buôn Ma Thuột,
                                    Đắk Lắk.</div>
                              </div>
                           </div>

                           <div class="d-flex align-items-start mb-3">
                              <div
                                 class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                 <i class="bi bi-telephone-fill text-urban"></i>
                              </div>
                              <div>
                                 <div
                                    class="fw-bold text-dark dark:text-gray-200 small text-uppercase tracking-wide mb-1">
                                    Hotline (Miễn phí)</div>
                                 <div class="text-muted small fw-bold">1800.ZYRO (1800.9999)</div>
                              </div>
                           </div>

                           <div class="d-flex align-items-start mb-3">
                              <div
                                 class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                 <i class="bi bi-envelope-paper-fill text-urban"></i>
                              </div>
                              <div>
                                 <div
                                    class="fw-bold text-dark dark:text-gray-200 small text-uppercase tracking-wide mb-1">
                                    Email CSKH</div>
                                 <div class="text-muted small font-monospace">support@zyro.vn</div>
                              </div>
                           </div>

                           <div class="d-flex align-items-start">
                              <div
                                 class="icon-box-sm rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                 <i class="bi bi-clock-fill text-urban"></i>
                              </div>
                              <div>
                                 <div
                                    class="fw-bold text-dark dark:text-gray-200 small text-uppercase tracking-wide mb-1">
                                    Giờ hoạt động</div>
                                 <div class="text-muted small">08:00 - 22:00 (Thứ 2 - Chủ Nhật)</div>
                              </div>
                           </div>
                        </div>

                        <!-- Google Maps Iframe -->
                        <div
                           class="rounded-4 overflow-hidden shadow-sm border border-light-subtle dark:border-gray-700 bg-light"
                           style="height: 300px;">
                           <iframe
                              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124738.45508821035!2d107.95460595507304!3d12.686520399105417!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3171f7d3dd101183%3A0xc34e2c02b1fdb570!2zQnXDtG4gTWEgVGh14buZdCwgxJDhuq9rIEzhuq8sIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1700000000000!5m2!1svi!2s"
                              width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                              referrerpolicy="no-referrer-when-downgrade">
                           </iframe>
                        </div>
                     </div>

                     <!-- CỘT PHẢI: FORM LIÊN HỆ -->
                     <div class="col-md-7">
                        <div
                           class="bg-white dark:bg-[#1a2533] p-4 p-lg-5 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 font-sans-vn h-100">
                           <h5 class="fw-bold text-urban-dark dark:text-white mb-4"><i
                                 class="bi bi-send-fill text-urban me-2"></i>Gửi Lời Nhắn</h5>

                           <form @submit.prevent="submitContact" novalidate autocomplete="off">

                              <!-- HONEYPOT (Lừa Bot Spam) -->
                              <input type="text" v-model="form.website_url" style="display: none;" tabindex="-1"
                                 autocomplete="off">

                              <div class="row g-3">
                                 <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Họ và tên <span
                                          class="text-danger">*</span></label>
                                    <input type="text" class="form-control custom-input" v-model="form.name"
                                       placeholder="Vui lòng nhập họ tên" required>
                                 </div>

                                 <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Số điện
                                       thoại</label>
                                    <input type="tel" class="form-control custom-input" v-model="form.phone"
                                       placeholder="Vui lòng nhập SĐT">
                                 </div>

                                 <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email <span
                                          class="text-danger">*</span></label>
                                    <input type="email" class="form-control custom-input" v-model="form.email"
                                       placeholder="Vui lòng nhập Email" required>
                                 </div>

                                 <div class="col-12 mt-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Chủ đề <span
                                          class="text-danger">*</span></label>
                                    <input type="text" class="form-control custom-input" v-model="form.subject"
                                       placeholder="Bạn cần hỗ trợ vấn đề gì?" required>
                                 </div>

                                 <div class="col-12 mt-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Nội dung <span
                                          class="text-danger">*</span></label>
                                    <textarea class="form-control custom-input" rows="5" v-model="form.message"
                                       placeholder="Mô tả chi tiết vấn đề của bạn..." required></textarea>
                                 </div>

                                 <!-- GOOGLE RECAPTCHA WIDGET KÈM TEXT CẢNH BÁO LỖI -->
                                 <div class="col-12 mt-3 d-flex flex-column align-items-center align-items-md-start">
                                    <div id="recaptcha-wrapper"></div>
                                    <transition name="fade">
                                       <div v-if="captchaError"
                                          class="text-danger small mt-2 fw-bold d-flex align-items-center">
                                          <i class="bi bi-exclamation-triangle-fill me-1"></i> Vui lòng xác nhận bạn
                                          không phải là Robot!
                                       </div>
                                    </transition>
                                 </div>

                                 <div class="col-12 mt-4 text-end">
                                    <button type="submit"
                                       class="btn btn-urban rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm w-100 hover-transform"
                                       :disabled="isSubmitting">
                                       <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                                       {{ isSubmitting ? 'ĐANG GỬI...' : 'GỬI YÊU CẦU' }} <i v-if="!isSubmitting"
                                          class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                 </div>
                              </div>
                           </form>
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
import { ref, reactive, onMounted } from 'vue';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import InfoNavigation from '@/components/client/info/InfoNavigation.vue';

const isLoading = ref(true);
const isSubmitting = ref(false);
const captchaError = ref(false);

const recaptchaSiteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY;

const form = reactive({
   name: '',
   phone: '',
   email: '',
   subject: '',
   message: '',
   website_url: ''
});

// Hàm khởi tạo vẽ Captcha
const renderCaptcha = () => {
   if (window.grecaptcha && window.grecaptcha.render) {
      const wrapper = document.getElementById('recaptcha-wrapper');
      if (wrapper && wrapper.innerHTML === '') {
         try {
            window.grecaptcha.render('recaptcha-wrapper', {
               'sitekey': recaptchaSiteKey,
               'callback': (response) => {
                  // Ẩn dòng chữ lỗi màu đỏ đi khi người dùng đã check thành công
                  if (response) captchaError.value = false;
               }
            });
         } catch (error) {
            console.error('Lỗi render reCAPTCHA:', error);
         }
      }
   }
};

const simulateLoading = () => {
   setTimeout(() => {
      isLoading.value = false;

      // Đợi DOM cập nhật xong (biến mất Skeleton) thì mới gọi thư viện vẽ Captcha
      setTimeout(() => {
         renderCaptcha();
      }, 150);
   }, 400);
};

const validateForm = () => {
   if (form.name.trim().length < 2) { ZyroSwal.toastError('Họ tên quá ngắn!'); return false; }
   const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
   if (!emailRegex.test(form.email)) { ZyroSwal.toastError('Định dạng email không hợp lệ!'); return false; }
   if (form.phone && form.phone.trim() !== '') {
      const phoneRegex = /^(0[3|5|7|8|9])+([0-9]{8})$/;
      if (!phoneRegex.test(form.phone)) { ZyroSwal.toastError('Số điện thoại không đúng chuẩn VN!'); return false; }
   }
   if (form.subject.trim().length < 5) { ZyroSwal.toastError('Chủ đề liên hệ quá ngắn!'); return false; }
   if (form.message.trim().length < 10) { ZyroSwal.toastError('Nội dung tin nhắn tối thiểu 10 ký tự!'); return false; }
   return true;
};

const submitContact = async () => {
   if (!validateForm()) return;

   // Lấy mã xác thực từ reCAPTCHA widget
   const recaptchaToken = window.grecaptcha ? window.grecaptcha.getResponse() : '';
   if (!recaptchaToken) {
      captchaError.value = true;
      ZyroSwal.toastError('Vui lòng xác thực Captcha!'); // Rút ngắn chữ để vừa khung Toast
      return;
   }

   captchaError.value = false;
   isSubmitting.value = true;

   // Gắn mã token vào form payload
   const payload = { ...form, 'g-recaptcha-response': recaptchaToken };

   try {
      const res = await api.post('/client/contact', payload);
      if (res.data.success) {
         ZyroSwal.toastSuccess(res.data.message);
         form.name = ''; form.phone = ''; form.email = ''; form.subject = ''; form.message = ''; form.website_url = '';
         // Đặt lại trạng thái reCAPTCHA sau khi gửi thành công
         if (window.grecaptcha) window.grecaptcha.reset();
      }
   } catch (error) {
      if (error.response?.status === 429) ZyroSwal.toastError('Bạn thao tác quá nhanh. Vui lòng đợi 1 phút.');
      else ZyroSwal.toastError(error.response?.data?.message || 'Hệ thống bận, vui lòng thử lại sau.');

      // Đặt lại reCAPTCHA nếu bị lỗi (để user bấm lại)
      if (window.grecaptcha) window.grecaptcha.reset();
   } finally {
      isSubmitting.value = false;
   }
};

onMounted(() => {
   window.scrollTo(0, 0);

   // Thiết lập callback khi Google Script tải xong
   window.onRecaptchaLoadCallback = () => {
      renderCaptcha();
   };

   // Thêm flag render=explicit để báo Google đừng tự tìm, mình sẽ tự chỉ định chỗ vẽ
   if (!document.getElementById('recaptcha-script')) {
      const script = document.createElement('script');
      script.id = 'recaptcha-script';
      script.src = "https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoadCallback&render=explicit";
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
   }

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

.text-urban-dark {
   color: var(--color-c-dark, #213448) !important;
}

.text-urban {
   color: var(--color-c-hover, #547792) !important;
}

.bg-urban-effect {
   background-color: rgba(84, 119, 146, 0.05) !important;
}

.btn-urban {
   background-color: var(--color-c-dark, #213448);
   color: white;
   border: none;
   transition: 0.2s ease;
}

.btn-urban:hover {
   background-color: var(--color-c-hover, #547792);
   color: white;
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
   width: 36px;
   height: 36px;
   background-color: #ffffff;
   border: 1px solid rgba(84, 119, 146, 0.2);
}

html.dark .icon-box-sm {
   background-color: #2b3035;
   border-color: rgba(255, 255, 255, 0.1);
}

.custom-input {
   background-color: #f8f9fa;
   border: 1.5px solid transparent;
   color: var(--color-c-dark);
   padding: 0.75rem 1rem;
   font-size: 0.95rem;
   border-radius: 0.5rem;
   transition: all 0.2s ease-in-out;
   box-shadow: none !important;
}

html.dark .custom-input {
   background-color: #212529;
   border-color: #373b3e;
   color: white;
}

.custom-input:focus {
   border-color: var(--color-c-hover) !important;
   background-color: #ffffff;
   outline: none;
   box-shadow: 0 0 0 3px rgba(84, 119, 146, 0.15) !important;
}

html.dark .custom-input:focus {
   background-color: #121416;
   box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.05) !important;
}

.custom-input::placeholder {
   color: #adb5bd;
}

.hover-transform {
   transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-transform:hover:not(:disabled) {
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

.fade-enter-active,
.fade-leave-active {
   transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
   opacity: 0;
}
</style>