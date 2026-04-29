<template>
  <section class="pt-5 pb-0 bg-white dark:bg-[#1a2533] overflow-hidden border-top dark:border-gray-700">
    <div class="zyro-container py-3">
      <div class="text-center mb-5">
        <h3 class="fw-bold text-uppercase mb-2 tracking-widest text-dark dark:text-white" style="letter-spacing: 3px;">ZYRO Lookbook</h3>
        <p class="text-muted small mb-0">Cảm hứng thời trang không giới hạn. Lướt để khám phá phong cách của bạn.</p>
      </div>
    </div>

    <div v-if="isLoading" class="lookbook-marquee-wrapper pb-5 pe-none">
      <div class="d-flex gap-3 px-3 w-100 overflow-hidden">
        <div v-for="i in 6" :key="'g-skel-'+i" class="marquee-item shimmer rounded-4 shadow-sm" :class="i % 2 !== 0 ? 'item-small' : 'item-large'"></div>
      </div>
    </div>

    <div v-else class="lookbook-marquee-wrapper pb-5">
      <div class="lookbook-marquee d-flex" @mouseenter="slowDownMarquee" @mouseleave="restoreMarqueeSpeed">
        <!-- Vòng 1 -->
        <div class="marquee-group d-flex align-items-center">
          <div class="marquee-item shadow-sm" v-for="(img, idx) in images" :key="'lb1'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
            <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img rounded-4">
          </div>
        </div>
        <!-- Vòng 2 (Lặp vô tận) -->
        <div class="marquee-group d-flex align-items-center">
          <div class="marquee-item shadow-sm" v-for="(img, idx) in images" :key="'lb2'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
            <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img rounded-4">
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({ 
  images: Array,
  isLoading: Boolean 
});

const slowDownMarquee = (e) => { e.currentTarget.getAnimations().forEach(anim => anim.playbackRate = 0.5); };
const restoreMarqueeSpeed = (e) => { e.currentTarget.getAnimations().forEach(anim => anim.playbackRate = 1); };
</script>

<style scoped>
.lookbook-marquee-wrapper { width: 100%; overflow: hidden; display: flex; }
.lookbook-marquee { display: flex; width: max-content; animation: scrollMarquee 40s linear infinite; }
@keyframes scrollMarquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
.marquee-group { display: flex; align-items: center; }
.marquee-item { flex-shrink: 0; width: 22vw; max-width: 320px; min-width: 200px; cursor: pointer; border-radius: 1rem; margin-right: 15px; }
.item-small { height: 280px; } .item-large { height: 380px; }

@media (max-width: 991px) { .marquee-item { width: 28vw; min-width: 180px; margin-right: 12px; } .item-small { height: 220px; } .item-large { height: 300px; } }
@media (max-width: 575px) { .marquee-item { width: 45vw; min-width: 160px; margin-right: 10px; } .item-small { height: 180px; } .item-large { height: 260px; } .lookbook-marquee { animation-duration: 25s; } }

.hover-zoom-img:hover { filter: brightness(1.1); transform: scale(1.02); }
.transition-all { transition: all 0.3s ease; }
.tracking-widest { letter-spacing: 2px; }

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
</style>