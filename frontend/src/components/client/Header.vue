<!-- File: frontend/src/components/client/Header.vue -->
<template>
  <header class="client-header fixed-top transition-all" 
          :class="[
            (isScrolled || !isHomePage) ? 'header-solid shadow-sm' : 'header-transparent',
            isHeaderHidden && !isMegaMenuOpen && !isSearchOpen && !isMiniCartOpen ? 'header-hidden' : ''
          ]">
    
    <nav class="navbar navbar-expand-lg transition-all p-0 position-relative z-index-3">
      <div class="zyro-container d-flex justify-content-between align-items-center transition-all"
           :style="{ height: headerHeight + 'px' }">
        
        <!-- CỘT TRÁI -->
        <div class="header-left d-flex flex-1 align-items-center gap-3 gap-lg-4">
          <button class="btn btn-link p-0 border-0 hover-opacity transition-color" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'"
                  @click="toggleSearch" title="Tìm kiếm">
            <i class="bi fs-4 transition-all" :class="isSearchOpen ? 'bi-x-lg' : 'bi-search'"></i>
          </button>

          <button class="btn btn-link p-0 border-0 hover-opacity transition-color" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'"
                  @click="toggleMegaMenu" title="Danh mục sản phẩm">
            <i class="bi fs-2 transition-all" :class="isMegaMenuOpen ? 'bi-x-lg' : 'bi-list'"></i>
          </button>

          <!-- ĐÃ ĐỔI: Chuyển thẻ <a> thành thẻ <router-link> trỏ vào đường dẫn /stores -->
          <router-link to="/stores" class="btn btn-link p-0 border-0 hover-opacity transition-color d-none d-sm-block" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'"
                  title="Hệ thống cửa hàng">
            <i class="bi bi-shop fs-4"></i>
          </router-link>
        </div>

        <!-- CỘT GIỮA: LOGO -->
        <div class="header-center d-flex justify-content-center flex-1 h-100 py-2">
          <router-link to="/" class="navbar-brand m-0 p-0 d-flex align-items-center h-100" @click="closeAllModals">
            <img :src="logoSrc" @error="handleLogoError" alt="ZYRO" 
                 class="logo-img transition-all" 
                 :class="{ 
                   'logo-scrolled': isScrolled, 
                   'logo-invert': (isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) 
                 }">
          </router-link>
        </div>

        <!-- CỘT PHẢI -->
        <div class="header-right d-flex justify-content-end align-items-center flex-1 gap-3 gap-lg-4">
          <router-link to="#" class="btn btn-link p-0 border-0 hover-opacity d-none d-md-block transition-color" 
                       :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" title="Yêu thích">
            <i class="bi bi-heart fs-5"></i>
          </router-link>
          
          <router-link to="#" class="btn btn-link p-0 border-0 hover-opacity d-none d-md-block transition-color" 
                       :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" title="Tài khoản">
            <i class="bi bi-person fs-5"></i>
          </router-link>
          
          <!-- ĐÃ ĐỔI: Kích hoạt Giỏ hàng trượt ngang (Mini Cart) -->
          <button class="btn btn-link p-0 border-0 hover-opacity position-relative transition-color" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" 
                  title="Giỏ hàng" @click="toggleMiniCart">
            <i class="bi fs-5 transition-all" :class="isMiniCartOpen ? 'bi-x-lg' : 'bi-bag'"></i>
            <span v-show="!isMiniCartOpen" class="position-absolute top-0 start-100 translate-middle badge rounded-pill font-monospace transition-color shadow-sm"
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'bg-dark text-white' : 'bg-white text-dark'"
                  style="font-size: 0.6rem; padding: 0.25em 0.45em;">
              {{ cartItemCount }}
            </span>
          </button>
        </div>
      </div>
    </nav>

    <!-- CÁC COMPONENTS ĐÍNH KÈM HEADER -->
    <MegaMenu :is-open="isMegaMenuOpen" :offset-top="megaMenuOffset" @close="isMegaMenuOpen = false" />
    <SearchModal :is-open="isSearchOpen" :offset-top="megaMenuOffset" @close="isSearchOpen = false" />
    <MiniCartDrawer :is-open="isMiniCartOpen" @close="isMiniCartOpen = false" />

  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import MegaMenu from './MegaMenu.vue';
