<template>
  <section class="py-5 bg-light dark:bg-[#121416]">
    <div class="zyro-container py-3 mb-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark dark:text-white">Dành Cho Bé</h3>
        <ul class="nav nav-underline gap-4 section-tabs">
          <li class="nav-item"><a class="nav-link fw-bold active" href="#" @click.prevent>TẤT CẢ SẢN PHẨM</a></li>
        </ul>
      </div>

      <div class="zyro-product-grid">
        <!-- ĐÃ KHÔI PHỤC: Dùng lại ảnh Thumbnail của Category từ API đẩy vào -->
        <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
          
          <img :src="data?.category?.thumbnail || defaultPlaceholder" 
               class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover object-position-top transition-transform group-hover-zoom" 
               @error="e => e.target.src=defaultPlaceholder">
               
          <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
          
          <div class="position-absolute top-0 end-0 p-4 w-100 text-end z-index-2">
            <h2 class="text-white fw-bold mb-0 text-shadow-lg text-uppercase tracking-widest">{{ data?.category?.name || 'KIDS COLLECTION' }}</h2>
          </div>
          
        </div>

        <div v-if="isLoading" class="banner-span-3 text-center py-5 w-100 d-flex justify-content-center align-items-center"><span class="spinner-border text-muted"></span></div>
        <div v-else-if="!data?.products || data.products.length === 0" class="banner-span-3 text-center py-5 w-100 d-flex justify-content-center align-items-center text-muted fst-italic">Chưa có sản phẩm trẻ em nào.</div>
        
        <div style="display: contents;" v-else>
          <ProductCard class="h-100" v-for="product in data.products.slice(0, 8)" :key="'kid' + product.id" :product="product"
            @quick-view="$emit('quick-view', $event)" @compare="$emit('compare', $event)" @wishlist="$emit('wishlist', $event)" @options="$emit('options', $event)" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import ProductCard from '@/components/client/ProductCard.vue';
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png'; // Đã import trở lại

defineProps({ data: Object, isLoading: Boolean });
defineEmits(['quick-view', 'compare', 'wishlist', 'options']);
</script>

<style scoped>
.zyro-product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
.banner-span-2 { grid-column: span 2; height: 100%; display: flex; flex-direction: column; }
.banner-span-3 { grid-column: span 3; }
.zyro-product-grid>* { height: 100%; }

@media (max-width: 1199px) { .zyro-product-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } .banner-span-2 { grid-column: span 2; } .banner-span-3 { grid-column: span 2; } }
@media (max-width: 991px) { .zyro-product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } .banner-span-2, .banner-span-3 { grid-column: span 3; min-height: 250px; } }
@media (max-width: 767px) { .zyro-product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } .banner-span-2, .banner-span-3 { grid-column: span 2; min-height: 200px; } }

.section-tabs .nav-link { color: #6c757d; border-bottom: 2px solid transparent; padding-bottom: 8px; transition: all 0.3s ease; font-size: 0.9rem; }
.section-tabs .nav-link:hover, .section-tabs .nav-link.active { color: #212529 !important; border-bottom-color: #212529 !important; }
html.dark .section-tabs .nav-link { color: #adb5bd; } html.dark .section-tabs .nav-link.active { color: #fff !important; border-bottom-color: #fff !important; }

.z-index-2 { z-index: 2; }
.group-hover-zoom { transition: transform 0.5s ease; } .group:hover .group-hover-zoom { transform: scale(1.05); }

/* Khôi phục độ mờ Overlay cũ */
.group-hover-opacity-25 { opacity: 0.1; } .group:hover .group-hover-opacity-25 { opacity: 0.2; }

.text-shadow { text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); }
.text-shadow-lg { text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); }
.tracking-widest { letter-spacing: 2px; } .tracking-wide { letter-spacing: 1px; }
.transition-all { transition: all 0.3s ease; }

.object-position-top { object-position: top center; }
</style>