<template>
  <aside class="main-sidebar sidebar-dark-primary d-flex flex-column shadow-lg position-relative"
    :style="{ width: isCollapsed ? '80px' : '260px', backgroundColor: 'var(--color-c-dark)', minHeight: '100vh', transition: 'width 0.3s ease' }">
    <!-- Nút Toggle thu gọn Desktop -->
    <button class="btn shadow-sm toggle-sidebar-btn d-none d-md-flex align-items-center justify-content-center"
      :class="isCollapsed ? 'btn-primary' : 'btn-dark'" @click="toggleSidebar">
      <i class="bi fw-bold" :class="isCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
    </button>

    <!-- Logo Brand -->
    <router-link to="/admin/dashboard"
      class="brand-link text-decoration-none text-white p-3 border-bottom border-secondary d-flex align-items-center"
      :class="isCollapsed ? 'justify-content-center' : ''"
      style="border-color: rgba(255,255,255,0.1) !important; height: 60px; overflow: hidden;">
      <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm flex-shrink-0"
        :class="isCollapsed ? '' : 'me-3'" style="width: 38px; height: 38px;">
        <!-- Màu logo dùng --color-c-hover -->
        <i class="bi bi-layers-fill fs-5" style="color: var(--color-c-hover);"></i>
      </div>
      <span class="brand-text fw-bold fs-5 tracking-wide text-nowrap transition-all" v-show="!isCollapsed"
        style="letter-spacing: 1px;">
        ZYRO
      </span>
    </router-link>

    <!-- Menu Navigation -->
    <div class="sidebar flex-grow-1 overflow-auto custom-scrollbar" :class="isCollapsed ? 'p-2' : 'p-3'">

      <!-- Shimmer Loading khi đang tải quyền -->
      <div v-if="isLoading" class="text-center text-white-50 mt-4">
        <div class="spinner-border spinner-border-sm mb-2 text-urban" role="status"></div>
        <p class="small" v-show="!isCollapsed">Đang tải cấu hình...</p>
      </div>

      <nav class="mt-2" v-else>
        <ul class="nav nav-pills nav-sidebar flex-column gap-2" role="menu">

          <template v-for="(item, index) in menuItems" :key="index">
            <!-- Menu Không có Menu con -->
            <li class="nav-item position-relative" v-if="!item.children">

              <!-- Badge hiển thị Cấp độ yêu cầu -->
              <span v-if="getModuleLevel(item.moduleCode) && !isCollapsed"
                class="position-absolute badge rounded-pill shadow-sm level-badge"
                :class="hasAccess(item.moduleCode) ? 'bg-success' : 'bg-danger'">
                Cấp {{ getModuleLevel(item.moduleCode) }}
              </span>

              <!-- Trường hợp: CÓ QUYỀN TRUY CẬP -->
              <router-link v-if="hasAccess(item.moduleCode)" :to="item.path"
                :active-class="item.path === '/admin/dashboard' ? 'ignore-active' : 'router-link-active'"
                class="nav-link text-white py-2 rounded shadow-sm-hover transition-all d-flex align-items-center"
                :class="isCollapsed ? 'justify-content-center px-0' : 'px-3'" :title="isCollapsed ? item.name : ''">
                <i class="nav-icon bi" :class="[item.icon, isCollapsed ? 'fs-5' : 'me-3']"></i>
                <p class="m-0 fw-semibold text-nowrap" v-show="!isCollapsed">{{ item.name }}</p>
              </router-link>

              <!-- Trường hợp: KHÔNG CÓ QUYỀN (Hiển thị Khóa) -->
              <div v-else class="nav-link py-2 rounded disabled-menu d-flex align-items-center"
                :class="isCollapsed ? 'justify-content-center px-0' : 'px-3'"
                :title="isCollapsed ? item.name + ' (Khóa)' : ''"
                @click="showAccessDenied(item.name, getModuleLevel(item.moduleCode))">
                <i class="nav-icon bi" :class="[item.icon, isCollapsed ? 'fs-5' : 'me-3']"></i>
                <p class="m-0 fw-semibold text-nowrap" v-show="!isCollapsed">{{ item.name }}</p>
                <i class="bi bi-lock-fill opacity-50"
                  :class="isCollapsed ? 'position-absolute top-0 start-100 translate-middle' : 'ms-auto'"></i>
              </div>
            </li>

            <!-- Menu Có Menu con -->
            <li class="nav-item mt-2 rounded shadow-sm position-relative transition-all"
              :class="[menuState[item.stateKey] && !isCollapsed ? 'menu-open group-active-bg' : '']" v-else>
              <a href="#" class="nav-link text-white py-2 rounded d-flex align-items-center transition-all"
                :class="[isCollapsed ? 'justify-content-center px-0' : 'justify-content-between px-3', { 'active-group': menuState[item.stateKey] && !isCollapsed }]"
                :title="isCollapsed ? item.name : ''" @click.prevent="handleDropdownClick(item)">
                <div class="d-flex align-items-center" :class="{ 'justify-content-center w-100': isCollapsed }">
                  <i class="nav-icon bi" :class="[item.icon, isCollapsed ? 'fs-5' : 'me-3']"></i>
                  <p class="m-0 fw-semibold text-nowrap" v-show="!isCollapsed">{{ item.name }}</p>
                </div>
                <i class="bi bi-chevron-left transition-icon" v-show="!isCollapsed"
                  :class="{ 'rotate-180': menuState[item.stateKey] }"></i>
              </a>

              <ul class="nav nav-treeview flex-column p-2 pt-1 gap-1" v-show="menuState[item.stateKey] && !isCollapsed"
                style="background-color: rgba(0,0,0,0.15); border-radius: 0 0 8px 8px;">
                <li class="nav-item position-relative" v-for="(subItem, subIndex) in item.children" :key="subIndex">

                  <!-- Badge hiển thị Cấp độ yêu cầu (Sub-menu) -->
                  <span v-if="getModuleLevel(subItem.moduleCode)"
                    class="position-absolute badge rounded-pill shadow-sm level-badge-sub"
                    :class="hasAccess(subItem.moduleCode) ? 'bg-success opacity-75' : 'bg-danger opacity-75'">
                    Cấp {{ getModuleLevel(subItem.moduleCode) }}
                  </span>

                  <!-- Trường hợp: CÓ QUYỀN TRUY CẬP (Sub-menu) -->
                  <router-link v-if="hasAccess(subItem.moduleCode)" :to="subItem.path"
                    class="nav-link text-white-50 py-2 px-3 rounded sub-link d-flex align-items-center">
                    <i class="bi bi-circle-fill fs-xs me-3 opacity-50" style="font-size: 6px;"></i>
                    <p class="m-0 fw-medium text-nowrap">{{ subItem.name }}</p>
                  </router-link>

                  <!-- Trường hợp: KHÔNG CÓ QUYỀN (Sub-menu Khóa) -->
                  <div v-else
                    class="nav-link text-white-50 py-2 px-3 rounded sub-link d-flex align-items-center disabled-menu"
                    @click="showAccessDenied(subItem.name, getModuleLevel(subItem.moduleCode))">
                    <i class="bi bi-lock-fill fs-xs me-3 opacity-50" style="font-size: 10px;"></i>
                    <p class="m-0 fw-medium text-nowrap">{{ subItem.name }}</p>
                  </div>
                </li>
              </ul>
            </li>
          </template>

        </ul>
      </nav>
    </div>
  </aside>
