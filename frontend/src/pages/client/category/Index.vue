<template>
  <div class="category-page-wrapper pb-5 mb-5">
    
    <!-- ĐẨY NỘI DUNG XUỐNG DƯỚI TRÁNH BỊ HEADER CHE KHUẤT -->
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-dark dark:text-gray-300" aria-current="page">
               <span v-if="isFirstLoad" class="shimmer d-inline-block rounded" style="width: 100px; height: 14px;"></span>
               <span v-else>{{ categoryName }}</span>
            </li>
          </ol>
        </nav>

        <!-- ========================================== -->
        <!-- SKELETON BANNER (CHỈ HIỆN LÚC MỚI VÀO TRANG) -->
        <!-- ========================================== -->
        <div v-if="isFirstLoad" class="row align-items-center mb-5 pb-2 pe-none">
          <div class="col-lg-3 d-none d-lg-block">
             <div class="shimmer rounded-3 w-75 mb-2" style="height: 50px;"></div>
             <div class="shimmer rounded-3 w-50" style="height: 50px;"></div>
          </div>
          <div class="col-lg-9 col-12">
             <div class="shimmer rounded-4 w-100 shadow-sm" style="height: 350px;"></div>
          </div>
        </div>

        <!-- BANNER DANH MỤC THẬT -->
        <div v-else class="row align-items-center mb-5 pb-2">
          <div class="col-lg-3 d-none d-lg-block">
             <h1 class="fw-bold text-dark dark:text-white m-0" style="font-size: 3.5rem; line-height: 1.1;">{{ categoryName }}</h1>
          </div>
          <div class="col-lg-9 col-12">
             <div class="category-banner rounded-4 overflow-hidden position-relative shadow-sm bg-light dark:bg-[#121416]" style="height: 350px;">
               <img :src="categoryBanner" @error="e => e.target.src='/client_placeholder.png'" class="w-100 h-100 object-fit-cover">
               <div class="position-absolute top-50 start-50 translate-middle text-center w-100 pe-none">
                 <h1 class="display-3 fw-bold text-white text-shadow-lg font-script" style="letter-spacing: 2px;">{{ isSearching ? 'Search Results' : 'The Mood Diary' }}</h1>
               </div>
             </div>
          </div>
        </div>

        <!-- BỘ SƯU TẬP NỔI BẬT (KHUNG ĐỎ) -->
        <div v-if="!isFirstLoad && highlightProducts.length > 0" class="highlight-collection border border-danger border-opacity-50 rounded-4 p-4 position-relative mb-5 bg-white dark:bg-[#1a2533]">
          <span class="position-absolute top-0 start-50 translate-middle bg-white dark:bg-[#1a2533] px-3 text-danger fw-bold small text-uppercase tracking-widest border border-danger rounded-pill shadow-sm">
            NỔI BẬT NHẤT
          </span>
          <div class="custom-grid-4 mt-3">
            <ProductCard v-for="product in highlightProducts" :key="'hi'+product.id" 
                         :product="product" @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
          </div>
        </div>

        <!-- SKELETON SUB-CATEGORIES -->
        <div v-if="isFirstLoad" class="sub-categories-wrapper d-flex gap-3 overflow-hidden pb-3 mb-5 pe-none">
           <div v-for="i in 6" :key="'subskel'+i" class="shimmer rounded-4 flex-shrink-0" style="width: 140px; height: 180px;"></div>
        </div>

        <!-- THANH PHÂN LOẠI TRỰC QUAN (SUB-CATEGORIES) -->
        <div v-else-if="subCategories.length > 0" class="sub-categories-wrapper d-flex gap-3 overflow-auto custom-scrollbar-x pb-3 mb-5">
          <router-link v-for="sub in subCategories" :key="sub.id" :to="`/category/${sub.slug}`" 
                       class="sub-category-card flex-shrink-0 position-relative rounded-4 overflow-hidden cursor-pointer group d-block border border-light-subtle dark:border-gray-700 bg-light dark:bg-[#212529]" style="width: 140px; height: 180px;">
            <img :src="sub.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale opacity-75">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
            <div class="position-absolute bottom-0 w-100 text-center pb-3 px-2 z-index-2">
              <span class="fw-bold text-dark bg-white bg-opacity-90 px-2 py-1 rounded small text-uppercase shadow-sm d-inline-block">{{ sub.name }}</span>
            </div>
          </router-link>
        </div>

        <!-- THANH BỘ LỌC (FILTER BAR) -->
        <div class="filter-bar d-flex flex-wrap align-items-center gap-3 mb-4 pb-3 border-bottom dark:border-gray-700">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center">
            Tổng: <span class="badge bg-danger rounded-circle ms-2" style="font-size: 0.65rem;">{{ isFirstLoad ? '...' : totalProducts }}</span>
          </button>
          
          <div class="dropdown">
            <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center" type="button" data-bs-toggle="dropdown" :disabled="isLoading">
              {{ sortLabel }} <i class="bi bi-chevron-down ms-2" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu shadow-sm border-0 rounded-3 dark:bg-[#2b3035]">
              <li><a class="dropdown-item small py-2 dark:text-gray-300 dark:hover:bg-gray-700" href="#" @click.prevent="applySort('newest')">Mới nhất</a></li>
              <li><a class="dropdown-item small py-2 dark:text-gray-300 dark:hover:bg-gray-700" href="#" @click.prevent="applySort('best_sales')">Bán chạy</a></li>
              <li><a class="dropdown-item small py-2 dark:text-gray-300 dark:hover:bg-gray-700" href="#" @click.prevent="applySort('price_asc')">Giá: Thấp đến Cao</a></li>
              <li><a class="dropdown-item small py-2 dark:text-gray-300 dark:hover:bg-gray-700" href="#" @click.prevent="applySort('price_desc')">Giá: Cao đến Thấp</a></li>
            </ul>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- SKELETON LƯỚI SẢN PHẨM (Hiện khi đang Lọc/Tải) -->
        <!-- ========================================== -->
        <div v-if="isLoading" class="custom-grid-4 mb-5 pe-none">
          <div v-for="i in 8" :key="'skel'+i" class="skeleton-card w-100" aria-hidden="true">
            <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100" style="aspect-ratio: 3/4;"></div>
            <div class="product-info px-1 w-100">
              <div class="skeleton-price shimmer mb-2" style="height: 22px; width: 45%; border-radius: 4px;"></div>
              <div class="skeleton-title shimmer mb-3" style="height: 16px; width: 90%; border-radius: 4px;"></div>
              <div class="d-flex gap-2">
                <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- LƯỚI SẢN PHẨM CHÍNH (4 CỘT) -->
        <div v-else-if="mainProducts.length > 0" class="custom-grid-4 mb-5">
          <!-- ĐÃ ĐỒNG BỘ: Gọi compareStore.add -->
          <ProductCard v-for="product in mainProducts" :key="'main'+product.id" 
                       :product="product" @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
        </div>
        
        <!-- NẾU KHÔNG CÓ SẢN PHẨM -->
        <div v-else class="text-center py-5 my-5 text-muted">
           <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
           <h5 class="fw-normal">Không tìm thấy sản phẩm nào phù hợp.</h5>
        </div>

        <!-- NÚT XEM THÊM -->
        <div class="text-center" v-if="!isLoading && currentPage < lastPage">
          <button @click="loadMore" class="btn btn-outline-dark dark:text-white dark:border-gray-500 rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all" :disabled="isLoadingMore">
            <span v-if="isLoadingMore" class="spinner-border spinner-border-sm me-2" role="status"></span>
            Tải thêm sản phẩm
          </button>
        </div>

      </div>
    </div>

    <!-- CÁC MODALS DÙNG CHUNG -->
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    
    <!-- ĐÃ ĐỒNG BỘ: Sử dụng CompareModal thông minh -->
    <CompareModal />

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios';

// ĐÃ THÊM: Import Store so sánh
import { useCompareStore } from '@/stores/compareStore';

import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const route = useRoute();
const router = useRouter();
const compareStore = useCompareStore();

// ==========================================
// STATE DATA
// ==========================================
const categoryName = ref('TẤT CẢ SẢN PHẨM');
const categoryBanner = ref('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop');
const isSearching = ref(false);

const subCategories = ref([]);
const highlightProducts = ref([]);
const mainProducts = ref([]);
const totalProducts = ref(0);

const isFirstLoad = ref(true);
const isLoading = ref(true);
const isLoadingMore = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);
const currentSort = ref('newest');

