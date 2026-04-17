<template>
  <div class="admin-index-wrapper pb-5 mb-5">
    
    <div class="container-fluid py-4" v-if="!isPageLoading">
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản lý Nhân sự</h3>
        </div>
        
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-admins-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill text-decoration-none">
            <i class="bi bi-person-plus-fill me-1"></i> Thêm Tài Khoản
          </router-link>
        </div>
      </div>

      <!-- TABS PHÂN LOẠI -->
      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap" v-for="tab in allTabs" :key="tab.id">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab"
               href="#"
               :class="[{ 'active-tab': activeTab === tab.id }, tab.id === 'deleted' ? 'text-danger' : '']"
               @click.prevent="switchTab(tab.id)">
              <i class="bi me-2" :class="tab.icon"></i>
              {{ tab.name }}
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === tab.id, 'bg-danger text-white border-danger': tab.id === 'deleted' && activeTab !== 'deleted'}">
                {{ tab.count }}
              </span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533]">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
            <i class="bi bi-list-ul me-2"></i>Danh sách hiển thị
            <span v-if="isRefreshing" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
          </h6>
          <div class="search-box position-relative" style="width: 100%; max-width: 280px;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" 
                   v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, email, SĐT...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </div>

        <div class="card-body p-0 mt-2">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Nhân viên</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Chức vụ (Role)</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 22%;">Liên hệ</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 18%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="paginatedStaff.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i> Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="staff in paginatedStaff" :key="staff.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': staff.deleted_at, 'bg-light dark:bg-[#1a2533]': staff.id === currentUserId && !staff.deleted_at}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getAvatarUrl(staff.avatar_url)" 
                           @error="handleImageError" 
                           class="rounded-circle object-fit-cover me-3 border shadow-sm flex-shrink-0" 
                           style="width: 45px; height: 45px;">
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate d-flex align-items-center" :title="staff.fullname">
                          {{ staff.fullname }} 
                          <span v-if="staff.id === currentUserId" class="badge bg-urban text-white ms-2" style="font-size: 0.65rem;">BẠN</span>
                          <span v-if="staff.id === 1 && staff.id !== currentUserId" class="badge bg-danger text-white ms-2" style="font-size: 0.65rem;">GỐC</span>
                        </h6>
                        <!-- ĐÃ FIX BẢO MẬT: Mask email và gỡ bỏ thẻ title chứa email thật -->
                        <small class="text-muted d-block text-truncate font-monospace mt-1">{{ maskEmail(staff.email) }}</small>
                      </div>
                    </div>
                  </td>
                  <td class="px-4">
                    <span class="badge rounded-pill px-3 py-2 d-inline-block text-truncate" style="max-width: 100%;" :class="staff.role?.badge_class || 'bg-secondary'" :title="staff.role?.label">{{ staff.role?.label || 'Chưa gán' }}</span>
                  </td>
                  <td class="px-4">
                    <div class="text-dark dark:text-gray-300 fw-medium small mb-1 text-truncate" :title="staff.phone"><i class="bi bi-telephone text-urban me-1"></i> {{ staff.phone || 'N/A' }}</div>
                    <div class="text-muted dark:text-gray-400 small text-truncate" :title="staff.address"><i class="bi bi-geo-alt text-urban me-1"></i> {{ staff.address || 'N/A' }}</div>
                  </td>
                  <td class="px-4 text-center">
                    <span v-if="staff.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                      <i class="bi bi-trash3-fill"></i> Đã xóa
                    </span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200" 
                              style="width: 110px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(staff.localStatus || staff.status)"
                              v-model="staff.localStatus"
                              @change="checkStatusChange(staff)"
                              :disabled="staff.isUpdatingStatus || staff.id === 1 || staff.id === currentUserId">
                        <option value="active">Hoạt động</option>
                        <option value="locked">Bị Khóa</option>
                      </select>
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="staff.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;"></div>
                        <template v-else-if="staff.isStatusChanged">
                          <button @click="saveStaffStatus(staff)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(staff)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border" @click="openQuickView(staff)" title="Xem hồ sơ">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!staff.deleted_at">
                        <router-link :to="{ name: 'admin-admins-edit', params: { id: staff.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Sửa & Thiết lập">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(staff.id, staff.fullname)" :disabled="staff.id === 1 || staff.id === currentUserId">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreStaff(staff.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="paginatedStaff.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else v-for="staff in paginatedStaff" :key="staff.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': staff.deleted_at}">
              <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3">
                  <img :src="getAvatarUrl(staff.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-3 border shadow-sm flex-shrink-0" style="width: 50px; height: 50px;">
                  <div class="overflow-hidden w-100">
                    <h6 class="mb-0 fw-bold dark:text-gray-200 text-truncate">{{ staff.fullname }}</h6>
                    <!-- ĐÃ FIX BẢO MẬT -->
                    <small class="text-muted dark:text-gray-400 d-block text-truncate font-monospace mt-1">{{ maskEmail(staff.email) }}</small>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 border-top dark:border-gray-700 pt-3 gap-2">
                   <span class="badge rounded-pill d-inline-block text-truncate" style="max-width: 60%;" :class="staff.role?.badge_class || 'bg-secondary'">{{ staff.role?.label || 'Chưa gán' }}</span>
                   <span v-if="staff.status === 'active'" class="text-success small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Hoạt động</span>
                   <span v-else-if="staff.status === 'locked'" class="text-warning small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Đang khóa</span>
                   <span v-else class="text-secondary small fw-bold flex-shrink-0">Đã xóa</span>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border" style="min-width: 46px;" @click="openQuickView(staff)"><i class="bi bi-eye"></i></button>
                  <router-link v-if="!staff.deleted_at" :to="{ name: 'admin-admins-edit', params: { id: staff.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1"><i class="bi bi-pencil-square"></i> Sửa</router-link>
                  <button v-if="staff.deleted_at" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold" @click="restoreStaff(staff.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  <button v-if="!staff.deleted_at" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border" style="min-width: 46px;" @click="confirmDelete(staff.id, staff.fullname)" :disabled="staff.id === 1 || staff.id === currentUserId"><i class="bi bi-trash"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
          <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedStaff.length) }}</span>
          <nav>
            <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
              <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage--"><i class="bi bi-chevron-left"></i></button></li>
              <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
                <button class="page-link dark:border-gray-600" :class="currentPage === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="currentPage = page">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage++"><i class="bi bi-chevron-right"></i></button></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu...</p>
    </div>

    <!-- POPUP QUICK VIEW -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-person-vcard text-urban me-2"></i>Hồ sơ Nhân sự</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 text-center" v-if="selectedStaff">
            <div class="position-relative d-inline-block mb-3">
              <img :src="getAvatarUrl(selectedStaff.avatar_url)" 
                   @error="handleImageError" 
                   class="rounded-circle shadow-sm border border-3 border-white dark:border-gray-700 object-fit-cover" 
                   style="width: 110px; height: 110px;">
              <span v-if="!selectedStaff.deleted_at" class="position-absolute bottom-0 end-0 p-2 border border-light dark:border-gray-800 rounded-circle" :class="selectedStaff.status === 'active' ? 'bg-success' : 'bg-warning'" style="width: 15px; height: 15px;"></span>
              <span v-else class="position-absolute bottom-0 end-0 p-2 border border-light dark:border-gray-800 rounded-circle bg-secondary" style="width: 15px; height: 15px;"></span>
            </div>
            
            <h5 class="fw-bold mb-1 d-flex align-items-center justify-content-center dark:text-white">
              {{ selectedStaff.fullname }}
              <span v-if="selectedStaff.id === currentUserId" class="badge bg-urban text-white ms-2 align-middle" style="font-size: 0.7rem;">BẠN</span>
              <span v-if="selectedStaff.id === 1 && selectedStaff.id !== currentUserId" class="badge bg-danger text-white ms-2 align-middle" style="font-size: 0.7rem;">GỐC</span>
            </h5>
            <!-- ĐÃ FIX BẢO MẬT: Mask email -->
            <p class="text-muted small mb-2 font-monospace dark:text-gray-400">{{ maskEmail(selectedStaff.email) }}</p>
            <span class="badge mb-4" :class="selectedStaff.role?.badge_class || 'bg-secondary'">{{ selectedStaff.role?.label || 'Chưa gán quyền' }}</span>
            
            <div class="text-start bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700">
              <div class="row mb-2">
                <div class="col-4 text-muted fw-semibold small"><i class="bi bi-telephone text-urban me-2"></i>SĐT:</div>
                <div class="col-8 fw-bold text-dark dark:text-gray-200">{{ selectedStaff.phone || 'Chưa cập nhật' }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted fw-semibold small"><i class="bi bi-geo-alt text-urban me-2"></i>Địa chỉ:</div>
                <div class="col-8 text-dark dark:text-gray-200">{{ selectedStaff.address || 'Chưa cập nhật' }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted fw-semibold small"><i class="bi bi-clock-history text-urban me-2"></i>Tạo lúc:</div>
                <div class="col-8 text-dark dark:text-gray-200">{{ formatDateTime(selectedStaff.created_at) }}</div>
              </div>
              <div class="row" v-if="selectedStaff.deleted_at">
                <div class="col-4 text-muted fw-semibold small"><i class="bi bi-trash3 text-danger me-2"></i>Xóa lúc:</div>
                <div class="col-8 text-danger fw-semibold">{{ formatDateTime(selectedStaff.deleted_at) }}</div>
              </div>
            </div>
            
            <div class="mt-4" v-if="!selectedStaff.deleted_at">
              <button type="button" @click="goToEditStaff(selectedStaff.id)" class="btn btn-outline-urban rounded-pill px-4 fw-semibold w-100">
                <i class="bi bi-pencil-square me-1"></i> Thiết lập tài khoản
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, onBeforeUnmount, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const route = useRoute();
const router = useRouter();

const staffs = ref([]);
const roles = ref([]);
const systemModules = ref([]);
const isPageLoading = ref(true);
const isRefreshing = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');
const currentPage = ref(1);
const itemsPerPage = 8;
const currentPageLevel = ref(null);

const currentAdmin = JSON.parse(localStorage.getItem('admin_info') || '{}');
const currentUserId = currentAdmin.id;

const selectedStaff = ref(null);
let quickViewModalInstance = null;

// Hàm che giấu Email (Data Masking)
const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0];
  const domain = parts[1];
  // Nếu local part quá ngắn (<= 2 ký tự), chỉ hiện ký tự đầu tiên
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  // Hiện 3 ký tự đầu, ẩn phần còn lại
  return name.substring(0, 3) + '***@' + domain;
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const openQuickView = (staff) => {
  selectedStaff.value = staff;
  if (!quickViewModalInstance) {
    quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  }
  quickViewModalInstance.show();
};

const goToEditStaff = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = '';
    document.body.style = '';
    router.push({ name: 'admin-admins-edit', params: { id } });
  }, 300);
}; 

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getAvatarUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const handleImageError = (e) => { e.target.src = defaultAvatar; };

