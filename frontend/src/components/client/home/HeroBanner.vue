<template>
  <section class="hero-section position-relative bg-light dark:bg-[#121416]">
    
    <!-- 1. SKELETON LOADER (HIỂN THỊ KHI ĐANG TẢI DỮ LIỆU) -->
    <div v-if="isLoading" class="h-100 w-100 position-relative d-flex align-items-center justify-content-center overflow-hidden">
      <!-- Nền shimmer -->
      <div class="position-absolute top-0 start-0 w-100 h-100 shimmer-bg"></div>
      
      <!-- Khối giả lập nội dung -->
      <div class="position-relative z-1 d-flex flex-column align-items-center w-100 px-4">
        <div class="shimmer-element rounded-pill mb-4" style="width: 180px; height: 16px;"></div>
        <div class="shimmer-element rounded-3 mb-5" style="width: 70%; max-width: 600px; height: 60px;"></div>
        <div class="shimmer-element rounded-pill mt-4" style="width: 180px; height: 45px;"></div>
      </div>
    </div>

    <!-- 2. KHUNG ĐỘNG (HIỂN THỊ KHI CÓ DATABASE) -->
    <div v-else-if="banners && banners.length > 0" id="heroCarousel" class="carousel slide carousel-fade h-100 animation-fade-in" data-bs-ride="carousel" data-bs-interval="5000">
      
      <!-- Chấm tròn -->
      <div class="carousel-indicators z-index-3 mb-4 pb-2">
        <button v-for="(banner, index) in banners" :key="'ind' + banner.id" type="button" 
                data-bs-target="#heroCarousel" :data-bs-slide-to="index" 
                :class="{ active: index === 0 }" :aria-current="index === 0 ? 'true' : 'false'">
        </button>
      </div>
      
      <!-- Slides -->
      <div class="carousel-inner h-100">
        <div v-for="(banner, index) in banners" :key="banner.id" class="carousel-item h-100" :class="{ active: index === 0 }">
          
          <div class="hero-bg position-absolute top-0 start-0 w-100 h-100 d-none d-md-block" :style="{ backgroundImage: `url('${banner.image_desktop}')` }"></div>
          <div class="hero-bg position-absolute top-0 start-0 w-100 h-100 d-block d-md-none" :style="{ backgroundImage: `url('${banner.image_mobile || banner.image_desktop}')` }"></div>
          
          <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-40"></div>
          
          <!-- Chỉnh vị trí (bám bottom), cách lề dưới 1 khoảng để không đè lên chấm tròn -->
          <div class="hero-content position-absolute bottom-26 start-50 translate-middle-x w-100 z-1 text-center px-4 d-flex flex-column align-items-center pb-5 mb-5">
            
            <span class="d-inline-block mb-3 fw-bold tracking-widest text-uppercase small px-3 py-1 border border-light border-opacity-25 rounded-pill text-white" style="background: rgba(0,0,0,0.2); backdrop-filter: blur(4px);">
              ZYRO EXCLUSIVE
            </span>
            
            <h1 class="display-2 fw-bolder mb-3 text-uppercase text-white text-shadow-lg font-sans-vn" style="letter-spacing: 3px; max-width: 900px; line-height: 1.2;">
              {{ banner.title }}
            </h1>
            
            <a :href="banner.target_url || '/category'" target="_blank" rel="noopener noreferrer" 
               class="btn rounded-pill px-4 py-2.5 fw-semibold text-uppercase mt-4 shadow-sm hover-scale-glass transition-all d-inline-flex align-items-center gap-2 font-sans-vn text-white border border-light border-opacity-50" 
               style="background: rgba(0,0,0,0.25); backdrop-filter: blur(6px); letter-spacing: 1px; font-size: 0.85rem;">
              Khám Phá Ngay <i class="bi bi-arrow-right fs-6"></i>
            </a>

          </div>
        </div>
      </div>

      <!-- Mũi tên qua lại -->
      <button v-if="banners.length > 1" class="carousel-control-prev z-index-3" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Trái</span>
      </button>
      <button v-if="banners.length > 1" class="carousel-control-next z-index-3" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Phải</span>
      </button>
    </div>

    <!-- 3. KHUNG FALLBACK (CHẠY NẾU DATABASE TRỐNG HOÀN TOÀN) -->
    <!-- ĐÃ FIX: Chỉnh flex để bám bottom tương tự -->
    <div v-else class="h-100 w-100 position-relative d-flex align-items-end justify-content-center text-center pb-5 mb-5">
      <div class="hero-bg position-absolute top-0 start-0 w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop');"></div>
      <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-40"></div>
      
      <div class="hero-content position-relative z-1 px-4 d-flex flex-column align-items-center pb-3">
        <span class="d-inline-block mb-3 fw-bold tracking-widest text-uppercase small px-3 py-1 border border-light border-opacity-25 rounded-pill text-white" style="background: rgba(0,0,0,0.2); backdrop-filter: blur(4px);">
          ZYRO EXCLUSIVE
        </span>
        <h1 class="display-2 fw-bolder mb-3 text-uppercase text-white text-shadow-lg font-sans-vn" style="letter-spacing: 3px; max-width: 900px; line-height: 1.2;">
          XU HƯỚNG THỜI TRANG MỚI
        </h1>
        
        <router-link to="/category" class="btn rounded-pill px-4 py-2.5 fw-semibold text-uppercase mt-4 shadow-sm hover-scale-glass transition-all d-inline-flex align-items-center gap-2 font-sans-vn text-white border border-light border-opacity-50" 
             style="background: rgba(0,0,0,0.25); backdrop-filter: blur(6px); letter-spacing: 1px; font-size: 0.85rem;">
          Khám Phá Ngay <i class="bi bi-arrow-right fs-6"></i>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  banners: {
    type: Array,
    default: () => []
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});
</script>

