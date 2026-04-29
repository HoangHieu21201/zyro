<template>
  <!-- ĐÃ FIX: Điều chỉnh padding-top chuẩn bằng chiều cao của Header, xóa bỏ các class mt-5 pt-5 thừa thãi -->
  <div class="category-page-wrapper pb-5 mb-5" style="padding-top: 110px;">
    
    <div>
      <div class="zyro-container">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-dark dark:text-gray-300" aria-current="page">
               <span v-if="isFirstLoad" class="shimmer d-inline-block rounded" style="width: 100px; height: 14px;"></span>
               <span v-else>{{ categoryName }}</span>
            </li>
          </ol>
        </nav>

        <!-- SKELETON BANNER -->
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
             <h1 class="fw-bold text-dark dark:text-white m-0 font-sans-vn" style="font-size: 3.5rem; line-height: 1.1;">{{ categoryName }}</h1>
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

        <!-- BỘ SƯU TẬP NỔI BẬT -->
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

        <!-- THANH PHÂN LOẠI TRỰC QUAN -->
        <div v-else-if="subCategories.length > 0" class="sub-categories-wrapper d-flex gap-3 overflow-auto custom-scrollbar-x pb-3 mb-5">
          <router-link v-for="sub in subCategories" :key="sub.id" :to="`/category/${sub.slug}`" 
                       class="sub-category-card flex-shrink-0 position-relative rounded-4 overflow-hidden cursor-pointer group d-block border border-light-subtle dark:border-gray-700 bg-light dark:bg-[#212529]" style="width: 140px; height: 180px;">
            <img :src="sub.image || '/client_placeholder.png'" @error="e => e.target.src='/client_placeholder.png'" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale opacity-75">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
            <div class="position-absolute bottom-0 w-100 text-center pb-3 px-2 z-index-2">
              <span class="fw-bold text-dark bg-white bg-opacity-90 px-2 py-1 rounded small text-uppercase shadow-sm d-inline-block font-sans-vn">{{ sub.name }}</span>
            </div>
          </router-link>
        </div>

        <!-- ========================================== -->
        <!-- GIAO DIỆN CHÍNH: SIDEBAR LỌC & DANH SÁCH SP -->
        <!-- ========================================== -->
        <div class="row g-4 mt-2">
          
          <!-- CỘT TRÁI: THANH LỌC (SIDEBAR) CHỈ HIỆN TRÊN PC -->
          <div class="col-lg-3 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top" style="top: 8px;">
              
              <div class="d-flex justify-content-between align-items-center mb-4 border-bottom dark:border-gray-700 pb-3">
                <h6 class="fw-bold mb-0 text-uppercase tracking-widest font-sans-vn text-dark dark:text-white d-flex align-items-center">
                  <i class="bi bi-funnel-fill text-urban me-2 fs-5"></i> BỘ LỌC
                </h6>
                <button v-if="hasActiveFilters" @click="resetFilters" class="btn btn-link text-danger p-0 text-decoration-none fw-bold small transition-color hover-danger font-sans-vn d-flex align-items-center" :disabled="isLoading">
                  Xóa lọc <i class="bi bi-x-circle ms-1"></i>
                </button>
              </div>
              
              <!-- Bộ lọc Giá -->
              <div class="mb-4 pb-4 border-bottom dark:border-gray-700 font-sans-vn">
                <h6 class="fw-bold small text-uppercase mb-3 text-dark dark:text-gray-300 tracking-wide">KHOẢNG GIÁ</h6>
                <div class="d-flex flex-column gap-3">
                  <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                    <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilter" value="all" v-model="selectedPriceRange" @change="selectPrice(null, null)">
                    <span class="small fw-medium text-dark dark:text-gray-300">Tất cả mức giá</span>
                  </label>
                  <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                    <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilter" value="under_200k" v-model="selectedPriceRange" @change="selectPrice(null, 200000)">
                    <span class="small fw-medium text-dark dark:text-gray-300">Dưới 200.000đ</span>
                  </label>
                  <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                    <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilter" value="200k_500k" v-model="selectedPriceRange" @change="selectPrice(200000, 500000)">
                    <span class="small fw-medium text-dark dark:text-gray-300">200.000đ - 500.000đ</span>
                  </label>
                  <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                    <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilter" value="500k_1m" v-model="selectedPriceRange" @change="selectPrice(500000, 1000000)">
                    <span class="small fw-medium text-dark dark:text-gray-300">500.000đ - 1.000.000đ</span>
                  </label>
                  <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                    <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilter" value="over_1m" v-model="selectedPriceRange" @change="selectPrice(1000000, null)">
                    <span class="small fw-medium text-dark dark:text-gray-300">Trên 1.000.000đ</span>
                  </label>
                </div>
              </div>

              <!-- Bộ lọc Màu sắc -->
              <div class="mb-4 pb-4 border-bottom dark:border-gray-700 font-sans-vn" v-if="filterOptions.colors && filterOptions.colors.length > 0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold small text-uppercase m-0 text-dark dark:text-gray-300 tracking-wide">MÀU SẮC</h6>
                  <span v-if="filters.colors.length > 0" class="badge bg-danger rounded-circle p-1" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ filters.colors.length }}</span>
                </div>
                <!-- ĐÃ FIX: Thiết kế lại Swatch màu sắc đẹp và clean hơn -->
                <div class="d-flex flex-wrap gap-2">
                  <div v-for="color in filterOptions.colors" :key="color.name" 
                        class="color-filter-swatch-wrapper cursor-pointer position-relative transition-all d-flex align-items-center justify-content-center"
                        :class="{'active': filters.colors.includes(color.name)}"
                        @click="toggleColorFilter(color.name)"
                        :title="color.name">
                      <div class="color-filter-swatch rounded-circle shadow-sm" :style="{ backgroundColor: color.hex }"></div>
                      <i v-if="filters.colors.includes(color.name)" class="bi bi-check position-absolute fs-6 fw-bold" :class="getContrastYIQ(color.hex) === '#ffffff' ? 'text-white' : 'text-dark'" style="text-shadow: 0 0 2px rgba(255,255,255,0.3);"></i>
                  </div>
                </div>
              </div>

              <!-- Bộ lọc Kích cỡ -->
              <div class="mb-4 font-sans-vn" v-if="filterOptions.sizes && filterOptions.sizes.length > 0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold small text-uppercase m-0 text-dark dark:text-gray-300 tracking-wide">KÍCH CỠ</h6>
                  <span v-if="filters.sizes.length > 0" class="badge bg-danger rounded-circle p-1" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ filters.sizes.length }}</span>
                </div>
                <!-- ĐÃ FIX: Thiết kế lại ô Kích cỡ vuông vắn, clean -->
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" v-for="size in filterOptions.sizes" :key="size"
                          class="btn size-filter-btn transition-all font-sans-vn"
                          :class="filters.sizes.includes(size) ? 'active' : ''"
                          @click="toggleSizeFilter(size)">
                      {{ size }}
                  </button>
                </div>
              </div>

              <!-- Nút Lọc Dữ Liệu -->
              <button class="btn btn-urban w-100 rounded-pill fw-bold py-2.5 mt-2 font-sans-vn shadow-sm transition-all hover-transform text-white d-flex align-items-center justify-content-center" 
                      @click="applyFilters" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                {{ isLoading ? 'Đang lọc...' : 'ÁP DỤNG BỘ LỌC' }}
              </button>
            </div>
          </div>

          <!-- CỘT PHẢI: LƯỚI SẢN PHẨM -->
          <div class="col-lg-9">
            
            <!-- TOP BAR SẮP XẾP -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom dark:border-gray-700">
              
              <!-- Hiển thị trên PC -->
              <div class="d-none d-lg-flex align-items-center gap-3 font-sans-vn">
                <div class="text-muted small fw-medium">
                  Tìm thấy <span class="text-dark dark:text-white fw-bold fs-6 mx-1">{{ isFirstLoad ? '...' : totalProducts }}</span> sản phẩm
                </div>
              </div>

              <!-- Nút Lọc trên Mobile -->
              <div class="d-lg-none d-flex align-items-center">
                <button class="btn btn-light dark:bg-[#2b3035] dark:text-white border dark:border-gray-600 shadow-sm fw-bold font-sans-vn d-flex align-items-center py-2 px-3 rounded-pill position-relative" @click="isMobileFilterOpen = true">
                  <i class="bi bi-funnel-fill me-2 text-urban"></i> Bộ lọc
                  <span v-if="hasActiveFilters" class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light dark:border-gray-800 rounded-circle"></span>
                </button>
              </div>
              
              <!-- Select Sắp xếp chuẩn -->
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted small fw-bold d-none d-sm-inline font-sans-vn text-uppercase"><i class="bi bi-sort-down me-1"></i></span>
                <select class="form-select form-select-sm border-light-subtle dark:border-gray-600 bg-light dark:bg-[#212529] dark:text-white fw-bold font-sans-vn shadow-sm cursor-pointer py-2 px-3" 
                        v-model="currentSort" @change="applySort" style="min-width: 170px; border-radius: 8px;">
                  <option value="newest">Mới nhất</option>
                  <option value="best_sales">Bán chạy nhất</option>
                  <option value="price_asc">Giá: Thấp đến Cao</option>
                  <option value="price_desc">Giá: Cao đến Thấp</option>
                </select>
              </div>
            </div>

            <!-- SKELETON LƯỚI SẢN PHẨM -->
            <div v-if="isLoading" class="custom-grid-3 mb-5 pe-none">
              <div v-for="i in 6" :key="'skel'+i" class="skeleton-card w-100" aria-hidden="true">
                <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100" style="aspect-ratio: 3/4;"></div>
                <div class="product-info px-1 w-100">
                  <div class="skeleton-price shimmer mb-2" style="height: 22px; width: 45%; border-radius: 4px;"></div>
                  <div class="skeleton-title shimmer mb-3" style="height: 16px; width: 90%; border-radius: 4px;"></div>
                  <div class="d-flex gap-2">
                    <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                    <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- LƯỚI SẢN PHẨM CHÍNH -->
            <div v-else-if="mainProducts.length > 0" class="custom-grid-3 mb-5">
              <ProductCard v-for="product in mainProducts" :key="'main'+product.id" 
                           :product="product" @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
            </div>
            
            <!-- NẾU KHÔNG CÓ SẢN PHẨM -->
            <div v-else class="text-center py-5 my-5 text-muted bg-light dark:bg-[#1a2533] rounded-4 border border-dashed dark:border-gray-700">
               <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
               <h5 class="fw-bold font-sans-vn">Không tìm thấy sản phẩm nào</h5>
               <p class="small font-sans-vn">Vui lòng thay đổi tùy chọn bộ lọc hoặc tìm kiếm từ khóa khác.</p>
               <button class="btn btn-urban text-white rounded-pill mt-3 px-4 fw-bold shadow-sm" @click="resetFilters">Xóa bộ lọc ngay</button>
            </div>

            <!-- NÚT XEM THÊM -->
            <div class="text-center" v-if="!isLoading && currentPage < lastPage">
              <button @click="loadMore" class="btn btn-outline-dark dark:text-white dark:border-gray-500 rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all shadow-sm" :disabled="isLoadingMore">
                <span v-if="isLoadingMore" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Tải thêm sản phẩm
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- CÁC MODALS DÙNG CHUNG -->
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal />

    <!-- ========================================== -->
    <!-- MENU OFFCANVAS LỌC DÀNH CHO MOBILE         -->
    <!-- ========================================== -->
    <transition name="slide-left">
      <div v-if="isMobileFilterOpen" class="mobile-filter-panel bg-white dark:bg-[#1a2533] shadow-lg d-flex flex-column position-fixed top-0 start-0 z-index-1060 h-100" style="width: 85%; max-width: 340px;">
        
        <div class="p-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center bg-light dark:bg-[#212529]">
          <h5 class="fw-bold m-0 text-dark dark:text-white font-sans-vn"><i class="bi bi-funnel-fill text-urban me-2"></i>Bộ Lọc</h5>
          <button class="btn-close dark:filter-invert" @click="isMobileFilterOpen = false"></button>
        </div>
        
        <div class="flex-grow-1 overflow-auto p-4 custom-scrollbar-y">
           
           <!-- Lọc giá Mobile -->
           <div class="mb-4 pb-4 border-bottom dark:border-gray-700 font-sans-vn">
              <h6 class="fw-bold small text-uppercase mb-3 text-dark dark:text-gray-300 tracking-wide">Khoảng Giá</h6>
              <div class="d-flex flex-column gap-3">
                <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                  <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilterMob" value="all" v-model="selectedPriceRange" @change="selectPrice(null, null)">
                  <span class="small fw-medium text-dark dark:text-gray-300">Tất cả mức giá</span>
                </label>
                <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                  <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilterMob" value="under_200k" v-model="selectedPriceRange" @change="selectPrice(null, 200000)">
                  <span class="small fw-medium text-dark dark:text-gray-300">Dưới 200.000đ</span>
                </label>
                <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                  <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilterMob" value="200k_500k" v-model="selectedPriceRange" @change="selectPrice(200000, 500000)">
                  <span class="small fw-medium text-dark dark:text-gray-300">200.000đ - 500.000đ</span>
                </label>
                <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                  <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilterMob" value="500k_1m" v-model="selectedPriceRange" @change="selectPrice(500000, 1000000)">
                  <span class="small fw-medium text-dark dark:text-gray-300">500.000đ - 1.000.000đ</span>
                </label>
                <label class="form-check custom-radio-label d-flex align-items-center m-0 cursor-pointer">
                  <input class="form-check-input m-0 me-2 cursor-pointer" type="radio" name="priceFilterMob" value="over_1m" v-model="selectedPriceRange" @change="selectPrice(1000000, null)">
                  <span class="small fw-medium text-dark dark:text-gray-300">Trên 1.000.000đ</span>
                </label>
              </div>
           </div>

           <!-- Lọc màu Mobile -->
           <div class="mb-4 pb-4 border-bottom dark:border-gray-700 font-sans-vn" v-if="filterOptions.colors && filterOptions.colors.length > 0">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold small text-uppercase m-0 text-dark dark:text-gray-300 tracking-wide">Màu Sắc</h6>
                <span v-if="filters.colors.length > 0" class="badge bg-danger rounded-circle p-1" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ filters.colors.length }}</span>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <div v-for="color in filterOptions.colors" :key="'mob'+color.name" 
                      class="color-filter-swatch-wrapper cursor-pointer position-relative transition-all d-flex align-items-center justify-content-center"
                      :class="{'active': filters.colors.includes(color.name)}"
                      @click="toggleColorFilter(color.name)"
                      :title="color.name">
                    <div class="color-filter-swatch rounded-circle shadow-sm" :style="{ backgroundColor: color.hex }"></div>
                    <i v-if="filters.colors.includes(color.name)" class="bi bi-check position-absolute fs-6 fw-bold" :class="getContrastYIQ(color.hex) === '#ffffff' ? 'text-white' : 'text-dark'" style="text-shadow: 0 0 2px rgba(255,255,255,0.3);"></i>
                </div>
              </div>
           </div>

           <!-- Lọc Size Mobile -->
           <div class="mb-4 font-sans-vn" v-if="filterOptions.sizes && filterOptions.sizes.length > 0">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold small text-uppercase m-0 text-dark dark:text-gray-300 tracking-wide">Kích Cỡ</h6>
                <span v-if="filters.sizes.length > 0" class="badge bg-danger rounded-circle p-1" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ filters.sizes.length }}</span>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" v-for="size in filterOptions.sizes" :key="'mob'+size"
                        class="btn size-filter-btn transition-all font-sans-vn"
                        :class="filters.sizes.includes(size) ? 'active' : ''"
                        @click="toggleSizeFilter(size)">
                    {{ size }}
                </button>
              </div>
           </div>

        </div>
        
        <div class="p-3 border-top dark:border-gray-700 bg-white dark:bg-[#1a2533] d-flex gap-2 shadow-sm-top">
           <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 flex-grow-1 fw-bold font-sans-vn py-2.5 rounded-pill" @click="resetFilters">Xóa lọc</button>
           <button class="btn btn-urban text-white flex-grow-1 fw-bold font-sans-vn py-2.5 rounded-pill shadow-sm" @click="applyFiltersMobile" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span> Áp dụng
           </button>
        </div>
      </div>
    </transition>
    <transition name="fade">
      <div v-if="isMobileFilterOpen" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 z-index-1050" style="backdrop-filter: blur(3px);" @click="isMobileFilterOpen = false"></div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios';
