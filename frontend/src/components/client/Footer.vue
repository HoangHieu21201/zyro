<!-- File: frontend/src/components/client/Footer.vue -->
<template>
  <footer class="client-footer pt-5 pb-4" style="background-color: var(--color-c-dark); color: #ffffff;">
    <!-- SỬ DỤNG GLOBAL CONTAINER -->
    <div class="zyro-container pt-3">
      
      <div class="row g-5 mb-5 pb-5 border-bottom border-light border-opacity-25">
        
        <!-- Brand -->
        <div class="col-lg-4 col-md-12">
          <!-- Logo to đùng uy tín -->
          <img :src="logoSrc" @error="handleLogoError" alt="ZYRO" class="footer-logo mb-4 transition-all hover-opacity">
          <p class="text-white-50 small pe-lg-5 lh-lg mb-0">
            Trở thành người đầu tiên khám phá các bộ sưu tập mới, những câu chuyện truyền cảm hứng và nhận đặc quyền dành riêng cho khách hàng của ZYRO.
          </p>
        </div>
        
        <!-- Links Columns -->
        <div class="col-lg-2 col-md-4 col-6">
          <h6 class="text-uppercase fw-bold mb-4 letter-spacing-1 text-white">Về ZYRO</h6>
          <ul class="list-unstyled d-flex flex-column gap-3 small">
            <li><router-link to="/about-us" class="text-decoration-none text-white-50 footer-link">Câu chuyện thương hiệu</router-link></li>
            <li><router-link to="/stores" class="text-decoration-none text-white-50 footer-link">Cửa hàng hệ thống</router-link></li>
            <li><router-link to="/careers" class="text-decoration-none text-white-50 footer-link">Tuyển dụng</router-link></li>
            <li><router-link to="/social-responsibility" class="text-decoration-none text-white-50 footer-link">Trách nhiệm xã hội</router-link></li>
          </ul>
        </div>
        
        <div class="col-lg-2 col-md-4 col-6">
          <h6 class="text-uppercase fw-bold mb-4 letter-spacing-1 text-white">Dịch Vụ</h6>
          <ul class="list-unstyled d-flex flex-column gap-3 small">
            <li><router-link to="/track-order" class="text-decoration-none text-white-50 footer-link">Tra cứu đơn hàng</router-link></li>
            <li><router-link to="/shipping-policy" class="text-decoration-none text-white-50 footer-link">Chính sách vận chuyển</router-link></li>
            <li><router-link to="/return-policy" class="text-decoration-none text-white-50 footer-link">Chính sách đổi trả</router-link></li>
            <li><router-link to="/faq" class="text-decoration-none text-white-50 footer-link">Câu hỏi thường gặp (FAQ)</router-link></li>
          </ul>
        </div>
        
        <!-- Contact, Social & Newsletter -->
        <div class="col-lg-4 col-md-4 col-12">
           <h6 class="text-uppercase fw-bold mb-4 letter-spacing-1 text-white">Liên Hệ & Kết Nối</h6>
           <div class="text-white-50 small mb-4 lh-lg">
             <p class="mb-1"><i class="bi bi-telephone me-2 text-white"></i> Hotline: <strong class="text-white">1900 9999</strong> (9:00 - 21:00)</p>
             <p class="mb-0"><i class="bi bi-envelope me-2 text-white"></i> Email: <strong class="text-white">support@zyro.vn</strong></p>
           </div>
           
           <div class="d-flex gap-3 mb-4">
             <a href="#" class="social-icon text-white rounded-circle d-flex align-items-center justify-content-center border border-white border-opacity-25 transition-all"><i class="bi bi-facebook"></i></a>
             <a href="#" class="social-icon text-white rounded-circle d-flex align-items-center justify-content-center border border-white border-opacity-25 transition-all"><i class="bi bi-instagram"></i></a>
             <a href="#" class="social-icon text-white rounded-circle d-flex align-items-center justify-content-center border border-white border-opacity-25 transition-all"><i class="bi bi-youtube"></i></a>
             <a href="#" class="social-icon text-white rounded-circle d-flex align-items-center justify-content-center border border-white border-opacity-25 transition-all"><i class="bi bi-tiktok"></i></a>
           </div>

           <!-- ĐÃ FIX: Form nhập email được dời sang đây, bọc thành UI nút khối xịn xò -->
           <div class="mt-2 pt-2">
             <p class="small text-white-50 mb-2 fst-italic">Đăng ký nhận thông tin ưu đãi mới nhất:</p>
             <form class="input-group shadow-sm" @submit.prevent="subscribeNewsletter">
               <input type="email" class="form-control border-0 px-3 py-2.5 shadow-none" placeholder="Nhập email của bạn..." required>
               <button type="submit" class="btn bg-white text-dark fw-bold text-uppercase text-nowrap px-4 hover-opacity transition-all" style="border-left: 1px solid #dee2e6;">Đăng ký</button>
             </form>
           </div>
        </div>
      </div>
      
      <!-- Bottom Footer -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
         <div class="d-flex gap-4 small fw-semibold text-white-50 order-2 order-md-1">
            <router-link to="/terms-of-service" class="text-decoration-none text-white-50 footer-link">Điều khoản sử dụng</router-link>
            <router-link to="/privacy-policy" class="text-decoration-none text-white-50 footer-link">Chính sách bảo mật dữ liệu</router-link>
         </div>
         
         <div class="d-flex gap-3 align-items-center order-1 order-md-2 text-white-50 opacity-75">
            <i class="bi bi-credit-card-fill fs-4" title="Thanh toán thẻ"></i>
            <i class="bi bi-paypal fs-4" title="Ví điện tử"></i>
            <i class="bi bi-cash-coin fs-4" title="Thanh toán khi nhận hàng"></i>
         </div>

         <div class="small text-white-50 order-3 text-center text-md-end font-monospace" style="font-size: 0.75rem;">
           &copy; {{ new Date().getFullYear() }} ZYRO STUDIOS. TẤT CẢ BẢN QUYỀN ĐƯỢC BẢO LƯU.
         </div>
      </div>

    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue';
import logoImage from '@/assets/images/logo/logozyro.png';
import placeholderImage from '@/assets/images/defaults/client_placeholder.png';

const logoSrc = ref(logoImage);
const handleLogoError = (e) => { e.target.src = placeholderImage; };
const subscribeNewsletter = () => { console.log("Subscribed"); };
</script>

<style scoped>
.client-footer { font-family: inherit; }
.footer-logo { height: 120px; object-fit: contain; }
.letter-spacing-1 { letter-spacing: 1px; }

/* Tiện ích link */
.footer-link { position: relative; transition: color 0.3s ease; }
.footer-link:hover { color: #ffffff !important; }

/* Tiện ích icon */
.social-icon { width: 36px; height: 36px; background-color: transparent; text-decoration: none; }
.social-icon:hover { background-color: #ffffff; color: var(--color-c-dark) !important; border-color: #ffffff !important; transform: translateY(-3px); }

/* Helpers */
.text-nowrap { white-space: nowrap; }
.hover-opacity { transition: opacity 0.2s ease; }
.hover-opacity:hover { opacity: 0.8; }
.transition-all { transition: all 0.3s ease; }

/* Custom Form Focus */
.form-control:focus { outline: none; box-shadow: none; background-color: #f8f9fa; }
</style>