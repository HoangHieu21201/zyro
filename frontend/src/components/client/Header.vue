<template>
  <header class="client-header fixed-top transition-all" 
          :class="[
            (isScrolled || !isHomePage) ? 'header-solid shadow-sm' : 'header-transparent',
            isHeaderHidden && !isMegaMenuOpen && !isSearchOpen && !isMiniCartOpen ? 'header-hidden' : ''
          ]">
    
    <nav class="navbar navbar-expand-lg transition-all p-0 position-relative z-index-3">
      <div class="zyro-container d-flex justify-content-between align-items-center transition-all"
           :style="{ height: headerHeight + 'px' }">
        
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

          <router-link to="/stores" class="btn btn-link p-0 border-0 hover-opacity transition-color d-none d-sm-block" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'"
                  title="Hệ thống cửa hàng">
            <i class="bi bi-shop fs-4"></i>
          </router-link>
        </div>

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

        <div class="header-right d-flex justify-content-end align-items-center flex-1 gap-3 gap-lg-4">
          
          <router-link to="/user/wishlist" class="btn btn-link p-0 border-0 hover-opacity d-none d-md-block transition-color" 
                       :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" title="Yêu thích">
            <i class="bi bi-heart fs-5"></i>
          </router-link>
          
          <router-link v-if="!isLoggedIn" to="/login" class="btn btn-link p-0 border-0 hover-opacity d-none d-md-block transition-color" 
                       :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" title="Đăng nhập / Đăng ký">
            <i class="bi bi-person fs-5"></i>
          </router-link>

          <div v-else class="position-relative d-none d-md-block" 
               @mouseenter="openUserMenu" 
               @mouseleave="closeUserMenu">
            
            <button class="btn btn-link p-0 border-0 hover-opacity transition-color" 
                    @click="isUserMenuOpen = !isUserMenuOpen"
                    :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" title="Tài khoản của tôi">
              <i class="bi bi-person-check fs-5"></i>
            </button>

            <transition name="fade-slide-up">
              <div v-show="isUserMenuOpen" class="custom-user-dropdown bg-white dark:bg-[#2b3035] shadow rounded-3 position-absolute end-0 mt-2 py-2" style="width: 220px;">
                <div class="px-3 py-2 border-bottom dark:border-gray-700 mb-1">
                  <span class="d-block small text-muted dark:text-gray-400">Xin chào,</span>
                  <strong class="d-block text-dark dark:text-white text-truncate">{{ userName }}</strong>
                </div>
                
                <router-link class="dropdown-item small py-2 px-3 transition-all dark:text-gray-200 dark:hover:bg-gray-700 d-flex align-items-center custom-dropdown-hover" to="/user/profile" @click="isUserMenuOpen = false">
                  <i class="bi bi-person-vcard me-2 opacity-75"></i> Thông tin tài khoản
                </router-link>
                
                <router-link class="dropdown-item small py-2 px-3 transition-all dark:text-gray-200 dark:hover:bg-gray-700 d-flex align-items-center custom-dropdown-hover" to="/user/orders" @click="isUserMenuOpen = false">
                  <i class="bi bi-box-seam me-2 opacity-75"></i> Đơn hàng của tôi
                </router-link>
                
                <div class="dropdown-divider dark:border-gray-700 my-1"></div>
                
                <a class="dropdown-item small py-2 px-3 text-danger cursor-pointer transition-all dark:hover:bg-gray-700 d-flex align-items-center custom-dropdown-hover" @click.prevent="handleLogout">
                  <i class="bi bi-box-arrow-right me-2 opacity-75"></i> Đăng xuất
                </a>
              </div>
            </transition>
          </div>

          <button class="btn btn-link p-0 border-0 hover-opacity position-relative transition-color" 
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'text-dark' : 'text-white'" 
                  title="Giỏ hàng" @click="toggleMiniCart">
            <i class="bi fs-5 transition-all" :class="isMiniCartOpen ? 'bi-x-lg' : 'bi-bag'"></i>
            <span v-show="!isMiniCartOpen" class="position-absolute top-0 start-100 translate-middle badge rounded-pill font-monospace transition-color shadow-sm"
                  :class="(isScrolled || !isHomePage || isMegaMenuOpen || isSearchOpen || isMiniCartOpen) ? 'bg-dark text-white' : 'bg-white text-dark'"
                  style="font-size: 0.6rem; padding: 0.25em 0.45em;">
              {{ cartStore.items.length }}
            </span>
          </button>
        </div>
      </div>
    </nav>

    <MegaMenu 
      :is-open="isMegaMenuOpen" 
      :offset-top="megaMenuOffset" 
      :categories="headerData.categories"
      :lookbooks="headerData.lookbooks"
      @close="isMegaMenuOpen = false" 
    />
    
    <SearchModal 
      :is-open="isSearchOpen" 
      :trending-products="headerData.trendingProducts"
      @close="isSearchOpen = false" 
    />
    
    <MiniCartDrawer :is-open="isMiniCartOpen" @close="isMiniCartOpen = false" />

  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import { useCartStore } from '@/stores/cartStore';
import { useWishlistStore } from '@/stores/wishlistStore';

import MegaMenu from './MegaMenu.vue';
import SearchModal from './SearchModal.vue';
import MiniCartDrawer from './MiniCartDrawer.vue';
import logoImage from '@/assets/images/logo/logozyro.png';
import placeholderImage from '@/assets/images/defaults/client_placeholder.png';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const isHomePage = computed(() => route.path === '/');
const logoSrc = ref(logoImage);
const isScrolled = ref(false);
const isHeaderHidden = ref(false); 

