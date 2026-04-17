<template>
  <div class="flash-sale-page-wrapper pb-5 mb-5">
    
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-danger fw-bold" aria-current="page">Flash Sale</li>
          </ol>
        </nav>

        <div v-if="isLoading && isFirstLoad" class="hero-flash-sale rounded-4 overflow-hidden position-relative shadow-sm bg-light dark:bg-[#121416] mb-4 pe-none">
           <div class="shimmer w-100 h-100 position-absolute inset-0"></div>
        </div>

        <div v-else-if="flashSaleData" class="hero-flash-sale rounded-4 overflow-hidden position-relative shadow-lg mb-4 bg-dark">
          <img :src="flashSaleData.banner || 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop'" class="position-absolute inset-0 w-100 h-100 object-fit-cover opacity-50" @error="e => e.target.src='/client_placeholder.png'">
          <div class="position-absolute inset-0 bg-gradient-flash-sale z-index-1"></div>

          <div class="position-relative z-index-2 d-flex flex-column justify-content-center align-items-center h-100 text-center px-3">
            <h1 class="flash-text-stroke">FLASH</h1>
            <h1 class="flash-text-solid text-white">SALE</h1>
            <p class="text-white mt-2 mb-4 font-monospace tracking-widest text-uppercase text-shadow" style="letter-spacing: 4px;">{{ statusText }}</p>
            
            <div class="countdown-glass d-flex gap-3 gap-md-4 px-4 py-3 rounded-4 shadow-lg backdrop-blur border border-white border-opacity-25">
              <div class="text-center"><span class="fs-1 fw-black text-danger lh-1">{{ countdown.days }}</span><small class="d-block text-white text-uppercase tracking-widest mt-1" style="font-size: 0.65rem;">Ngày</small></div>
              <div class="text-center"><span class="fs-1 fw-black text-white lh-1">:</span></div>
              <div class="text-center"><span class="fs-1 fw-black text-danger lh-1">{{ countdown.hours }}</span><small class="d-block text-white text-uppercase tracking-widest mt-1" style="font-size: 0.65rem;">Giờ</small></div>
              <div class="text-center"><span class="fs-1 fw-black text-white lh-1">:</span></div>
              <div class="text-center"><span class="fs-1 fw-black text-danger lh-1">{{ countdown.minutes }}</span><small class="d-block text-white text-uppercase tracking-widest mt-1" style="font-size: 0.65rem;">Phút</small></div>
              <div class="text-center"><span class="fs-1 fw-black text-white lh-1">:</span></div>
              <div class="text-center"><span class="fs-1 fw-black text-danger lh-1">{{ countdown.seconds }}</span><small class="d-block text-white text-uppercase tracking-widest mt-1" style="font-size: 0.65rem;">Giây</small></div>
            </div>
          </div>
        </div>

        <div v-else-if="!isLoading && !flashSaleData" class="hero-flash-sale rounded-4 d-flex flex-column align-items-center justify-content-center bg-light dark:bg-[#1a2533] border dark:border-gray-700 mb-4">
           <i class="bi bi-clock-history text-muted mb-3" style="font-size: 4rem;"></i>
           <h2 class="fw-bold text-dark dark:text-white font-monospace">HIỆN CHƯƠNG TRÌNH ĐÃ KẾT THÚC</h2>
           <p class="text-muted">Hãy quay lại sau để săn những deal cực hot nhé!</p>
        </div>

        <!-- DẢI BĂNG MARQUEE -->
        <div class="marquee-container bg-danger text-white py-2 mb-5 overflow-hidden rounded-pill shadow-sm" v-if="flashSaleData || isLoading">
          <div class="marquee-content fw-bold text-uppercase tracking-widest small">
            <span>✦ FLASH SALE ✦ LIMITED EDITION ✦ EXCLUSIVE DEALS ✦ MUST HAVE ITEM ✦ OUT OF STOCK SOON ✦ FLASH SALE ✦ LIMITED EDITION ✦ EXCLUSIVE DEALS ✦ MUST HAVE ITEM ✦ OUT OF STOCK SOON ✦ </span>
            <span>✦ FLASH SALE ✦ LIMITED EDITION ✦ EXCLUSIVE DEALS ✦ MUST HAVE ITEM ✦ OUT OF STOCK SOON ✦ FLASH SALE ✦ LIMITED EDITION ✦ EXCLUSIVE DEALS ✦ MUST HAVE ITEM ✦ OUT OF STOCK SOON ✦ </span>
          </div>
        </div>

        <!-- ID Neo (Anchor) để tự động cuộn lên -->
        <div id="flash-sale-grid-start"></div>

        <!-- ========================================== -->
        <!-- MAIN CONTENT (ĐÃ ĐỔI SANG FLEXBOX CHUẨN KÍCH THƯỚC) -->
        <!-- ========================================== -->
        <div class="d-flex flex-column flex-lg-row" style="gap: 1.5rem;" v-if="flashSaleData || (isLoading && isFirstLoad)">
          
          <!-- CỘT TRÁI: SIDEBAR -->
          <div class="sidebar-wrapper flex-shrink-0">
            
            <div class="d-lg-none mb-3">
               <button class="btn btn-outline-dark dark:text-white dark:border-gray-500 w-100 fw-bold d-flex justify-content-between align-items-center rounded-3 py-3" @click="showMobileFilter = !showMobileFilter">
                  <span><i class="bi bi-funnel me-2"></i> BỘ LỌC ĐIỀU HƯỚNG</span>
                  <i class="bi" :class="showMobileFilter ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
               </button>
            </div>

            <!-- NỘI DUNG SIDEBAR -->
            <div class="sticky-top transition-all" :class="{'d-none d-lg-block': !showMobileFilter}" style="top: 100px; z-index: 10;">
               <div class="p-4 bg-light dark:bg-[#1a2533] rounded-4 border dark:border-gray-700 shadow-sm">
                  
                  <h6 class="fw-bold text-dark dark:text-white text-uppercase tracking-widest mb-4 pb-3 border-bottom dark:border-gray-600 d-flex align-items-center">
                    <i class="bi bi-sliders text-danger me-2"></i> Điều hướng
                  </h6>

                  <!-- LỌC: DANH MỤC -->
                  <div class="mb-4">
                     <label class="small fw-bold text-muted mb-3 text-uppercase tracking-wide">Danh Mục</label>
                     <div class="d-flex flex-column gap-2">
                        <label class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeCategory === 'all'}">
                           <input type="radio" name="categoryFilter" value="all" v-model="activeCategory" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-dark dark:text-gray-300 fs-6">Tất cả sản phẩm</span>
                        </label>
                        
                        <!-- Lấy danh mục từ Backend trả về -->
                        <div v-if="isLoading && isFirstLoad" class="ms-4 mt-2">
                          <div class="shimmer rounded mb-2" style="width: 80%; height: 16px;"></div>
                          <div class="shimmer rounded mb-2" style="width: 60%; height: 16px;"></div>
                        </div>
                        <label v-else v-for="cat in availableCategories" :key="cat" class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeCategory === cat}">
                           <input type="radio" name="categoryFilter" :value="cat" v-model="activeCategory" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-dark dark:text-gray-300 fs-6">{{ cat }}</span>
                        </label>
                     </div>
                  </div>

                  <!-- LỌC: SẮP XẾP -->
                  <div class="mb-2">
                     <label class="small fw-bold text-muted mb-3 text-uppercase tracking-wide">Sắp xếp theo</label>
                     <div class="d-flex flex-column gap-2">
                        <label class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeSort === 'default'}">
                           <input type="radio" name="sortFilter" value="default" v-model="activeSort" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-dark dark:text-gray-300 fs-6">Nổi bật</span>
                        </label>
                        <label class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeSort === 'discount_desc'}">
                           <input type="radio" name="sortFilter" value="discount_desc" v-model="activeSort" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-danger fs-6">Ưu đãi tốt nhất</span>
                        </label>
                        <label class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeSort === 'price_asc'}">
                           <input type="radio" name="sortFilter" value="price_asc" v-model="activeSort" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-dark dark:text-gray-300 fs-6">Giá tăng dần</span>
                        </label>
                        <label class="form-check custom-radio-btn p-2 rounded-3 cursor-pointer d-flex align-items-center m-0 transition-all" :class="{'active-filter': activeSort === 'price_desc'}">
                           <input type="radio" name="sortFilter" value="price_desc" v-model="activeSort" class="d-none">
                           <div class="radio-indicator me-2"></div>
                           <span class="fw-semibold text-dark dark:text-gray-300 fs-6">Giá giảm dần</span>
                        </label>
                     </div>
                  </div>

               </div>
            </div>
          </div>

          <!-- CỘT PHẢI: LƯỚI SẢN PHẨM KHỔNG LỒ (ĐÃ GỠ COL-LG-9) -->
          <div class="main-content-wrapper flex-grow-1" style="min-width: 0;">
            
            <div v-if="isLoading" class="custom-grid-4 mb-5 pe-none">
              <div v-for="i in 8" :key="'skel'+i" class="skeleton-card w-100 h-100 d-flex flex-column" aria-hidden="true">
                <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100 flex-shrink-0" style="aspect-ratio: 3/4;"></div>
                <div class="product-info px-1 w-100 flex-grow-1">
                  <div class="skeleton-price shimmer mb-2" style="height: 22px; width: 45%; border-radius: 4px;"></div>
                  <div class="skeleton-title shimmer mb-3" style="height: 16px; width: 90%; border-radius: 4px;"></div>
                </div>
              </div>
            </div>

            <!-- RENDER DANH MỤC (CATEGORIES) -->
            <div v-else-if="Object.keys(groupedProducts).length > 0">
              
              <div v-for="(group, catName) in groupedProducts" :key="catName" class="category-group mb-5 pb-4">
                 
                 <!-- ĐÃ FIX: Gỡ bỏ sticky header, để nó cuộn tự nhiên theo luồng trang -->
                 <div class="d-flex align-items-center gap-3 mb-4 bg-white dark:bg-[#1a2533] py-2">
                    <h4 class="fw-bold m-0 text-uppercase tracking-widest text-dark dark:text-white d-flex align-items-center bg-white dark:bg-[#1a2533]">
                      <span class="bg-danger rounded-pill me-3" style="width: 5px; height: 24px;"></span>
                      {{ catName }}
                    </h4>
                    <div class="flex-grow-1 border-top border-light-subtle dark:border-gray-600 mt-1"></div>
                 </div>

                 <!-- LƯỚI 4 CỘT CHUẨN XÁC VÀ BỔ SUNG CLASS H-100 -->
                 <div class="custom-grid-4">
                   <div class="position-relative h-100" v-for="product in group" :key="'fs-prod-'+product.id">
                     
                     <ProductCard class="h-100" :product="product" @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
                     
                     <div class="position-absolute top-0 start-0 m-2 pe-none z-index-2" v-if="product.discount_percent >= 40">
                        <span class="badge bg-dark text-warning border border-warning shadow-sm flash-pulse px-2 py-1">
                          <i class="bi bi-fire"></i> HOT
                        </span>
                     </div>
                   </div>
                 </div>

              </div>
              
              <div class="text-center pt-3 border-top dark:border-gray-700" v-if="!isLoading && currentPage < lastPage">
                <button @click="loadMore" class="btn btn-outline-danger rounded-pill px-5 py-3 fw-bold text-uppercase tracking-widest hover-bg-danger transition-all shadow-sm" :disabled="isLoadingMore">
                  <span v-if="isLoadingMore" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  Tải Thêm Sản Phẩm Khác
                </button>
              </div>

            </div>

            <!-- KHÔNG TÌM THẤY SP TRONG BỘ LỌC -->
            <div v-else class="text-center py-5 my-5 text-muted bg-light dark:bg-[#1a2533] rounded-4 border dark:border-gray-700">
               <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
               <h5 class="fw-normal">Không có sản phẩm nào phù hợp với bộ lọc.</h5>
               <button v-if="activeCategory !== 'all'" @click="activeCategory = 'all'" class="btn btn-link text-danger fw-bold mt-2">Xóa bộ lọc</button>
            </div>

          </div>
        </div>

      </div>
    </div>

    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import { useCompareStore } from '@/stores/compareStore';

