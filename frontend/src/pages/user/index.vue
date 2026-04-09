<!-- File: frontend/src/pages/user/Index.vue -->
<template>
  <div class="home-page-wrapper">

    <!-- =======================================================
         1. HERO BANNER SECTION (FLUID - TRÀN VIỀN 100%)
    ======================================================== -->
    <section class="hero-section position-relative d-flex align-items-center justify-content-center text-center">
      <div class="hero-bg position-absolute top-0 start-0 w-100 h-100"
        style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop');">
      </div>
      <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>

      <div class="hero-content position-relative z-1 text-white px-4 animation-fade-up">
        <span class="d-block mb-3 fw-medium tracking-widest text-uppercase small">Bộ Sưu Tập Mới Nhất</span>
        <h1 class="display-3 fw-bold mb-4 text-uppercase" style="letter-spacing: 4px;">Summer<br>Vibes 2026</h1>
        <router-link to="/category"
          class="btn btn-light rounded-pill px-5 py-3 fw-bold text-uppercase mt-2 hover-scale transition-all"
          style="letter-spacing: 1px;">
          Khám Phá Ngay
        </router-link>
      </div>
    </section>

    <!-- =======================================================
         2. FLASH SALE SECTION (BOXED)
    ======================================================== -->
    <section class="flash-sale-section py-5" style="background-color: #ffb482;">
      <div class="zyro-container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
          <div class="d-flex align-items-center gap-4">
            <div class="flash-sale-title text-danger fw-bold fst-italic lh-1"
              style="font-size: 2.2rem; text-shadow: 2px 2px 0px #fff;">
              <span class="text-white text-shadow-none d-block fs-6 fst-normal mb-1">Duy Nhất</span>
              FLASH SALE
            </div>
            <div class="countdown-box bg-danger text-white rounded-3 d-flex gap-3 px-4 py-2 shadow">
              <div class="text-center"><span class="fs-4 fw-bold">02</span><small class="d-block"
                  style="font-size: 0.65rem;">ngày</small></div>
              <div class="text-center"><span class="fs-4 fw-bold">12</span><small class="d-block"
                  style="font-size: 0.65rem;">giờ</small></div>
              <div class="text-center"><span class="fs-4 fw-bold">20</span><small class="d-block"
                  style="font-size: 0.65rem;">phút</small></div>
              <div class="text-center"><span class="fs-4 fw-bold">02</span><small class="d-block"
                  style="font-size: 0.65rem;">giây</small></div>
            </div>
          </div>
          <router-link to="/category"
            class="btn bg-white rounded-pill px-4 py-2 text-danger fw-bold shadow-sm hover-scale transition-all">
            Xem tất cả <i class="bi bi-chevron-right small"></i>
          </router-link>
        </div>

        <div class="zyro-product-grid">
          <ProductCard class="h-100" v-for="product in flashSaleProducts" :key="'fs' + product.id" :product="product"
            @quick-view="handleOpenQuickView" @compare="handleAddToCompare" @wishlist="handleAddToWishlist"
            @options="handleGoToDetail" />
        </div>
      </div>
    </section>

    <!-- =======================================================
         3. HÀNG MỚI VỀ (NATIVE SWIPER CÓ TABS)
    ======================================================== -->
    <section class="py-5 bg-white overflow-hidden">
      <div class="zyro-container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
          <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark">Hàng Mới Về</h3>
          <ul class="nav nav-underline gap-4 section-tabs">
            <li class="nav-item"><a class="nav-link fw-bold active" href="#" @click.prevent>NAM</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>NỮ</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>TRẺ EM</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>GIÀY DÉP</a></li>
          </ul>
        </div>

        <div class="product-swiper-container pb-3 px-1 cursor-grab" ref="swiperRef" @mousedown="startDrag"
          @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
          <div class="product-swiper-slide" v-for="product in latestProducts" :key="'new' + product.id">
            <ProductCard class="h-100" :product="product" @quick-view="handleOpenQuickView"
              @compare="handleAddToCompare" @wishlist="handleAddToWishlist" @options="handleGoToDetail" />
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

    <!-- =======================================================
         4. ĐƯỢC YÊU THÍCH NHẤT (GRID 5 CỘT)
    ======================================================== -->
    <section class="py-5 bg-light dark:bg-[#121416]">
      <div class="zyro-container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
          <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark dark:text-white">Được Yêu Thích Nhất</h3>
          <ul class="nav nav-underline gap-4 section-tabs">
            <li class="nav-item"><a class="nav-link fw-bold active" href="#" @click.prevent>HOT TREND</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>BEST SELLER</a></li>
          </ul>
        </div>

        <div class="zyro-product-grid">
          <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
            <img src="../../assets/images/logo/logozyro.png"
              class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform group-hover-zoom">
            <div
              class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25 group-hover-opacity-50 transition-all">
            </div>
            <div class="position-absolute bottom-0 start-0 p-4 w-100 z-index-2">
              <h2 class="text-white fw-bold mb-2 text-shadow-lg text-uppercase tracking-widest">Độ Dáng<br>Ngày Hè</h2>
              <p class="text-white mb-3 text-shadow fw-medium">Tự tin tỏa sáng mọi góc nhìn!</p>
              <router-link to="/category" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">Khám phá ngay <i
                  class="bi bi-arrow-right"></i></router-link>
            </div>
          </div>

          <ProductCard class="h-100" v-for="product in mostLovedProducts" :key="'loved' + product.id" :product="product"
            @quick-view="handleOpenQuickView" @compare="handleAddToCompare" @wishlist="handleAddToWishlist"
            @options="handleGoToDetail" />
        </div>
      </div>
    </section>

    <!-- =======================================================
         5. BỘ SƯU TẬP (BANNER + SWIPER ĐỒNG BỘ)
    ======================================================== -->
    <section class="py-5 bg-white">
      <div class="zyro-container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
          <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark">Bộ Sưu Tập</h3>
          <ul class="nav nav-underline gap-4 section-tabs">
            <li class="nav-item"><a class="nav-link fw-bold active" href="#" @click.prevent>POLO COOL 2025</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>PEACEFUL</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>SPORT NHẸ TÊNH</a></li>
          </ul>
        </div>

        <div class="zyro-product-grid">
          
          <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
            <img src="https://images.unsplash.com/photo-1618354691438-25bc04584c23?q=80&w=600&auto=format&fit=crop"
              class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform group-hover-zoom">
            <div class="position-absolute top-0 end-0 p-3 z-index-2">
              <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Beverly_Hills_Polo_Club_logo.svg"
                style="height: 50px; filter: brightness(0) invert(1);" class="opacity-75">
            </div>
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all"></div>
            <div class="position-absolute bottom-0 start-0 p-3 w-100 text-center z-index-2">
              <h4 class="text-white fw-bold mb-0 text-shadow-lg text-uppercase tracking-widest" style="letter-spacing: 5px;">B H P C</h4>
            </div>
          </div>

          <div class="collection-swiper-wrapper position-relative h-100">
            
            <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border text-urban rounded-circle shadow-sm position-absolute top-50 start-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all hover-btn-urban" 
                    style="width: 40px; height: 40px; margin-left: -15px;" @click="scrollCollection('left')">
              <i class="bi bi-chevron-left fw-bold"></i>
            </button>

            <div class="product-swiper-container h-100 pb-3 px-1 cursor-grab" ref="collectionSwiperRef" @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
              <div class="product-swiper-slide-3 h-100" v-for="product in collectionProducts" :key="'col' + product.id">
                <ProductCard class="h-100" :product="product"
                  @quick-view="handleOpenQuickView" @compare="handleAddToCompare" @wishlist="handleAddToWishlist"
                  @options="handleGoToDetail" />
              </div>
            </div>

            <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 border text-urban rounded-circle shadow-sm position-absolute top-50 end-0 translate-middle-y z-index-2 d-none d-md-flex align-items-center justify-content-center transition-all hover-btn-urban" 
                    style="width: 40px; height: 40px; margin-right: -15px;" @click="scrollCollection('right')">
              <i class="bi bi-chevron-right fw-bold"></i>
            </button>

          </div>
        </div>

        <div class="text-center mt-5">
          <router-link to="/category"
            class="btn btn-outline-dark rounded-pill px-5 py-2.5 fw-bold text-uppercase tracking-widest hover-bg-dark transition-all text-decoration-none">
            Xem Thêm BST
          </router-link>
        </div>
      </div>
    </section>

    <!-- =======================================================
         6. DÀNH CHO BÉ
    ======================================================== -->
    <section class="py-5 bg-light dark:bg-[#121416]">
      <div class="zyro-container py-3 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
          <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark dark:text-white">Dành Cho Bé</h3>
          <ul class="nav nav-underline gap-4 section-tabs">
            <li class="nav-item"><a class="nav-link fw-bold active" href="#" @click.prevent>ÁO KHOÁC</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>ÁO</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>QUẦN</a></li>
            <li class="nav-item"><a class="nav-link fw-bold" href="#" @click.prevent>ĐỒ MẶC TRONG</a></li>
          </ul>
        </div>

        <div class="zyro-product-grid">
          <div class="banner-span-2 position-relative rounded-4 overflow-hidden group cursor-pointer shadow-sm w-100 h-100">
            <img src="https://images.unsplash.com/photo-1519278409-1f56fdda70db?q=80&w=600&auto=format&fit=crop"
              class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform group-hover-zoom">
            <div
              class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-10 group-hover-opacity-25 transition-all">
            </div>
            <div class="position-absolute top-0 end-0 p-4 w-100 text-end z-index-2">
              <h2 class="text-white fw-bold mb-0 text-shadow-lg text-uppercase tracking-widest">KIDS<br>COLLECTION</h2>
            </div>
          </div>

          <ProductCard class="h-100" v-for="product in kidsProducts" :key="'kid' + product.id" :product="product"
            @quick-view="handleOpenQuickView" @compare="handleAddToCompare" @wishlist="handleAddToWishlist"
            @options="handleGoToDetail" />
        </div>
      </div>
    </section>

    <!-- =======================================================
         7. THƯ VIỆN LOOKBOOK (INFINITE SCROLL MƯỢT MÀ)
    ======================================================== -->
    <section class="pt-5 pb-0 bg-white dark:bg-[#1a2533] overflow-hidden border-top dark:border-gray-700">
      <div class="zyro-container py-3">
        <div class="text-center mb-5">
          <h3 class="fw-bold text-uppercase mb-2 tracking-widest text-dark dark:text-white" style="letter-spacing: 3px;">ZYRO Lookbook</h3>
          <p class="text-muted small mb-0">Cảm hứng thời trang không giới hạn. Lướt để khám phá phong cách của bạn.</p>
        </div>
      </div>

      <!-- ĐÃ BỔ SUNG SỰ KIỆN LÀM CHẬM HIỆU ỨNG THAY VÌ DỪNG HẲN -->
      <div class="lookbook-marquee-wrapper pb-5">
        <div class="lookbook-marquee d-flex" @mouseenter="slowDownMarquee" @mouseleave="restoreMarqueeSpeed">
          
          <!-- Lặp lại Mảng ảnh 1 -->
          <div class="marquee-group d-flex align-items-center">
            <div class="marquee-item shadow-sm" v-for="(img, idx) in lookbookImages" :key="'lb1'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
              <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img">
            </div>
          </div>

          <!-- Lặp lại Mảng ảnh 2 (Để vòng lặp không bị khựng) -->
          <div class="marquee-group d-flex align-items-center">
            <div class="marquee-item shadow-sm" v-for="(img, idx) in lookbookImages" :key="'lb2'+idx" :class="idx % 2 === 0 ? 'item-small' : 'item-large'">
              <img :src="img" class="w-100 h-100 object-fit-cover transition-all hover-zoom-img">
            </div>
          </div>

        </div>
      </div>
    </section>

    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal :compare-list="compareList" @remove="removeFromCompare" @clear="clearCompare" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const router = useRouter();

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

