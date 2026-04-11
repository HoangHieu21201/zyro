<!-- File: frontend/src/pages/admin/brand/Create.vue -->
<template>
  <div class="brand-create-wrapper mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-brands' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Thêm Thương Hiệu</h3>
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
                      <span class="fw-bold text-muted dark:text-gray-400">Chưa cấp phát</span>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-normal">Tự động</span>
                  </div>
                  <small class="text-muted mt-1 d-block fst-italic"><i class="bi bi-info-circle me-1"></i>Hệ thống tự động xếp thương hiệu này xuống cuối cùng.</small>
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
                  <img :src="previewLogo" class="rounded-4 object-fit-contain shadow-sm mb-3 bg-white p-2 dark:border dark:border-gray-600" style="width: 100%; height: 160px;">
                  <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-semibold" @click="$refs.logoInput.click()">
                    <i class="bi bi-cloud-upload me-1"></i> Tải ảnh lên
                  </button>
                  <input type="file" ref="logoInput" @change="onLogoChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.logo">{{ errors.logo[0] }}</div>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-brands' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Thương Hiệu
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const isSaving = ref(false);
const errors = ref({});

const previewLogo = ref(defaultImage);
const logoInput = ref(null);

const form = ref({ 
  name: '', description: '', status: 'active', logo: null 
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const onLogoChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  form.value.logo = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewLogo.value = e.target.result; };
  reader.readAsDataURL(file);
};

const saveBrand = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/brands', formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã tạo thương hiệu mới', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-brands' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};
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