<!-- File: frontend/src/pages/client/category/Index.vue -->
<template>
  <div class="category-page-wrapper pb-5 mb-5">
    
    <!-- ĐẨY NỘI DUNG XUỐNG DƯỚI TRÁNH BỊ HEADER CHE KHUẤT -->
    <div class="pt-5 mt-5">
      <!-- SỬ DỤNG CLASS GLOBAL ĐỂ ĐỒNG BỘ 170PX MARGIN -->
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">{{ categoryName }}</li>
          </ol>
        </nav>

        <!-- BANNER DANH MỤC TRONG KHUNG (ĐÃ FIX GIỐNG MẪU: TEXT BÊN TRÁI, BANNER BÊN PHẢI) -->
        <div class="row align-items-center mb-5 pb-2">
          <div class="col-lg-3 d-none d-lg-block">
             <h1 class="fw-bold text-dark dark:text-white m-0" style="font-size: 3.5rem;">{{ categoryName }}</h1>
          </div>
          <div class="col-lg-9 col-12">
             <div class="category-banner rounded-4 overflow-hidden position-relative shadow-sm" style="height: 350px;">
               <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop" class="w-100 h-100 object-fit-cover">
               <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                 <h1 class="display-3 fw-bold text-white text-shadow-lg font-script" style="letter-spacing: 2px;">The Mood Diary</h1>
               </div>
             </div>
          </div>
        </div>

        <!-- BỘ SƯU TẬP NỔI BẬT (KHUNG ĐỎ CHUẨN DESIGN WOLF CALIE) -->
        <div class="highlight-collection border border-danger border-opacity-50 rounded-4 p-4 position-relative mb-5 bg-white">
          <span class="position-absolute top-0 start-50 translate-middle bg-white px-3 text-danger fw-bold small text-uppercase tracking-widest border border-danger rounded-pill shadow-sm">
            ZYRO SPORT NHẸ TÊNH
          </span>
          <div class="custom-grid-4 mt-3">
            <ProductCard v-for="product in highlightProducts" :key="'hi'+product.id" 
                         :product="product" @quick-view="handleOpenQuickView" @compare="handleAddToCompare" />
          </div>
        </div>

        <!-- THANH PHÂN LOẠI TRỰC QUAN (SUB-CATEGORIES) -->
        <div class="sub-categories-wrapper d-flex gap-3 overflow-auto custom-scrollbar-x pb-3 mb-5">
          <div v-for="sub in subCategories" :key="sub.id" class="sub-category-card flex-shrink-0 position-relative rounded-4 overflow-hidden cursor-pointer group" style="width: 140px; height: 180px;">
            <img :src="sub.image" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale opacity-75">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
            <div class="position-absolute bottom-0 w-100 text-center pb-3 px-2 z-index-2">
              <span class="fw-bold text-dark bg-white bg-opacity-75 px-2 py-1 rounded small text-uppercase shadow-sm d-inline-block">{{ sub.name }}</span>
            </div>
          </div>
        </div>

        <!-- THANH BỘ LỌC (FILTER BAR) -->
        <div class="filter-bar d-flex flex-wrap align-items-center gap-3 mb-4 pb-3 border-bottom dark:border-gray-700">
          <button class="btn btn-light bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center">
            Bộ lọc <span class="badge bg-danger rounded-circle ms-2" style="font-size: 0.65rem;">0</span>
          </button>
          
          <div class="dropdown">
            <button class="btn btn-light bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center" type="button" data-bs-toggle="dropdown">
              Sắp xếp <i class="bi bi-chevron-down ms-2" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu shadow-sm border-0 rounded-3">
              <li><a class="dropdown-item small py-2" href="#">Mới nhất</a></li>
              <li><a class="dropdown-item small py-2" href="#">Bán chạy</a></li>
              <li><a class="dropdown-item small py-2" href="#">Giá: Thấp đến Cao</a></li>
              <li><a class="dropdown-item small py-2" href="#">Giá: Cao đến Thấp</a></li>
            </ul>
          </div>

          <div class="dropdown">
            <button class="btn btn-light bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center" type="button" data-bs-toggle="dropdown">
              Hạng <i class="bi bi-chevron-down ms-2" style="font-size: 0.75rem;"></i>
            </button>
          </div>

          <div class="dropdown">
            <button class="btn btn-light bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center" type="button" data-bs-toggle="dropdown">
              Mức giá <i class="bi bi-chevron-down ms-2" style="font-size: 0.75rem;"></i>
            </button>
          </div>

          <div class="dropdown">
            <button class="btn btn-light bg-c-effect border-0 rounded-pill px-4 py-2 fw-semibold small text-muted hover-bg-dark transition-all d-flex align-items-center" type="button" data-bs-toggle="dropdown">
              Loại sản phẩm <i class="bi bi-chevron-down ms-2" style="font-size: 0.75rem;"></i>
            </button>
          </div>
        </div>

        <!-- LƯỚI SẢN PHẨM CHÍNH (4 CỘT) -->
        <div class="custom-grid-4 mb-5">
          <ProductCard v-for="product in mainProducts" :key="'main'+product.id" 
                       :product="product" @quick-view="handleOpenQuickView" @compare="handleAddToCompare" />
        </div>

        <!-- NÚT XEM THÊM -->
        <div class="text-center">
          <button class="btn btn-outline-dark rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all">
            Tải thêm sản phẩm
          </button>
        </div>

      </div>
    </div>

    <!-- CÁC MODALS DÙNG CHUNG -->
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal :compare-list="compareList" @remove="removeFromCompare" @clear="clearCompare" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';

