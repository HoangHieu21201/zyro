<!-- File: frontend/src/components/admin/Sidebar.vue -->
<template>
  <aside 
    class="main-sidebar sidebar-dark-primary d-flex flex-column shadow-lg position-relative"
    :style="{ width: isCollapsed ? '80px' : '260px', backgroundColor: 'var(--color-c-dark)', minHeight: '100vh', transition: 'width 0.3s ease' }"
  >
    <!-- Nút Toggle thu gọn Desktop -->
    <button class="btn shadow-sm toggle-sidebar-btn d-none d-md-flex align-items-center justify-content-center"
      :class="isCollapsed ? 'btn-primary' : 'btn-dark'" @click="toggleSidebar">
      <i class="bi fw-bold" :class="isCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
    </button>

    <!-- Logo Brand -->
    <router-link to="/admin"
      class="brand-link text-decoration-none text-white p-3 border-bottom border-secondary d-flex align-items-center"
      :class="isCollapsed ? 'justify-content-center' : ''"
      style="border-color: rgba(255,255,255,0.1) !important; height: 60px; overflow: hidden;">
      <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm flex-shrink-0"
        :class="isCollapsed ? '' : 'me-3'" style="width: 38px; height: 38px;">
        <!-- Màu logo dùng --color-c-hover -->
        <i class="bi bi-layers-fill fs-5" style="color: var(--color-c-hover);"></i>
      </div>
      <span class="brand-text fw-bold fs-5 tracking-wide text-nowrap transition-all" v-show="!isCollapsed" style="letter-spacing: 1px;">
        ZYRO
      </span>
    </router-link>

    <!-- Menu Navigation -->
    <div class="sidebar flex-grow-1 overflow-auto custom-scrollbar" :class="isCollapsed ? 'p-2' : 'p-3'">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column gap-2" role="menu">

          <template v-for="(item, index) in menuItems" :key="index">
            <!-- Menu Không có Menu con -->
            <li class="nav-item position-relative" v-if="!item.children">
              <router-link :to="item.path"
                :active-class="item.path === '/admin' ? 'ignore-active' : 'router-link-active'"
                class="nav-link text-white py-2 rounded shadow-sm-hover transition-all d-flex align-items-center"
                :class="isCollapsed ? 'justify-content-center px-0' : 'px-3'" 
                :title="isCollapsed ? item.name : ''">
                <i class="nav-icon bi" :class="[item.icon, isCollapsed ? 'fs-5' : 'me-3']"></i>
                <p class="m-0 fw-semibold text-nowrap" v-show="!isCollapsed">{{ item.name }}</p>
              </router-link>
            </li>

            <!-- Menu Có Menu con -->
            <li class="nav-item mt-2 rounded shadow-sm position-relative transition-all"
              :class="[menuState[item.stateKey] && !isCollapsed ? 'menu-open group-active-bg' : '']" v-else>
              <a href="#" class="nav-link text-white py-2 rounded d-flex align-items-center transition-all"
                :class="[isCollapsed ? 'justify-content-center px-0' : 'justify-content-between px-3', { 'active-group': menuState[item.stateKey] && !isCollapsed }]"
                :title="isCollapsed ? item.name : ''" 
                @click.prevent="handleDropdownClick(item)">
                <div class="d-flex align-items-center" :class="{ 'justify-content-center w-100': isCollapsed }">
                  <i class="nav-icon bi" :class="[item.icon, isCollapsed ? 'fs-5' : 'me-3']"></i>
                  <p class="m-0 fw-semibold text-nowrap" v-show="!isCollapsed">{{ item.name }}</p>
                </div>
                <i class="bi bi-chevron-left transition-icon" v-show="!isCollapsed" :class="{ 'rotate-180': menuState[item.stateKey] }"></i>
              </a>

              <ul class="nav nav-treeview flex-column p-2 pt-1 gap-1" v-show="menuState[item.stateKey] && !isCollapsed"
                style="background-color: rgba(0,0,0,0.15); border-radius: 0 0 8px 8px;">
                <li class="nav-item position-relative" v-for="(subItem, subIndex) in item.children" :key="subIndex">
                  <router-link :to="subItem.path"
                    class="nav-link text-white-50 py-2 px-3 rounded sub-link d-flex align-items-center">
                    <i class="bi bi-circle-fill fs-xs me-3 opacity-50" style="font-size: 6px;"></i>
                    <p class="m-0 fw-medium text-nowrap">{{ subItem.name }}</p>
                  </router-link>
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
import { ref, reactive } from 'vue';

const emit = defineEmits(['toggle-collapse']);

const isCollapsed = ref(false);

const menuItems = ref([
  { name: 'Tổng quan', path: '/admin/dashboard', icon: 'bi-grid-1x2-fill' },
  { name: 'Phân Quyền', path: '/admin/roles', icon: 'bi-shield-fill-check' },
  { name: 'Quản trị viên', path: '/admin/admins', icon: 'bi-person-badge-fill' },
  {
    name: 'Sản phẩm', icon: 'bi-box-seam', stateKey: 'products',
    children: [
      { name: 'Danh mục', path: '/admin/categories' },
      { name: 'Thương hiệu', path: '/admin/brands' },
      { name: 'Sản phẩm & Biến thể', path: '/admin/products' }
    ]
  },
  {
    name: 'Đơn hàng', icon: 'bi-receipt-cutoff', stateKey: 'orders',
    children: [
      { name: 'Danh sách đơn', path: '/admin/orders' },
      { name: 'Hoàn trả', path: '/admin/returns' }
    ]
  }
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

.sub-link {
  transition: all 0.2s ease;
}
.sub-link:hover {
  background-color: rgba(84, 119, 146, 0.1) !important; 
  color: var(--color-c-light) !important;
  transform: translateX(3px);
}
.sub-link:hover i {
  color: var(--color-c-light) !important;
}

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
</style>