<!-- File: frontend/src/pages/admin/role/Create.vue -->
<template>
  <div class="role-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold text-dark dark:text-white mb-0">
          <router-link :to="{ name: 'admin-roles' }" class="text-decoration-none text-muted me-2 hover:text-urban transition-all">
            <i class="bi bi-arrow-left-circle"></i>
          </router-link>
          Thêm Role Mới
        </h3>
      </div>

      <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4">
        <div class="alert alert-danger d-flex align-items-center mb-4 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800" role="alert" v-if="Object.keys(errors).length > 0">
          <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
          <div class="small">Vui lòng kiểm tra lại các trường bị báo đỏ bên dưới.</div>
        </div>

        <form @submit.prevent="saveRole">
          <div class="row g-4 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark dark:text-gray-200">Tên hiển thị (Label) <span class="text-danger">*</span></label>
              <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="roleForm.label" :class="{'is-invalid': errors.label}" placeholder="VD: Kế Toán Trưởng">
              <div class="invalid-feedback fw-bold">{{ errors.label?.[0] }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark dark:text-gray-200">Mã hệ thống (Value) <span class="text-danger">*</span></label>
              <input type="text" class="form-control font-monospace py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="roleForm.value" :class="{'is-invalid': errors.value}" placeholder="VD: accountant_manager">
              <div class="invalid-feedback fw-bold">{{ errors.value?.[0] }}</div>
            </div>
          </div>

          <div class="mb-4 p-4 bg-light dark:bg-[#212529] rounded-4 border border-light-subtle dark:border-gray-700 shadow-sm">
            <label class="form-label fw-bold text-urban mb-3">Định vị Cấp độ (Level) <span class="text-danger">*</span></label>
            <div class="d-flex align-items-stretch gap-4 flex-wrap flex-md-nowrap">
                <div class="input-group shadow-sm flex-shrink-0 border border-urban rounded" style="width: 140px; height: fit-content;">
                    <button class="btn btn-light bg-white dark:bg-[#1a2533] border-end dark:border-gray-700" type="button" @click="roleForm.level > 1 ? roleForm.level-- : null">
                        <i class="bi bi-dash-lg text-urban fw-bold"></i>
                    </button>
                    <input type="text" class="form-control text-center fw-bold fs-5 text-urban bg-white dark:bg-[#1a2533] px-0" :value="roleForm.level" readonly>
                    <button class="btn btn-light bg-white dark:bg-[#1a2533] border-start dark:border-gray-700" type="button" @click="roleForm.level < 10 ? roleForm.level++ : null">
                        <i class="bi bi-plus-lg text-urban fw-bold"></i>
                    </button>
                </div>
                <div class="flex-grow-1 p-3 bg-white dark:bg-[#1a2533] rounded-3 border border-info border-opacity-50 shadow-sm w-100">
                    <h6 class="fw-bold text-info mb-3" style="font-size: 0.85rem;">
                        <i class="bi bi-eye-fill me-1"></i>Trang mà cấp độ này có thể truy cập:
                    </h6>
                    <div class="d-flex flex-wrap gap-2 custom-scrollbar-y" style="max-height: 110px; overflow-y: auto;">
                        <span v-if="accessibleModulesPreview.length === 0" class="text-muted small fst-italic">Không có quyền truy cập trang nào.</span>
                        <span v-else v-for="m in accessibleModulesPreview" :key="m.id" class="badge bg-light text-dark dark:bg-[#2b3035] dark:text-gray-300 border border-secondary-subtle dark:border-gray-600 fw-medium py-2 px-2" style="font-size: 0.75rem;">
                            <i class="bi bi-check2 text-success me-1"></i> {{ m.module_name }} <span class="text-muted opacity-75">(Cấp {{ m.required_level }})</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-muted dark:text-gray-400 d-block fst-italic"><i class="bi bi-info-circle-fill me-1"></i>Số Level càng nhỏ thì quyền hạn càng lớn.</small>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-dark dark:text-gray-200">Màu sắc Nhãn (Tùy chọn)</label>
            <select class="form-select py-2 fw-semibold dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="roleForm.badge_class" :class="roleForm.badge_class">
              <option value="">Mặc định (Xám)</option>
              <option value="bg-primary text-white" class="bg-primary text-white">Xanh dương (Primary)</option>
              <option value="bg-success text-white" class="bg-success text-white">Xanh lá (Success)</option>
              <option value="bg-danger text-white" class="bg-danger text-white">Đỏ (Danger)</option>
              <option value="bg-warning text-dark" class="bg-warning text-dark">Vàng (Warning)</option>
              <option value="bg-info text-dark" class="bg-info text-dark">Xanh ngọc (Info)</option>
              <option value="bg-dark text-white" class="bg-dark text-white">Đen (Dark)</option>
            </select>
          </div>
          
          <hr class="dark:border-gray-700 my-4">
          <div class="text-end">
            <router-link :to="{ name: 'admin-roles' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy</router-link>
            <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Tạo Mới
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const isSaving = ref(false);
const errors = ref({}); 
const roleForm = ref({ label: '', value: '', badge_class: '', level: 5 });
const systemModules = ref([]);

const getHeaders = () => ({
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const accessibleModulesPreview = computed(() => {
    if (!roleForm.value.level) return [];
    return systemModules.value
        .filter(m => roleForm.value.level <= m.required_level)
        .sort((a,b) => a.required_level - b.required_level);
});

const fetchModules = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() });
    systemModules.value = res.data.data || [];
  } catch (err) { 
      console.error('Lỗi tải modules', err); 
  }
};

const saveRole = async () => {
  isSaving.value = true;
  errors.value = {};
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/roles', roleForm.value, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Thành công', text: res.data.message, timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-roles' });
  } catch (err) { 
    if (err.response && err.response.data && err.response.data.errors) {
      errors.value = err.response.data.errors; 
    } else {
      Swal.fire('Lỗi', err.response?.data?.message || 'Không thể tạo Role mới', 'error');
    }
  } finally { 
      isSaving.value = false; 
  }
};

onMounted(() => {
  fetchModules();
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; } 
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }
.hover\:text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.25); }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>