<template>
  <div class="search-modal-wrapper">
    <!-- Lớp phủ tối màn hình -->
    <transition name="fade">
      <div v-if="isOpen" class="search-backdrop" @click="closeModal"></div>
    </transition>

    <!-- Popup đè lên toàn bộ Header -->
    <transition name="mega-slide">
      <div v-if="isOpen" class="search-panel shadow-lg rounded-4 bg-white dark:bg-[#1a2533] d-flex flex-column"
           style="top: 20px; max-height: calc(100vh - 40px);">
        
        <!-- Search Content -->
        <div class="search-content custom-scrollbar-y px-4 px-lg-5 pt-4 pb-2 flex-grow-1" style="min-height: 60vh;">
          
          <!-- TIÊU ĐỀ & Ô NHẬP LIỆU -->
          <div class="text-center mb-4 mt-2">
            <h6 class="fw-bold text-dark dark:text-white text-uppercase tracking-widest mb-4">Tìm Kiếm</h6>
            <div class="position-relative mx-auto" style="max-width: 600px;">
              <input type="text" 
                     ref="searchInput"
                     class="form-control form-control-lg rounded-pill px-4 shadow-none search-input border-secondary-subtle dark:border-gray-600 dark:bg-[#212529] dark:text-white" 
                     placeholder="Tìm kiếm trong ZYRO..."
                     v-model="searchQuery"
                     @keyup.enter="submitSearch">
              <button class="btn position-absolute top-50 end-0 translate-middle-y pe-3 border-0 text-urban bg-transparent" @click="submitSearch">
                <i class="bi bi-search fs-5"></i>
              </button>
            </div>
          </div>

          <transition name="fade-slide" mode="out-in">
            
            <!-- ============================================== -->
            <!-- TRẠNG THÁI 1: KHI Ô TÌM KIẾM TRỐNG             -->
            <!-- ============================================== -->
            <div v-if="!searchQuery.trim()" key="empty-state" class="w-100">
              <!-- LỊCH SỬ TÌM KIẾM -->
              <div v-if="recentSearches.length > 0" class="mb-5 mx-auto" style="max-width: 800px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="text-muted dark:text-gray-400 small fw-semibold">Tìm kiếm gần đây</span>
                  <button class="btn btn-link text-danger p-0 text-decoration-none small" style="font-size: 0.8rem;" @click="clearHistory">Xoá lịch sử</button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <span v-for="kw in recentSearches" :key="kw" 
                        class="badge bg-light text-dark dark:bg-[#212529] dark:text-gray-300 border dark:border-gray-700 rounded-pill px-3 py-2 fw-normal cursor-pointer hover-bg-effect transition-all"
                        @click="clickRecentSearch(kw)">
                    {{ kw }}
                  </span>
                </div>
              </div>

              <!-- SẢN PHẨM XU HƯỚNG -->
              <div class="mb-4 mx-auto" style="max-width: 1200px;">
                <h6 class="text-center text-muted dark:text-gray-400 fw-semibold mb-4">Sản phẩm xu hướng</h6>
                
                <div v-if="trendingProducts && trendingProducts.length > 0" class="trending-products-grid custom-scrollbar-x pb-3">
                  <div v-for="product in trendingProducts.slice(0, 8)" :key="'trend'+product.id" class="product-item flex-shrink-0">
                    <ProductCard 
                      :product="product" 
                      @quick-view="handleOpenQuickView" 
                      @compare="handleAddToCompare" 
                      @wishlist="handleAddToWishlist"
                      @options="handleGoToDetail"
                    />
                  </div>
                </div>
                
                <div v-else class="trending-products-grid custom-scrollbar-x pb-3 pe-none overflow-hidden">
                  <div v-for="i in 5" :key="'trend-skeleton-'+i" class="product-item flex-shrink-0">
                    <div class="skeleton-card w-100" aria-hidden="true">
                      <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100"></div>
                      <div class="product-info px-1 w-100">
                        <div class="skeleton-price shimmer mb-2"></div>
                        <div class="skeleton-title shimmer mb-3"></div>
                        <div class="d-flex gap-2">
                          <div class="skeleton-swatch shimmer rounded-circle"></div>
                          <div class="skeleton-swatch shimmer rounded-circle"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ============================================== -->
            <!-- TRẠNG THÁI 2: ĐANG TÌM KIẾM (LIVE SEARCH)      -->
            <!-- ============================================== -->
            <div v-else key="results-state" class="mb-4 mx-auto w-100" style="max-width: 1200px;">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="text-muted dark:text-gray-400 fw-semibold mb-0">
                  Gợi ý cho "<span class="text-dark dark:text-white fw-bold">{{ searchQuery }}</span>"
                </h6>
                <div v-if="isSearching" class="spinner-border spinner-border-sm text-secondary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              
              <div class="transition-all" :class="{ 'opacity-50 pe-none': isSearching && displayResults.length > 0 }">
                
                <div v-if="displayResults.length > 0" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                  <div v-for="product in displayResults" :key="'search'+product.id" class="col">
                    <ProductCard 
                      :product="product" 
                      @quick-view="handleOpenQuickView" 
                      @compare="handleAddToCompare" 
                      @wishlist="handleAddToWishlist"
                      @options="handleGoToDetail"
                    />
                  </div>
                </div>

                <div v-else-if="isSearching" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                  <div class="col" v-for="i in 5" :key="'search-skeleton-'+i">
                    <div class="skeleton-card w-100" aria-hidden="true">
                      <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100"></div>
                      <div class="product-info px-1 w-100">
                        <div class="skeleton-price shimmer mb-2"></div>
                        <div class="skeleton-title shimmer mb-3"></div>
                        <div class="d-flex gap-2">
                          <div class="skeleton-swatch shimmer rounded-circle"></div>
                          <div class="skeleton-swatch shimmer rounded-circle"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-else class="text-center py-5 w-100 text-muted d-flex flex-column align-items-center">
                  <i class="bi bi-search fs-1 mb-3 opacity-25"></i>
                  <span class="fst-italic">Không tìm thấy sản phẩm nào phù hợp.</span>
                </div>

              </div>
            </div>

          </transition>
        </div>

        <!-- NÚT ĐÓNG DƯỚI CÙNG -->
        <div class="search-footer text-center pb-4 pt-2 bg-white dark:bg-[#1a2533] rounded-bottom-4 shadow-sm-top z-index-1">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 shadow-sm border rounded-pill px-5 py-2 fw-bold hover-bg-effect transition-all" @click="closeModal">
            <i class="bi bi-x-lg me-1"></i> Đóng
          </button>
        </div>

      </div>
    </transition>

    <QuickViewModal 
      :is-open="isQuickViewOpen" 
      :product="selectedProductData" 
      @close="isQuickViewOpen = false" 
    />

    <CompareModal 
      :compare-list="compareList"
      @remove="removeFromCompare"
      @clear="clearCompare"
    />
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios'; 