import SearchModal from './SearchModal.vue';
import MiniCartDrawer from './MiniCartDrawer.vue'; // Nhúng Mini Cart
import logoImage from '@/assets/images/logo/logozyro.png';
import placeholderImage from '@/assets/images/defaults/client_placeholder.png';

const route = useRoute();
const isHomePage = computed(() => route.path === '/');

const logoSrc = ref(logoImage);
const isScrolled = ref(false);
const isHeaderHidden = ref(false); 

// Quản lý trạng thái các Modals
const isMegaMenuOpen = ref(false); 
const isSearchOpen = ref(false); 
const isMiniCartOpen = ref(false); 
const cartItemCount = ref(2); // Demo có 2 sp

let lastScrollPosition = 0;

const handleLogoError = (e) => { e.target.src = placeholderImage; };
const headerHeight = computed(() => isScrolled.value ? 60 : 90);
const megaMenuOffset = computed(() => headerHeight.value);

// Logic Toggle Thông Minh (Đóng cái kia nếu mở cái này)
const toggleMegaMenu = () => { 
  isMegaMenuOpen.value = !isMegaMenuOpen.value; 
  if (isMegaMenuOpen.value) { isSearchOpen.value = false; isMiniCartOpen.value = false; }
};
const toggleSearch = () => { 
  isSearchOpen.value = !isSearchOpen.value; 
  if (isSearchOpen.value) { isMegaMenuOpen.value = false; isMiniCartOpen.value = false; }
};
const toggleMiniCart = () => {
  isMiniCartOpen.value = !isMiniCartOpen.value;
  if (isMiniCartOpen.value) { isMegaMenuOpen.value = false; isSearchOpen.value = false; }
};

const closeAllModals = () => { 
  isMegaMenuOpen.value = false; 
  isSearchOpen.value = false; 
  isMiniCartOpen.value = false; 
};

// Vô hiệu hóa ẩn Smart Header khi có Modal đang mở
watch([isMegaMenuOpen, isSearchOpen, isMiniCartOpen], ([megaOpen, searchOpen, cartOpen]) => {
  if (megaOpen || searchOpen || cartOpen) {
    isHeaderHidden.value = false;
  }
});

const handleScroll = () => {
  if (isMegaMenuOpen.value || isSearchOpen.value || isMiniCartOpen.value) return; 

  const currentScrollPosition = window.scrollY;
  isScrolled.value = currentScrollPosition > 40;

  if (currentScrollPosition <= 0) {
    isHeaderHidden.value = false;
    return;
  }

  if (Math.abs(currentScrollPosition - lastScrollPosition) < 2) return;

  if (currentScrollPosition > lastScrollPosition && currentScrollPosition > 100) {
    isHeaderHidden.value = true;
  } else if (currentScrollPosition < lastScrollPosition) {
    isHeaderHidden.value = false;
  }

  lastScrollPosition = currentScrollPosition;
};

onMounted(() => { window.addEventListener('scroll', handleScroll); });
onUnmounted(() => { window.removeEventListener('scroll', handleScroll); });
</script>

<style scoped>
.client-header { width: 100%; }
.z-index-3 { z-index: 1040; }
.header-hidden { transform: translateY(-100%); }
.header-transparent { background-color: transparent; text-shadow: 0px 2px 4px rgba(0,0,0,0.3); }
.header-solid, .header-transparent:has(~ .mega-menu-backdrop), .client-header:has(.mega-menu-backdrop), .client-header:has(.search-backdrop), .client-header:has(.minicart-backdrop) {
  background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); text-shadow: none;
}
.flex-1 { flex: 1; }
.logo-img { height: 100%; max-height: 75px; object-fit: contain; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3)); }
.logo-scrolled { max-height: 45px; }
.logo-invert { filter: brightness(0) !important; }
.hover-opacity { transition: opacity 0.2s ease, transform 0.2s ease; }
.hover-opacity:hover { opacity: 0.7; transform: translateY(-2px); }
.transition-all { transition: all 0.3s ease-in-out; }
.transition-color { transition: color 0.3s ease, background-color 0.3s ease; }
</style>