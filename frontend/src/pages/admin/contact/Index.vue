<template>
  <div class="contact-index-wrapper pb-5 mb-5">
    
    <!-- LOADING LẦN ĐẦU TIÊN (SHIMMER LOGO) -->
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải hộp thư liên hệ...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Liên Hệ & Phản Hồi</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <!-- BADGE CẤP ĐỘ TRANG -->
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <!-- TABS TRẠNG THÁI -->
      <div class="mb-3 overflow-auto custom-scrollbar-x">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-nowrap" style="gap: 8px;">
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

      <!-- BỘ LỌC TÌM KIẾM & SẮP XẾP -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] animation-fade-in position-relative">
        <div class="card-body p-3 px-md-4">
          <div class="row g-3 align-items-center">
            <div class="col-xl-5 col-md-6">
              <div class="d-flex justify-content-between">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm kiếm nhanh</label>
              </div>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Nhập tên, email, sđt hoặc tiêu đề...">
            </div>
            
            <div class="col-xl-3 col-md-4">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-sort-down me-1"></i>Sắp xếp</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 fw-semibold" 
                      v-model="filters.sort" @change="applyFilters">
                <option value="desc">Mới nhất đến cũ nhất</option>
                <option value="asc">Cũ nhất đến mới nhất</option>
              </select>
            </div>

            <div class="col-xl-4 col-md-2 text-end mt-4 mt-md-auto">
               <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all w-100" @click="resetFilters" v-if="filters.search">
                 <i class="bi bi-x-circle me-1"></i>Xóa lọc
               </button>
            </div>
          </div>
        </div>
      </div>

      <!-- BẢNG DỮ LIỆU LIÊN HỆ -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all position-relative overflow-hidden">
        
        <!-- ĐÃ FIX: SỬ DỤNG COMPONENT LOADING DOTS ĐỒNG BỘ -->
        <div v-if="isLoading && !isFirstLoad" class="position-absolute top-0 start-0 w-100 h-100 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.75;">
           <LoadingDots color="var(--color-c-hover)" :size="12" />
        </div>

        <div class="card-body p-0">
          <!-- GIAO DIỆN DESKTOP -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 35%;">Nội dung tin nhắn</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Thời gian gửi</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="contacts.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted font-sans-vn">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không tìm thấy thư liên hệ nào.
                  </td>
                </tr>
                <tr v-else v-for="contact in contacts" :key="contact.id">
                  
                  <td class="px-4 py-3">
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

                  <td class="px-4 py-3 cursor-pointer" @click="openReplyModal(contact)">
                     <div class="fw-bold text-urban line-clamp-1 mb-1 font-sans-vn" :title="contact.subject">{{ contact.subject }}</div>
                     <div class="text-muted small line-clamp-2 fst-italic font-sans-vn" :title="contact.message">{{ contact.message }}</div>
                  </td>

                  <td class="px-4 text-center font-monospace">
                    <span class="text-muted small">{{ formatDateTime(contact.created_at) }}</span>
                  </td>

                  <td class="px-4 text-center font-sans-vn">
                    <span class="badge border px-3 py-1.5 shadow-sm w-100 text-start" :class="getStatusClass(contact.status)">
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

          <!-- GIAO DIỆN DÀNH CHO MOBILE (THẺ CARDS) -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="contacts.length === 0" class="text-center py-5 text-muted">Không tìm thấy thư liên hệ nào.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="contact in contacts" :key="'mob-'+contact.id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-3 border-bottom dark:border-gray-700 pb-2">
                     <span class="text-muted small font-monospace">{{ formatDateTime(contact.created_at) }}</span>
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

      <!-- PHÂN TRANG -->
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

    <!-- TÍCH HỢP MODAL TỪ FILE RIÊNG -->
    <ReplyModal ref="replyModalRef" @refresh="fetchData" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import ReplyModal from './ReplyModal.vue';
// ĐÃ IMPORT COMPONENT LOADING VÀO ĐÂY
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

// Tham chiếu đến Modal
const replyModalRef = ref(null);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary bg-opacity-10 text-primary', 5: 'bg-success bg-opacity-10 text-success' };
  return map[level] || 'bg-secondary';
};

const getStatusClass = (status) => {
  if (status === 'replied') return 'bg-success bg-opacity-10 text-success border-success';
  return 'bg-warning bg-opacity-10 text-warning border-warning';
};

const switchTab = (tab) => {
  activeTab.value = tab;
  filters.value.status = (tab === 'all') ? '' : tab;
  applyFilters();
};

const applyFilters = () => { pagination.value.current_page = 1; fetchData(); };
const resetFilters = () => { filters.value.search = ''; applyFilters(); };
const onSearchInput = () => { if (searchTimeout) clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { applyFilters(); }, 500); };
const changePage = (page) => { if (page >= 1 && page <= pagination.value.last_page) { pagination.value.current_page = page; fetchData(); } };

const fetchData = async () => {
  isLoading.value = true;
  try {
    if (isFirstLoad.value) {
        const resModules = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
        systemModules.value = resModules.data.data;
        const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_contacts'));
        if (currentModule) currentPageLevel.value = currentModule.required_level;
    }

    const params = new URLSearchParams();
    params.append('page', pagination.value.current_page);
    params.append('sort', filters.value.sort);
    if(filters.value.status) params.append('status', filters.value.status);
    if(filters.value.search) params.append('search', filters.value.search);

    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts?${params.toString()}`, { headers: getHeaders() });
    
    contacts.value = res.data.data.data || [];
    counts.value = res.data.counts || {};
    pagination.value = { current_page: res.data.data.current_page, last_page: res.data.data.last_page };
  } catch (err) { 
    console.error(err); 
  } finally { 
    isLoading.value = false; 
    isFirstLoad.value = false; 
  }
};

const openReplyModal = (contact) => {
  if (replyModalRef.value) {
     replyModalRef.value.openModal(contact);
  }
};

const deleteContact = (contact) => {
  ZyroSwal.confirmDelete(contact.subject).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/contacts/${contact.id}`, { headers: getHeaders() });
        ZyroSwal.toastSuccess('Xóa thư thành công');
        fetchData();
      } catch(e) { ZyroSwal.toastError('Xóa thất bại'); }
    }
  });
};

onMounted(() => { fetchData(); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.hover-bg-effect:hover { background-color: rgba(84, 119, 146, 0.05); }
.cursor-pointer { cursor: pointer; }

.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

/* TABS STYLING */
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

/* UTILS */
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { box-shadow: none !important; border-color: var(--color-c-hover, #547792) !important; }

.hover-danger:hover { color: #dc3545 !important; border-color: #dc3545 !important; }
.hover-text-danger:hover { color: #dc3545 !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* TRUNCATION */
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word; line-height: 1.4; }
</style>