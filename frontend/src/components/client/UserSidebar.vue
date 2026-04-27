<template>
  <div class="user-sidebar-container">
    
    <!-- DESKTOP -->
    <div class="d-none d-lg-block sticky-top" style="top: 130px;">
      <div class="d-flex align-items-center mb-4 pb-4 border-bottom dark:border-gray-700">
        <!-- AVATAR GLOW THEO MÀU HẠNG -->
        <div class="position-relative flex-shrink-0 avatar-wrapper" :class="{'vip-glow': isTopTier}" :style="{'--tier-color': tierColor}">
          <img :src="userAvatar" @error="handleAvatarError" 
               class="rounded-circle object-fit-cover border border-3 bg-light dark:bg-gray-800" 
               :style="{ borderColor: tierColor + ' !important' }"
               style="width: 65px; height: 65px; position: relative; z-index: 2;">
        </div>

        <div class="overflow-hidden ms-3">
          <h6 class="fw-bold text-dark dark:text-white mb-1 text-truncate">{{ userName }}</h6>
          <!-- BADGE HẠNG TỰ ĐỘNG THEO MÀU -->
          <span class="badge fw-bold shadow-sm d-inline-flex align-items-center tracking-wide px-2 py-1" 
                :style="{ backgroundColor: tierColor, color: getContrastYIQ(tierColor) }" style="font-size: 0.7rem;">
            <i class="bi bi-stars me-1" style="font-size: 0.7rem;"></i> {{ tierName }}
          </span>
        </div>
      </div>

      <ul class="nav flex-column gap-2">
        <li class="nav-item" v-for="(item, index) in menuItems" :key="index">
          <router-link :to="item.path" 
                       class="nav-link px-3 py-2.5 rounded-3 fw-semibold transition-all d-flex align-items-center"
                       :class="isActive(item.path) ? 'active-nav' : 'text-muted dark:text-gray-400 hover-nav'">
            <i class="bi fs-5 me-3 w-20px text-center" :class="item.icon"></i> 
            <span>{{ item.name }}</span>
          </router-link>
        </li>
        <li class="nav-item mt-3 pt-3 border-top dark:border-gray-700">
          <a href="#" @click.prevent="handleLogout" 
             class="nav-link px-3 py-2.5 rounded-3 fw-bold text-danger hover-danger-nav transition-all d-flex align-items-center">
            <i class="bi bi-box-arrow-right fs-5 me-3 w-20px text-center"></i> 
            <span>Đăng xuất</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- MOBILE -->
    <div class="d-lg-none">
      <div class="mobile-menu-trigger bg-white dark:bg-[#1a2533] shadow-sm border rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between cursor-pointer" 
           @click="isMobileMenuOpen = true">
        <div class="d-flex align-items-center overflow-hidden">
          <div class="position-relative me-3 flex-shrink-0 avatar-wrapper" :class="{'vip-glow': isTopTier}" :style="{'--tier-color': tierColor}">
             <img :src="userAvatar" @error="handleAvatarError" class="rounded-circle border border-2 object-fit-cover" 
                  :style="{ borderColor: tierColor + ' !important' }" style="width: 45px; height: 45px;">
          </div>
          <div class="overflow-hidden">
            <div class="small text-muted mb-0">Tài khoản</div>
            <div class="fw-bold text-dark dark:text-white text-truncate">{{ userName }}</div>
          </div>
        </div>
        <i class="bi bi-grid-fill text-urban fs-4 ms-2"></i>
      </div>

      <transition name="fade">
        <div v-if="isMobileMenuOpen" class="mobile-sidebar-backdrop" @click="isMobileMenuOpen = false"></div>
      </transition>

      <transition name="slide-left">
        <div v-if="isMobileMenuOpen" class="mobile-sidebar-panel bg-white dark:bg-[#1a2533] shadow-lg">
          <div class="p-4 d-flex flex-column h-100">
            <div class="text-center position-relative mb-4 pt-4">
              <button class="btn-close-custom position-absolute top-0 end-0" @click="isMobileMenuOpen = false"><i class="bi bi-x-lg fs-5"></i></button>
              
              <div class="d-inline-block position-relative mb-3 avatar-wrapper" :class="{'vip-glow': isTopTier}" :style="{'--tier-color': tierColor}">
                <img :src="userAvatar" @error="handleAvatarError" class="rounded-circle shadow-sm border border-4 object-fit-cover" 
                     :style="{ borderColor: tierColor + ' !important' }" style="width: 90px; height: 90px; position: relative; z-index: 2;">
              </div>
              <h5 class="fw-bold text-dark dark:text-white mb-1 text-truncate px-3">{{ userName }}</h5>
              <span class="badge px-3 py-1 rounded-pill small fw-bold tracking-wide shadow-sm" :style="{ backgroundColor: tierColor, color: getContrastYIQ(tierColor) }">
                <i class="bi bi-stars"></i> {{ tierName }}
              </span>
            </div>

            <div class="flex-grow-1 overflow-auto custom-scrollbar-y py-2">
               <ul class="nav flex-column gap-1">
                  <li class="nav-item" v-for="(item, index) in menuItems" :key="'mob'+index" @click="isMobileMenuOpen = false">
                    <router-link :to="item.path" class="nav-link px-3 py-3 rounded-3 fw-semibold d-flex align-items-center" :class="isActive(item.path) ? 'active-nav-mobile' : 'text-muted dark:text-gray-400'">
                      <i class="bi fs-5 me-3 w-20px text-center" :class="item.icon"></i> <span>{{ item.name }}</span>
                    </router-link>
                  </li>
               </ul>
            </div>

            <div class="mt-auto pt-3">
              <button @click="handleLogout" class="btn btn-light dark:bg-[#2b3035] dark:text-danger w-100 py-3 rounded-4 fw-bold text-danger d-flex align-items-center justify-content-center gap-2 transition-all">
                <i class="bi bi-box-arrow-right fs-5"></i> Đăng xuất
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import api from '@/utils/axios';

