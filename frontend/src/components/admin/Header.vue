<template>
  <!-- Thêm class động để tự động đổi màu nền Header khi bật Dark Mode -->
  <nav class="app-header navbar navbar-expand shadow-sm px-3 py-2 border-bottom transition-all"
    :class="isDarkMode ? 'bg-dark border-secondary' : 'bg-white'">
    <div class="container-fluid">

      <ul class="navbar-nav ms-auto align-items-center">
        <!-- NÚT TOGGLE DARK MODE -->
        <li class="nav-item me-3" v-if="isLoggedIn">
          <button @click="toggleTheme"
            class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0 theme-toggle-btn"
            :class="isDarkMode ? 'btn-secondary border-secondary' : 'btn-light border-light'"
            style="width: 36px; height: 36px; transition: all 0.3s;"
            :title="isDarkMode ? 'Chuyển sang nền sáng' : 'Chuyển sang nền tối'">
            <i class="bi"
              :class="isDarkMode ? 'bi-moon-stars-fill text-light fs-6' : 'bi-sun-fill text-warning fs-5'"></i>
          </button>
        </li>

        <!-- ========================================== -->
        <!-- NÚT CHUÔNG THÔNG BÁO REAL-TIME (MỚI THÊM)  -->
        <!-- ========================================== -->
        <li class="nav-item dropdown me-3 notification-menu-container" ref="notiMenuContainer" v-if="isLoggedIn">
          <button @click="toggleNotiMenu"
            class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0 theme-toggle-btn position-relative"
            :class="isDarkMode ? 'btn-secondary border-secondary' : 'btn-light border-light'"
            style="width: 36px; height: 36px; transition: all 0.3s;"
            title="Thông báo hệ thống">
            <i class="bi bi-bell-fill" :class="isDarkMode ? 'text-light' : 'text-brand'"></i>
            <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white dark:border-dark" style="font-size: 0.65rem; padding: 0.35em 0.5em;">
              {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
          </button>

          <div class="dropdown-menu dropdown-menu-end shadow-lg border mt-2 transition-all p-0 overflow-hidden"
            :class="[{ 'show': isNotiMenuActive }, isDarkMode ? 'bg-dark border-secondary' : 'bg-white border-0']"
            style="width: 350px; right: -10px !important;">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" :class="isDarkMode ? 'border-secondary' : ''">
               <h6 class="m-0 fw-bold font-sans-vn" :class="isDarkMode ? 'text-white' : 'text-dark'">Thông báo mới</h6>
               <a href="#" v-if="unreadCount > 0" @click.prevent="markAllAsRead" class="small fw-semibold text-decoration-none text-brand transition-all hover-opacity">Đánh dấu đọc hết</a>
            </div>
            
            <div class="custom-scrollbar-y" style="max-height: 400px; overflow-y: auto;">
               <div v-if="isLoadingNoti" class="text-center p-4">
                  <div class="spinner-border spinner-border-sm text-brand" role="status"></div>
               </div>
               <div v-else-if="notifications.length === 0" class="text-center p-5 text-muted small font-sans-vn">
                  <i class="bi bi-bell-slash fs-2 d-block mb-2 opacity-50"></i>
                  Bạn đã xem hết thông báo.
               </div>
               <div v-else>
                  <a href="#" v-for="noti in notifications" :key="noti.id" 
                     @click.prevent="handleNotiClick(noti)"
                     class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3 transition-all text-wrap"
                     :class="[
                        isDarkMode ? 'border-secondary hover-dark text-light' : 'text-dark hover-light',
                        !noti.read_at ? (isDarkMode ? 'bg-secondary bg-opacity-25' : 'bg-brand-soft') : ''
                     ]">
                     <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1 shadow-sm"
                          :class="getIconClass(noti.data?.type)" style="width: 38px; height: 38px;">
                        <i class="bi text-white fs-6" :class="getIconName(noti.data?.type)"></i>
                     </div>
                     <div class="font-sans-vn">
                        <h6 class="fw-bold mb-1 line-clamp-2" style="font-size: 0.9rem;" :class="!noti.read_at ? (isDarkMode ? 'text-white' : 'text-dark') : 'text-muted'">
                           {{ noti.data?.title }}
                        </h6>
                        <p class="small mb-1 opacity-75 line-clamp-2" style="font-size: 0.8rem; line-height: 1.4;">{{ noti.data?.message }}</p>
                        <small class="text-muted font-monospace" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>{{ formatTime(noti.created_at) }}</small>
                     </div>
                  </a>
               </div>
            </div>
          </div>
        </li>

        <!-- Trường hợp 1: Đã đăng nhập - Hiển thị Menu User -->
        <li v-if="isLoggedIn" class="nav-item dropdown user-menu-container" ref="userMenuContainer">
          <a href="#" @click.prevent="toggleUserMenu"
            class="nav-link d-flex align-items-center dropdown-toggle text-decoration-none"
            :class="isDarkMode ? 'text-light' : 'text-dark'">
            <img :src="adminUser.avatar" class="user-image rounded-circle shadow-sm me-2" alt="User Image">
            <span class="d-none d-md-inline fw-semibold text-truncate font-sans-vn" style="max-width: 150px;">{{ adminUser.name }}</span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end shadow border mt-2 transition-all font-sans-vn"
            :class="[{ 'show': isUserMenuActive }, isDarkMode ? 'bg-dark border-secondary' : 'bg-white border-0']">
            <li class="user-header-modern text-white text-center p-3 rounded-top">
              <img :src="adminUser.avatar" class="rounded-circle d-block mx-auto mt-2 mb-2 shadow"
                style="width: 60px; height: 60px; object-fit: cover;" alt="User Image">
              <p class="mb-0 fw-bold">{{ adminUser.name }}</p>
              <small class="text-light opacity-75">{{ adminUser.roleName }}</small>
            </li>

            <li class="mt-2">
              <router-link :to="{ name: 'admin-profile' }" class="dropdown-item py-2 fw-medium"
                :class="isDarkMode ? 'text-light hover-dark' : 'hover-light'">
                <i class="bi bi-person me-2"></i> Hồ sơ cá nhân
              </router-link>
            </li>
            <li>
              <hr class="dropdown-divider" :class="isDarkMode ? 'border-secondary' : ''">
            </li>
            <li>
              <a href="#" @click.prevent="handleLogout" class="dropdown-item py-2 fw-bold"
                :class="isDarkMode ? 'text-danger hover-dark' : 'text-danger hover-light'">
                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
              </a>
            </li>
          </ul>
        </li>

        <!-- Trường hợp 2: Chưa đăng nhập - Hiển thị nút Đăng nhập -->
        <li v-else class="nav-item">
          <router-link :to="{ name: 'admin-login' }" class="btn btn-brand-outline px-3 py-1 fw-bold font-sans-vn">
            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
          </router-link>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

