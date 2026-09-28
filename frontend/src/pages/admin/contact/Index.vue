<template>
  <div class="contact-index-wrapper">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải hộp thư liên hệ...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Liên Hệ & Phản Hồi</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <div class="mb-3 overflow-auto custom-scrollbar-x d-flex justify-content-between align-items-center">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-nowrap" style="gap: 8px; flex-grow: 1;">
          <li class="nav-item text-nowrap">
             <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-inbox-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ counts.all || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
             <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-envelope-exclamation-fill me-2 text-warning"></i> Chờ xử lý
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'pending'}">{{ counts.pending || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
             <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'replied' }" @click.prevent="switchTab('replied')">
              <i class="bi bi-envelope-check-fill me-2 text-success"></i> Đã phản hồi
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'replied'}">{{ counts.replied || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] animation-fade-in position-relative">
        <div class="card-body p-3 px-md-4">
          <div class="row g-3 align-items-end">
            <div class="col-xl-4 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm kiếm nhanh</label>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Tên, email, sđt...">
            </div>
            
            <div class="col-xl-3 col-md-3">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-sort-down me-1"></i>Sắp xếp</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 fw-semibold" 
                      v-model="filters.sort" @change="applyFilters">
                <option value="desc">Mới nhất đến cũ</option>
                <option value="asc">Cũ nhất đến mới</option>
              </select>
            </div>

            <div class="col-xl-5 col-md-5 text-end d-flex gap-2 justify-content-end">
               <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all" @click="resetFilters" v-if="filters.search">
                 <i class="bi bi-x-circle me-1"></i>Xóa lọc
               </button>
               
               <button v-if="selectedContacts.length > 0" class="btn btn-urban text-white rounded-pill px-4 fw-bold shadow-sm transition-all animation-fade-in" @click="openBulkReplyModal">
                 <i class="bi bi-reply-all-fill me-1"></i>Trả lời hàng loạt ({{ selectedContacts.length }})
               </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all position-relative overflow-hidden">
        
        <div v-if="isLoading && !isFirstLoad" class="position-absolute top-0 start-0 w-100 h-100 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.75;">
           <LoadingDots color="var(--color-c-hover)" :size="12" />
        </div>

        <div class="card-body p-0">
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-center border-0" style="width: 5%;">
                    <input class="form-check-input cursor-pointer" type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" :disabled="pendingContacts.length === 0">
                  </th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Khách hàng</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 35%;">Nội dung tin nhắn</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-center" style="width: 10%;">Thời gian</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-center" style="width: 12%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 13%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="contacts.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted font-sans-vn">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không tìm thấy thư liên hệ nào.
                  </td>
                </tr>
                <tr v-else v-for="contact in contacts" :key="contact.id" :class="{'bg-urban-soft': selectedContacts.includes(contact.id)}">
                  
                  <td class="px-4 py-3 text-center">
                    <input class="form-check-input cursor-pointer" type="checkbox" :value="contact.id" v-model="selectedContacts" v-if="contact.status === 'pending'">
                  </td>
                  
                  <td class="px-2 py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center me-3 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 40px; height: 40px;">
                         <i class="bi bi-person-fill text-muted fs-5"></i>
                      </div>
                      <div class="overflow-hidden">
                        <div class="fw-bold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn">{{ contact.name }}</div>
                        <div class="text-muted small font-monospace"><i class="bi bi-envelope me-1"></i>{{ contact.email }}</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-2 py-3 cursor-pointer" @click="openReplyModal(contact)">
                     <div class="fw-bold text-urban line-clamp-1 mb-1 font-sans-vn" :title="contact.subject">{{ contact.subject }}</div>
                     <div class="text-muted small line-clamp-2 fst-italic font-sans-vn" :title="contact.message">{{ contact.message }}</div>
                  </td>

                  <td class="px-2 text-center font-monospace">
                    <div v-if="contact.status === 'replied'">
                       <div class="small text-success fw-bold mb-1" title="Thời gian phản hồi"><i class="bi bi-reply-fill"></i> {{ formatDateTime(contact.replied_at) || 'N/A' }}</div>
                       <div class="text-muted" style="font-size: 0.75rem;" title="Thời gian gửi yêu cầu"><i class="bi bi-box-arrow-in-right"></i> {{ formatDateTime(contact.created_at) }}</div>
                    </div>
                    <div v-else>
                       <span class="text-muted small" title="Thời gian gửi yêu cầu"><i class="bi bi-clock"></i> {{ formatDateTime(contact.created_at) }}</span>
                    </div>
                  </td>

                  <td class="px-2 text-center font-sans-vn">
                    <span class="badge border px-3 py-1.5 shadow-sm w-100 text-center" :class="getStatusClass(contact.status)">
                      <i class="bi me-1" :class="contact.status === 'replied' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'"></i>
                      {{ contact.status === 'replied' ? 'Đã phản hồi' : 'Chờ xử lý' }}
                    </span>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-2 font-sans-vn">
                      <button v-if="contact.status === 'pending'" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border fw-semibold text-nowrap" title="Phản hồi" @click="openReplyModal(contact)">
                        <i class="bi bi-reply-fill"></i> Phản hồi
                      </button>
                      <button v-else class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border fw-semibold text-nowrap" title="Xem chi tiết" @click="openReplyModal(contact)">
                        <i class="bi bi-file-text"></i> Chi tiết
                      </button>

                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border px-3" title="Xóa" @click="deleteContact(contact)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="contacts.length === 0" class="text-center py-5 text-muted">Không tìm thấy thư liên hệ nào.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="contact in contacts" :key="'mob-'+contact.id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]">
                <div class="card-body p-3 position-relative">
                  <div class="position-absolute top-0 end-0 p-3" v-if="contact.status === 'pending'">
                     <input class="form-check-input cursor-pointer" type="checkbox" :value="contact.id" v-model="selectedContacts" style="width: 1.25rem; height: 1.25rem;">
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3 border-bottom dark:border-gray-700 pb-2 pe-5">
                     <div class="font-monospace">
                         <div v-if="contact.status === 'replied'" class="small text-success fw-bold"><i class="bi bi-reply-fill"></i> {{ formatDateTime(contact.replied_at) || 'N/A' }}</div>
                         <div :class="contact.status === 'replied' ? 'text-muted mt-1' : 'text-muted small'" :style="contact.status === 'replied' ? 'font-size: 0.75rem;' : ''">
                             <i class="bi bi-box-arrow-in-right" v-if="contact.status === 'replied'"></i>
                             <i class="bi bi-clock" v-else></i> {{ formatDateTime(contact.created_at) }}
                         </div>
                     </div>
                     <span class="badge border" :class="getStatusClass(contact.status)">
                        <i class="bi me-1" :class="contact.status === 'replied' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'"></i>
                        {{ contact.status === 'replied' ? 'Đã phản hồi' : 'Chờ xử lý' }}
                     </span>
                  </div>

                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light dark:bg-[#1a2533] rounded-circle d-flex align-items-center justify-content-center me-2 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 35px; height: 35px;">
                       <i class="bi bi-person-fill text-muted"></i>
                    </div>
                    <div class="overflow-hidden w-100">
                      <div class="fw-bold dark:text-gray-200 line-clamp-1" style="font-size: 0.95rem;">{{ contact.name }}</div>
                      <small class="text-muted dark:text-gray-400 font-monospace">{{ contact.email }}</small>
                    </div>
                  </div>
                  
                  <div class="mb-3 bg-light dark:bg-[#1a2533] p-2 rounded-3 border dark:border-gray-700 cursor-pointer" @click="openReplyModal(contact)">
                    <div class="fw-bold text-urban line-clamp-1 mb-1" style="font-size: 0.9rem;">{{ contact.subject }}</div>
                    <div class="text-muted small line-clamp-2 fst-italic">{{ contact.message }}</div>
                  </div>

                  <div class="d-flex gap-2">
                      <button v-if="contact.status === 'pending'" class="btn btn-urban text-white shadow-sm fw-bold btn-sm w-100 py-2 d-flex justify-content-center gap-1" @click="openReplyModal(contact)">
                        <i class="bi bi-reply-fill"></i> PHẢN HỒI
                      </button>
                      <button v-else class="btn btn-outline-urban shadow-sm fw-bold btn-sm w-100 py-2 d-flex justify-content-center gap-1" @click="openReplyModal(contact)">
                        <i class="bi bi-file-text"></i> CHI TIẾT
                      </button>
                      <button class="btn btn-outline-danger shadow-sm fw-bold btn-sm py-2 px-3" @click="deleteContact(contact)">
                        <i class="bi bi-trash"></i>
                      </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="pagination.last_page > 1">
        <span class="text-muted dark:text-gray-400 small font-sans-vn">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center font-sans-vn">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }">
              <button class="page-link dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="changePage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>

    </div>

    <ReplyModal ref="replyModalRef" @refresh="fetchData" />
    <BulkReplyModal ref="bulkReplyModalRef" :contact-ids="selectedContacts" @refresh="onBulkReplySuccess" />

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import ReplyModal from './ReplyModal.vue';
import BulkReplyModal from './BulkReplyModal.vue';
import LoadingDots from '@/components/admin/LoadingDots.vue'; 

const route = useRoute();
const contacts = ref([]);
const counts = ref({});
const systemModules = ref([]);
const currentPageLevel = ref(null);

const isFirstLoad = ref(true);
const isLoading = ref(false);

const pagination = ref({ current_page: 1, last_page: 1 });
const filters = ref({ search: '', status: 'pending', sort: 'desc' }); 
const activeTab = ref('pending');
let searchTimeout = null;

const replyModalRef = ref(null);
const bulkReplyModalRef = ref(null);

const selectedContacts = ref([]);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const pendingContacts = computed(() => {
    return contacts.value.filter(c => c.status === 'pending');
});

const isAllSelected = computed(() => {
    return pendingContacts.value.length > 0 && selectedContacts.value.length === pendingContacts.value.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedContacts.value = pendingContacts.value.map(c => c.id);
    } else {
        selectedContacts.value = [];
    }
};

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getStatusClass = (status) => {
  if(status === 'pending') return 'bg-warning-soft text-warning border-warning border-opacity-25';
  if(status === 'replied') return 'bg-success-soft text-success border-success border-opacity-25';
  return 'bg-secondary-soft text-secondary border-secondary border-opacity-25';
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary' };
  return map[level] || 'bg-secondary';
};

const fetchModules = async () => {
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/system-modules`, { headers: getHeaders() });
    systemModules.value = res.data;
    const currentPath = route.path;
    const currentModule = systemModules.value.find(m => currentPath.includes(m.route_path));
    if(currentModule) currentPageLevel.value = currentModule.level;
  } catch(e) {}
};

const fetchData = async (page = 1) => {
  isLoading.value = true;
  selectedContacts.value = []; // Reset bulk select khi chuyển trang hoặc load lại
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts`, {
      params: { page, ...filters.value },
      headers: getHeaders()
    });
    
    contacts.value = res.data.data.data;
    pagination.value = {
      current_page: res.data.data.current_page,
      last_page: res.data.data.last_page
    };
    counts.value = res.data.counts;
  } catch (error) {
    ZyroSwal.toastError("Không thể tải dữ liệu liên hệ.");
  } finally {
    isLoading.value = false;
    isFirstLoad.value = false;
  }
};