</template>

<script setup>
// Đã thêm onUnmounted để dọn dẹp kết nối
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const emit = defineEmits(['toggle-collapse']);

const route = useRoute();
const isLoading = ref(true);
const systemModules = ref([]);
const isCollapsed = ref(false);

const userLevel = computed(() => {
  try {
    const info = JSON.parse(localStorage.getItem('admin_info') || '{}');
    return info.role?.level || 999;
  } catch (e) {
    return 999;
  }
});

const menuItems = ref([
  { name: 'Tổng quan', path: '/admin/dashboard', icon: 'bi-grid-1x2-fill', moduleCode: 'admin_dashboard' },
  { name: 'Phân Quyền', path: '/admin/roles', icon: 'bi-shield-fill-check', moduleCode: 'admin_roles' },
  {
    name: 'Tài khoản', icon: 'bi-person-circle', stateKey: 'accounts',
    children: [
      { name: 'Nội bộ', path: '/admin/admins', moduleCode: 'admin_staff' },
      { name: 'Khách hàng', path: '/admin/users', moduleCode: 'admin_users' },
      { name: 'Hạng thành viên', path: '/admin/tiers', moduleCode: 'admin_tiers' }
    ]
  },
  {
    name: 'Sản phẩm', icon: 'bi-box-seam', stateKey: 'products',
    children: [
      { name: 'Danh mục', path: '/admin/categories', moduleCode: 'admin_categories' },
      { name: 'Thương hiệu', path: '/admin/brands', moduleCode: 'admin_brands' },
      { name: 'SP & Biến thể', path: '/admin/products', moduleCode: 'admin_products' },
      { name: 'Lookbook', path: '/admin/lookbooks', moduleCode: 'admin_lookbooks' },
      { name: 'Flash Sale', path: '/admin/flash-sales', moduleCode: 'admin_flash_sales' },
      { name: 'Kho hàng', path: '/admin/inventory', moduleCode: 'admin_inventory' }

    ]
  },
  {
    name: 'Đơn hàng', icon: 'bi-receipt-cutoff', stateKey: 'orders',
    children: [
      { name: 'Danh sách đơn', path: '/admin/orders', moduleCode: 'admin_orders' },
      { name: 'Trả hàng', path: '/admin/returns', moduleCode: 'admin_orders' },
      { name: 'Đánh giá', path: '/admin/reviews', moduleCode: 'admin_reviews' }
    ]
  },
  {
    name: 'Marketing', icon: 'bi-megaphone-fill', stateKey: 'marketing',
    children: [
      { name: 'Voucher', path: '/admin/vouchers', moduleCode: 'admin_vouchers' },
      { name: 'Banner', path: '/admin/banners', moduleCode: 'admin_banners' },
      { name: 'Lượt thích', path: '/admin/wishlists', moduleCode: 'admin_wishlists' },
      { name: 'Liên hệ (contact)', path: '/admin/contacts', moduleCode: 'admin_contacts' }
    ]
  },
  // chat bot và chat reaitilme nằm chung 1 cha
  {
    name: 'Chat', icon: 'bi-chat-dots-fill', stateKey: 'chat',
    children: [
      { name: 'Hỗ trợ trực tuyến', path: '/admin/chats', moduleCode: 'admin_chats' },
      { name: 'Chatbot AI', path: '/admin/chatbot', moduleCode: 'admin_chatbot' }
    ]
  },
  
]);