import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const router = useRouter();
const compareStore = useCompareStore();

// STATE DATA
const flashSaleData = ref(null);
const products = ref([]);
const totalProducts = ref(0);

// Biến lưu danh sách danh mục từ API
const availableCategories = ref([]);

const isFirstLoad = ref(true);
const isLoading = ref(true);
const isLoadingMore = ref(false);
const showMobileFilter = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);

// LỌC & SẮP XẾP
const activeCategory = ref('all');
const activeSort = ref('default');

// Khi bấm lọc, GỌI LẠI BACKEND và TỰ ĐỘNG CUỘN LÊN
watch([activeCategory, activeSort], () => {
  fetchFlashSaleData(1, false);

  nextTick(() => {
    const gridStart = document.getElementById('flash-sale-grid-start');
    if (gridStart) {
      const yOffset = -90; 
      const y = gridStart.getBoundingClientRect().top + window.scrollY + yOffset;
      window.scrollTo({ top: y, behavior: 'smooth' });
    }
  });
});

// Phân nhóm hiển thị theo từng Header Danh mục dựa trên dữ liệu Backend đã lọc
const groupedProducts = computed(() => {
  if (products.value.length === 0) return {};
  
  const groups = {};
  products.value.forEach(p => {
      const catName = p.category ? p.category.name.split(' | ')[0].trim() : 'Sản phẩm khác';
      if (!groups[catName]) groups[catName] = [];
      groups[catName].push(p);
  });
  
  const sortedKeys = Object.keys(groups).sort();
  const sortedGroups = {};
  sortedKeys.forEach(k => { sortedGroups[k] = groups[k]; });
  
  return sortedGroups;
});