const sortLabel = computed(() => {
  const map = { 'newest': 'Mới nhất', 'best_sales': 'Bán chạy', 'price_asc': 'Giá: Thấp đến Cao', 'price_desc': 'Giá: Cao đến Thấp' };
  return map[currentSort.value] || 'Sắp xếp';
});

// ==========================================
// API FETCHING LOGIC
// ==========================================
const fetchCategoryData = async (page = 1, append = false) => {
  if (append) isLoadingMore.value = true;
  else isLoading.value = true; 
  
  try {
    const params = { page: page, sort: currentSort.value };
    
    if (route.params.slug) params.slug = route.params.slug;
    if (route.query.search) {
        params.search = route.query.search;
        isSearching.value = true;
    } else {
        isSearching.value = false;
    }
    
    const res = await api.get('/client/category-page', { params });
    const payload = res.data.data;
    
    if (payload.category) {
        categoryName.value = payload.category.name;
        if (payload.category.thumbnail) categoryBanner.value = payload.category.thumbnail;
    } else if (route.query.search) {
        categoryName.value = `TÌM KIẾM: "${route.query.search}"`;
        categoryBanner.value = 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=2070&auto=format&fit=crop';
    } else {
        categoryName.value = 'TẤT CẢ SẢN PHẨM';
        categoryBanner.value = 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop';
    }
    
    subCategories.value = payload.sub_categories || [];
    
    if (!append) {
        highlightProducts.value = payload.highlight_products || [];
        mainProducts.value = payload.products || [];
    } else {
        mainProducts.value = [...mainProducts.value, ...payload.products];
    }
    
    currentPage.value = payload.current_page;
    lastPage.value = payload.last_page;
    totalProducts.value = payload.total;
    
  } catch (err) {
    console.error('Lỗi khi tải trang Danh mục:', err);
  } finally {
    isLoading.value = false;
    isFirstLoad.value = false; 
    isLoadingMore.value = false;
  }
};