// ĐÃ THAY ĐỔI: Import ZyroSwal thay cho Swal mặc định
import { ZyroSwal } from '@/components/client/ZyroSwal';

import ProductCard from './ProductCard.vue';
import QuickViewModal from './QuickViewModal.vue';
import CompareModal from './CompareModal.vue'; 

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  trendingProducts: { type: Array, default: () => [] } 
});

const emit = defineEmits(['close']);
const router = useRouter();

const searchInput = ref(null);
const searchQuery = ref('');
const recentSearches = ref([]);

const isSearching = ref(false);
const searchResults = ref([]);
let searchTimeout = null;
const hasFetchedAPI = ref(false);

onMounted(() => {
  const savedHistory = localStorage.getItem('zyro_recent_searches');
  if (savedHistory) {
    try { recentSearches.value = JSON.parse(savedHistory); } catch(e) {}
  }
});

const displayResults = computed(() => {
  if (hasFetchedAPI.value && searchResults.value.length > 0) {
    return searchResults.value;
  }
  const q = searchQuery.value.trim().toLowerCase();
  if (q !== '') {
    return props.trendingProducts.filter(p => 
      p.name.toLowerCase().includes(q) || (p.slug && p.slug.toLowerCase().includes(q))
    );
  }
  return [];
});

watch(searchQuery, (newVal) => {
  const query = newVal.trim();
  if (query === '') {
    searchResults.value = [];
    isSearching.value = false;
    hasFetchedAPI.value = false;
    return;
  }
  isSearching.value = true;
  hasFetchedAPI.value = false;
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    try {
      const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/client/products/search?q=${query}`);
      if (res.data.success) {
        searchResults.value = res.data.data;
        hasFetchedAPI.value = true;
      }
    } catch (error) {
      console.error('Lỗi Live Search:', error);
      searchResults.value = [];
    } finally {
      isSearching.value = false;
    }
  }, 500); 
});

const isQuickViewOpen = ref(false);
const selectedProductData = ref({});
const handleOpenQuickView = (product) => {
  selectedProductData.value = product;
  isQuickViewOpen.value = true;
};

// ĐÃ ĐỒNG BỘ: Sử dụng ZyroSwal.toastSuccess
const handleAddToWishlist = (product) => {
  ZyroSwal.toastSuccess('Đã thêm vào danh sách yêu thích');
};

const handleGoToDetail = (product) => {
  closeModal();
  router.push(`/product/${product.id}`);
};

const compareList = ref([]);

// ĐÃ ĐỒNG BỘ: Sử dụng ZyroSwal.toastSuccess
const handleAddToCompare = (product) => {
  if (compareList.value.find(p => p.id === product.id)) {
    ZyroSwal.toastSuccess('Sản phẩm đã có trong danh sách');
    return;
  }
  if (compareList.value.length >= 10) {
    ZyroSwal.toastSuccess('Chỉ được so sánh tối đa 10 sản phẩm');
    return;
  }
  compareList.value.push(product);
  ZyroSwal.toastSuccess('Đã thêm vào bảng so sánh');
};

const removeFromCompare = (index) => compareList.value.splice(index, 1);
const clearCompare = () => compareList.value = [];

const closeModal = () => {
  emit('close');
};

const clearHistory = () => {
  recentSearches.value = [];
  localStorage.removeItem('zyro_recent_searches');
};

const clickRecentSearch = (kw) => {
  searchQuery.value = kw;
};

const submitSearch = () => {
  const query = searchQuery.value.trim();
  if (query !== '') {
    let history = [...recentSearches.value];
    history = history.filter(item => item.toLowerCase() !== query.toLowerCase());
    history.unshift(query);
    if (history.length > 8) history.pop();
    recentSearches.value = history;
    localStorage.setItem('zyro_recent_searches', JSON.stringify(history));
    closeModal();
    router.push({ path: '/category', query: { search: query } });
    searchQuery.value = '';
    searchResults.value = [];
  }
};

watch(() => props.isOpen, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden';
    nextTick(() => {
      if (searchInput.value) searchInput.value.focus();
    });
  } else {
    document.body.style.overflow = '';
  }
});
</script>

<style scoped>
.search-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
}
.search-panel {
  position: fixed;
  left: 50%;
  transform: translateX(-50%); 
  width: 96%; 
  max-width: 1300px;
  z-index: 1060; 
  overflow: hidden; 
  border: 1px solid rgba(0,0,0,0.08);
}
html.dark .search-panel { border: 1px solid rgba(255,255,255,0.05); }
.search-content { overflow-y: auto; }
.search-input { transition: all 0.3s ease; }
.search-input:focus {
  border-color: var(--color-c-hover, #547792);
  box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.15);
}
.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; }
.trending-products-grid {
  display: flex; gap: 1.5rem; overflow-x: auto;
  scroll-snap-type: x mandatory; scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch; padding-bottom: 10px;
}
.product-item { width: calc(20% - 1.2rem); min-width: 220px; scroll-snap-align: start; }
.skeleton-card { width: 100%; }
.skeleton-img-wrapper { aspect-ratio: 3 / 4; background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper { background-color: #2b3035; }
.skeleton-price { height: 22px; width: 45%; border-radius: 4px; background-color: #f0f0f0; }
html.dark .skeleton-price { background-color: #2b3035; }
.skeleton-title { height: 16px; width: 90%; border-radius: 4px; background-color: #f0f0f0; }
html.dark .skeleton-title { background-color: #2b3035; }
.skeleton-swatch { width: 18px; height: 18px; background-color: #f0f0f0; }
html.dark .skeleton-swatch { background-color: #2b3035; }
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
.shadow-sm-top { box-shadow: 0 -4px 10px rgba(0,0,0,0.03); }
.tracking-widest { letter-spacing: 2px; }
.transition-all { transition: all 0.3s ease; }
.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.mega-slide-enter-active, .mega-slide-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mega-slide-enter-from, .mega-slide-leave-to { transform: translate(-50%, -20px) scale(0.98); opacity: 0; }
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.25s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }
</style>