const handleAddToWishlist = (product) => {
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm vào danh sách yêu thích', showConfirmButton: false, timer: 1500 });
};

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
// LOGIC KÉO THẢ MƯỢT MÀ CHUNG CHO MỌI SWIPER
// ========================================================
let activeSwiper = null;
let isDown = false;
let startX;
let scrollLeft;

const startDrag = (e) => {
  isDown = true;
  activeSwiper = e.currentTarget;
  activeSwiper.classList.add('active-drag');
  startX = e.pageX - activeSwiper.offsetLeft;
  scrollLeft = activeSwiper.scrollLeft;
};
const endDrag = () => {
  isDown = false;
  if (activeSwiper) {
     activeSwiper.classList.remove('active-drag');
     activeSwiper = null;
  }
};
const doDrag = (e) => {
  if (!isDown || !activeSwiper) return;
  e.preventDefault();
  const x = e.pageX - activeSwiper.offsetLeft;
  const walk = (x - startX) * 1.5;
  activeSwiper.scrollLeft = scrollLeft - walk;
};

// ========================================================
// LOGIC NÚT BẤM CHO SWIPER BỘ SƯU TẬP
// ========================================================
const collectionSwiperRef = ref(null);
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

// ========================================================
// THƯ VIỆN LOOKBOOK: ĐIỀU CHỈNH TỐC ĐỘ BẰNG JS (WEB ANIMATIONS API)
// ========================================================
const slowDownMarquee = (e) => {
  const animations = e.currentTarget.getAnimations();
  animations.forEach(anim => {
    // Giảm tốc độ xuống 50% thay vì dừng hẳn
    anim.playbackRate = 0.5; 
  });
};

