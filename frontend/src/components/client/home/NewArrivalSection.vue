<template>
  <section class="py-5 bg-white overflow-hidden">
    <div class="zyro-container py-3">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark">Hàng Mới Về</h3>
        <ul class="nav nav-underline gap-4 section-tabs">
          <li class="nav-item">
            <a class="nav-link fw-bold" :class="{ active: activeTab === 'all' }" href="#"
              @click.prevent="changeTab('all')">TẤT CẢ</a>
          </li>
          <li class="nav-item" v-for="tab in data.tabs" :key="tab.id">
            <a class="nav-link fw-bold" :class="{ active: activeTab === tab.id }" href="#"
              @click.prevent="changeTab(tab.id)">{{ tab.name }}</a>
          </li>
        </ul>
      </div>

      <div class="product-swiper-container pb-3 px-1 cursor-grab" ref="swiperRef" @mousedown="startDrag"
        @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
        <!-- Loader của toàn bộ trang (Load lần đầu) -->
        <div v-if="isLoading" class="text-center py-5 w-100"><span class="spinner-border text-muted"></span></div>

        <!-- HIỆU ỨNG SKELETON ĐÃ ĐƯỢC CHUẨN HÓA 100% THEO PRODUCT CARD -->
        <template v-else-if="isTabLoading">
          <div class="product-swiper-slide" v-for="i in 5" :key="'skeleton-' + i">
            <div class="skeleton-card w-100" aria-hidden="true">
              <!-- Khung ảnh đúng tỷ lệ 3:4, margin bottom 3 -->
              <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100"></div>

              <!-- Khung thông tin giống hệt padding px-1 của thẻ thật -->
              <div class="product-info px-1 w-100">
                <!-- Mô phỏng dòng Giá (To hơn, ngắn hơn) -->
                <div class="skeleton-price shimmer mb-2"></div>
                <!-- Mô phỏng Tên sản phẩm (Nhỏ hơn, dài hơn) -->
                <div class="skeleton-title shimmer mb-3"></div>
                <!-- Mô phỏng 3 nút chọn màu sắc (Tròn) -->
                <div class="d-flex gap-2">
                  <div class="skeleton-swatch shimmer rounded-circle"></div>
                  <div class="skeleton-swatch shimmer rounded-circle"></div>
                  <div class="skeleton-swatch shimmer rounded-circle"></div>
                </div>
              </div>
            </div>
          </div>
          <span class="visually-hidden" role="status">Đang tải sản phẩm...</span>
        </template>

        <div v-else-if="displayProducts.length === 0" class="text-center py-5 w-100 text-muted">Chưa có sản phẩm.</div>

        <!-- Render danh sách displayProducts -->
        <div v-else class="product-swiper-slide" v-for="product in displayProducts" :key="'new' + product.id">
          <ProductCard class="h-100" :product="product" @quick-view="$emit('quick-view', $event)"
            @compare="$emit('compare', $event)" @wishlist="$emit('wishlist', $event)"
            @options="$emit('options', $event)" />
        </div>
      </div>

      <div class="text-center mt-4">
        <router-link to="/category"
          class="btn btn-outline-dark rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all text-decoration-none">
          Xem Thêm
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, watch } from 'vue';
import ProductCard from '@/components/client/ProductCard.vue';

import axios from 'axios';

const props = defineProps({ data: Object, isLoading: Boolean });
defineEmits(['quick-view', 'compare', 'wishlist', 'options']);

const activeTab = ref('all');
const displayProducts = ref([]);
const isTabLoading = ref(false);

watch(() => props.data?.products, (newProducts) => {
  if (newProducts && activeTab.value === 'all') {
    displayProducts.value = newProducts;
  }
}, { immediate: true });

const changeTab = async (tabId) => {
  if (activeTab.value === tabId) return;

  activeTab.value = tabId;

  if (tabId === 'all') {
    displayProducts.value = props.data.products;
    return;
  }

  isTabLoading.value = true;
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/client/home/new-arrivals-tab?category_id=${tabId}`);

    if (response.data.success) {
      displayProducts.value = response.data.data;

      if (swiperRef.value) {
        swiperRef.value.scrollLeft = 0;
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu tab:', error);
    displayProducts.value = [];
  } finally {
    isTabLoading.value = false;
  }
};

const swiperRef = ref(null);
let isDown = false; let startX; let scrollLeft;
const startDrag = (e) => { isDown = true; swiperRef.value.classList.add('active-drag'); startX = e.pageX - swiperRef.value.offsetLeft; scrollLeft = swiperRef.value.scrollLeft; };
const endDrag = () => { isDown = false; if (swiperRef.value) swiperRef.value.classList.remove('active-drag'); };
const doDrag = (e) => { if (!isDown) return; e.preventDefault(); const x = e.pageX - swiperRef.value.offsetLeft; const walk = (x - startX) * 1.5; swiperRef.value.scrollLeft = scrollLeft - walk; };
</script>

<style scoped>
.section-tabs .nav-link {
  color: #6c757d;
  border-bottom: 2px solid transparent;
  padding-bottom: 8px;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.section-tabs .nav-link:hover,
.section-tabs .nav-link.active {
  color: #212529 !important;
  border-bottom-color: #212529 !important;
}

.cursor-grab {
  cursor: grab;
}

.cursor-grab:active {
  cursor: grabbing;
}

.product-swiper-container {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.product-swiper-container.active-drag {
  scroll-snap-type: none;
  scroll-behavior: auto;
}

.product-swiper-slide {
  width: calc((100% - 80px) / 5);
  min-width: 220px;
  flex-shrink: 0;
  scroll-snap-align: start;
}

.product-swiper-container::-webkit-scrollbar {
  height: 0px;
  display: none;
}

.hover-bg-dark:hover {
  background-color: #212529 !important;
  color: #fff !important;
}

.tracking-wide {
  letter-spacing: 1px;
}

.tracking-widest {
  letter-spacing: 2px;
}

/* ======================================================
   CSS HIỆU ỨNG SKELETON (ĐỒNG BỘ TỶ LỆ PRODUCT CARD)
====================================================== */
.skeleton-card {
  width: 100%;
}

/* Khung ảnh dùng aspect-ratio thay vì height cố định */
.skeleton-img-wrapper {
  aspect-ratio: 3 / 4;
  background-color: #f0f0f0;
}

/* Khung giá: To và ngắn */
.skeleton-price {
  height: 22px;
  width: 45%;
  border-radius: 4px;
  background-color: #f0f0f0;
}

/* Khung tên SP: Nhỏ và dài */
.skeleton-title {
  height: 16px;
  width: 90%;
  border-radius: 4px;
  background-color: #f0f0f0;
}

/* Khung nút màu sắc: Hình tròn giống swatch-item */
.skeleton-swatch {
  width: 18px;
  height: 18px;
  background-color: #f0f0f0;
}

/* Hiệu ứng lấp lánh (Shimmer) mượt mà */
.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}

@keyframes placeholderShimmer {
  0% {
    background-position: -400px 0;
  }

  100% {
    background-position: 400px 0;
  }
}
</style>