const onBulkReplySuccess = () => {
    selectedContacts.value = [];
    fetchData(pagination.value.current_page);
};

const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchData(1);
  }, 500);
};

const applyFilters = () => {
  fetchData(1);
};

const resetFilters = () => {
  filters.value.search = '';
  fetchData(1);
};

const switchTab = (tab) => {
  activeTab.value = tab;
  filters.value.status = tab;
  fetchData(1);
};

const changePage = (page) => {
  if(page >= 1 && page <= pagination.value.last_page) {
    fetchData(page);
  }
};

const openReplyModal = (contact) => {
   if (replyModalRef.value) {
       replyModalRef.value.openModal(contact);
   }
};

const openBulkReplyModal = () => {
   if (bulkReplyModalRef.value) {
       bulkReplyModalRef.value.openModal();
   }
};

const deleteContact = async (contact) => {
  const confirmed = await ZyroSwal.fire({
    title: 'Xóa liên hệ này?',
    text: `Bạn đang xóa liên hệ từ ${contact.name}. Không thể khôi phục!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý xóa',
    cancelButtonText: 'Hủy'
  });

  if (confirmed.isConfirmed) {
    try {
      const res = await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts/${contact.id}`, { headers: getHeaders() });
      ZyroSwal.toastSuccess(res.data.message);
      
      // Load lại trang hiện tại (hoặc về trang 1 nếu hết list)
      if(contacts.value.length === 1 && pagination.value.current_page > 1) {
         fetchData(pagination.value.current_page - 1);
      } else {
         fetchData(pagination.value.current_page);
      }
    } catch (e) {
      ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra khi xóa!');
    }
  }
};