const route = useRoute();
const router = useRouter();

const userName = ref('Khách hàng');
const userAvatar = ref('');
const tierName = ref('Thành viên Mới');
const tierColor = ref('#6c757d');
const isTopTier = ref(false);
const isMobileMenuOpen = ref(false);
const defaultAvatarUrl = '/client_placeholder.png'; 

const menuItems = ref([
  { name: 'Hồ sơ của tôi', path: '/user/profile', icon: 'bi-person-circle' },
  { name: 'Đơn mua hàng', path: '/user/orders', icon: 'bi-receipt' },
  { name: 'Sản phẩm yêu thích', path: '/user/wishlist', icon: 'bi-heart' },
  { name: 'Sổ địa chỉ', path: '/user/address', icon: 'bi-geo-alt' },
  { name: 'Đổi mật khẩu', path: '/user/password', icon: 'bi-shield-lock' }
]);

const getImageUrl = (path) => {
  if (!path) return defaultAvatarUrl;
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};

const handleAvatarError = (e) => { e.target.src = defaultAvatarUrl; };

// Tính toán màu text (trắng/đen) dựa trên độ sáng của màu nền
const getContrastYIQ = (hexcolor) => {
  if (!hexcolor) return '#ffffff';
  hexcolor = hexcolor.replace("#", "");
  const r = parseInt(hexcolor.substr(0,2),16);
  const g = parseInt(hexcolor.substr(2,2),16);
  const b = parseInt(hexcolor.substr(4,2),16);
  const yiq = ((r*299)+(g*587)+(b*114))/1000;
  return (yiq >= 128) ? '#111111' : '#ffffff';
};