import defaultAvatar from '../../assets/images/defaults/avatar1.png';

const router = useRouter();
const isUserMenuActive = ref(false);
const userMenuContainer = ref(null);

// LOGIC DARK MODE
const isDarkMode = ref(false);

const initTheme = () => {
  const savedTheme = localStorage.getItem('admin_theme');
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDarkMode.value = true;
    document.documentElement.setAttribute('data-bs-theme', 'dark');
    document.body.classList.add('dark-mode');
  } else {
    isDarkMode.value = false;
    document.documentElement.setAttribute('data-bs-theme', 'light');
    document.body.classList.remove('dark-mode');
  }
};

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  if (isDarkMode.value) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
    document.body.classList.add('dark-mode');
    localStorage.setItem('admin_theme', 'dark');
  } else {
    document.documentElement.setAttribute('data-bs-theme', 'light');
    document.body.classList.remove('dark-mode');
    localStorage.setItem('admin_theme', 'light');
  }
};

const isLoggedIn = computed(() => {
  return !!localStorage.getItem('admin_token');
});

const getAdminData = () => {
  const savedInfo = localStorage.getItem('admin_info');
  const roleId = localStorage.getItem('admin_role');

  if (savedInfo) {
    const admin = JSON.parse(savedInfo);
    return {
      id: admin.id,
      name: admin.fullname || 'Quản trị viên',
      roleName: roleId == 1 ? 'Super Admin' : 'Nhân viên',
      avatar: admin.avatar_url ? `http://127.0.0.1:8000/storage/${admin.avatar_url}` : defaultAvatar
    };
  }

  return { name: 'Guest', roleName: 'Chưa xác định', avatar: defaultAvatar };
};

const adminUser = ref(getAdminData());

// ==========================================
// LOGIC THÔNG BÁO (NOTIFICATIONS)
// ==========================================
const notifications = ref([]);
const unreadCount = ref(0);
const isLoadingNoti = ref(false);
const isNotiMenuActive = ref(false);
const notiMenuContainer = ref(null);

const toggleUserMenu = () => {
  isUserMenuActive.value = !isUserMenuActive.value;
  isNotiMenuActive.value = false;
};

const toggleNotiMenu = () => {
  isNotiMenuActive.value = !isNotiMenuActive.value;
  isUserMenuActive.value = false;
  if (isNotiMenuActive.value && notifications.value.length === 0) {
    fetchNotifications();
  }
};

const getHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const fetchNotifications = async () => {
  isLoadingNoti.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/notifications`, { headers: getHeaders() });
    if (res.data.success) {
      notifications.value = res.data.data.data;
      unreadCount.value = res.data.unread_count;
    }
  } catch (err) {
    console.error("Lỗi lấy danh sách thông báo:", err);
  } finally {
    isLoadingNoti.value = false;
  }
};

const markAsRead = async (noti) => {
  if (noti.read_at) return;
  try {
    const res = await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/notifications/${noti.id}/read`, {}, { headers: getHeaders() });
    if (res.data.success) {
      noti.read_at = new Date().toISOString();
      unreadCount.value = res.data.unread_count;
    }
  } catch (err) {
    console.error(err);
  }
};

