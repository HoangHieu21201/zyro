<template>
  <div class="home-page-wrapper">
    <HeroBanner :banners="homeData.hero_banners" />

    <FlashSaleSection 
      v-if="homeData.flash_sale" 
      :flashSale="homeData.flash_sale"
      @quick-view="handleOpenQuickView" 
      @compare="handleAddToCompare" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <NewArrivalSection 
      :data="homeData.new_arrivals" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="handleAddToCompare" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <TrendingSection 
      :data="homeData.most_loved" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="handleAddToCompare" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <LookbookSection 
      v-if="homeData.lookbooks && homeData.lookbooks.length > 0"
      :lookbooks="homeData.lookbooks" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="handleAddToCompare" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <KidsSection 
      :data="homeData.kids" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="handleAddToCompare" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <GallerySection :images="lookbookImages" />

    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal :compare-list="compareList" @remove="removeFromCompare" @clear="clearCompare" />
  </div>
</template>

<script setup>
// ĐÃ THÊM: import onUnmounted
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

import HeroBanner from '@/components/client/home/HeroBanner.vue';
import FlashSaleSection from '@/components/client/home/FlashSaleSection.vue';
import NewArrivalSection from '@/components/client/home/NewArrivalSection.vue';
import TrendingSection from '@/components/client/home/TrendingSection.vue';
import LookbookSection from '@/components/client/home/LookbookSection.vue';
import KidsSection from '@/components/client/home/KidsSection.vue';
import GallerySection from '@/components/client/home/GallerySection.vue';

import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const router = useRouter();
const isLoading = ref(true);

const homeData = ref({
  hero_banners: [],
  flash_sale: null,
  new_arrivals: { tabs: [], products: [] },
  most_loved: { hot_trends: [], best_sellers: [] },
  kids: { category: null, products: [] }, 
  lookbooks: []
});

const fetchHomeData = async () => {
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/client/home`);
    if(res.data.success) {
      homeData.value = res.data.data;
    }
  } catch (error) {
    console.error('Lỗi lấy dữ liệu trang chủ:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleGoToDetail = (product) => { router.push(`/product/${product.id}`); };

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
  if (compareList.value.find(p => p.id === product.id)) return Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Sản phẩm đã có trong danh sách', showConfirmButton: false, timer: 2000 });
  if (compareList.value.length >= 10) return Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Tối đa 10 sản phẩm', showConfirmButton: false, timer: 2000 });
  compareList.value.push(product);
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm vào bảng so sánh', showConfirmButton: false, timer: 1500 });
};
const removeFromCompare = (index) => { compareList.value.splice(index, 1); };
const clearCompare = () => { compareList.value = []; };

const lookbookImages = ref([
  'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
  'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=400',
  'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=400',
  'https://images.unsplash.com/photo-1551163943-3f6a855d1153?w=400',
]);

// ==========================================
// ĐÃ THÊM: LẮNG NGHE SỰ KIỆN TỪ BACKEND
// ==========================================
const setupRealtime = () => {
  if (window.Echo) {
    // Lắng nghe trên kênh public 'client.home'
    window.Echo.channel('client.home')
      .listen('.HomeUpdated', () => {
        // Khi Admin vừa sửa cái gì đó, gọi lại API để load giao diện mới!
        fetchHomeData(); 
      });
  }
};

onMounted(() => { 
  window.scrollTo(0, 0); 
  fetchHomeData();
  setupRealtime(); // Bật ăng-ten
});

onUnmounted(() => {
  // Dọn dẹp ăng-ten khi chuyển sang trang khác
  if (window.Echo) {
    window.Echo.leaveChannel('client.home');
  }
});
</script>

<style scoped>
.home-page-wrapper { width: 100%; }
</style>