// ==========================================
// ACTIONS
// ==========================================
const applySort = (sortType) => {
  currentSort.value = sortType;
  fetchCategoryData(1, false);
};

const loadMore = () => {
  if (currentPage.value < lastPage.value) {
    fetchCategoryData(currentPage.value + 1, true);
  }
};

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

watch(
  () => [route.params.slug, route.query.search],
  () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    isFirstLoad.value = true; 
    fetchCategoryData(1, false);
  }
);

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

onMounted(() => { 
    window.scrollTo(0, 0); 
    fetchCategoryData();
});
</script>

<style scoped>
.category-page-wrapper { width: 100%; }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }
.text-shadow-lg { text-shadow: 2px 4px 10px rgba(0,0,0,0.6); }
.tracking-widest { letter-spacing: 2px; }
.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; border-color: #212529 !important; }

.font-script { font-family: 'Georgia', serif; font-style: italic; }

.custom-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
@media (max-width: 991px) { .custom-grid-4 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.1); }
.group-hover-opacity-25 { opacity: 0; }
.group:hover .group-hover-opacity-25 { opacity: 0.2; }
.z-index-2 { z-index: 2; }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

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

.skeleton-card { width: 100%; }
.skeleton-img-wrapper { background-color: #f0f0f0; }
.skeleton-price { background-color: #f0f0f0; }
.skeleton-title { background-color: #f0f0f0; }
.skeleton-swatch { background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper, html.dark .skeleton-price, html.dark .skeleton-title, html.dark .skeleton-swatch { background-color: #2b3035; }

.transition-all { transition: all 0.3s ease; }
</style>