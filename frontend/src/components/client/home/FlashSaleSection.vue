<template>
  <section v-if="isLoading || (flashSale && flashSale.products && flashSale.products.length > 0)" class="flash-sale-section py-5" style="background-color: #ffb482;">
    <div class="zyro-container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-4">
          <div class="flash-sale-title text-danger fw-bold fst-italic lh-1" style="font-size: 2.2rem; text-shadow: 2px 2px 0px #fff;">
            <span class="text-white text-shadow-none d-block fs-6 fst-normal mb-1">{{ statusText }}</span>
            FLASH SALE
          </div>
          <div class="countdown-box bg-danger text-white rounded-3 d-flex gap-3 px-4 py-2 shadow" :class="{'opacity-50 pe-none': isLoading}">
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.days }}</span><small class="d-block" style="font-size: 0.65rem;">ngày</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.hours }}</span><small class="d-block" style="font-size: 0.65rem;">giờ</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.minutes }}</span><small class="d-block" style="font-size: 0.65rem;">phút</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.seconds }}</span><small class="d-block" style="font-size: 0.65rem;">giây</small></div>
          </div>
        </div>
        <router-link to="/flash-sale" class="btn bg-white rounded-pill px-4 py-2 text-danger fw-bold shadow-sm hover-scale transition-all" :class="{'opacity-50 pe-none': isLoading}">
          Xem tất cả <i class="bi bi-chevron-right small"></i>
        </router-link>
      </div>

      <div class="zyro-product-grid">
        <template v-if="isLoading">
          <div v-for="i in 5" :key="'fs-skel-' + i" class="skeleton-card w-100" aria-hidden="true">
             <div class="skeleton-img-wrapper shimmer-orange rounded-3 mb-3 w-100"></div>
             <div class="product-info px-1 w-100">
               <div class="skeleton-price shimmer-orange mb-2"></div>
               <div class="skeleton-title shimmer-orange mb-3"></div>
               <div class="d-flex gap-2">
                  <div class="skeleton-swatch shimmer-orange rounded-circle"></div>
                  <div class="skeleton-swatch shimmer-orange rounded-circle"></div>
                  <div class="skeleton-swatch shimmer-orange rounded-circle"></div>
               </div>
             </div>
          </div>
        </template>

        <template v-else-if="flashSale && flashSale.products">
          <ProductCard class="h-100" v-for="product in flashSale.products.slice(0, 5)" :key="'fs' + product.id" :product="product"
            @quick-view="$emit('quick-view', $event)" @compare="$emit('compare', $event)" @wishlist="$emit('wishlist', $event)" @options="$emit('options', $event)" />
        </template>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onUnmounted, watch } from 'vue';
import ProductCard from '@/components/client/ProductCard.vue';

const props = defineProps({ 
  flashSale: Object,
  isLoading: Boolean 
});
const emit = defineEmits(['quick-view', 'compare', 'wishlist', 'options']);

const statusText = ref('Đang tải...');
const countdown = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' });
let timerInterval = null;

const updateCountdown = () => {
  if (!props.flashSale) return;
  
  const now = new Date().getTime();
  const startTime = new Date(props.flashSale.start_time).getTime();
  const endTime = new Date(props.flashSale.end_time).getTime();
  let distance = 0;

  if (now < startTime) { statusText.value = 'Sắp bắt đầu sau'; distance = startTime - now; } 
  else if (now >= startTime && now <= endTime) { statusText.value = 'Kết thúc trong'; distance = endTime - now; } 
  else { clearInterval(timerInterval); return; }

  const days = Math.floor(distance / (1000 * 60 * 60 * 24));
  const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((distance % (1000 * 60)) / 1000);

  countdown.value = {
    days: days < 10 ? '0' + days : days,
    hours: hours < 10 ? '0' + hours : hours,
    minutes: minutes < 10 ? '0' + minutes : minutes,
    seconds: seconds < 10 ? '0' + seconds : seconds
  };
};

watch(() => props.flashSale, () => {
  if(timerInterval) clearInterval(timerInterval);
  if(props.flashSale) {
      updateCountdown();
      timerInterval = setInterval(updateCountdown, 1000);
  }
}, { immediate: true });

onUnmounted(() => { if(timerInterval) clearInterval(timerInterval); });
</script>

<style scoped>
.zyro-product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
@media (max-width: 1199px) { .zyro-product-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } }
@media (max-width: 991px) { .zyro-product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } }
@media (max-width: 767px) { .zyro-product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } }
.hover-scale:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); }
.transition-all { transition: all 0.3s ease; }

.skeleton-card { width: 100%; }
.skeleton-img-wrapper { aspect-ratio: 3 / 4; background-color: rgba(255, 255, 255, 0.3); }
.skeleton-price { height: 22px; width: 45%; border-radius: 4px; background-color: rgba(255, 255, 255, 0.3); }
.skeleton-title { height: 16px; width: 90%; border-radius: 4px; background-color: rgba(255, 255, 255, 0.3); }
.skeleton-swatch { width: 18px; height: 18px; background-color: rgba(255, 255, 255, 0.3); }

.shimmer-orange {
  background-image: linear-gradient(
    to right, 
    rgba(255, 255, 255, 0.15) 0%, 
    rgba(255, 255, 255, 0.5) 20%, 
    rgba(255, 255, 255, 0.15) 40%, 
    rgba(255, 255, 255, 0.15) 100%
  );
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmerOrange 1.5s infinite linear;
}
html.dark .shimmer-orange {
  background-image: linear-gradient(to right, rgba(0, 0, 0, 0.1) 0%, rgba(255, 255, 255, 0.15) 20%, rgba(0, 0, 0, 0.1) 40%, rgba(0, 0, 0, 0.1) 100%);
}

@keyframes placeholderShimmerOrange {
  0% { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}
</style>