import { useCompareStore } from '@/stores/compareStore';

import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const route = useRoute();
const router = useRouter();
const compareStore = useCompareStore();

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

const isMobileFilterOpen = ref(false);

// ==========================================
// STATE CHO BỘ LỌC
// ==========================================
const filterOptions = ref({ sizes: [], colors: [] });
const selectedPriceRange = ref('all'); 

const filters = ref({
  minPrice: null,
  maxPrice: null,
  colors: [],
  sizes: []
});

const hasActiveFilters = computed(() => {
  return filters.value.minPrice !== null || 
         filters.value.maxPrice !== null || 
         filters.value.colors.length > 0 || 
         filters.value.sizes.length > 0;
});

const selectPrice = (min, max) => {
  filters.value.minPrice = min;
  filters.value.maxPrice = max;
};

const toggleColorFilter = (colorName) => {
  const index = filters.value.colors.indexOf(colorName);
  if (index > -1) filters.value.colors.splice(index, 1);
  else filters.value.colors.push(colorName);
};

const toggleSizeFilter = (size) => {
  const index = filters.value.sizes.indexOf(size);
  if (index > -1) filters.value.sizes.splice(index, 1);
  else filters.value.sizes.push(size);
};

const applyFilters = () => {
  fetchCategoryData(1, false);
};

