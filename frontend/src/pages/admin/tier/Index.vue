<!-- File: frontend/src/pages/admin/tier/Index.vue -->
<template>
  <div class="tier-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải cấu hình hạng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Cấu Hình Hạng Thành Viên</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-tiers-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all">
            <i class="bi bi-award-fill me-1"></i> Thêm Hạng Mới
          </router-link>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
            <i class="bi bi-star-half me-2 text-warning fs-5"></i> Các mốc Hạng thành viên hệ thống
            <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
          </h6>
        </div>
        
        <div class="card-body p-0">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 900px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Huy hiệu & Hạng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Điều kiện đạt hạng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Đặc quyền</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 10%;">Số khách</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="tiers.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Hệ thống chưa cấu hình Hạng.
                  </td>
                </tr>
                <tr v-else v-for="tier in tiers" :key="tier.id">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light dark:bg-[#212529] rounded-circle p-2 me-3 border shadow-sm dark:border-gray-600 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                         <img v-if="tier.icon" :src="getImageUrl(tier.icon)" @error="handleImageError" class="object-fit-contain" style="width: 35px; height: 35px;">
                         <i v-else class="bi bi-gem text-warning fs-3"></i>
                      </div>
                      <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 fs-5 text-truncate" :title="tier.name">{{ tier.name }}</h6>
                    </div>
                  </td>
                  
                  <td class="px-4 text-muted dark:text-gray-400 small">
                    <div class="mb-1"><i class="bi bi-cash-coin text-urban me-2"></i>Chi tiêu tối thiểu: <strong class="text-danger dark:text-red-400">{{ formatCurrency(tier.min_spent) }}</strong></div>
                    <div><i class="bi bi-bag-check text-urban me-2"></i>Số đơn hoàn tất: <strong class="text-dark dark:text-white">{{ tier.min_orders }} đơn</strong></div>
                  </td>
                  
                  <td class="px-4">
                     <span class="badge bg-success bg-opacity-10 text-success border border-success mb-1 w-100 text-start">
                        <i class="bi bi-percent me-1"></i> Giảm {{ tier.discount_percent }}% / Đơn
                     </span>
                     <span class="badge bg-info bg-opacity-10 text-info border border-info w-100 text-start">
                        <i class="bi bi-gift me-1"></i> {{ tier.yearly_service_quota }} Dịch vụ / Năm
                     </span>
                  </td>

                  <td class="px-4 text-center fw-bold text-dark dark:text-white fs-5">
                    {{ tier.users_count || 0 }}
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <router-link :to="{ name: 'admin-tiers-edit', params: { id: tier.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Chỉnh sửa cấu hình hạng">
                        <i class="bi bi-pencil-square"></i> Sửa
                      </router-link>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(tier.id, tier.name)" :disabled="tier.users_count > 0" :title="tier.users_count > 0 ? 'Đang có khách hàng thuộc hạng này' : 'Xóa vĩnh viễn'">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="tiers.length === 0" class="text-center py-5 text-muted">Hệ thống chưa cấu hình Hạng.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="tier in tiers" :key="tier.id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center mb-3 border-bottom dark:border-gray-700 pb-3">
                    <div class="bg-light dark:bg-[#1a2533] rounded-circle p-2 me-3 border shadow-sm dark:border-gray-600 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                       <img v-if="tier.icon" :src="getImageUrl(tier.icon)" @error="handleImageError" class="object-fit-contain" style="width: 30px; height: 30px;">
                       <i v-else class="bi bi-gem text-warning fs-4"></i>
                    </div>
                    <div class="overflow-hidden w-100">
                      <h5 class="mb-1 fw-bold dark:text-gray-200 text-truncate">{{ tier.name }}</h5>
                      <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ tier.users_count || 0 }} Khách hàng</span>
                    </div>
                  </div>
                  
                  <div class="small text-muted dark:text-gray-400 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                      <span>Chi tiêu tối thiểu:</span>
                      <strong class="text-danger dark:text-red-400">{{ formatCurrency(tier.min_spent) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                      <span>Số đơn hoàn tất:</span>
                      <strong class="text-dark dark:text-white">{{ tier.min_orders }} đơn</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                      <span>Giảm giá hóa đơn:</span>
                      <strong class="text-success">{{ tier.discount_percent }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                      <span>Lượt dịch vụ miễn phí:</span>
                      <strong class="text-info">{{ tier.yearly_service_quota }} lượt/năm</strong>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    <router-link :to="{ name: 'admin-tiers-edit', params: { id: tier.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm"><i class="bi bi-pencil-square"></i> Sửa</router-link>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border shadow-sm" @click="confirmDelete(tier.id, tier.name)" :disabled="tier.users_count > 0"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();

const tiers = ref([]);
const systemModules = ref([]);
const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : null;
const handleImageError = (e) => { e.target.style.display = 'none'; };

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);

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

const fetchData = async (isSilent = false) => {
  if (!isFirstLoad.value && !isSilent) isLoading.value = true;
  
  try {
    const [resTiers, resModules] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/tiers', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() })
    ]);

    tiers.value = Array.isArray(resTiers.data.data) ? resTiers.data.data : [];
    
    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_tiers'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.tiers')
      .listen('.MembershipTierEvent', () => { fetchData(true); });
  }
};

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Xóa vĩnh viễn?', text: `Hạng "${name}" sẽ bị xóa cứng khỏi hệ thống. Bạn không thể hoàn tác thao tác này!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/tiers/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        await fetchData(); 
      } catch(e) {
        isLoading.value = false;
        Swal.fire('Lỗi', e.response?.data?.message || 'Không thể xóa hạng này', 'error');
      }
    }
  });
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.tiers'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.transition-all { transition: all 0.3s ease; }
</style>