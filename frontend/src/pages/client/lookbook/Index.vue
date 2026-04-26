<template>
  <div class="category-page-wrapper pb-5 mb-5 bg-white dark:bg-[#121416]">
    
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-3">
        
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-urban">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300" aria-current="page">
               <span v-if="isFirstLoad" class="shimmer d-inline-block rounded" style="width: 100px; height: 14px;"></span>
               <span v-else>Bộ Sưu Tập / Combo</span>
            </li>
          </ol>
        </nav>

        <div class="row align-items-center mb-5 pb-2">
          <div class="col-lg-3 d-none d-lg-block">
             <h1 class="fw-bold text-urban-dark dark:text-white m-0 font-sans-vn" style="font-size: 3.5rem; line-height: 1.1;">LOOK<br>BOOKS</h1>
          </div>
          <div class="col-lg-9 col-12">
             <div class="category-banner rounded-4 overflow-hidden position-relative shadow-sm bg-urban-effect dark:bg-[#121416]" style="height: 350px;">
               <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop" class="w-100 h-100 object-fit-cover opacity-75">
               <div class="position-absolute top-50 start-50 translate-middle text-center w-100 pe-none">
                 <h1 class="display-3 fw-bold text-white text-shadow-lg font-decor" style="letter-spacing: 2px;">The Style Combos</h1>
               </div>
             </div>
          </div>
        </div>

        <div class="filter-bar d-flex flex-wrap align-items-center gap-3 mb-5 pb-3 border-bottom dark:border-gray-700">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 bg-urban-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted d-flex align-items-center pe-none font-sans-vn">
            Tổng số Lookbook: <span class="badge bg-danger rounded-circle ms-2" style="font-size: 0.65rem;">{{ isFirstLoad ? '...' : totalLookbooks }}</span>
          </button>
        </div>

        <div v-if="isFirstLoad" class="pe-none mb-5">
          <div v-for="s in 2" :key="'skel-sec-'+s" class="lookbook-section-wrapper mb-5 pb-4 border-bottom border-light-subtle dark:border-gray-800">
             <div class="d-flex justify-content-between mb-4">
               <div class="shimmer rounded" style="width: 250px; height: 32px;"></div>
               <div class="shimmer rounded" style="width: 120px; height: 24px;"></div>
             </div>
             <div class="zyro-product-grid">
               <div class="banner-span-2 shimmer rounded-4 w-100 h-100" style="min-height: 400px;"></div>
               <div class="collection-swiper-wrapper d-flex gap-3 overflow-hidden">
                  <div v-for="i in 3" :key="'skel-feat-'+i" class="product-swiper-slide-5 h-100 skeleton-card">
                     <div class="skeleton-img-wrapper shimmer rounded-3 w-100 h-100"></div>
                  </div>
               </div>
             </div>
          </div>
        </div>

        <div v-else-if="displayedLookbooks.length > 0">
          
          <div v-for="lb in displayedLookbooks" :key="lb.id" class="lookbook-section-wrapper mb-5 pb-5 border-bottom border-light-subtle dark:border-gray-800">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
              <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-urban-dark dark:text-white font-sans-vn">{{ lb.name }}</h3>
              <router-link :to="`/lookbook/${lb.slug}`" class="text-urban-dark dark:text-gray-300 text-decoration-none fw-semibold d-flex align-items-center gap-2 transition-all hover-text-urban font-sans-vn">
                Xem chi tiết BST <i class="bi bi-arrow-right"></i>
              </router-link>
            </div>

            <div class="zyro-product-grid">
              
              <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100 bg-urban-effect" @click="handleGoToLookbookDetail(lb)">
                <img :src="lb.main_image || '/client_placeholder.png'" 
                     @error="e => e.target.src='/client_placeholder.png'" 
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform group-hover-zoom"
                     :alt="lb.name">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
                
                <div class="position-absolute bottom-0 start-0 w-100 p-4 z-index-2 d-flex flex-column justify-content-end" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); min-height: 50%;">
                  <h4 class="text-white fw-bold mb-2 text-uppercase tracking-widest line-clamp-2 font-sans-vn" style="letter-spacing: 2px;">{{ lb.name }}</h4>
                  <p class="text-gray-300 line-clamp-2 small mb-3 opacity-75 font-sans-vn" v-if="lb.description">{{ lb.description }}</p>
                  <div class="text-danger fw-bold fs-5 d-flex align-items-center">
                    <i class="bi bi-tag-fill me-2 fs-5"></i> Chỉ từ {{ formatCurrency(lb.price_estimate) }}
                  </div>
                </div>
              </div>

              <div class="collection-swiper-wrapper position-relative h-100">
                <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border rounded-circle shadow-sm position-absolute top-50 start-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all btn-nav-urban" 
                        style="width: 40px; height: 40px; margin-left: -15px;" @click="scrollCollection($event, 'left')">
                  <i class="bi bi-chevron-left fw-bold"></i>
                </button>

                <div class="product-swiper-container h-100 pb-3 px-1 cursor-grab" 
                     @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
                  
                  <div v-if="!lb.products || lb.products.length === 0" class="w-100 d-flex align-items-center justify-content-center h-100 min-h-[300px] text-muted fst-italic border border-dashed rounded-4 dark:border-gray-700 bg-urban-effect">
                     Bộ sưu tập này chưa có sản phẩm.
                  </div>
                  
                  <div v-else class="product-swiper-slide-5 h-100" v-for="product in lb.products" :key="'col-' + product.id">
                    <ProductCard class="h-100" :product="product"
                      @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
                  </div>

                  <div v-if="lb.products && lb.products.length > 0" class="product-swiper-slide-5 h-100 d-flex align-items-center justify-content-center px-3">
                    <button @click="handleGoToLookbookDetail(lb)" class="btn btn-outline-urban rounded-circle d-flex align-items-center justify-content-center transition-all" style="width: 60px; height: 60px;">
                      <i class="bi bi-arrow-right fs-4"></i>
                    </button>
                  </div>
                </div>

                <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border rounded-circle shadow-sm position-absolute top-50 end-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all btn-nav-urban" 
                        style="width: 40px; height: 40px; margin-right: -15px;" @click="scrollCollection($event, 'right')">
                  <i class="bi bi-chevron-right fw-bold"></i>
                </button>
              </div>

            </div>
          </div>
          
          <div class="text-center mt-4" v-if="hasMoreToLoad">
            <button @click="loadMore" class="btn btn-urban rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest transition-all shadow-sm" :disabled="isLoadingMore">
              <span v-if="isLoadingMore" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Hiển thị thêm Combo
            </button>
          </div>

          <div v-if="isLoadingMore" class="pe-none mt-5">
             <div class="d-flex justify-content-between mb-4">
               <div class="shimmer rounded" style="width: 250px; height: 32px;"></div>
             </div>
             <div class="zyro-product-grid">
               <div class="banner-span-2 shimmer rounded-4 w-100 h-100" style="min-height: 400px;"></div>
               <div class="collection-swiper-wrapper d-flex gap-3 overflow-hidden">
                  <div v-for="i in 3" :key="'skel-load-'+i" class="product-swiper-slide-5 h-100 skeleton-card">
                     <div class="skeleton-img-wrapper shimmer rounded-3 w-100 h-100"></div>
                  </div>
               </div>
             </div>
          </div>

        </div>

        <div v-else class="text-center py-5 my-5 text-muted">
           <i class="bi bi-images fs-1 d-block mb-3 opacity-50"></i>
           <h5 class="fw-normal font-sans-vn">Hiện chưa có bộ sưu tập nào được xuất bản.</h5>
        </div>

      </div>
    </div>

    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';

