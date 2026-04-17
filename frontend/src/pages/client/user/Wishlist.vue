<template>
  <div class="user-wishlist-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Sản phẩm yêu thích</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- CỘT TRÁI: SIDEBAR QUẢN LÝ -->
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <!-- CỘT PHẢI: NỘI DUNG WISHLIST -->
          <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in pl-4 pb-5 px-3 min-vh-50">
              
              <!-- HEADER & BỘ LỌC CÔNG CỤ -->
              <div class="mb-4 pb-4 border-bottom dark:border-gray-700">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                  <div>
                    <h4 class="fw-bold text-c-dark dark:text-white mb-1">Sản Phẩm Yêu Thích</h4>
                    <p class="text-muted small mb-0">Bạn đang có <strong class="text-urban">{{ meta.total }}</strong> sản phẩm trong danh sách.</p>
                  </div>
                </div>

                <!-- TOOLBAR (TÌM KIẾM & LỌC) -->
                <div class="p-3 bg-light dark:bg-[#212529] rounded-3 border dark:border-gray-700">
                  <div class="row g-3 align-items-center">
                    
                    <!-- Ô TÌM KIẾM -->
                    <div class="col-md-5">
                      <div class="position-relative">
                        <input type="text" class="form-control custom-input ps-5" placeholder="Tìm theo tên sản phẩm..." v-model="searchQuery" @input="debounceSearch">
                        <i class="bi bi-search position-absolute text-muted" style="top: 50%; left: 15px; transform: translateY(-50%);"></i>
                      </div>
                    </div>

                    <!-- BỘ LỌC THỜI GIAN -->
                    <div class="col-md-4">
                      <div class="d-flex align-items-center bg-white dark:bg-[#1a2533] rounded-3 border dark:border-gray-600 px-2 overflow-hidden shadow-sm-hover transition-all custom-select-wrapper">
                         <i class="bi bi-calendar-event text-muted ms-2"></i>
                         <select class="form-select border-0 shadow-none fw-medium text-dark dark:text-gray-200 bg-transparent" v-model="filterParams.date" @change="applyFilters">
                           <option value="all">Tất cả thời gian</option>
                           <option value="today">Đã thêm hôm nay</option>
                           <option value="week">Thêm trong tuần này</option>
                           <option value="month">Thêm trong tháng này</option>
                         </select>
                      </div>
                    </div>

                    <!-- BỘ LỌC SẮP XẾP -->
                    <div class="col-md-3">
                      <div class="d-flex align-items-center bg-white dark:bg-[#1a2533] rounded-3 border dark:border-gray-600 px-2 overflow-hidden shadow-sm-hover transition-all custom-select-wrapper">
                         <i class="bi bi-sort-down text-muted ms-2"></i>
                         <select class="form-select border-0 shadow-none fw-medium text-dark dark:text-gray-200 bg-transparent" v-model="filterParams.sort" @change="applyFilters">
                           <option value="latest">Mới nhất</option>
                           <option value="oldest">Cũ nhất</option>
                           <!-- ĐÃ FIX: Đổi dấu "->" thành chữ "đến" cho chuyên nghiệp hơn -->
                           <option value="price_asc">Giá tăng dần</option>
                           <option value="price_desc">Giá giảm dần</option>
                         </select>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <!-- TRẠNG THÁI LOADING BAN ĐẦU -->
              <div v-if="isLoading && wishlists.length === 0" class="row g-4 pe-none">
                 <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" v-for="i in 4" :key="'skel'+i">
                    <div class="skeleton-card w-100">
                      <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100" style="aspect-ratio: 3/4;"></div>
                      <div class="product-info px-1 w-100">
                        <div class="skeleton-price shimmer mb-2" style="height: 22px; width: 45%; border-radius: 4px;"></div>
                        <div class="skeleton-title shimmer mb-3" style="height: 16px; width: 90%; border-radius: 4px;"></div>
                      </div>
                    </div>
                 </div>
              </div>

              <!-- TRẠNG THÁI TRỐNG HOẶC KHÔNG TÌM THẤY -->
              <div v-else-if="wishlists.length === 0" class="text-center py-5 my-3 animation-fade-in">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 100px; height: 100px;">
                  <i class="bi bi-heart text-muted opacity-50" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold text-dark dark:text-white">Không tìm thấy sản phẩm</h5>
                <p class="text-muted mb-4">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm nhé.</p>
                <button v-if="hasActiveFilter" @click="resetFilters" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">Xóa bộ lọc</button>
                <router-link v-else to="/category" class="btn btn-urban text-white rounded-pill px-4 py-2 fw-bold shadow-sm hover-transform">
                  Khám phá ngay <i class="bi bi-arrow-right ms-1"></i>
                </router-link>
              </div>

              <!-- LƯỚI SẢN PHẨM (ĐÃ CHUYỂN THÀNH 4 CỘT) -->
              <transition-group name="list" tag="div" class="row g-4" v-else>
                <!-- col-xl-3 (4 items/hàng), col-lg-4 (3 items/hàng) -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" v-for="product in wishlists" :key="product.id">
                  <div class="hover-transform h-100">
                    <ProductCard 
                      :product="product" 
                      :is-wishlist-card="true"
                      @quick-view="handleOpenQuickView" 
                      @compare="compareStore.add" 
                      @options="handleGoToDetail" 
                    />
                  </div>
                </div>
              </transition-group>

              <!-- NÚT TẢI THÊM (LOAD MORE) -->
              <div class="text-center mt-5" v-if="meta.current_page < meta.last_page">
                 <button class="btn btn-outline-dark dark:text-gray-300 dark:border-gray-500 rounded-pill px-5 py-2 fw-bold text-uppercase tracking-wide shadow-sm transition-all hover-btn-urban d-inline-flex align-items-center"
                         @click="loadMore" :disabled="isLoadingMore">
                    <span v-if="isLoadingMore" class="spinner-border spinner-border-sm me-2"></span>
                    <span v-else class="me-2"><i class="bi bi-arrow-clockwise"></i></span>
                    Tải thêm sản phẩm
                 </button>
              </div>

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
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';