onMounted(() => {
  fetchModules();
  fetchData();
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.bg-urban-soft { background-color: rgba(84, 119, 146, 0.1) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; }
.btn-outline-urban:hover { background: var(--color-c-hover, #547792); color: white; }
.hover-urban-outline:hover { color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; background-color: rgba(84, 119, 146, 0.05); }

.hover-danger:hover { background-color: #dc3545 !important; color: white !important; border-color: #dc3545 !important; }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.15) !important; }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.15) !important; }
.bg-secondary-soft { background-color: rgba(108, 117, 125, 0.15) !important; }

.font-sans-vn { font-family: 'Inter', sans-serif; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.custom-tab { color: #6c757d; font-weight: 600; border-bottom: 3px solid transparent; transition: 0.2s; }
.custom-tab:hover { color: var(--color-c-hover); }
.active-tab { color: var(--color-c-hover) !important; border-bottom-color: var(--color-c-hover) !important; }
.tab-badge { background-color: #e9ecef; color: #495057; font-size: 0.75rem; }
.active-badge { background-color: var(--color-c-hover); color: white; }

.shadow-sm-hover { transition: box-shadow 0.2s; }
.shadow-sm-hover:focus { box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.25) !important; border-color: var(--color-c-hover) !important; }

.animation-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Logo Shimmer */
.logo-shimmer {
  font-size: 4rem;
  font-weight: 900;
  background: linear-gradient(90deg, #547792, #213448, #547792);
  background-size: 200% auto;
  color: transparent;
  -webkit-background-clip: text;
  animation: shimmer 2s linear infinite;
}
@keyframes shimmer {
  0% { background-position: -200% center; }
  100% { background-position: 200% center; }
}
</style>