const applyFiltersMobile = () => {
  isMobileFilterOpen.value = false;
  fetchCategoryData(1, false);
};

const resetFilters = () => {
  filters.value = { minPrice: null, maxPrice: null, colors: [], sizes: [] };
  selectedPriceRange.value = 'all';
  isMobileFilterOpen.value = false;
  fetchCategoryData(1, false);
};

// ĐÃ THÊM: Tính toán màu sắc tương phản để đổ màu chữ cho dấu tick (Trắng/Đen)
const getContrastYIQ = (hexcolor) => {
  if (!hexcolor) return '#ffffff';
  hexcolor = hexcolor.replace("#", "");
  if (hexcolor.length === 3) hexcolor = hexcolor.split('').map(c => c + c).join('');
  const r = parseInt(hexcolor.substr(0,2),16);
  const g = parseInt(hexcolor.substr(2,2),16);
  const b = parseInt(hexcolor.substr(4,2),16);
  const yiq = ((r*299)+(g*587)+(b*114))/1000;
  return (yiq >= 128) ? '#111111' : '#ffffff';
};

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
    
    // Gắn tham số lọc vào request API
    if (filters.value.minPrice !== null) params.min_price = filters.value.minPrice;
    if (filters.value.maxPrice !== null) params.max_price = filters.value.maxPrice;
    if (filters.value.colors.length > 0) params.colors = filters.value.colors.join(',');
    if (filters.value.sizes.length > 0) params.sizes = filters.value.sizes.join(',');
    
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
    filterOptions.value = payload.available_filters || { sizes: [], colors: [] };
    
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