const markAllAsRead = async () => {
  try {
    const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/notifications/mark-all-read`, {}, { headers: getHeaders() });
    if (res.data.success) {
      notifications.value.forEach(n => n.read_at = new Date().toISOString());
      unreadCount.value = 0;
    }
  } catch (err) {
    console.error(err);
  }
};

const handleNotiClick = (noti) => {
  markAsRead(noti);
  isNotiMenuActive.value = false;
  if (noti.data?.url && noti.data.url !== '#') {
    router.push(noti.data.url);
  }
};

// Khởi tạo Echo để nhận dữ liệu Real-time
const setupEcho = () => {
  const user = getAdminData();
  if (user && user.id && window.Echo) {
    window.Echo.private(`App.Models.Admin.${user.id}`)
      .listen('.AdminAlert', (e) => {
         // Chèn thông báo lên đầu danh sách
         notifications.value.unshift({
           id: e.id,
           data: e.data,
           read_at: null,
           created_at: e.created_at
         });
         unreadCount.value++;

         // Bắn Toast SweetAlert thông báo nhanh
         Swal.fire({
           toast: true,
           position: 'top-end',
           icon: e.data.type === 'danger' ? 'error' : (e.data.type || 'info'),
           title: e.data.title,
           text: e.data.message,
           showConfirmButton: false,
           timer: 5000,
           timerProgressBar: true
         });
      });
  }
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} lúc ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getIconClass = (type) => {
  const map = { 'success': 'bg-success', 'warning': 'bg-warning', 'danger': 'bg-danger', 'info': 'bg-info' };
  return map[type] || 'bg-primary';
};

const getIconName = (type) => {
  const map = { 'success': 'bi-check-circle', 'warning': 'bi-exclamation-triangle', 'danger': 'bi-x-circle', 'info': 'bi-info-circle' };
  return map[type] || 'bi-bell';
};

// ==========================================
// ĐĂNG XUẤT VÀ ĐÓNG MENU CHUNG
// ==========================================
const handleLogout = () => {
  Swal.fire({
    title: 'Xác nhận đăng xuất?',
    text: "Bạn sẽ phải đăng nhập lại để tiếp tục quản trị!",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#009981',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đăng xuất ngay',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('admin_token');
      localStorage.removeItem('admin_role');
      localStorage.removeItem('admin_info');

      Swal.fire({ icon: 'success', title: 'Đã đăng xuất', timer: 1000, showConfirmButton: false }).then(() => {
        router.push({ name: 'admin-login' });
      });
    }
  });
};

const closeMenus = (event) => {
  if (userMenuContainer.value && !userMenuContainer.value.contains(event.target)) {
    isUserMenuActive.value = false;
  }
  if (notiMenuContainer.value && !notiMenuContainer.value.contains(event.target)) {
    isNotiMenuActive.value = false;
  }
};

onMounted(() => {
  initTheme();
  document.addEventListener('click', closeMenus);
  if (isLoggedIn.value) {
    fetchNotifications();
    setupEcho();
  }
});

onUnmounted(() => {
  document.removeEventListener('click', closeMenus);
  const user = getAdminData();
  if (user && user.id && window.Echo) {
     window.Echo.leave(`App.Models.Admin.${user.id}`);
  }
});
</script>

<style scoped>
.app-header { min-height: 60px; z-index: 1000; }
.transition-all { transition: all 0.3s ease; }
.theme-toggle-btn:hover { transform: rotate(15deg) scale(1.1); }

.text-brand { color: #009981 !important; }
.bg-brand-soft { background-color: rgba(0, 153, 129, 0.08) !important; }

.user-image { width: 36px; height: 36px; transition: transform 0.2s; object-fit: cover; }
.nav-link:hover .user-image { transform: scale(1.05); }

.btn-brand-outline { border: 1.5px solid #009981; color: #009981; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
.btn-brand-outline:hover { background-color: #009981; color: #fff; }

.dropdown-menu { border-radius: 12px; animation: slideInUp 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); transform-origin: top right; right: 0 !important; display: none; }
.dropdown-menu.show { display: block; }

.user-header-modern { background: var(--color-c-dark, #213448) !important; margin-top: -8px; }

.dropdown-item { font-size: 14px; transition: background-color 0.2s, color 0.2s; }
.hover-light:hover { background-color: #f8f9fa; color: #009981 !important; }
.hover-dark:hover { background-color: #343a40 !important; color: #00ebc4 !important; }
.hover-opacity:hover { opacity: 0.7; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
html.dark .custom-scrollbar-y::-webkit-scrollbar-thumb { background: #495057; }

@keyframes slideInUp {
  from { opacity: 0; transform: translateY(10px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>