<!-- File: frontend/src/pages/admin/role/Index.vue -->
<template>
  <div class="role-index-wrapper pb-5 mb-5">
    
    <div class="container-fluid py-4" v-if="!isPageLoading">
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-6 col-12 mb-3 mb-lg-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản lý Phân Quyền</h3>
        </div>
        
        <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link v-if="activeTab === 'roles'" :to="{ name: 'admin-roles-create' }" class="btn btn-urban px-3 py-1.5 fw-bold shadow-sm text-white rounded-pill text-decoration-none">
            <i class="bi bi-plus-circle me-1"></i> Thêm Role
          </router-link>
        </div>
      </div>

      <!-- TABS -->
      <div class="mb-4 overflow-auto custom-scrollbar-x">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-nowrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'roles' }" @click.prevent="switchTab('roles')">
              <i class="bi bi-person-badge me-2"></i> DS Chức vụ
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash me-2"></i> Thùng rác
              <span class="badge bg-danger rounded-pill ms-2" v-if="deletedCount > 0">{{ deletedCount }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'modules' }" @click.prevent="switchTab('modules')">
              <i class="bi bi-shield-lock me-2"></i> Cài đặt Cấp độ
            </a>
          </li>
        </ul>
      </div>

      <!-- TAB 1 & 2: ROLES & THÙNG RÁC -->
      <div v-if="activeTab === 'roles' || activeTab === 'deleted'" class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533]">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <div class="d-flex align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi me-2" :class="activeTab === 'roles' ? 'bi-list-ul' : 'bi-trash'"></i>
              {{ activeTab === 'roles' ? 'Danh sách Roles' : 'Roles đã xóa' }}
              <span v-if="isRefreshingRoles" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
          </div>
          <div class="search-box position-relative" style="width: 100%; max-width: 280px;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQueryRoles" @input="currentPageRoles = 1" placeholder="Tìm kiếm role...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </div>

        <div class="card-body p-0 mt-2">
          <!-- [GIAO DIỆN PC] TABLE TRUYỀN THỐNG -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 800px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 10%;">ID</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Tên hiển thị</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Mã hệ thống</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Quyền Hạn (Level)</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="isLoadingRoles">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2 text-urban"></span> Đang tải dữ liệu...
                  </td>
                </tr>
                <tr v-else-if="paginatedRoles.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không tìm thấy dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="role in paginatedRoles" :key="role.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': role.deleted_at}">
                  <td class="px-4 fw-bold text-muted dark:text-gray-400 font-monospace">#{{ role.id }}</td>
                  <!-- Bổ sung text-truncate và title cho Tên hiển thị -->
                  <td class="px-4 fw-semibold text-truncate" :title="role.label">
                    <span class="badge rounded-pill px-3 py-2 d-inline-block text-truncate" style="max-width: 100%;" :class="role.badge_class || 'bg-secondary'">{{ role.label }}</span>
                  </td>
                  <!-- Bổ sung text-truncate cho Mã hệ thống -->
                  <td class="px-4 text-muted dark:text-gray-400 font-monospace small text-truncate" :title="role.value">{{ role.value }}</td>
                  <td class="px-4">
                    <span class="badge border py-2 px-3 shadow-sm" :class="getLevelColor(role.level)">
                      <i class="bi bi-star-fill me-1 text-warning" v-if="role.level === 1"></i> Cấp {{ role.level || 5 }}
                    </span>
                  </td>
                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <template v-if="!role.deleted_at">
                        <router-link :to="{ name: 'admin-roles-edit', params: { id: role.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Sửa">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDeleteRole(role.id, role.label)" :disabled="role.id === 1" title="Xóa">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreRole(role.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- [GIAO DIỆN MOBILE] DẠNG LIST CARDS -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
             <div v-if="isLoadingRoles" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2 text-urban"></span> Đang tải...
             </div>
             <div v-else-if="paginatedRoles.length === 0" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>Trống.
             </div>
             <div v-else v-for="role in paginatedRoles" :key="role.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': role.deleted_at}">
                <div class="card-body p-3">
                   <!-- Cắt chữ chống tràn cho Badge Tên Role -->
                   <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                      <span class="badge rounded-pill px-3 py-2 fs-6 d-inline-block text-truncate" style="max-width: 65%;" :class="role.badge_class || 'bg-secondary'" :title="role.label">
                        {{ role.label }}
                      </span>
                      <span class="badge border py-1.5 px-2 shadow-sm flex-shrink-0" :class="getLevelColor(role.level)">
                        <i class="bi bi-star-fill me-1 text-warning" v-if="role.level === 1"></i> Cấp {{ role.level || 5 }}
                      </span>
                   </div>
                   <!-- Cắt chữ chống tràn cho Mã hệ thống (Value) -->
                   <div class="d-flex justify-content-between align-items-center mb-3 text-muted dark:text-gray-400 small gap-2">
                      <span class="flex-shrink-0"><i class="bi bi-hash"></i> ID: <strong>{{ role.id }}</strong></span>
                      <span class="font-monospace text-truncate text-end" style="max-width: 70%;" :title="role.value">
                        <i class="bi bi-code-slash"></i> {{ role.value }}
                      </span>
                   </div>
                   <!-- Actions Mobile -->
                   <div class="d-flex justify-content-end gap-2 pt-3 border-top dark:border-gray-700">
                      <template v-if="!role.deleted_at">
                        <router-link :to="{ name: 'admin-roles-edit', params: { id: role.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm">
                          <i class="bi bi-pencil-square"></i> Sửa
                        </router-link>
                        <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border shadow-sm flex-grow-0" style="min-width: 42px;" @click="confirmDeleteRole(role.id, role.label)" :disabled="role.id === 1">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 shadow-sm fw-bold" @click="restoreRole(role.id)">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                   </div>
                </div>
             </div>
          </div>
        </div>

        <!-- Phân trang -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPageRoles > 1">
          <span class="text-muted dark:text-gray-400 small text-center">
            Hiển thị {{ (currentPageRoles - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPageRoles * itemsPerPage, processedRoles.length) }}
          </span>
          <nav>
            <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
              <li class="page-item" :class="{ disabled: currentPageRoles === 1 }">
                <button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPageRoles--"><i class="bi bi-chevron-left"></i></button>
              </li>
              <li class="page-item" v-for="page in totalPageRoles" :key="page" :class="{ active: currentPageRoles === page }">
                <button class="page-link dark:border-gray-600" :class="currentPageRoles === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="currentPageRoles = page">
                  {{ page }}
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPageRoles === totalPageRoles }">
                <button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPageRoles++"><i class="bi bi-chevron-right"></i></button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <!-- TAB 3: CÀI ĐẶT MODULES -->
      <div v-if="activeTab === 'modules'" class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533]">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <div class="d-flex align-items-center gap-3">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi bi-hdd-network me-2"></i>Cấp độ truy cập trang
              <span v-if="isRefreshingModules" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
          </div>
          <div class="d-flex gap-2 w-100 w-lg-auto" style="max-width: 100%;">
            <div class="search-box position-relative flex-grow-1" style="max-width: 250px;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQueryModules" @input="currentPageModules = 1" placeholder="Tìm trang...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
            <button v-if="isSuperAdmin" class="btn btn-outline-urban fw-bold rounded-pill px-3 shadow-sm d-flex align-items-center" @click="syncModules" :disabled="isSyncing">
              <i class="bi bi-arrow-repeat me-1" :class="{'bi-spin': isSyncing}"></i> <span class="d-none d-md-inline">{{ isSyncing ? 'Đang đồng bộ...' : 'Đồng bộ' }}</span>
            </button>
          </div>
        </div>

        <div class="card-body p-0 mt-2">
          <!-- [GIAO DIỆN PC] TABLE TRUYỀN THỐNG -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 800px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Tên Trang (Module)</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Mã Route (Code)</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 20%;">Cấp tối thiểu</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 20%;">Cấu hình</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="isLoadingModules">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2 text-urban"></span> Đang tải...
                  </td>
                </tr>
                <tr v-else-if="paginatedModules.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-hdd me-2 fs-3 d-block opacity-50 mb-2"></i> Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="module in paginatedModules" :key="module.id">
                  <!-- Thêm text-truncate cho cột tên và mã code -->
                  <td class="px-4 fw-bold text-dark dark:text-gray-200 text-truncate" :title="module.module_name">{{ module.module_name }}</td>
                  <td class="px-4 text-truncate" :title="module.module_code">
                    <span class="text-muted dark:text-gray-400 font-monospace small bg-light dark:bg-[#2b3035] dark:border-gray-700 border px-2 py-1 rounded d-inline-block text-truncate" style="max-width: 100%;">{{ module.module_code }}</span>
                  </td>
                  <td class="px-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                      <span v-if="editingModuleId !== module.id" class="badge shadow-sm px-3 py-2" :class="getLevelColor(module.required_level)">Cấp {{ module.required_level }}</span>
                      <div v-if="isSuperAdmin && editingModuleId === module.id" class="input-group input-group-sm shadow-sm border border-urban rounded" style="width: 100px;">
                        <button class="btn btn-light bg-white dark:bg-[#212529] text-urban border-end dark:border-gray-600" type="button" @click="editLevelValue > 1 ? editLevelValue-- : null"><i class="bi bi-dash"></i></button>
                        <input type="text" class="form-control text-center fw-bold text-urban px-0 bg-white dark:bg-[#212529]" :value="editLevelValue" readonly>
                        <button class="btn btn-light bg-white dark:bg-[#212529] text-urban border-start dark:border-gray-600" type="button" @click="editLevelValue < 10 ? editLevelValue++ : null"><i class="bi bi-plus"></i></button>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 text-center">
                    <button v-if="isSuperAdmin && editingModuleId !== module.id" class="btn btn-sm btn-outline-urban fw-semibold rounded-pill px-3 shadow-sm" @click="startEditModule(module)">
                      <i class="bi bi-sliders me-1"></i> Đổi cấp
                    </button>
                    <div v-if="isSuperAdmin && editingModuleId === module.id" class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" @click="saveModuleLevel(module.id)" :disabled="isSavingLevel"><i class="bi bi-check-lg"></i> Lưu</button>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:text-red-400 border dark:border-gray-600 text-danger rounded-pill px-3 shadow-sm" @click="editingModuleId = null">Hủy</button>
                    </div>
                    <span v-if="!isSuperAdmin" class="text-muted small"><i class="bi bi-dash-lg"></i> Không có quyền</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- [GIAO DIỆN MOBILE] DẠNG LIST CARDS -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
             <div v-if="isLoadingModules" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2 text-urban"></span> Đang tải...
             </div>
             <div v-else-if="paginatedModules.length === 0" class="text-center py-4 text-muted">
                <i class="bi bi-hdd fs-2 d-block mb-2 opacity-50"></i>Trống.
             </div>
             <div v-else v-for="module in paginatedModules" :key="module.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]">
                <div class="card-body p-3">
                   <h6 class="fw-bold text-dark dark:text-gray-200 mb-1 text-truncate" :title="module.module_name">{{ module.module_name }}</h6>
                   <p class="text-muted dark:text-gray-400 small font-monospace mb-3 text-truncate" :title="module.module_code"><i class="bi bi-link-45deg"></i> {{ module.module_code }}</p>
                   
                   <div class="d-flex justify-content-between align-items-center pt-3 border-top dark:border-gray-700">
                      <div>
                        <span v-if="editingModuleId !== module.id" class="badge shadow-sm px-3 py-2" :class="getLevelColor(module.required_level)">Cấp {{ module.required_level }}</span>
                        <div v-if="isSuperAdmin && editingModuleId === module.id" class="input-group input-group-sm shadow-sm border border-urban rounded" style="width: 100px;">
                          <button class="btn btn-light bg-white dark:bg-[#212529] text-urban border-end dark:border-gray-600" type="button" @click="editLevelValue > 1 ? editLevelValue-- : null"><i class="bi bi-dash"></i></button>
                          <input type="text" class="form-control text-center fw-bold text-urban px-0 bg-white dark:bg-[#212529]" :value="editLevelValue" readonly>
                          <button class="btn btn-light bg-white dark:bg-[#212529] text-urban border-start dark:border-gray-600" type="button" @click="editLevelValue < 10 ? editLevelValue++ : null"><i class="bi bi-plus"></i></button>
                        </div>
                      </div>

                      <div>
                        <button v-if="isSuperAdmin && editingModuleId !== module.id" class="btn btn-sm btn-outline-urban fw-semibold rounded-pill px-3 shadow-sm" @click="startEditModule(module)">
                          <i class="bi bi-sliders"></i> Sửa
                        </button>
                        <div v-if="isSuperAdmin && editingModuleId === module.id" class="d-flex gap-1">
                          <button class="btn btn-sm btn-success fw-bold px-2 rounded shadow-sm" @click="saveModuleLevel(module.id)" :disabled="isSavingLevel"><i class="bi bi-check-lg"></i></button>
                          <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded shadow-sm" @click="editingModuleId = null"><i class="bi bi-x-lg"></i></button>
                        </div>
                      </div>
                   </div>
                </div>
             </div>
          </div>

        </div>
        
        <!-- Phân trang Modules -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPageModules > 1">
          <span class="text-muted dark:text-gray-400 small text-center">
            Hiển thị {{ (currentPageModules - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPageModules * itemsPerPage, processedModules.length) }}
          </span>
          <nav>
            <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
              <li class="page-item" :class="{ disabled: currentPageModules === 1 }">
                <button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPageModules--"><i class="bi bi-chevron-left"></i></button>
              </li>
              <li class="page-item" v-for="page in totalPageModules" :key="page" :class="{ active: currentPageModules === page }">
                <button class="page-link dark:border-gray-600" :class="currentPageModules === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="currentPageModules = page">
                  {{ page }}
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPageModules === totalPageModules }">
                <button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPageModules++"><i class="bi bi-chevron-right"></i></button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

    </div>

    <!-- Shimmer Loading Toàn trang -->
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">
        Đang tải dữ liệu...
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();