const applySort = () => {
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
    filters.value = { minPrice: null, maxPrice: null, colors: [], sizes: [] };
    selectedPriceRange.value = 'all';
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

.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.hover-danger:hover { color: #dc3545 !important; }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }
.text-shadow-lg { text-shadow: 2px 4px 10px rgba(0,0,0,0.6); }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }
.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; border-color: #212529 !important; }

.font-script { font-family: 'Georgia', serif; font-style: italic; }
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* LƯỚI SẢN PHẨM KHỚP BÊN PHẢI */
.custom-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
@media (max-width: 1199px) { .custom-grid-3 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 991px) { .custom-grid-3 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-3 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

/* KHỐI NỔI BẬT TRẢN FULL MÀN HÌNH */
.custom-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
@media (max-width: 991px) { .custom-grid-4 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.1); }
.group-hover-opacity-25 { opacity: 0; }
.group:hover .group-hover-opacity-25 { opacity: 0.2; }
.z-index-2 { z-index: 2; }
.z-index-1050 { z-index: 1050; }
.z-index-1060 { z-index: 1060; }
.shadow-sm-top { box-shadow: 0 -4px 15px rgba(0,0,0,0.03); }

/* ==========================================
   CSS CHO BỘ LỌC CHUẨN ZARA / UNIQLO
========================================== */
.custom-radio-label { transition: opacity 0.2s; }
.custom-radio-label:hover { opacity: 0.7; }
.custom-radio-label input[type="radio"] { accent-color: var(--color-c-dark, #213448); width: 1.1rem; height: 1.1rem; }

/* Swatch màu vòng tròn bao ngoài */
.color-filter-swatch-wrapper { 
  width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid transparent; 
}
.color-filter-swatch-wrapper.active { 
  border-color: var(--color-c-dark, #213448); padding: 2px; 
}
html.dark .color-filter-swatch-wrapper.active { border-color: #ffffff; }

.color-filter-swatch { 
  width: 100%; height: 100%; border: 1px solid #dee2e6; 
}
html.dark .color-filter-swatch { border-color: #495057; }

/* Button Size vuông vắn */
.size-filter-btn { 
  min-width: 45px; height: 36px; display: inline-flex; align-items: center; justify-content: center; 
  background-color: #fff; border: 1px solid #dee2e6; color: #495057; font-weight: 500; font-size: 0.85rem; border-radius: 6px; 
}
.size-filter-btn:hover { border-color: var(--color-c-hover, #547792); color: var(--color-c-hover, #547792); }
.size-filter-btn.active { border-color: var(--color-c-dark, #213448); color: var(--color-c-dark, #213448); font-weight: 700; border-width: 1.5px; }

html.dark .size-filter-btn { background-color: transparent; border-color: #495057; color: #adb5bd; }
html.dark .size-filter-btn:hover { border-color: #f8f9fa; color: #f8f9fa; }
html.dark .size-filter-btn.active { border-color: #fff; color: #fff; }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover { background: #adb5bd; }
.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

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
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }

.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; border-width: 2px !important; }
html.dark .border-dashed { border-color: #373b3e !important; }

/* OFFCANVAS ANIMATION */
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>