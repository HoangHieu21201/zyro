<template>
  <section class="py-5 bg-white">
    <div class="zyro-container py-3">
      
      <!-- HEADER VÀ TABS -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark">Bộ Sưu Tập</h3>
        
        <!-- ĐÃ CẬP NHẬT: Render các Tabs tương ứng với danh sách Lookbook lấy từ DB -->
        <ul class="nav nav-underline gap-4 section-tabs custom-scrollbar-x flex-nowrap overflow-auto pb-1">
          <li class="nav-item" v-for="lb in lookbooks" :key="lb.id">
            <a class="nav-link fw-bold text-nowrap text-uppercase" 
               :class="{ 'active': activeTab === lb.id }" 
               href="#" 
               @click.prevent="activeTab = lb.id">
               {{ lb.name }}
            </a>
          </li>
        </ul>
      </div>

      <!-- NỘI DUNG LOOKBOOK ĐANG ACTIVE -->
      <div class="zyro-product-grid" v-if="activeLookbook">
        
        <!-- Banner Bên Trái -->
        <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
          <!-- ĐÃ THÊM: Logic Fallback Image 2 lớp bảo vệ -->
          <img :src="activeLookbook.main_image || '/client_placeholder.png'" 
               @error="e => e.target.src='/client_placeholder.png'" 
               class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform group-hover-zoom"
               :alt="activeLookbook.name">
          <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
          
          <!-- Lớp phủ Giá và Tên (Gradient đen dưới đáy cho dễ nhìn) -->
          <div class="position-absolute bottom-0 start-0 w-100 p-4 z-index-2 d-flex flex-column justify-content-end" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); min-height: 50%;">
            <h4 class="text-white fw-bold mb-1 text-uppercase tracking-widest line-clamp-2" style="letter-spacing: 2px;">{{ activeLookbook.name }}</h4>
            <div class="text-danger fw-bold fs-4 d-flex align-items-center">
              <i class="bi bi-tag-fill me-2 fs-5"></i> {{ formatCurrency(activeLookbook.price_estimate) }}
            </div>
          </div>
        </div>

        <!-- Khung chứa Slider Bên Phải -->
        <div class="collection-swiper-wrapper position-relative h-100">
          <!-- Nút trượt Trái -->
          <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border text-urban rounded-circle shadow-sm position-absolute top-50 start-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all hover-btn-urban" 
                  style="width: 40px; height: 40px; margin-left: -15px;" @click="scrollCollection('left')">
            <i class="bi bi-chevron-left fw-bold"></i>
          </button>

          <!-- Swiper Container (Sản phẩm của riêng Lookbook này) -->
          <div class="product-swiper-container h-100 pb-3 px-1 cursor-grab" ref="collectionSwiperRef" @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
            <div v-if="isLoading" class="w-100 d-flex align-items-center justify-content-center h-100 min-h-[300px]"><span class="spinner-border text-muted"></span></div>
            <div v-else-if="!activeLookbook.products || activeLookbook.products.length === 0" class="w-100 d-flex align-items-center justify-content-center h-100 min-h-[300px] text-muted fst-italic border border-dashed rounded-4">
               Lookbook này đang cháy hàng.
            </div>
            
            <div v-else class="product-swiper-slide-3 h-100" v-for="product in activeLookbook.products" :key="'col' + product.id">
              <ProductCard class="h-100" :product="product"
                @quick-view="$emit('quick-view', $event)" @compare="$emit('compare', $event)" @wishlist="$emit('wishlist', $event)" @options="$emit('options', $event)" />
            </div>
          </div>

          <!-- Nút trượt Phải -->
          <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border text-urban rounded-circle shadow-sm position-absolute top-50 end-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all hover-btn-urban" 
                  style="width: 40px; height: 40px; margin-right: -15px;" @click="scrollCollection('right')">
            <i class="bi bi-chevron-right fw-bold"></i>
          </button>
        </div>
      </div>

      <div class="text-center mt-5">
        <router-link to="/lookbook" class="btn btn-outline-dark rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all text-decoration-none">
          Xem Tất Cả BST
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import ProductCard from '@/components/client/ProductCard.vue';
// ĐÃ XÓA: import defaultPlaceholder từ assets vì dùng trực tiếp /client_placeholder.png ở public folder mượt hơn