const loadUserData = (e) => {
  const userStr = localStorage.getItem('user_info');
  if (userStr) {
    try {
      const user = JSON.parse(userStr);
      userName.value = user.full_name || user.fullName || user.name;
      userAvatar.value = getImageUrl(user.avatar_url); 
      
      // Ưu tiên lấy dữ liệu trực tiếp từ Event (Profile gửi qua)
      if (e && e.detail && e.detail.tierName) {
         tierName.value = e.detail.tierName;
         tierColor.value = e.detail.tierColor;
         isTopTier.value = e.detail.isTopTier;
      } else {
         tierName.value = user.tier_name || 'Thành viên mới';
         // Nếu không có event, dùng màu mặc định chờ Profile emit sang
         tierColor.value = '#adb5bd'; 
      }
    } catch (err) {
      console.error("Lỗi đọc dữ liệu user", err);
    }
  }
};

const isActive = (path) => route.path.startsWith(path);

const handleLogout = () => {
  isMobileMenuOpen.value = false;
  ZyroSwal.confirmLogout().then(async (result) => {
    if (result.isConfirmed) {
      try { await api.post('/client/logout'); } catch (error) {} 
      finally {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user_info');
        ZyroSwal.toastSuccess('Hẹn gặp lại bạn!');
        setTimeout(() => { router.push('/login'); }, 1200);
      }
    }
  });
};

onMounted(() => {
  loadUserData();
  window.addEventListener('user-profile-updated', loadUserData);
});

onUnmounted(() => {
  window.removeEventListener('user-profile-updated', loadUserData);
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.w-20px { width: 24px; }
.transition-all { transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
.tracking-wide { letter-spacing: 0.5px; }

.user-sidebar-container .nav-link { color: #6c757d; font-size: 0.95rem; }
html.dark .user-sidebar-container .nav-link { color: #adb5bd; }

.user-sidebar-container .hover-nav:hover { background-color: var(--color-c-effect, #EBF1F5); color: var(--color-c-hover, #547792) !important; transform: translateX(5px); }
html.dark .user-sidebar-container .hover-nav:hover { background-color: rgba(255,255,255,0.05); color: #fff !important; }
.hover-danger-nav:hover { background-color: rgba(220, 53, 69, 0.08); transform: translateX(5px); }

.active-nav { background-color: var(--color-c-effect, #EBF1F5); color: var(--color-c-hover, #547792) !important; border-left: 4px solid var(--color-c-hover, #547792) !important; font-weight: 700; }
html.dark .active-nav { background-color: rgba(255,255,255,0.05); color: #fff !important; border-left-color: #fff !important; }

/* HIỆU ỨNG GLOW CHO AVATAR */
.avatar-wrapper { position: relative; border-radius: 50%; display: inline-flex; }
.avatar-wrapper img { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.vip-glow::before {
  content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px;
  border-radius: 50%; z-index: 1;
  background: var(--tier-color);
  filter: blur(8px);
  animation: pulse-glow 2s infinite alternate;
}
@keyframes pulse-glow {
  0% { opacity: 0.6; filter: blur(5px); transform: scale(0.98); }
  100% { opacity: 1; filter: blur(12px); transform: scale(1.05); }
}

.mobile-menu-trigger { border: 1px solid #eee !important; }
html.dark .mobile-menu-trigger { border-color: #373b3e !important; }
.mobile-sidebar-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.5); z-index: 2000; backdrop-filter: blur(2px); }
.mobile-sidebar-panel { position: fixed; top: 0; left: 0; width: 85%; max-width: 320px; height: 100vh; z-index: 2001; border-radius: 0 24px 24px 0; }
.active-nav-mobile { background-color: var(--color-c-effect, #EBF1F5); color: var(--color-c-hover, #547792) !important; border-right: 5px solid var(--color-c-hover, #547792); font-weight: 700; }
html.dark .active-nav-mobile { background-color: rgba(255,255,255,0.05); color: #fff !important; border-right-color: #fff !important; }
.btn-close-custom { background: transparent; border: none; padding: 12px; color: #6c757d; transition: all 0.2s; }
.btn-close-custom:hover { color: #000; transform: rotate(90deg); }
html.dark .btn-close-custom:hover { color: #fff; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>