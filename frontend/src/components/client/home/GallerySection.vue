<!-- File: frontend/src/components/client/home/GallerySection.vue -->
<template>
  <section class="pt-5 pb-0 bg-white dark:bg-[#1a2533] overflow-hidden border-top dark:border-gray-700">
    <div class="zyro-container py-3">
      <div class="text-center mb-5">
        <h3 class="fw-bold text-uppercase mb-2 tracking-widest text-dark dark:text-white" style="letter-spacing: 3px;">ZYRO Lookbook</h3>
        <p class="text-muted small mb-0">Cảm hứng thời trang không giới hạn. Lướt để khám phá phong cách của bạn.</p>
      </div>
    </div>

    <div class="lookbook-marquee-wrapper pb-5">
      <div class="lookbook-marquee d-flex" @mouseenter="slowDownMarquee" @mouseleave="restoreMarqueeSpeed">
        <!-- Vòng 1 -->
        <div class="marquee-group d-flex align-items-center">
          <div class="marquee-item shadow-sm" v-for="(img, idx) in images" :key="'lb1'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
            <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img">
          </div>
        </div>
        <!-- Vòng 2 (Lặp vô tận) -->
        <div class="marquee-group d-flex align-items-center">
          <div class="marquee-item shadow-sm" v-for="(img, idx) in images" :key="'lb2'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
            <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img">
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ images: Array });
const slowDownMarquee = (e) => { e.currentTarget.getAnimations().forEach(anim => anim.playbackRate = 0.5); };
const restoreMarqueeSpeed = (e) => { e.currentTarget.getAnimations().forEach(anim => anim.playbackRate = 1); };
</script>

<style scoped>
.lookbook-marquee-wrapper { width: 100%; overflow: hidden; display: flex; }
.lookbook-marquee { display: flex; width: max-content; animation: scrollMarquee 40s linear infinite; }
@keyframes scrollMarquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
.marquee-group { display: flex; align-items: center; }
.marquee-item { flex-shrink: 0; width: 22vw; max-width: 320px; min-width: 200px; cursor: pointer; }
.item-small { height: 280px; } .item-large { height: 380px; }
@media (max-width: 991px) { .marquee-item { width: 28vw; min-width: 180px; } .item-small { height: 220px; } .item-large { height: 300px; } }
@media (max-width: 575px) { .marquee-item { width: 45vw; min-width: 160px; } .item-small { height: 180px; } .item-large { height: 260px; } .lookbook-marquee { animation-duration: 25s; } }
.hover-zoom-img:hover { filter: brightness(1.1); transform: scale(1.02); }
.transition-all { transition: all 0.3s ease; }
.tracking-widest { letter-spacing: 2px; }
</style>