import { useCompareStore } from '@/stores/compareStore';
import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const router = useRouter();
const compareStore = useCompareStore();

const lookbooks = ref([]);
const totalLookbooks = ref(0);

const isFirstLoad = ref(true); 
const isLoading = ref(true);
const isLoadingMore = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);

const visibleLimit = ref(3);

const displayedLookbooks = computed(() => {
  return lookbooks.value.slice(0, visibleLimit.value);
});

const hasMoreToLoad = computed(() => {
  return visibleLimit.value < lookbooks.value.length || currentPage.value < lastPage.value;
});

const fetchLookbooks = async (page = 1, append = false) => {
  if (append) isLoadingMore.value = true;
  else isLoading.value = true; 
  
  try {
    const res = await api.get('/client/lookbook-page', { params: { page } });
    const payload = res.data.data;
    
    if (!append) {
        lookbooks.value = payload.lookbooks || [];
    } else {
        lookbooks.value = [...lookbooks.value, ...payload.lookbooks];
    }
    
    currentPage.value = payload.current_page;
    lastPage.value = payload.last_page;
    totalLookbooks.value = payload.total || lookbooks.value.length;
    
  } catch (err) {
    console.error('Lỗi khi tải danh sách Lookbook:', err);
  } finally {
    isLoading.value = false;
    isFirstLoad.value = false; 
    isLoadingMore.value = false;
  }
};

const loadMore = async () => {
  if (isLoadingMore.value) return;
  isLoadingMore.value = true;
  
  if (visibleLimit.value < lookbooks.value.length) {
    setTimeout(() => {
      visibleLimit.value += 3;
      isLoadingMore.value = false;
    }, 400);
  } else if (currentPage.value < lastPage.value) {
    await fetchLookbooks(currentPage.value + 1, true);
    visibleLimit.value += 3;
  } else {
    isLoadingMore.value = false;
  }
};