const restoreMarqueeSpeed = (e) => {
  const animations = e.currentTarget.getAnimations();
  animations.forEach(anim => {
    // Trả lại tốc độ mặc định
    anim.playbackRate = 1; 
  });
};

// ========================================================
// MOCK DATA 
// ========================================================
const flashSaleProducts = ref([
  { id: 201, name: 'Áo Sơ Mi Nữ Lụa Satin', price: 299000, old_price: 500000, discount_percent: 40, image: 'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=400&auto=format&fit=crop' },
  { id: 202, name: 'Áo Thun Nữ Trơn Ôm Body', price: 149000, old_price: 250000, discount_percent: 40, image: 'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=400&auto=format&fit=crop' },
  { id: 203, name: 'Chân Váy Xếp Ly Cao Cấp', price: 350000, old_price: 500000, discount_percent: 30, image: 'https://images.unsplash.com/photo-1583496661160-c588c4af5d4a?q=80&w=400&auto=format&fit=crop' },
  { id: 204, name: 'Áo Thun Typography Nữ', price: 199000, old_price: 398000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=400&auto=format&fit=crop' },
  { id: 205, name: 'Áo Khoác Gió Nữ Thể Thao', price: 450000, old_price: 650000, discount_percent: 30, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=400&auto=format&fit=crop' },
]);

const latestProducts = ref([
  { id: 101, name: 'Sơ Mi Tay Dài Nam Đen Siêu Co Giãn', price: 549000, old_price: null, is_new: true, image: 'https://images.unsplash.com/photo-1596755094514-f87e32f85e23?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#2c3e50' }, { hex: '#8e44ad' }] },
  { id: 102, name: 'Sơ Mi Nam Cộc Tay Cafe', price: 469000, old_price: null, is_new: true, image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#bdc3c7' }, { hex: '#3498db' }] },
  { id: 103, name: 'Áo Thun Nam Basic Slimfit', price: 149000, old_price: 299000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#7f8fa6' }, { hex: '#000000' }] },
  { id: 106, name: 'Sơ Mi Nam Nano Kẻ Caro', price: 469000, old_price: null, image: 'https://images.unsplash.com/photo-1588359348347-9bc6cbb6858a?q=80&w=600&auto=format&fit=crop' },
  { id: 105, name: 'Áo Phông Tay Raglan Dài Chỉ Ngang', price: 249000, old_price: 539000, discount_percent: 54, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=600&auto=format&fit=crop' }
]);

const mostLovedProducts = ref([
  { id: 301, name: 'Áo Polo Thể Thao Nữ Năng Động', price: 329000, old_price: null, image: 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#fab1a0' }, { hex: '#c8d6e5' }] },
  { id: 302, name: 'Sơ Mi Nữ Dài Tay Dấu Nẹp', price: 319000, old_price: 449000, discount_percent: 29, image: 'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=600&auto=format&fit=crop' },
  { id: 303, name: 'Áo Phông Nữ Slimfit Coton', price: 99000, old_price: 149000, discount_percent: 34, image: 'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#ffffff' }, { hex: '#000000' }] },
  { id: 304, name: 'Áo Phông Nữ Ôm Croptop', price: 99000, old_price: null, image: 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=600&auto=format&fit=crop' },
  { id: 305, name: 'Áo Phông Nữ In Creative', price: 299000, old_price: 599000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=600&auto=format&fit=crop' },
  { id: 306, name: 'Bộ Thể Thao T-Shirt Nữ', price: 299000, old_price: null, image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop' },
  { id: 307, name: 'Quần Ống Rộng Kaki Thời Trang', price: 420000, old_price: 550000, discount_percent: 23, image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop' },
  { id: 308, name: 'Chân Váy Ngắn Chữ A Basic', price: 250000, old_price: null, image: 'https://images.unsplash.com/photo-1583496661160-c588c4af5d4a?q=80&w=600&auto=format&fit=crop' }
]);

const collectionProducts = ref([
  { id: 401, name: 'Áo Khoác Gió Thể Thao Nữ Năng Động', price: 799000, old_price: null, image: 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#fab1a0' }, { hex: '#c8d6e5' }] },
  { id: 402, name: 'Áo Phông Nữ Sport', price: 299000, old_price: null, image: 'https://images.unsplash.com/photo-1583496661160-c588c4af5d4a?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#ffffff' }, { hex: '#000000' }] },
  { id: 403, name: 'Bộ Thể Thao T-Shirt Nữ Sport', price: 299000, old_price: null, image: 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#000000' }, { hex: '#ffffff' }] },
  { id: 404, name: 'Quần Sooc Thể Thao Nữ', price: 199000, old_price: 250000, discount_percent: 20, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#bdc3c7' }, { hex: '#2c3e50' }] },
  { id: 405, name: 'Chân Váy Nữ Thể Thao Dáng Ngắn', price: 250000, old_price: null, image: 'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#ffffff' }, { hex: '#ff7675' }] },
]);

const kidsProducts = ref([
  { id: 501, name: 'Đồ Bộ Kid Ba Lỗ Phối Viền', price: 159000, old_price: 249000, discount_percent: 36, image: 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#55efc4' }, { hex: '#81ecec' }, { hex: '#fab1a0' }] },
  { id: 502, name: 'Bộ Đồ Thu Đông Trẻ Em', price: 399000, old_price: 499000, discount_percent: 20, image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#ffeaa7' }, { hex: '#fab1a0' }, { hex: '#ff7675' }] },
  { id: 503, name: 'Bộ Đồ Bé Trai Alien', price: 269000, old_price: null, image: 'https://images.unsplash.com/photo-1519278409-1f56fdda70db?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#2d3436' }, { hex: '#ffeaa7' }, { hex: '#fab1a0' }] },
  { id: 504, name: 'Áo Phông Cá Sấu Lớn', price: 149000, old_price: 199000, discount_percent: 25, image: 'https://images.unsplash.com/photo-1594882645126-14020914d58d?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#ffffff' }] },
  { id: 505, name: 'Áo Phông Bé Trai Túi Hộp', price: 119000, old_price: 249000, discount_percent: 52, image: 'https://images.unsplash.com/photo-1560506840-0ca20786fb8a?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#fdcb6e' }, { hex: '#d63031' }, { hex: '#2d3436' }] },
  { id: 506, name: 'Bộ Đồ Bé Trai Tràn Ngực', price: 299000, old_price: 499000, discount_percent: 40, image: 'https://images.unsplash.com/photo-1514316454349-750a7fd3da3a?q=80&w=600&auto=format&fit=crop', colors: [{ hex: '#55efc4' }] },
  { id: 507, name: 'Áo Khoác Nỉ Lông Cừu Dày', price: 350000, old_price: null, image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=600&auto=format&fit=crop' },
  { id: 508, name: 'Quần Thể Thao Bo Gấu Mùa Đông', price: 199000, old_price: 250000, discount_percent: 20, image: 'https://images.unsplash.com/photo-1519278409-1f56fdda70db?q=80&w=600&auto=format&fit=crop' }
]);

const lookbookImages = ref([
  'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1551163943-3f6a855d1153?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=400&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=400&auto=format&fit=crop',
]);

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.home-page-wrapper {
  width: 100%;
}

.hero-section {
  height: 100vh;
  width: 100%;
  overflow: hidden;
  margin-top: 0;
}

.hero-bg {
  background-size: cover;
  background-position: center 20%;
  background-repeat: no-repeat;
  animation: bg-zoom 20s linear infinite alternate;
}

@keyframes bg-zoom {
  0% { transform: scale(1); }
  100% { transform: scale(1.05); }
}

/* HIỆU ỨNG HOVER NÚT ĐIỀU HƯỚNG BỘ SƯU TẬP */
.hover-btn-urban:hover { 
  background-color: var(--color-c-hover, #547792) !important; 
  color: white !important; 
  border-color: var(--color-c-hover, #547792) !important; 
}

/* =======================================================
   ĐỒNG BỘ CSS GRID 5 CỘT VÀ 4 CỘT CHÍNH XÁC 100%
======================================================== */
.zyro-product-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
}

.custom-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

/* Banner chiếm 2 cột ngang, và CHỈ 1 hàng dọc */
.banner-span-2 {
  grid-column: span 2;
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* Banner chiếm 1 cột vuông */
.banner-card {
  grid-column: span 1;
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* KHU VỰC CỦA SWIPER BỘ SƯU TẬP (Span 3) */
.collection-swiper-wrapper {
  grid-column: span 3;
  min-width: 0; 
}

/* Ép tất cả các thẻ con đều cao bằng nhau */
.zyro-product-grid>*,
.custom-grid-4>* {
  height: 100%;
}

/* --- RESPONSIVE CHO GRID --- */
@media (max-width: 1199px) {
  .zyro-product-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; }
  .custom-grid-4 { grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .banner-span-2 { grid-column: span 2; }
  .collection-swiper-wrapper { grid-column: span 2; }
}

@media (max-width: 991px) {
  .zyro-product-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .banner-span-2,
  .banner-card { grid-column: span 3; min-height: 250px; }
  .collection-swiper-wrapper { grid-column: span 3; }
}

@media (max-width: 767px) {
  .zyro-product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .custom-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .banner-span-2,
  .banner-card { grid-column: span 2; min-height: 200px; }
  .collection-swiper-wrapper { grid-column: span 2; }
}

/* =======================================================
   NATIVE SWIPER CHUNG
======================================================== */
.cursor-grab { cursor: grab; }
.cursor-grab:active { cursor: grabbing; }

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

.product-swiper-slide-3 {
  width: calc((100% - 40px) / 3); 
  min-width: 220px;
  flex-shrink: 0;
  scroll-snap-align: start;
}

@media (max-width: 1199px) { .product-swiper-slide-3 { width: calc((100% - 20px) / 2); } }
@media (max-width: 767px) { .product-swiper-slide-3 { width: calc((100% - 12px) / 2); min-width: 160px; } }

.product-swiper-container::-webkit-scrollbar { height: 0px; display: none; }

/* =======================================================
   THƯ VIỆN LOOKBOOK (INFINITE MARQUEE) - ĐÃ BỎ LỆNH PAUSED
======================================================== */
.lookbook-marquee-wrapper {
  width: 100%;
  overflow: hidden;
  display: flex;
}

.lookbook-marquee {
  display: flex;
  width: max-content;
  animation: scrollMarquee 40s linear infinite;
}

@keyframes scrollMarquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); } 
}

.marquee-group {
  display: flex;
  align-items: center;
}

.marquee-item {
  flex-shrink: 0;
  width: 22vw;
  max-width: 320px;
  min-width: 200px;
  cursor: pointer;
}

.item-small { height: 280px; }
.item-large { height: 380px; }

@media (max-width: 991px) {
  .marquee-item { width: 28vw; min-width: 180px; }
  .item-small { height: 220px; }
  .item-large { height: 300px; }
}

@media (max-width: 575px) {
  .marquee-item { width: 45vw; min-width: 160px; }
  .item-small { height: 180px; }
  .item-large { height: 260px; }
  .lookbook-marquee { animation-duration: 25s; }
}

/* THIẾT KẾ SECTION TABS */
.section-tabs .nav-link {
  color: #6c757d;
  border-bottom: 2px solid transparent;
  padding-bottom: 8px;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

html.dark .section-tabs .nav-link { color: #adb5bd; }
.section-tabs .nav-link:hover { color: var(--color-c-hover, #547792); }

.section-tabs .nav-link.active {
  color: #212529 !important;
  border-bottom-color: #212529 !important;
}

html.dark .section-tabs .nav-link.active {
  color: #fff !important;
  border-bottom-color: #fff !important;
}

/* UTILS */
.z-index-2 { z-index: 2; }
.group-hover-zoom { transition: transform 0.5s ease; }
.group:hover .group-hover-zoom { transform: scale(1.05); }

.group-hover-opacity-50 { opacity: 0.25; }
.group:hover .group-hover-opacity-50 { opacity: 0.4; }

.group-hover-opacity-25 { opacity: 0.1; }
.group:hover .group-hover-opacity-25 { opacity: 0.2; }

.hover-zoom-img:hover { filter: brightness(1.1); transform: scale(1.02); }

.text-shadow { text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); }
.text-shadow-lg { text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }

.animation-fade-up {
  animation: fadeUp 1s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.transition-all { transition: all 0.3s ease; }

.hover-scale:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.hover-bg-dark:hover {
  background-color: #212529 !important;
  color: #fff !important;
  border-color: #212529 !important;
}
</style>