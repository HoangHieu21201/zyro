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
        
        <div class="search-content custom-scrollbar-y px-4 px-lg-5 pt-4 pb-2 flex-grow-1">
          
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

          <!-- LỊCH SỬ TÌM KIẾM -->
          <div class="mb-5 mx-auto" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted dark:text-gray-400 small fw-semibold">Tìm kiếm gần đây</span>
              <button class="btn btn-link text-danger p-0 text-decoration-none small" style="font-size: 0.8rem;" @click="clearHistory">Xoá</button>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <span v-for="kw in recentSearches" :key="kw" 
                    class="badge bg-light text-dark dark:bg-[#212529] dark:text-gray-300 border dark:border-gray-700 rounded-pill px-3 py-2 fw-normal cursor-pointer hover-bg-effect transition-all"
                    @click="searchQuery = kw; submitSearch()">
                {{ kw }}
              </span>
            </div>
          </div>

          <!-- SẢN PHẨM XU HƯỚNG -->
          <div class="mb-4 mx-auto" style="max-width: 1200px;">
            <h6 class="text-center text-muted dark:text-gray-400 fw-semibold mb-4">Sản phẩm xu hướng</h6>
            
            <div class="trending-products-grid custom-scrollbar-x pb-3">
              <div v-for="product in trendingProducts" :key="product.id" class="product-item flex-shrink-0">
                <ProductCard 
                  :product="product" 
                  @quick-view="handleOpenQuickView" 
                  @compare="handleAddToCompare" 
                />
              </div>
            </div>
          </div>

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

    <!-- NHÚNG COMPONENT SO SÁNH -->
    <CompareModal 
      :compare-list="compareList"
      @remove="removeFromCompare"
      @clear="clearCompare"
    />
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import Swal from 'sweetalert2'; 

import ProductCard from './ProductCard.vue';
import QuickViewModal from './QuickViewModal.vue';
import CompareModal from './CompareModal.vue'; 

const props = defineProps({
  isOpen: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const searchInput = ref(null);
const searchQuery = ref('');

// Trạng thái Quick View
const isQuickViewOpen = ref(false);
const selectedProductData = ref({});

const handleOpenQuickView = (product) => {
  selectedProductData.value = product;
  isQuickViewOpen.value = true;
};

// ==========================================
// STATE & LOGIC CHO CHỨC NĂNG SO SÁNH
// ==========================================
const compareList = ref([]);

const handleAddToCompare = (product) => {
  // Kiểm tra trùng lặp
  if (compareList.value.find(p => p.id === product.id)) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Sản phẩm đã có trong danh sách', showConfirmButton: false, timer: 2000 });
    return;
  }
  
  // ĐÃ MỞ KHÓA GIỚI HẠN LÊN 10 (Theo yêu cầu vuốt Swiper linh hoạt)
  if (compareList.value.length >= 10) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Chỉ được so sánh tối đa 10 sản phẩm', showConfirmButton: false, timer: 2000 });
    return;
  }
  
  compareList.value.push(product);
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm vào bảng so sánh', showConfirmButton: false, timer: 1500 });
};

const removeFromCompare = (index) => {
  compareList.value.splice(index, 1);
};

const clearCompare = () => {
  compareList.value = [];
};
// ==========================================

// Mock Lịch sử tìm kiếm
const recentSearches = ref(['Áo thun nam', 'Quần Jeans', 'Áo Polo', 'Váy hoa']);

// Mock Dữ liệu sản phẩm xu hướng
const trendingProducts = ref([
  { id: 1, name: 'Sơ Mi Tay Dài Nam Siêu Co Giãn', price: 549000, old_price: null, image: 'https://images.unsplash.com/photo-1596755094514-f87e32f85e23?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#8ba6b4'}, {hex: '#000000'}, {hex: '#4b0082'}] },
  { id: 2, name: 'Sơ Mi Nam Cộc Tay Cafe Túi Ngực', price: 469000, old_price: null, image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#8ba6b4'}, {hex: '#ffffff'}, {hex: '#00a8ff'}] },
  { id: 3, name: 'Áo Thun Nam Basic Slimfit Cotton', price: 149000, old_price: 299000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#bdc3c7'}, {hex: '#00a8ff'}, {hex: '#000000'}] },
  { id: 4, name: 'Sơ Mi Nam Cộc Tay Nano Kẻ Caro', price: 469000, old_price: null, image: 'https://images.unsplash.com/photo-1588359348347-9bc6cbb6858a?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#dfe6e9'}] },
  { id: 5, name: 'Áo Phông Tay Raglan Dài Chỉ Ngang', price: 249000, old_price: 539000, discount_percent: 54, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#34495e'}, {hex: '#bdc3c7'}] },
]);

const closeModal = () => {
  emit('close');
};

const clearHistory = () => {
  recentSearches.value = [];
};

const submitSearch = () => {
  if (searchQuery.value.trim() !== '') {
    console.log('Searching for:', searchQuery.value);
    closeModal();
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

.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  border-color: var(--color-c-hover, #547792);
  box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.15);
}

.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; }

/* Lưới Sản phẩm Xu hướng (Vuốt ngang) */
.trending-products-grid {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 10px;
}
.product-item {
  width: calc(20% - 1.2rem); 
  min-width: 220px; 
  scroll-snap-align: start;
}

.shadow-sm-top { box-shadow: 0 -4px 10px rgba(0,0,0,0.03); }
.tracking-widest { letter-spacing: 2px; }

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
</style>