const startDrag = (e) => { 
    const el = e.currentTarget;
    el.dataset.isDown = "true"; 
    el.classList.add('active-drag'); 
    el.dataset.startX = e.pageX - el.offsetLeft; 
    el.dataset.scrollLeftInit = el.scrollLeft; 
};
const endDrag = (e) => { 
    const el = e.currentTarget;
    el.dataset.isDown = "false"; 
    el.classList.remove('active-drag'); 
};
const doDrag = (e) => { 
    const el = e.currentTarget;
    if (el.dataset.isDown !== "true") return; 
    e.preventDefault(); 
    const startX = parseFloat(el.dataset.startX);
    const scrollLeftInit = parseFloat(el.dataset.scrollLeftInit);
    const x = e.pageX - el.offsetLeft; 
    const walk = (x - startX) * 1.5; 
    el.scrollLeft = scrollLeftInit - walk; 
};

const scrollCollection = (e, direction) => {
  const wrapper = e.currentTarget.closest('.collection-swiper-wrapper');
  const slider = wrapper.querySelector('.product-swiper-container');
  if (slider) {
    const cardWidth = slider.offsetWidth / 3; 
    if (direction === 'left') {
      slider.scrollBy({ left: -cardWidth, behavior: 'smooth' });
    } else {
      slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
    }
  }
};

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN').format(val || 0) + 'đ';

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

const handleGoToLookbookDetail = (lb) => {
  if (lb && lb.slug) {
      router.push(`/lookbook/${lb.slug}`);
  }
};

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

onMounted(() => { 
    window.scrollTo(0, 0); 
    fetchLookbooks();
});
</script>

<style scoped>
.category-page-wrapper { width: 100%; }

.text-urban-dark { color: var(--color-c-dark, #213448) !important; }
.bg-urban-effect { background-color: var(--color-c-effect, #ebf1f5) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }
html.dark .hover-text-urban:hover { color: #fff !important; }

.font-sans-vn { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
.font-decor { font-family: 'Georgia', serif; font-style: italic; }
.text-shadow-lg { text-shadow: 2px 4px 10px rgba(0,0,0,0.6); }

.zyro-product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
.banner-span-2 { grid-column: span 2; height: 100%; display: flex; flex-direction: column; min-height: 450px; }
.collection-swiper-wrapper { grid-column: span 3; min-width: 0; }
.zyro-product-grid>* { height: 100%; }

@media (max-width: 1199px) { .zyro-product-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } .banner-span-2 { grid-column: span 2; } .collection-swiper-wrapper { grid-column: span 2; } }
@media (max-width: 991px) { .zyro-product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } .banner-span-2 { grid-column: span 3; min-height: 350px; } .collection-swiper-wrapper { grid-column: span 3; } }
@media (max-width: 767px) { .zyro-product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } .banner-span-2 { grid-column: span 2; min-height: 250px; } .collection-swiper-wrapper { grid-column: span 2; } }

.cursor-grab { cursor: grab; } .cursor-grab:active { cursor: grabbing; }
.product-swiper-container { display: flex; gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
.product-swiper-container.active-drag { scroll-snap-type: none; scroll-behavior: auto; }

.product-swiper-slide-5 { width: calc((100% - 40px) / 3); min-width: 220px; flex-shrink: 0; scroll-snap-align: start; }
@media (max-width: 1199px) { .product-swiper-slide-5 { width: calc((100% - 20px) / 2); } }
@media (max-width: 767px) { .product-swiper-slide-5 { width: calc((100% - 12px) / 2); min-width: 160px; } }
.product-swiper-container::-webkit-scrollbar { height: 0px; display: none; }

.btn-urban { background-color: var(--color-c-dark, #213448); color: #fff; border: 1px solid var(--color-c-dark, #213448); }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); color: #fff; }
.btn-outline-urban { background-color: transparent; color: var(--color-c-dark, #213448); border: 1px solid var(--color-c-dark, #213448); }
.btn-outline-urban:hover { background-color: var(--color-c-dark, #213448); color: #fff; }
.btn-nav-urban { color: var(--color-c-hover, #547792) !important; }
.btn-nav-urban:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }

.z-index-2 { z-index: 2; }
.group-hover-zoom { transition: transform 0.5s ease; } .group:hover .group-hover-zoom { transform: scale(1.05); }
.group-hover-opacity-25 { opacity: 0; } .group:hover .group-hover-opacity-25 { opacity: 0.3; }

.tracking-widest { letter-spacing: 2px; } .tracking-wide { letter-spacing: 1px; }
.transition-all { transition: all 0.3s ease; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; }

.shimmer {
  background: var(--color-c-effect, #ebf1f5);
  background-image: linear-gradient(to right, var(--color-c-effect) 0%, rgba(255,255,255,0.6) 20%, var(--color-c-effect) 40%, var(--color-c-effect) 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
.skeleton-card { width: 100%; }
.skeleton-img-wrapper { background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper { background-color: #2b3035; }
</style>