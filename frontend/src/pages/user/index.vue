<template>
   <div class="home-page-wrapper">
    <!-- ĐÃ FIX: Truyền cờ isLoading vào HeroBanner để kích hoạt Skeleton -->
    <HeroBanner :banners="homeData.hero_banners" :is-loading="isLoading" />

    <!-- ĐÃ FIX: Chuyển @compare thành compareStore.add -->
    <FlashSaleSection 
      v-if="homeData.flash_sale" 
      :flashSale="homeData.flash_sale"
      @quick-view="handleOpenQuickView" 
      @compare="compareStore.add" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <NewArrivalSection 
      :data="homeData.new_arrivals" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="compareStore.add" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <TrendingSection 
      :data="homeData.most_loved" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="compareStore.add" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <LookbookSection 
      v-if="homeData.lookbooks && homeData.lookbooks.length > 0"
      :lookbooks="homeData.lookbooks" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="compareStore.add" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <KidsSection 
      :data="homeData.kids" 
      :isLoading="isLoading"
      @quick-view="handleOpenQuickView" 
      @compare="compareStore.add" 
      @wishlist="handleAddToWishlist" 
      @options="handleGoToDetail" 
    />

    <GallerySection :images="lookbookImages" />

    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    
    <!-- ĐÃ FIX: Truyền data và actions từ Store thẳng vào Modal -->
    <CompareModal 
      :compare-list="compareStore.items" 
      @remove="compareStore.remove" 
      @clear="compareStore.clear" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';

// ĐÃ THÊM: Gọi Store Compare vào
import { useCompareStore } from '@/stores/compareStore';

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
const compareStore = useCompareStore(); // Khởi tạo Store
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
  ZyroSwal.toastSuccess('Đã thêm vào danh sách yêu thích');
};

const lookbookImages = ref([
  'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
  'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=400',
  'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=400',
  'https://images.unsplash.com/photo-1551163943-3f6a855d1153?w=400',
]);

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.channel('client.home')
      .listen('.HomeUpdated', () => {
        fetchHomeData(); 
      });
  }
};

onMounted(() => { 
  window.scrollTo(0, 0); 
  fetchHomeData();
  setupRealtime();
});

onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leaveChannel('client.home');
  }
});
</script>

<style scoped>
.home-page-wrapper { width: 100%; }
</style>