<style scoped>
.hero-section { height: 100vh; width: 100%; overflow: hidden; margin-top: 0; }
.hero-bg { background-size: cover; background-position: center 20%; background-repeat: no-repeat; animation: bg-zoom 20s linear infinite alternate; }
@keyframes bg-zoom { 0% { transform: scale(1); } 100% { transform: scale(1.05); } }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.tracking-widest { letter-spacing: 2px; }
.text-shadow-lg { text-shadow: 0 4px 15px rgba(0,0,0,0.5); }

/* Đã bỏ animation fadeUp cho .active để chữ không bị nháy lại dư thừa khi đổi slide */

/* HIỆU ỨNG HOVER MỚI DÀNH RIÊNG CHO NÚT KÍNH (GLASSMORPHISM) */
.hover-scale-glass { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
.hover-scale-glass:hover { 
  transform: translateY(-4px) scale(1.03); 
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3) !important; 
  background-color: rgba(255, 255, 255, 0.2) !important; /* Sáng lên nhẹ khi hover */
  color: white !important; 
  border-color: rgba(255, 255, 255, 0.8) !important; 
}
.hover-scale-glass:hover i { transform: translateX(4px); transition: transform 0.3s; }

.z-index-3 { z-index: 3; }
.opacity-40 { opacity: 0.4; }
.animation-fade-in { animation: fadeIn 1.2s ease-in-out forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* ==========================================
   CSS CHO SKELETON LOADER
========================================== */
.shimmer-bg {
  background: #e2e8f0;
  background-image: linear-gradient(to right, #e2e8f0 0%, #f1f5f9 20%, #e2e8f0 40%, #e2e8f0 100%);
  background-repeat: no-repeat;
  background-size: 100vw 100vh;
  animation: placeholderShimmer 2s infinite linear forwards;
}

.shimmer-element {
  background: rgba(0, 0, 0, 0.06);
  position: relative;
  overflow: hidden;
}

html.dark .shimmer-bg {
  background: #1a2533;
  background-image: linear-gradient(to right, #1a2533 0%, #2b3035 20%, #1a2533 40%, #1a2533 100%);
}

html.dark .shimmer-element {
  background: rgba(255, 255, 255, 0.05);
}

@keyframes placeholderShimmer { 
  0% { background-position: -100vw 0; } 
  100% { background-position: 100vw 0; } 
}
</style>