const props = defineProps({ 
  lookbooks: { type: Array, default: () => [] }, 
  isLoading: Boolean 
});

defineEmits(['quick-view', 'compare', 'wishlist', 'options']);

const activeTab = ref(null);

// Lắng nghe dữ liệu, khi API trả về có dữ liệu thì tự active Tab đầu tiên
watch(() => props.lookbooks, (newVal) => {
  if (newVal && newVal.length > 0 && !activeTab.value) {
    activeTab.value = newVal[0].id;
  }
}, { immediate: true });

const activeLookbook = computed(() => {
  if (!props.lookbooks || props.lookbooks.length === 0) return null;
  return props.lookbooks.find(lb => lb.id === activeTab.value) || props.lookbooks[0];
});

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

// Swiper Logic
const collectionSwiperRef = ref(null);
let isDown = false; let startX; let scrollLeft;

const startDrag = (e) => { isDown = true; collectionSwiperRef.value.classList.add('active-drag'); startX = e.pageX - collectionSwiperRef.value.offsetLeft; scrollLeft = collectionSwiperRef.value.scrollLeft; };
const endDrag = () => { isDown = false; if (collectionSwiperRef.value) collectionSwiperRef.value.classList.remove('active-drag'); };
const doDrag = (e) => { if (!isDown) return; e.preventDefault(); const x = e.pageX - collectionSwiperRef.value.offsetLeft; const walk = (x - startX) * 1.5; collectionSwiperRef.value.scrollLeft = scrollLeft - walk; };

const scrollCollection = (direction) => {
  if (collectionSwiperRef.value) {
    const cardWidth = collectionSwiperRef.value.offsetWidth / 3; 
    const scrollAmount = cardWidth; 
    if (direction === 'left') {
      collectionSwiperRef.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
      collectionSwiperRef.value.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  }
};
</script>

<style scoped>
.zyro-product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
.banner-span-2 { grid-column: span 2; height: 100%; display: flex; flex-direction: column; }
.collection-swiper-wrapper { grid-column: span 3; min-width: 0; }
.zyro-product-grid>* { height: 100%; }

@media (max-width: 1199px) { .zyro-product-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } .banner-span-2 { grid-column: span 2; } .collection-swiper-wrapper { grid-column: span 2; } }
@media (max-width: 991px) { .zyro-product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } .banner-span-2 { grid-column: span 3; min-height: 250px; } .collection-swiper-wrapper { grid-column: span 3; } }
@media (max-width: 767px) { .zyro-product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } .banner-span-2 { grid-column: span 2; min-height: 200px; } .collection-swiper-wrapper { grid-column: span 2; } }

.cursor-grab { cursor: grab; } .cursor-grab:active { cursor: grabbing; }
.product-swiper-container { display: flex; gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
.product-swiper-container.active-drag { scroll-snap-type: none; scroll-behavior: auto; }
.product-swiper-slide-3 { width: calc((100% - 40px) / 3); min-width: 220px; flex-shrink: 0; scroll-snap-align: start; }
@media (max-width: 1199px) { .product-swiper-slide-3 { width: calc((100% - 20px) / 2); } }
@media (max-width: 767px) { .product-swiper-slide-3 { width: calc((100% - 12px) / 2); min-width: 160px; } }
.product-swiper-container::-webkit-scrollbar { height: 0px; display: none; }

.section-tabs .nav-link { color: #6c757d; border-bottom: 2px solid transparent; padding-bottom: 8px; transition: all 0.3s ease; font-size: 0.9rem; }
.section-tabs .nav-link:hover, .section-tabs .nav-link.active { color: #212529 !important; border-bottom-color: #212529 !important; }

.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: transparent; border-radius: 10px; }
.custom-scrollbar-x:hover::-webkit-scrollbar-thumb { background: #dee2e6; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.hover-btn-urban:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; border-color: #212529 !important; }

.z-index-2 { z-index: 2; }
.group-hover-zoom { transition: transform 0.5s ease; } .group:hover .group-hover-zoom { transform: scale(1.05); }
.group-hover-opacity-25 { opacity: 0.1; } .group:hover .group-hover-opacity-25 { opacity: 0.3; }
.text-shadow-lg { text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.9); }
.tracking-widest { letter-spacing: 2px; } .tracking-wide { letter-spacing: 1px; }
.transition-all { transition: all 0.3s ease; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }
</style>  