const menuState = reactive({
  products: false, orders: false
});

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);

  if (isCollapsed.value) {
    Object.keys(menuState).forEach(key => menuState[key] = false);
  }
};

const handleDropdownClick = (item) => {
  if (isCollapsed.value) {
    isCollapsed.value = false;
    emit('toggle-collapse', false);
    setTimeout(() => {
      menuState[item.stateKey] = true;
    }, 250);
  } else {
    menuState[item.stateKey] = !menuState[item.stateKey];
  }
};

// ================= LOGIC PHÂN QUYỀN =================
const getHeaders = () => ({
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const fetchSidebarData = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
    systemModules.value = res.data.data || [];
  } catch (err) {
    console.error("Lỗi tải dữ liệu cấu hình phân quyền Sidebar", err);
  } finally {
    isLoading.value = false;
  }
};

const getModuleLevel = (code) => {
  if (!code) return null;
  const mod = systemModules.value.find(m => m.module_code === code);
  return mod ? mod.required_level : null;
};

const hasAccess = (code) => {
  if (!code) return true;
  const requiredLevel = getModuleLevel(code);
  if (!requiredLevel) return false; // Nếu không tìm thấy cấu hình DB -> Khóa an toàn
  return userLevel.value <= requiredLevel; // Số Level càng NHỎ quyền càng LỚN
};

