<!-- File: frontend/src/pages/admin/brand/Edit.vue -->
<template>
  <div class="brand-edit-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-brands' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Cập Nhật Thương Hiệu</h3>
      </div>

      <form @submit.prevent="saveBrand" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái: Thông tin chính -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h5>
              
              <div class="row g-4">
                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Tên thương hiệu <span class="text-danger">*</span></label>
                  <!-- ĐÃ THÊM: required minlength="3" maxlength="255" -->
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.name" :class="{'is-invalid': errors.name}" placeholder="VD: Nike, Adidas..." required minlength="3" maxlength="255">
                  <div class="invalid-feedback">{{ errors.name?.[0] }}</div>
                </div>

                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Thứ tự hiển thị</label>
                  <div class="p-2 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 d-flex align-items-center justify-content-between" style="min-height: 42px;">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-sort-numeric-down text-urban me-2 fs-5"></i>
                      <span class="fw-bold text-dark dark:text-white">{{ form.sort_order ? '#' + form.sort_order : 'Chưa cấp phát' }}</span>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-normal" title="Được hệ thống tự động quản lý">Tự động</span>
                  </div>
                  <small class="text-muted mt-1 d-block fst-italic"><i class="bi bi-info-circle me-1"></i>Sử dụng thao tác Kéo/Thả ngoài trang Danh sách để thay đổi.</small>
                </div>

                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Mô tả thương hiệu</label>
                  <textarea class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                            rows="5" v-model="form.description" placeholder="Nhập mô tả chi tiết về thương hiệu này..."></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Cột Phải: Hình ảnh & Trạng thái -->
          <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
                <h5 class="fw-bold text-urban mb-4"><i class="bi bi-image me-2"></i>Hình ảnh & Cài đặt</h5>

                <div class="mb-4">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Trạng thái</label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.status">
                    <option value="active">Hiển thị (Active)</option>
                    <option value="hidden">Đang ẩn (Hidden)</option>
                  </select>
                </div>

                <!-- Logo -->
                <div class="mb-4 text-center p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 d-block mb-3">Logo Thương Hiệu</label>
                  
                  <div class="bg-white rounded-4 shadow-sm border border-light-subtle dark:border-gray-600 mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 100%; height: 160px; padding: 10px;">
                    <img :src="previewLogo" class="object-fit-contain w-100 h-100">
                  </div>

                  <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-urban rounded-pill px-3 fw-semibold flex-grow-1 shadow-sm" @click="$refs.logoInput.click()">
                      <i class="bi bi-cloud-upload me-1"></i> Thay ảnh
                    </button>
                    <button v-if="hasOldLogo || form.logo" type="button" class="btn btn-sm btn-light text-danger border dark:border-gray-600 rounded-pill px-3 shadow-sm" @click="removeLogo">
                      <i class="bi bi-trash3"></i> Xóa
                    </button>
                  </div>
                  <input type="file" ref="logoInput" @change="onLogoChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.logo">{{ errors.logo[0] }}</div>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-brands' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy bỏ</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Cập Nhật Ngay
          </button>
        </div>
      </form>

    </div>
    
    <div v-else class="text-center py-5"><span class="spinner-border text-urban"></span></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const route = useRoute();
const brandId = parseInt(route.params.id);

const isLoading = ref(true);
const isSaving = ref(false);
const errors = ref({});

const previewLogo = ref(defaultImage);
const logoInput = ref(null);
const hasOldLogo = ref(false);

const form = ref({ 
  name: '', description: '', sort_order: '', status: 'active',
  logo: null, remove_logo: false
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const fetchData = async () => {
  try {
    const resDetail = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/brands/${brandId}`, { headers: getHeaders() });
    const b = resDetail.data.data;
    
    form.value.name = b.name;
    form.value.description = b.description;
    form.value.sort_order = b.sort_order;
    form.value.status = b.status;

    if (b.logo) {
       previewLogo.value = `http://127.0.0.1:8000/storage/${b.logo}`;
       hasOldLogo.value = true;
    }
  } catch (err) { 
    Swal.fire('Lỗi', 'Không tìm thấy thương hiệu', 'error'); 
    router.push({ name: 'admin-brands' }); 
  } finally { isLoading.value = false; }
};

const onLogoChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  form.value.logo = file;
  form.value.remove_logo = false;
  const reader = new FileReader();
  reader.onload = (e) => { previewLogo.value = e.target.result; };
  reader.readAsDataURL(file);
};

const removeLogo = () => {
  previewLogo.value = defaultImage; 
  form.value.logo = null; 
  form.value.remove_logo = true; 
  hasOldLogo.value = false;
};

const saveBrand = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT');

  Object.keys(form.value).forEach(key => { 
    if(key !== 'sort_order' && form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]); 
    }
  });

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/brands/${brandId}`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật thương hiệu thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-brands' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};

onMounted(fetchData);
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>