<template>
  <div class="user-sidebar-container">
    
    <div class="d-none d-lg-block sticky-top" style="top: 130px;">
      <div class="d-flex align-items-center mb-4 pb-4 border-bottom dark:border-gray-700">
        
        <div class="position-relative flex-shrink-0 avatar-wrapper" 
             :class="{'vip-glow': isTopTier}" 
             :style="{'--tier-color': tierColor, width: '65px', height: '65px'}">
             
          <div v-if="isTopTier" class="crown-icon">
             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path d="M235.25,83.56a16,16,0,0,0-18.77-5.18L174,94.24l-38.16-57.26a16,16,0,0,0-26.6,0L71.1,94.24,28.51,78.38a16,16,0,0,0-18.77,5.18,16.21,16.21,0,0,0-2.48,19.06L38,168.39A24,24,0,0,0,59.3,184H185.69a24,24,0,0,0,21.32-15.61l30.72-65.77A16.21,16.21,0,0,0,235.25,83.56Z"></path></svg>
          </div>

          <img :src="userAvatar" @error="handleAvatarError" 
               class="rounded-circle object-fit-cover border border-3 bg-transparent" 
               :style="{ borderColor: tierColor + ' !important' }"
               style="width: 100%; height: 100%; position: relative; z-index: 2;">
        </div>

        <div class="overflow-hidden ms-3">
          <h6 class="fw-bold text-dark dark:text-white mb-1 text-truncate">{{ userName }}</h6>
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

    <div class="d-lg-none">
      <div class="mobile-menu-trigger bg-white dark:bg-[#1a2533] shadow-sm border rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between cursor-pointer" 
           @click="isMobileMenuOpen = true">
        <div class="d-flex align-items-center overflow-hidden">
          
          <div class="position-relative me-3 flex-shrink-0 avatar-wrapper" 
               :class="{'vip-glow': isTopTier}" 
               :style="{'--tier-color': tierColor, width: '45px', height: '45px'}">
               
             <div v-if="isTopTier" class="crown-icon crown-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path d="M235.25,83.56a16,16,0,0,0-18.77-5.18L174,94.24l-38.16-57.26a16,16,0,0,0-26.6,0L71.1,94.24,28.51,78.38a16,16,0,0,0-18.77,5.18,16.21,16.21,0,0,0-2.48,19.06L38,168.39A24,24,0,0,0,59.3,184H185.69a24,24,0,0,0,21.32-15.61l30.72-65.77A16.21,16.21,0,0,0,235.25,83.56Z"></path></svg>
             </div>

             <img :src="userAvatar" @error="handleAvatarError" class="rounded-circle border border-2 object-fit-cover bg-transparent" 
                  :style="{ borderColor: tierColor + ' !important' }" style="width: 100%; height: 100%; position: relative; z-index: 2;">
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
              
              <div class="d-inline-block position-relative mb-3 avatar-wrapper" 
                   :class="{'vip-glow': isTopTier}" 
                   :style="{'--tier-color': tierColor, width: '90px', height: '90px'}">

                <div v-if="isTopTier" class="crown-icon crown-lg">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path d="M235.25,83.56a16,16,0,0,0-18.77-5.18L174,94.24l-38.16-57.26a16,16,0,0,0-26.6,0L71.1,94.24,28.51,78.38a16,16,0,0,0-18.77,5.18,16.21,16.21,0,0,0-2.48,19.06L38,168.39A24,24,0,0,0,59.3,184H185.69a24,24,0,0,0,21.32-15.61l30.72-65.77A16.21,16.21,0,0,0,235.25,83.56Z"></path></svg>
                </div>

                <img :src="userAvatar" @error="handleAvatarError" class="rounded-circle shadow-sm border border-4 object-fit-cover bg-transparent" 
                     :style="{ borderColor: tierColor + ' !important' }" style="width: 100%; height: 100%; position: relative; z-index: 2;">
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
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import api from '@/utils/axios';

const route = useRoute();
const router = useRouter();

const userName = ref('Khách hàng');
const userAvatar = ref('');
const tierName = ref('Member');
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