const isSuperAdmin = computed(() => {
  try {
    const info = JSON.parse(localStorage.getItem('admin_info') || '{}');
    return info.role && info.role.level === 1;
  } catch (e) {
    return false;
  }
});

const isPageLoading = ref(true);
const activeTab = ref('roles'); // 'roles', 'deleted', 'modules'
const itemsPerPage = 8; 

// State Roles
const roles = ref([]);
const isLoadingRoles = ref(false);
const isRefreshingRoles = ref(false);
const searchQueryRoles = ref('');
const currentPageRoles = ref(1);

// State Modules
const systemModules = ref([]);
const isLoadingModules = ref(false);
const isRefreshingModules = ref(false);
const searchQueryModules = ref('');
const currentPageModules = ref(1);
const editingModuleId = ref(null);
const editLevelValue = ref(1);
const isSavingLevel = ref(false);
const isSyncing = ref(false);
const currentPageLevel = ref(null); 

const getHeaders = () => ({
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const handleAxiosError = (e, defaultMsg = 'Lỗi hệ thống') => {
  if (e.response) {
    if (e.response.status === 401) {
      Swal.fire('Lỗi xác thực', 'Phiên đăng nhập đã hết hạn!', 'error');
    } else if (e.response.status === 403) {
      Swal.fire('Từ chối', e.response.data.message || 'Bạn không có quyền thực hiện thao tác này!', 'warning');
    } else {
      Swal.fire('Lỗi', e.response.data.message || defaultMsg, 'error');
    }
  } else {
    Swal.fire('Lỗi', 'Mất kết nối Server', 'error');
  }
};

const getLevelColor = (level) => {
  if(!level) return 'bg-secondary';
  const l = parseInt(level);
  switch (l) {
    case 1: return 'bg-danger text-white border-danger shadow-sm';        
    case 2: return 'bg-warning text-dark border-warning';                  
    case 3: return 'bg-info text-dark border-info';                        
    case 4: return 'bg-primary bg-opacity-10 text-primary border-primary'; 
    case 5: return 'bg-success bg-opacity-10 text-success border-success'; 
    default: return 'bg-light dark:bg-gray-700 text-secondary dark:text-gray-300 border-secondary'; 
  }
};

// ================= TÍNH TOÁN DANH SÁCH =================
const processedRoles = computed(() => {
  let res = roles.value;
  // Lọc Role theo Tab
  if (activeTab.value === 'deleted') {
    res = res.filter(r => r.deleted_at);
  } else if (activeTab.value === 'roles') {
    res = res.filter(r => !r.deleted_at);
  }
  
  if (searchQueryRoles.value) {
    const q = searchQueryRoles.value.toLowerCase();
    res = res.filter(r => r.label.toLowerCase().includes(q) || r.value.toLowerCase().includes(q));
  }
  return res;
});

const paginatedRoles = computed(() => {
  const start = (currentPageRoles.value - 1) * itemsPerPage;
  return processedRoles.value.slice(start, start + itemsPerPage);
});

const totalPageRoles = computed(() => Math.ceil(processedRoles.value.length / itemsPerPage) || 1);

const deletedCount = computed(() => roles.value.filter(r => r.deleted_at).length);

const processedModules = computed(() => {
  let res = systemModules.value;
  if (searchQueryModules.value) {
    const q = searchQueryModules.value.toLowerCase();
    res = res.filter(m => m.module_name.toLowerCase().includes(q) || m.module_code.toLowerCase().includes(q));
  }
  return res;
});

const paginatedModules = computed(() => {
  const start = (currentPageModules.value - 1) * itemsPerPage;
  return processedModules.value.slice(start, start + itemsPerPage);
});

const totalPageModules = computed(() => Math.ceil(processedModules.value.length / itemsPerPage) || 1);

// ================= API CALLS =================
const fetchRoles = async (isSilent = false) => {
  if (isSilent) isRefreshingRoles.value = true;
  else isLoadingRoles.value = true;

  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/roles', { headers: getHeaders() });
    roles.value = res.data.data;
  } catch (err) { 
      console.error('Lỗi tải roles', err); 
  } finally { 
      if (isSilent) isRefreshingRoles.value = false;
      else isLoadingRoles.value = false; 
  }
};

const fetchModules = async (isSilent = false) => {
  if (isSilent) isRefreshingModules.value = true;
  else isLoadingModules.value = true;

  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
    systemModules.value = res.data.data || [];
    
    const currentCode = route.meta.moduleCode;
    if (currentCode) {
      const currentModule = systemModules.value.find(m => m.module_code === currentCode);
      if (currentModule) currentPageLevel.value = currentModule.required_level;
    }
  } catch (err) { 
      console.error('Lỗi tải modules', err); 
  } finally { 
      if (isSilent) isRefreshingModules.value = false;
      else isLoadingModules.value = false; 
  }
};

