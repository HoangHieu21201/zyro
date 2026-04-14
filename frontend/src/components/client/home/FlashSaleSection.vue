<!-- File: frontend/src/components/client/home/FlashSaleSection.vue -->
<template>
  <section v-if="flashSale && flashSale.products.length > 0" class="flash-sale-section py-5" style="background-color: #ffb482;">
    <div class="zyro-container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-4">
          <div class="flash-sale-title text-danger fw-bold fst-italic lh-1" style="font-size: 2.2rem; text-shadow: 2px 2px 0px #fff;">
            <span class="text-white text-shadow-none d-block fs-6 fst-normal mb-1">{{ statusText }}</span>
            FLASH SALE
          </div>
          <div class="countdown-box bg-danger text-white rounded-3 d-flex gap-3 px-4 py-2 shadow">
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.days }}</span><small class="d-block" style="font-size: 0.65rem;">ngày</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.hours }}</span><small class="d-block" style="font-size: 0.65rem;">giờ</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.minutes }}</span><small class="d-block" style="font-size: 0.65rem;">phút</small></div>
            <div class="text-center"><span class="fs-4 fw-bold">{{ countdown.seconds }}</span><small class="d-block" style="font-size: 0.65rem;">giây</small></div>
          </div>
        </div>
        <router-link to="/flash-sale" class="btn bg-white rounded-pill px-4 py-2 text-danger fw-bold shadow-sm hover-scale transition-all">
          Xem tất cả <i class="bi bi-chevron-right small"></i>
        </router-link>
      </div>

      <div class="zyro-product-grid">
        <!-- ĐÃ FIX: Giới hạn slice(0, 5) để ép hiển thị đúng 1 hàng duy nhất -->
        <ProductCard class="h-100" v-for="product in flashSale.products.slice(0, 5)" :key="'fs' + product.id" :product="product"
          @quick-view="$emit('quick-view', $event)" @compare="$emit('compare', $event)" @wishlist="$emit('wishlist', $event)" @options="$emit('options', $event)" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onUnmounted, watch } from 'vue';
import ProductCard from '@/components/client/ProductCard.vue';

const props = defineProps({ flashSale: Object });
const emit = defineEmits(['quick-view', 'compare', 'wishlist', 'options']);

const statusText = ref('Kết thúc trong');
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
  updateCountdown();
  timerInterval = setInterval(updateCountdown, 1000);
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
</style>