const fetchData = async (isSilent = false) => {
  if (isSilent) isRefreshing.value = true; else isPageLoading.value = true;
  try {
    const [resStaff, resRole, resModules] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/admins', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/roles', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() })
    ]);
    staffs.value = resStaff.data.data.map(s => ({ ...s, localStatus: s.status, isStatusChanged: false, isUpdatingStatus: false }));
    roles.value = resRole.data.data;
    systemModules.value = resModules.data.data;
    const module = systemModules.value.find(m => m.module_code === route.meta.moduleCode);
    if (module) currentPageLevel.value = module.required_level;
  } catch (err) { console.error(err); } finally { isPageLoading.value = false; isRefreshing.value = false; }
};

const allTabs = computed(() => {
  const tabs = [
    { id: 'all', name: 'Tất cả', count: staffs.value.filter(s => !s.deleted_at).length, icon: 'bi-people-fill' },
    { id: 'locked', name: 'Bị khóa', count: staffs.value.filter(s => s.status === 'locked' && !s.deleted_at).length, icon: 'bi-lock-fill text-warning' }
  ];
  roles.value.forEach(r => tabs.push({ id: `role_${r.id}`, name: r.label, count: staffs.value.filter(s => s.role_id === r.id && !s.deleted_at).length, icon: 'bi-person-badge text-primary' }));
  tabs.push({ id: 'deleted', name: 'Thùng rác', count: staffs.value.filter(s => s.deleted_at).length, icon: 'bi-trash3-fill text-danger' });
  return tabs;
});

