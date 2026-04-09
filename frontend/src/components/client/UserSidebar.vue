<!-- File: frontend/src/components/client/UserSidebar.vue -->
<template>
  <div class="sticky-top" style="top: 100px;">
    <!-- Thông tin tóm tắt User -->
    <div class="d-flex align-items-center mb-4 pb-4 border-bottom dark:border-gray-700">
      <img :src="currentUser.avatar" class="rounded-circle object-fit-cover shadow-sm border border-2 border-white dark:border-gray-600 me-3" style="width: 55px; height: 55px;">
      <div class="overflow-hidden">
        <h6 class="fw-bold text-c-dark dark:text-white mb-1 text-truncate">{{ currentUser.name }}</h6>
        <span class="badge bg-warning text-dark fw-bold shadow-sm d-inline-flex align-items-center">
          <i class="bi bi-star-fill me-1" style="font-size: 0.65rem;"></i> Hạng Vàng
        </span>
      </div>
    </div>

    <!-- Menu Điều Hướng (Tự động bôi đậm thẻ Active dựa theo URL) -->
    <div class="user-nav-menu custom-scrollbar-x pb-2 pb-lg-0">
      <ul class="nav flex-row flex-lg-column gap-2 flex-nowrap">
        
        <li class="nav-item flex-shrink-0" v-for="(item, index) in menuItems" :key="index">
          <router-link :to="item.path" 
                       class="nav-link px-3 py-2.5 rounded-3 fw-semibold transition-all d-flex align-items-center"
                       :class="isActive(item.path) ? 'active-nav' : 'text-muted dark:text-gray-400 hover-nav'">
            <i class="bi fs-5 me-2 w-20px text-center" :class="item.icon"></i> {{ item.name }}
          </router-link>
        </li>
        
        <li class="nav-item flex-shrink-0 mt-lg-3 pt-lg-3 border-top-lg dark:border-gray-700">
          <a href="#" @click.prevent="handleLogout" class="nav-link px-3 py-2.5 rounded-3 fw-bold text-danger hover-danger-nav transition-all d-flex align-items-center">
            <i class="bi bi-box-arrow-right fs-5 me-2 w-20px text-center"></i> Đăng xuất
          </a>
        </li>

      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();

// Mock User Data (Thực tế lấy từ Vuex / Pinia / LocalStorage)
const currentUser = ref({
  name: 'Alex Nguyễn',
  avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=150&auto=format&fit=crop'
});

// Danh sách các trang trong Sidebar
const menuItems = ref([
  { name: 'Hồ sơ của tôi', path: '/user/profile', icon: 'bi-person-circle' },
  { name: 'Đơn mua hàng', path: '/user/orders', icon: 'bi-receipt' },
  { name: 'Sản phẩm yêu thích', path: '/user/wishlist', icon: 'bi-heart' },
  { name: 'Sổ địa chỉ', path: '/user/address', icon: 'bi-geo-alt' },
  { name: 'Đổi mật khẩu', path: '/user/password', icon: 'bi-shield-lock' }
]);

// Hàm check Active Route
const isActive = (path) => {
  return route.path.startsWith(path);
};

// Hàm đăng xuất
const handleLogout = () => {
  Swal.fire({
    title: 'Xác nhận đăng xuất?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: 'var(--color-c-dark)',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đăng xuất',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      // Thực hiện logic logout tại đây
      router.push('/login');
    }
  });
};
</script>

<style scoped>
.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }

/* SIDEBAR MENU NAV */
.user-nav-menu .nav-link { color: #6c757d; border: 1px solid transparent; }
html.dark .user-nav-menu .nav-link { color: #adb5bd; }

.user-nav-menu .hover-nav:hover { background-color: var(--color-c-effect); color: var(--color-c-hover) !important; transform: translateX(4px); }
html.dark .user-nav-menu .hover-nav:hover { background-color: rgba(255,255,255,0.05); }

.user-nav-menu .hover-danger-nav:hover { background-color: rgba(220, 53, 69, 0.1); transform: translateX(4px); }

.user-nav-menu .active-nav { background-color: var(--color-c-effect); color: var(--color-c-hover) !important; border-left: 3px solid var(--color-c-hover) !important; }
html.dark .user-nav-menu .active-nav { background-color: rgba(255,255,255,0.05); }

/* UTILS */
.w-20px { width: 22px; display: inline-block; }
.transition-all { transition: all 0.3s ease; }

/* Scrollbar Mobile */
.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

@media (min-width: 992px) {
  .border-top-lg { border-top: 1px solid #dee2e6; }
  html.dark .border-top-lg { border-color: #373b3e !important; }
}
</style>