const confirmDeleteRole = (id, roleName) => {
  Swal.fire({
    title: 'Đưa vào thùng rác?', 
    html: `Chức vụ <b>${roleName}</b> sẽ bị tạm khóa khỏi hệ thống.<br>Bạn có thể khôi phục lại sau trong tab Thùng rác.`, 
    icon: 'warning',
    showCancelButton: true, 
    confirmButtonColor: '#d33', 
    cancelButtonColor: '#6c757d', 
    confirmButtonText: 'Xóa ngay'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await axios.delete(`http://127.0.0.1:8000/api/v1/admin/roles/${id}`, { headers: getHeaders() });
        Swal.fire({ icon: 'success', title: 'Đã xóa', text: res.data.message, timer: 1500, showConfirmButton: false }); 
        fetchRoles(true);
      } catch (err) { 
          handleAxiosError(err, 'Không thể xóa Role này');
      }
    }
  });
};

const restoreRole = (id) => {
  Swal.fire({
    title: 'Khôi phục Role?',
    text: "Chức vụ này sẽ hoạt động trở lại.",
    icon: 'info',
    showCancelButton: true,
    confirmButtonColor: 'var(--color-c-hover, #547792)',
    confirmButtonText: 'Khôi phục ngay'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await axios.post(`http://127.0.0.1:8000/api/v1/admin/roles/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Thành công', text: res.data.message, showConfirmButton: false, timer: 1500 });
        fetchRoles(true);
      } catch (err) { 
        handleAxiosError(err, 'Không thể khôi phục Role này');
      }
    }
  });
};

const syncModules = async () => {
  isSyncing.value = true;
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/modules/sync', {}, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Hoàn tất', text: res.data.message, timer: 2000, showConfirmButton: false });
    fetchModules(true);
  } catch (err) { 
      handleAxiosError(err, 'Không thể đồng bộ cấu hình trang');
  } finally { 
      isSyncing.value = false; 
  }
};

const startEditModule = (module) => {
  editingModuleId.value = module.id;
  editLevelValue.value = module.required_level;
};

const saveModuleLevel = async (moduleId) => {
  isSavingLevel.value = true;
  try {
    const payload = { required_level: editLevelValue.value };
    await axios.put(`http://127.0.0.1:8000/api/v1/admin/modules/${moduleId}/level`, payload, { headers: getHeaders() });
    
    const target = systemModules.value.find(m => m.id === moduleId);
    if(target) target.required_level = editLevelValue.value;
    
    const currentCode = route.meta.moduleCode;
    if(target.module_code === currentCode) currentPageLevel.value = editLevelValue.value;

    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã cập nhật cấp độ trang', timer: 1500, showConfirmButton: false });
    editingModuleId.value = null;
  } catch (err) { 
      handleAxiosError(err, 'Không thể lưu cấp độ cho trang này');
  } finally { 
      isSavingLevel.value = false; 
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  currentPageRoles.value = 1;
};

// ================= LẮNG NGHE REAL-TIME =================
const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.roles')
      .listen('.RoleEvent', () => {
        fetchRoles(true);
      });
  }
};

onMounted(async () => {
  isPageLoading.value = true; 
  await Promise.all([fetchRoles(false), fetchModules(false)]);
  setupRealtime(); 
  isPageLoading.value = false; 
});

onUnmounted(() => {
  if (window.Echo) window.Echo.leave('admin.roles');
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }

.logo-shimmer { 
  font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; 
  background: linear-gradient(120deg, var(--color-c-dark, #213448) 30%, var(--color-c-light, #94B4C1) 50%, var(--color-c-dark, #213448) 70%); 
  background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; 
}
@keyframes shine { to { background-position: 200% center; } }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: var(--color-c-hover, #547792); }
.custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; border-bottom: 2px solid var(--color-c-hover, #547792) !important; }

.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; } 
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }

.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); transition: 0.2s; } 
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.25); }

.bi-spin { display: inline-block; animation: bi-spin 2s infinite linear; }
@keyframes bi-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(359deg); } }

.invalid-feedback { font-size: 0.8rem; }

.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>