const switchTab = (id) => { activeTab.value = id; currentPage.value = 1; };

const getStatusSelectClass = (status) => {
  const map = { 'active': 'text-success border-success bg-success bg-opacity-10', 'locked': 'text-warning border-warning bg-warning bg-opacity-10' };
  return map[status] || 'bg-light text-secondary';
};

const checkStatusChange = (s) => { s.isStatusChanged = (s.localStatus !== s.status); };
const cancelStatusChange = (s) => { s.localStatus = s.status; s.isStatusChanged = false; };

const saveStaffStatus = async (staff) => {
  staff.isUpdatingStatus = true;
  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('status', staff.localStatus);
  formData.append('fullname', staff.fullname);
  formData.append('email', staff.email);
  formData.append('role_id', staff.role_id);
  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${staff.id}`, formData, { headers: getHeaders() });
    staff.status = staff.localStatus; staff.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật trạng thái thành công', showConfirmButton: false, timer: 1500 });
  } catch (error) { cancelStatusChange(staff); Swal.fire('Lỗi', 'Không thể lưu trạng thái mới', 'error'); } finally { staff.isUpdatingStatus = false; }
};

const processedStaff = computed(() => {
  let res = staffs.value;
  if (activeTab.value === 'deleted') res = res.filter(s => s.deleted_at);
  else {
    res = res.filter(s => !s.deleted_at);
    if (activeTab.value === 'locked') res = res.filter(s => s.status === 'locked');
    else if (activeTab.value.startsWith('role_')) { const rid = parseInt(activeTab.value.split('_')[1]); res = res.filter(s => s.role_id === rid); }
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    res = res.filter(s => s.fullname.toLowerCase().includes(q) || s.email.toLowerCase().includes(q) || (s.phone && s.phone.includes(q)));
  }
  return res.sort((a,b) => (a.id === currentUserId ? -1 : (b.id === currentUserId ? 1 : b.id - a.id)));
});

const paginatedStaff = computed(() => processedStaff.value.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage));
const totalPages = computed(() => Math.ceil(processedStaff.value.length / itemsPerPage) || 1);

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Xác nhận xóa?', text: `Nhân sự "${name}" sẽ bị chuyển vào thùng rác.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' })
    .then(async (result) => { if (result.isConfirmed) { try { await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${id}`, { headers: getHeaders() }); fetchData(true); } catch (e) { console.error(e); } } });
};

const restoreStaff = async (id) => { try { await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${id}/restore`, {}, { headers: getHeaders() }); fetchData(true); } catch (e) { Swal.fire('Lỗi', e.response?.data?.message || 'Không thể khôi phục', 'error'); } };

const setupRealtime = () => { if (window.Echo) window.Echo.private('admin.admins').listen('.AdminEvent', () => fetchData(true)); };
const getLevelColor = (l) => { const map = { 1: 'bg-danger text-white border-danger', 2: 'bg-warning text-dark border-warning', 3: 'bg-info text-dark border-info', 4: 'bg-primary bg-opacity-10 text-primary border-primary', 5: 'bg-success bg-opacity-10 text-success border-success' }; return map[l] || 'bg-light text-secondary border-secondary'; };

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if(window.Echo) window.Echo.leave('admin.admins'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); transition: 0.2s; background: transparent; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.logo-shimmer { font-size: 3.5rem; font-weight: 900; background: linear-gradient(120deg, #213448 30%, #547792 50%, #213448 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }
.tab-badge { font-size: 0.7rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }
.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>