// ĐẾM NGƯỢC
const statusText = ref('Đang tải...');
const countdown = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' });
let timerInterval = null;

const updateCountdown = () => {
  if (!flashSaleData.value) return;
  const now = new Date().getTime();
  const startTime = new Date(flashSaleData.value.start_time).getTime();
  const endTime = new Date(flashSaleData.value.end_time).getTime();
  let distance = 0;

  if (now < startTime) { statusText.value = 'Sắp diễn ra'; distance = startTime - now; } 
  else if (now >= startTime && now <= endTime) { statusText.value = 'Đang diễn ra - Kết thúc trong'; distance = endTime - now; } 
  else { 
      statusText.value = 'Đã kết thúc'; 
      clearInterval(timerInterval); 
      return; 
  }

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

const startTimer = () => {
  if(timerInterval) clearInterval(timerInterval);
  updateCountdown();
  timerInterval = setInterval(updateCountdown, 1000);
};

// FETCH DATA NỐI VỚI BỘ LỌC BACKEND
const fetchFlashSaleData = async (page = 1, append = false) => {
  if (append) isLoadingMore.value = true;
  else isLoading.value = true; 
  
  try {
    const params = { 
      page,
      category: activeCategory.value,
      sort: activeSort.value
    };

    const res = await api.get('/client/flash-sale-page', { params });
    const payload = res.data.data;
    
    flashSaleData.value = payload.flash_sale;
    
    // Ghi nhận tất cả danh mục từ Backend
    if (payload.available_categories) {
        availableCategories.value = payload.available_categories;
    }
    
    if (!append) {
        products.value = payload.products.data || [];
        startTimer();
    } else {
        products.value = [...products.value, ...payload.products.data];
    }
    
    currentPage.value = payload.products.current_page;
    lastPage.value = payload.products.last_page;
    totalProducts.value = payload.products.total;
    
  } catch (err) {
    console.error('Lỗi khi tải trang Flash Sale:', err);
  } finally {
    isLoading.value = false;
    isFirstLoad.value = false; 
    isLoadingMore.value = false;
  }
};

const loadMore = () => {
  if (currentPage.value < lastPage.value) {
    fetchFlashSaleData(currentPage.value + 1, true);
  }
};

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

onMounted(() => { 
    window.scrollTo(0, 0); 
    fetchFlashSaleData();
});

onUnmounted(() => { if(timerInterval) clearInterval(timerInterval); });
</script>

<style scoped>
.flash-sale-page-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }
.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }
.hover-bg-danger:hover { background-color: #dc3545 !important; color: #fff !important; }

/* HERO BANNER */
.hero-flash-sale { height: 450px; display: flex; align-items: center; justify-content: center; }
.bg-gradient-flash-sale { background: radial-gradient(circle at center, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%); }

.flash-text-stroke {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 8rem; font-weight: 900; line-height: 0.8;
  color: transparent; -webkit-text-stroke: 2px rgba(255, 255, 255, 0.8);
  margin: 0; letter-spacing: -2px; animation: pulseStroke 2s infinite alternate;
}
.flash-text-solid {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 8rem; font-weight: 900; line-height: 0.8;
  margin: 0; margin-top: -30px; letter-spacing: -2px; text-shadow: 0 10px 30px rgba(0,0,0,0.8);
}
@media (max-width: 768px) {
  .hero-flash-sale { height: 350px; }
  .flash-text-stroke, .flash-text-solid { font-size: 4.5rem; }
  .flash-text-solid { margin-top: -15px; }
}

@keyframes pulseStroke { 0% { -webkit-text-stroke-color: rgba(255, 255, 255, 0.4); } 100% { -webkit-text-stroke-color: rgba(255, 255, 255, 1); } }

/* GLASSMORPHISM COUNTDOWN */
.backdrop-blur { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
.fw-black { font-weight: 900; }

/* MARQUEE ANIMATION */
.marquee-container { white-space: nowrap; }
.marquee-content { display: inline-block; animation: marquee 25s linear infinite; }
.marquee-content span { padding-right: 20px; }
@keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

/* BỘ LỌC (SIDEBAR) ĐƯỢC FIX LẠI KÍCH THƯỚC */
.sidebar-wrapper {
  /* Set width cứng cho sidebar để nó không bị bóp méo, 
     giữ Lưới 4 cột của thẻ sp luôn rộng rãi */
}
@media (min-width: 992px) {
  .sidebar-wrapper { width: 260px; }
}

.custom-radio-btn { border: 1.5px solid transparent; background-color: transparent; }
.custom-radio-btn:hover { background-color: var(--color-c-effect); }
html.dark .custom-radio-btn:hover { background-color: #212529; }

.radio-indicator { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #adb5bd; transition: all 0.2s ease; position: relative; }
.active-filter { background-color: var(--color-c-effect); }
html.dark .active-filter { background-color: #212529; }
.active-filter .radio-indicator { border-color: var(--color-c-dark); }
html.dark .active-filter .radio-indicator { border-color: #f8f9fa; }
.active-filter .radio-indicator::after {
  content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  width: 8px; height: 8px; border-radius: 50%; background-color: var(--color-c-dark);
}
html.dark .active-filter .radio-indicator::after { background-color: #f8f9fa; }

/* ==========================================
   GRID & SKELETON (ĐÃ CHUYỂN SANG 4 CỘT)
========================================== */
.custom-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
@media (max-width: 1199px) { .custom-grid-4 { grid-template-columns: repeat(3, 1fr); gap: 1.25rem; } }
@media (max-width: 991px) { .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

.shimmer {
  background: #f6f7f8; background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat; background-size: 800px 100%; animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035; background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.skeleton-card { width: 100%; }
.transition-all { transition: all 0.3s ease; }

.flash-pulse { animation: pulseRed 1.5s infinite; }
@keyframes pulseRed { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }
</style>