// =====================================
// ĐÃ ĐỒNG BỘ: BẢNG MÀU VÀ THUẬT TOÁN HSL NHƯ PROFILE
// =====================================
const getTierColorFromName = (name) => {
  if (!name) return '#9ca3af'; 
  const lName = name.toLowerCase();
  
  if (lName.includes('khởi đầu') || lName.includes('member')) return '#9ca3af'; 
  if (lName.includes('fan cứng') || lName.includes('bronze')) return '#3b82f6'; 
  if (lName.includes('đồng')) return '#f59e0b';
  // Đã đồng bộ màu Bạc
  if (lName.includes('bạc') || lName.includes('silver')) return '#00d2ff'; 
  if (lName.includes('vàng') || lName.includes('gold')) return '#ffc107'; 
  if (lName.includes('bạch kim') || lName.includes('platinum')) return '#d946ef'; 
  if (lName.includes('kim cương') || lName.includes('diamond')) return '#8b5cf6'; 
  if (lName.includes('vip') || lName.includes('master')) return '#f43f5e'; 

  // Thuật toán màu ngẫu nhiên cho hạng mới (Saturation 90%, Lightness 55%)
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const h = Math.abs(hash) % 360;
  return `hsl(${h}, 90%, 55%)`;
};

const loadUserData = (e) => {
  const userStr = localStorage.getItem('user_info');
  if (userStr) {
    try {
      const user = JSON.parse(userStr);
      userName.value = user.full_name || user.fullName || user.name;
      userAvatar.value = getImageUrl(user.avatar_url); 
      
      // Nếu sự kiện (Event) được bắn ra từ trang Profile thì bắt lấy
      if (e && e.detail && e.detail.tierName) {
         tierName.value = e.detail.tierName;
         tierColor.value = e.detail.tierColor;
      } else {
         // Nếu không có event (F5 hoặc vào tab khác), móc từ localStorage và chạy thuật toán map màu
         tierName.value = user.tier_name || 'Member';
         tierColor.value = getTierColorFromName(tierName.value); 
      }
      
      // Vương miện chỉ hiện cho những người thoát cấp Mặc Định
      isTopTier.value = !tierName.value.toLowerCase().includes('mới') && 
                        !tierName.value.toLowerCase().includes('khởi đầu') && 
                        !tierName.value.toLowerCase().includes('member');
                        
    } catch (err) {
      console.error(err);
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

/* ====================================================
   HIỆU ỨNG GLOW CHO AVATAR
==================================================== */
.avatar-wrapper { position: relative; border-radius: 50%; display: inline-flex; }
.avatar-wrapper img { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

.vip-glow::before {
  content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px;
  border-radius: 50%; z-index: 0;
  background: var(--tier-color);
  filter: blur(8px);
  animation: pulse-glow 2s infinite alternate;
}

@keyframes pulse-glow {
  0% { opacity: 0.6; filter: blur(5px); transform: scale(0.98); }
  100% { opacity: 1; filter: blur(12px); transform: scale(1.05); }
}

/* ====================================================
   HIỆU ỨNG CROWN ĐỘI NGHIÊNG NHẤP NHÁY
==================================================== */
.crown-icon {
  position: absolute;
  top: -12px;
  right: -6px;
  z-index: 10;
  animation: floatCrown 1.5s infinite alternate ease-in-out;
}
.crown-icon svg { width: 26px; height: 26px; fill: #ffc107; }

/* Mobile trigger */
.crown-sm { top: -8px; right: -5px; }
.crown-sm svg { width: 20px; height: 20px; }

/* Mobile Panel lớn */
.crown-lg { top: -14px; right: -8px; }
.crown-lg svg { width: 34px; height: 34px; }

@keyframes floatCrown {
  0% { 
    transform: translateY(0) rotate(25deg); 
    filter: drop-shadow(0 2px 4px rgba(255, 193, 7, 0.4)) brightness(1); 
  }
  100% { 
    transform: translateY(-4px) rotate(15deg); 
    filter: drop-shadow(0 0 12px rgba(255, 193, 7, 0.9)) brightness(1.3); 
  }
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