import UserSidebar from '@/components/client/UserSidebar.vue';
import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

import { useWishlistStore } from '@/stores/wishlistStore';
import { useCompareStore } from '@/stores/compareStore';

const router = useRouter();
const wishlistStore = useWishlistStore();
const compareStore = useCompareStore();

const wishlists = ref([]);
const isLoading = ref(true);
const isLoadingMore = ref(false);

// State cho Search & Filter
const searchQuery = ref('');
const filterParams = ref({
  date: 'all',
  sort: 'latest',
  page: 1
});
const meta = ref({ current_page: 1, last_page: 1, total: 0 });

let searchTimeout = null;

// Kiểm tra xem user có đang dùng bộ lọc nào không
const hasActiveFilter = computed(() => {
  return searchQuery.value !== '' || filterParams.value.date !== 'all' || filterParams.value.sort !== 'latest';
});

// Chờ 0.5s sau khi ngưng gõ phím mới search (Debounce)
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filterParams.value.page = 1; // Về trang 1 khi search
    fetchWishlists(false);
  }, 500);
};

const applyFilters = () => {
  filterParams.value.page = 1;
  fetchWishlists(false);
};

const resetFilters = () => {
  searchQuery.value = '';
  filterParams.value = { date: 'all', sort: 'latest', page: 1 };
  fetchWishlists(false);
};

const fetchWishlists = async (isAppend = false) => {
  if (isAppend) {
    isLoadingMore.value = true;
  } else {
    isLoading.value = true;
  }

  try {
    const params = {
      search: searchQuery.value,
      date: filterParams.value.date,
      sort: filterParams.value.sort,
      page: filterParams.value.page
    };

    const res = await api.get('/client/user/wishlist', { params });
    
    if (res.data.success) {
      if (isAppend) {
        wishlists.value = [...wishlists.value, ...res.data.data];
      } else {
        wishlists.value = res.data.data;
      }
      meta.value = res.data.meta;
    }
  } catch (error) {
    console.error('Lỗi lấy danh sách yêu thích:', error);
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const loadMore = () => {
  if (filterParams.value.page < meta.value.last_page) {
    filterParams.value.page++;
    fetchWishlists(true);
  }
};

// REACTIVITY: Lắng nghe Store. Nếu xóa 1 thẻ, tự động trừ đi ở trên màn hình
watch(() => wishlistStore.items, (newStoreItems) => {
    const beforeLength = wishlists.value.length;
    wishlists.value = wishlists.value.filter(p => newStoreItems.includes(p.id));
    const afterLength = wishlists.value.length;
    
    // Cập nhật lại số lượng (total) hiển thị trên header nếu có trừ đi
    if (beforeLength > afterLength) {
       meta.value.total -= (beforeLength - afterLength);
    }
}, { deep: true });

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

onMounted(() => { 
    window.scrollTo(0, 0); 
    fetchWishlists();
});
</script>

<style scoped>
.user-wishlist-wrapper { width: 100%; padding-top: 26px;}

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark, #213448) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }

.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.hover-btn-urban:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); }

.tracking-wide { letter-spacing: 1px; }

/* CSS Input xịn xò */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  padding: 0.65rem 1rem; 
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important; 
}
html.dark .custom-input { background-color: #1a2533; border-color: #373b3e; color: white; }
.custom-input:focus {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; 
}
html.dark .custom-input:focus { background-color: #212529; box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important; }

/* Wrapper Select Tùy chỉnh */
.custom-select-wrapper {
  height: calc(1.5em + 1.3rem + 3px); /* Bằng với input search */
}
.custom-select-wrapper:focus-within {
  border-color: var(--color-c-hover) !important;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2);
}
html.dark .custom-select-wrapper:focus-within {
  border-color: #495057 !important;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}
.shadow-sm-hover:hover { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }

/* Custom Dropdown Arrow */
select.form-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 16px 12px; padding-right: 2rem;
}
html.dark select.form-select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: scale(0.9); }
.list-leave-active { position: absolute; } /* Fix layout jump when removing */

/* SKELETON CSS */
.skeleton-card { width: 100%; }
.skeleton-img-wrapper { background-color: #f0f0f0; }
.skeleton-price { background-color: #f0f0f0; }
.skeleton-title { background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper, html.dark .skeleton-price, html.dark .skeleton-title { background-color: #2b3035; }

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

.transition-all { transition: all 0.3s ease; }
</style>