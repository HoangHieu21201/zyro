<template>
  <section class="hero-section position-relative">
    
    <!-- KHUNG ĐỘNG: Chạy khi lấy được Database -->
    <div v-if="banners && banners.length > 0" id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="4000">
      
      <!-- Chấm tròn -->
      <div class="carousel-indicators z-index-3 mb-4">
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
          
          <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
          
          <div class="hero-content position-absolute top-50 start-50 translate-middle w-100 z-1 text-white text-center px-4">
            <span class="d-block mb-3 fw-medium tracking-widest text-uppercase small">Bộ Sưu Tập Mới Nhất</span>
            <h1 class="display-3 fw-bold mb-4 text-uppercase text-shadow-lg" style="letter-spacing: 4px;">{{ banner.title }}</h1>
            <a :href="banner.target_url || '/category'" class="btn btn-light rounded-pill px-5 py-3 fw-bold text-uppercase mt-2 hover-scale transition-all" style="letter-spacing: 1px;">
              Khám Phá Ngay
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

    <!-- KHUNG FALLBACK TĨNH: Chạy nếu Database bị rỗng (Bảo toàn UI gốc của sếp) -->
    <div v-else class="h-100 w-100 position-relative d-flex align-items-center justify-content-center text-center">
      <div class="hero-bg position-absolute top-0 start-0 w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop');"></div>
      <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
      <div class="hero-content position-relative z-1 text-white px-4 animation-fade-up">
        <span class="d-block mb-3 fw-medium tracking-widest text-uppercase small">Bộ Sưu Tập Mới Nhất</span>
        <h1 class="display-3 fw-bold mb-4 text-uppercase" style="letter-spacing: 4px;">Summer<br>Vibes 2026</h1>
        <router-link to="/category" class="btn btn-light rounded-pill px-5 py-3 fw-bold text-uppercase mt-2 hover-scale transition-all" style="letter-spacing: 1px;">
          Khám Phá Ngay
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
  }
});
</script>

<style scoped>
.hero-section { height: 100vh; width: 100%; overflow: hidden; margin-top: 0; }
.hero-bg { background-size: cover; background-position: center 20%; background-repeat: no-repeat; animation: bg-zoom 20s linear infinite alternate; }
@keyframes bg-zoom { 0% { transform: scale(1); } 100% { transform: scale(1.05); } }

.tracking-widest { letter-spacing: 2px; }
.text-shadow-lg { text-shadow: 2px 2px 8px rgba(0,0,0,0.8); }
.animation-fade-up { animation: fadeUp 1s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
.hover-scale:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); }
.transition-all { transition: all 0.3s ease; }

/* Thêm css xử lý animation khi slider chuyển đổi */
.z-index-3 { z-index: 3; }
.carousel-item.active .hero-content span { animation: fadeUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; opacity: 0; }
.carousel-item.active .hero-content h1 { animation: fadeUp 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.2s forwards; opacity: 0; }
.carousel-item.active .hero-content a { animation: fadeUp 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.4s forwards; opacity: 0; }
</style>