const isMegaMenuOpen = ref(false); 
const isSearchOpen = ref(false); 
const isMiniCartOpen = ref(false); 
const isUserMenuOpen = ref(false); 

let lastScrollPosition = 0;

const handleLogoError = (e) => { e.target.src = placeholderImage; };
const headerHeight = computed(() => isScrolled.value ? 60 : 90);
const megaMenuOffset = computed(() => headerHeight.value);

// ========================================================
// STATE & LOGIC AUTH (ĐĂNG NHẬP / ĐĂNG XUẤT / MENU USER)
// ========================================================
const isLoggedIn = ref(false);
const userName = ref('');
let userMenuTimeout = null;

const openUserMenu = () => {
  clearTimeout(userMenuTimeout);
  isUserMenuOpen.value = true;
};

const closeUserMenu = () => {
  userMenuTimeout = setTimeout(() => {
    isUserMenuOpen.value = false;
  }, 200); 
};

const checkAuthStatus = () => {
  const token = localStorage.getItem('access_token');
  const userStr = localStorage.getItem('user_info');
  
  if (token && userStr) {
    isLoggedIn.value = true;
    try {
      const userObj = JSON.parse(userStr);
      userName.value = userObj.full_name || userObj.fullName || userObj.name || 'Khách hàng';
      wishlistStore.fetchWishlist(); // ĐÃ THÊM: Tự động kéo danh sách yêu thích
    } catch (e) {
      console.error('Lỗi parse thông tin user');
    }
  } else {
    isLoggedIn.value = false;
    userName.value = '';
    wishlistStore.items = []; // ĐÃ THÊM: Xóa danh sách nếu chưa đăng nhập
  }
};

watch(() => route.path, () => {
  checkAuthStatus();
  isUserMenuOpen.value = false;
});

const handleLogout = () => {
  ZyroSwal.confirmLogout().then(async (result) => {
    if (result.isConfirmed) {
      try {
        const token = localStorage.getItem('access_token');
        if (token) {
          // Bắn API logout lên server để hủy token
          await axios.post(`${import.meta.env.VITE_API_BASE_URL}/client/logout`, {}, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }
      } catch (error) {
        console.error('Lỗi API logout', error);
      } finally {
        // Luôn dọn dẹp localStorage dù API có lỗi hay không
        localStorage.removeItem('access_token');
        localStorage.removeItem('user_info');
        
        isLoggedIn.value = false;
        userName.value = '';
        isUserMenuOpen.value = false;

        ZyroSwal.toastSuccess('Đăng xuất thành công!');

        setTimeout(() => {
          if (route.path.includes('/user/') || route.path.includes('/checkout')) {
            router.push('/login');
          } else {
            window.location.reload(); 
          }
        }, 1200);
      }
    }
  });
};

// ========================================================

const headerData = ref({ categories: [], lookbooks: [], trendingProducts: [] });

const fetchHeaderData = async () => {
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/client/home`);
    if(res.data.success) {
      headerData.value.categories = res.data.data.mega_menu_categories || [];
      headerData.value.lookbooks = res.data.data.lookbooks || [];
      headerData.value.trendingProducts = res.data.data.most_loved?.hot_trends || [];
    }
  } catch (error) {
    console.error('Lỗi lấy dữ liệu Header:', error);
  }
};

const toggleMegaMenu = () => { 
  isMegaMenuOpen.value = !isMegaMenuOpen.value; 
  if (isMegaMenuOpen.value) { isSearchOpen.value = false; isMiniCartOpen.value = false; isUserMenuOpen.value = false; }
};
const toggleSearch = () => { 
  isSearchOpen.value = !isSearchOpen.value; 
  if (isSearchOpen.value) { isMegaMenuOpen.value = false; isMiniCartOpen.value = false; isUserMenuOpen.value = false; }
};
const toggleMiniCart = () => {
  isMiniCartOpen.value = !isMiniCartOpen.value;
  if (isMiniCartOpen.value) { isMegaMenuOpen.value = false; isSearchOpen.value = false; isUserMenuOpen.value = false; }
};

const closeAllModals = () => { 
  isMegaMenuOpen.value = false; 
  isSearchOpen.value = false; 
  isMiniCartOpen.value = false; 
  isUserMenuOpen.value = false;
};

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
    isUserMenuOpen.value = false;
  } else if (currentScrollPosition < lastScrollPosition) {
    isHeaderHidden.value = false;
  }

  lastScrollPosition = currentScrollPosition;
};

onMounted(() => { 
  checkAuthStatus(); 
  window.addEventListener('scroll', handleScroll); 
  fetchHeaderData();
  cartStore.initCart(); 
});

onUnmounted(() => { 
  window.removeEventListener('scroll', handleScroll); 
});
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
.cursor-pointer { cursor: pointer; }

/* =========================================
   STYLE CHO CUSTOM USER DROPDOWN (VUE)
========================================= */
.custom-user-dropdown {
  top: 100%;
  border: 1px solid rgba(0,0,0,0.08);
}

.custom-user-dropdown::before {
  content: '';
  position: absolute;
  top: -15px; 
  left: 0;
  width: 100%;
  height: 15px;
  background-color: transparent;
}

html.dark .custom-user-dropdown {
  border-color: rgba(255,255,255,0.05);
}
.custom-dropdown-hover {
  text-decoration: none;
  color: #212529;
}
.custom-dropdown-hover:hover {
  background-color: var(--color-c-effect, #EBF1F5);
  color: var(--color-c-hover, #547792);
}
html.dark .custom-dropdown-hover { color: #f8f9fa; }

.fade-slide-up-enter-active,
.fade-slide-up-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-slide-up-enter-from,
.fade-slide-up-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>