import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const categoryName = ref('NỮ');

// --- LOGIC QUICK VIEW ---
const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

// --- LOGIC COMPARE ---
const compareList = ref([]);
const handleAddToCompare = (product) => {
  if (compareList.value.find(p => p.id === product.id)) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Sản phẩm đã có trong danh sách', showConfirmButton: false, timer: 2000 });
    return;
  }
  if (compareList.value.length >= 10) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Tối đa 10 sản phẩm', showConfirmButton: false, timer: 2000 });
    return;
  }
  compareList.value.push(product);
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm vào bảng so sánh', showConfirmButton: false, timer: 1500 });
};
const removeFromCompare = (index) => { compareList.value.splice(index, 1); };
const clearCompare = () => { compareList.value = []; };

// ========================================================
// MOCK DATA 
// ========================================================
const subCategories = ref([
  { id: 1, name: 'ÁO KHOÁC', image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=300&auto=format&fit=crop' },
  { id: 2, name: 'ÁO', image: 'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=300&auto=format&fit=crop' },
  { id: 3, name: 'QUẦN', image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=300&auto=format&fit=crop' },
  { id: 4, name: 'ĐỒ THỂ THAO', image: 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=300&auto=format&fit=crop' },
  { id: 5, name: 'MẶC TRONG & LÓT', image: 'https://images.unsplash.com/photo-1618354691438-25bc04584c23?q=80&w=300&auto=format&fit=crop' },
  { id: 6, name: 'SẢN PHẨM KHÁC', image: 'https://images.unsplash.com/photo-1560506840-0ca20786fb8a?q=80&w=300&auto=format&fit=crop' },
]);

const highlightProducts = ref([
  { id: 301, name: 'Áo Polo Thể Thao Nữ Năng Động', price: 329000, old_price: null, image: 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=600&auto=format&fit=crop', colors: [{hex: '#fab1a0'}, {hex: '#c8d6e5'}] },
  { id: 302, name: 'Sơ Mi Nữ Dài Tay Dấu Nẹp', price: 319000, old_price: 449000, discount_percent: 29, image: 'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=600&auto=format&fit=crop' },
  { id: 303, name: 'Áo Phông Nữ Slimfit Coton', price: 99000, old_price: 149000, discount_percent: 34, image: 'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=600&auto=format&fit=crop', colors: [{hex: '#ffffff'}, {hex: '#000000'}] },
  { id: 305, name: 'Áo Phông Nữ In Creative', price: 299000, old_price: 599000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=600&auto=format&fit=crop' },
]);

const mainProducts = ref([
  { id: 401, name: 'Áo Khoác Gió Nữ Thể Thao', price: 450000, old_price: 650000, discount_percent: 30, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#8ba6b4'}, {hex: '#000000'}] },
  { id: 402, name: 'Chân Váy Xếp Ly Cao Cấp', price: 350000, old_price: 500000, discount_percent: 30, image: 'https://images.unsplash.com/photo-1583496661160-c588c4af5d4a?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#000000'}, {hex: '#ffffff'}] },
  { id: 403, name: 'Quần Jeans Ống Suông Nữ', price: 420000, old_price: null, image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#2980b9'}] },
  { id: 404, name: 'Áo Thun Typography Nữ', price: 199000, old_price: 398000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#f5f6fa'}, {hex: '#2c3e50'}] },
  { id: 405, name: 'Đồ Tập Yoga Nữ Thoáng Khí', price: 280000, old_price: 350000, discount_percent: 20, image: 'https://images.unsplash.com/photo-1618354691438-25bc04584c23?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#8e44ad'}, {hex: '#000000'}] },
  { id: 406, name: 'Sơ Mi Lụa Cộc Tay Mùa Hè', price: 350000, old_price: null, image: 'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#f1c40f'}, {hex: '#ffffff'}] },
  { id: 407, name: 'Quần Short Kaki Nữ Năng Động', price: 210000, old_price: 250000, discount_percent: 16, image: 'https://images.unsplash.com/photo-1594882645126-14020914d58d?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#e67e22'}, {hex: '#ecf0f1'}] },
  { id: 408, name: 'Áo Khoác Len Mỏng Cài Cúc', price: 390000, old_price: 490000, discount_percent: 20, image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=400&auto=format&fit=crop', colors: [{hex: '#c8d6e5'}, {hex: '#fab1a0'}] },
]);

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.category-page-wrapper { width: 100%; }

/* TIỆN ÍCH CHỮ & MÀU */
.hover-text-dark:hover { color: #000 !important; }
.text-shadow-lg { text-shadow: 2px 4px 10px rgba(0,0,0,0.6); }
.tracking-widest { letter-spacing: 2px; }
.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; }

/* FONT CHỮ KIỂU CÁCH CHO BANNER */
.font-script { font-family: 'Georgia', serif; font-style: italic; }

/* CSS GRID CHO SẢN PHẨM (4 CỘT TRÊN PC) */
.custom-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
@media (max-width: 991px) { .custom-grid-4 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

/* HIỆU ỨNG CARD SUB-CATEGORY */
.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.1); }
.group-hover-opacity-25 { opacity: 0; }
.group:hover .group-hover-opacity-25 { opacity: 0.2; }
.z-index-2 { z-index: 2; }

/* THANH CUỘN NGANG */
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

.transition-all { transition: all 0.3s ease; }
</style>