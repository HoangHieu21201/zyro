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
        <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
          
          <img :src="data?.category?.thumbnail || defaultPlaceholder" 
               class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover object-position-top transition-transform group-hover-zoom" 
               @error="e => e.target.src=defaultPlaceholder">
               
          <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
          
          <div class="position-absolute top-0 end-0 p-4 w-100 text-end z-index-2">
            <h2 class="text-white fw-bold mb-0 text-shadow-lg text-uppercase tracking-widest">{{ data?.category?.name || 'KIDS COLLECTION' }}</h2>
          </div>
          
        </div>

        <template v-if="isLoading">
          <div v-for="i in 8" :key="'kids-skel-' + i" class="skeleton-card w-100" aria-hidden="true">
             <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100"></div>
             <div class="product-info px-1 w-100">
               <div class="skeleton-price shimmer mb-2"></div>
               <div class="skeleton-title shimmer mb-3"></div>
               <div class="d-flex gap-2">
                 <div class="skeleton-swatch shimmer rounded-circle"></div>
                 <div class="skeleton-swatch shimmer rounded-circle"></div>
                 <div class="skeleton-swatch shimmer rounded-circle"></div>
               </div>
             </div>
          </div>
        </template>
        
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
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';

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
.group-hover-opacity-25 { opacity: 0.1; } .group:hover .group-hover-opacity-25 { opacity: 0.2; }
.text-shadow { text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); } .text-shadow-lg { text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); }
.tracking-widest { letter-spacing: 2px; } .tracking-wide { letter-spacing: 1px; }
.transition-all { transition: all 0.3s ease; }
.object-position-top { object-position: top center; }

.skeleton-card { width: 100%; }
.skeleton-img-wrapper { aspect-ratio: 3 / 4; background-color: #f0f0f0; }
.skeleton-price { height: 22px; width: 45%; border-radius: 4px; background-color: #f0f0f0; }
.skeleton-title { height: 16px; width: 90%; border-radius: 4px; background-color: #f0f0f0; }
.skeleton-swatch { width: 18px; height: 18px; background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper, html.dark .skeleton-price, html.dark .skeleton-title, html.dark .skeleton-swatch { background-color: #2b3035; }

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