const showAccessDenied = (menuName, reqLevel) => {
  Swal.fire({
    toast: true, position: 'top-end', icon: 'error',
    title: 'Truy cập bị từ chối!',
    text: `Tính năng "${menuName}" yêu cầu Cấp ${reqLevel}. Bạn đang ở Cấp ${userLevel.value}.`,
    showConfirmButton: false, timer: 4000, timerProgressBar: true,
  });
};

// ================= LẮNG NGHE REAL-TIME =================
const setupRealtime = () => {
  if (window.Echo) {
    // Lắng nghe kênh 'admin.modules'
    window.Echo.private('admin.modules')
      .listen('.ModuleEvent', () => {
        // Chỉ cần gọi lại API là Sidebar tự cập nhật lại badge Cấp độ
        fetchSidebarData();
      });
  }
};

onMounted(() => {
  fetchSidebarData();
  setupRealtime(); // Kích hoạt Real-time

  // Tự động bung menu con nếu đang ở URL đó
  const currentPath = route.path;
  menuItems.value.forEach(item => {
    if (item.children) {
      const isChildActive = item.children.some(subItem => {
        return currentPath === subItem.path || currentPath.startsWith(subItem.path + '/');
      });
      if (isChildActive) {
        menuState[item.stateKey] = true;
      }
    }
  });
});

// Nhớ ngắt kết nối khi chuyển Layout (dọn dẹp bộ nhớ)
onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leave('admin.modules');
  }
});
</script>

<style scoped>
/* Scrollbar custom cho Sidebar */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background-color: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

.nav-link {
  transition: all 0.2s ease;
  overflow: hidden;
}

.shadow-sm-hover:hover {
  background-color: rgba(255, 255, 255, 0.08);
  transform: translateX(3px);
}

.group-active-bg {
  background-color: rgba(0, 0, 0, 0.2) !important;
}

/* Trạng thái Khóa (Disabled) */
.disabled-menu {
  background-color: rgba(0, 0, 0, 0.2) !important;
  color: #6c757d !important;
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(100%);
}

.disabled-menu:hover {
  background-color: rgba(0, 0, 0, 0.3) !important;
  color: #dc3545 !important;
  /* Hiện đỏ khi cố click */
}

/* Badge Cấp độ (Main menu) */
.level-badge {
  top: 6px;
  right: 8px;
  font-size: 0.65rem;
  padding: 3px 6px;
  z-index: 2;
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* Badge Cấp độ (Sub menu) */
.level-badge-sub {
  top: 8px;
  right: 12px;
  font-size: 0.6rem;
  padding: 2px 5px;
  z-index: 2;
}

/* Các Sub-link */
.sub-link {
  transition: all 0.2s ease;
}

.sub-link:hover:not(.disabled-menu) {
  background-color: rgba(84, 119, 146, 0.1) !important;
  color: var(--color-c-light) !important;
  transform: translateX(3px);
}

.sub-link:hover:not(.disabled-menu) i {
  color: var(--color-c-light) !important;
}

/* Trạng thái Active */
.active-group {
  background-color: var(--color-c-hover) !important;
  color: #fff !important;
  box-shadow: 0 4px 10px rgba(84, 119, 146, 0.3);
}

.router-link-active,
.router-link-exact-active {
  background-color: var(--color-c-hover) !important;
  color: #fff !important;
  box-shadow: 0 4px 10px rgba(84, 119, 146, 0.3);
}

.sub-link.router-link-active {
  background-color: rgba(84, 119, 146, 0.15) !important;
  color: var(--color-c-light) !important;
  box-shadow: none;
  font-weight: 600;
}

.sub-link.router-link-active i {
  color: var(--color-c-light) !important;
  opacity: 1 !important;
}

.transition-icon {
  transition: transform 0.3s ease;
  font-size: 12px;
  opacity: 0.8;
}

.rotate-180 {
  transform: rotate(-90deg);
}

.transition-all {
  transition: all 0.3s ease;
}

.text-urban {
  color: var(--color-c-light) !important;
}
</style>