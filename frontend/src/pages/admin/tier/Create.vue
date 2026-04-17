<template>
  <div class="tier-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-tiers' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Thêm Hạng Thành Viên</h3>
      </div>

      <form @submit.prevent="saveTier" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Thông tin & Điều kiện xét duyệt</h5>
              
              <div class="row g-4">
                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Tên hạng (VD: Đồng, Bạc, Vàng) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control form-control-lg bg-light dark:bg-[#212529] border-0 shadow-sm-hover dark:text-white" 
                         v-model="form.name" :class="{'is-invalid': errors.name}" placeholder="Nhập tên hạng...">
                  <div class="invalid-feedback">{{ errors.name?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Chi tiêu tối thiểu đạt hạng (VNĐ) <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <span class="input-group-text bg-white dark:bg-[#212529] dark:text-gray-400 dark:border-gray-700">₫</span>
                    <input type="number" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                           v-model="form.min_spent" :class="{'is-invalid': errors.min_spent}" min="0" placeholder="VD: 5000000">
                    <div class="invalid-feedback">{{ errors.min_spent?.[0] }}</div>
                  </div>
                  <small class="text-danger fw-bold mt-1 d-block" v-if="form.min_spent">{{ formatCurrency(form.min_spent) }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Số đơn hoàn tất tối thiểu <span class="text-danger">*</span></label>
                  <input type="number" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.min_orders" :class="{'is-invalid': errors.min_orders}" min="0" placeholder="VD: 5">
                  <div class="invalid-feedback">{{ errors.min_orders?.[0] }}</div>
                </div>

                <div class="col-12 mt-4 pt-4 border-top dark:border-gray-700">
                  <h6 class="fw-bold text-urban mb-3"><i class="bi bi-gift-fill me-2"></i>Quyền Lợi & Ưu Đãi Áp Dụng</h6>
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Phần trăm giảm giá / Đơn <span class="text-danger">*</span></label>
                      <div class="input-group shadow-sm-hover">
                        <input type="number" step="0.01" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                               v-model="form.discount_percent" :class="{'is-invalid': errors.discount_percent}" min="0" max="100" placeholder="VD: 5">
                        <span class="input-group-text bg-white dark:bg-[#212529] dark:text-gray-400 dark:border-gray-700">%</span>
                        <div class="invalid-feedback">{{ errors.discount_percent?.[0] }}</div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Lượt dịch vụ miễn phí / Năm <span class="text-danger">*</span></label>
                      <div class="input-group shadow-sm-hover">
                        <input type="number" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                               v-model="form.yearly_service_quota" :class="{'is-invalid': errors.yearly_service_quota}" min="0" placeholder="VD: 10">
                        <span class="input-group-text bg-white dark:bg-[#212529] dark:text-gray-400 dark:border-gray-700">Lượt</span>
                        <div class="invalid-feedback">{{ errors.yearly_service_quota?.[0] }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Cột Phải -->
          <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100 text-center">
                <h5 class="fw-bold text-urban mb-4 text-start"><i class="bi bi-gem me-2"></i>Huy hiệu Hạng</h5>

                <div class="mb-4 p-4 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <div class="bg-white dark:bg-[#1a2533] rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm border dark:border-gray-600 mb-3" style="width: 120px; height: 120px;">
                     <img v-if="previewIcon" :src="previewIcon" class="object-fit-contain" style="width: 70px; height: 70px;">
                     <i v-else class="bi bi-star-fill text-muted fs-1 opacity-50"></i>
                  </div>
                  
                  <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-semibold" @click="$refs.iconInput.click()">
                    <i class="bi bi-cloud-upload me-1"></i> Tải huy hiệu lên
                  </button>
                  <input type="file" ref="iconInput" @change="onIconChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.icon">{{ errors.icon[0] }}</div>
                  <p class="text-muted small mt-3 mb-0">Hỗ trợ định dạng SVG, PNG nền trong suốt. Kích thước khuyến nghị: 100x100px.</p>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-tiers' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy bỏ</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Hạng Thành Viên
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

const router = useRouter();
const isSaving = ref(false);
const errors = ref({});
const previewIcon = ref(null);
const iconInput = ref(null);

const form = ref({ name: '', min_spent: 0, min_orders: 0, discount_percent: 0, yearly_service_quota: 0, icon: null });

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);

const onIconChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  form.value.icon = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewIcon.value = e.target.result; };
  reader.readAsDataURL(file);
};

const saveTier = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  Object.keys(form.value).forEach(key => { if(form.value[key] !== null) formData.append(key, form.value[key]); });

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/tiers', formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã tạo cấu hình hạng thành viên', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-tiers' });
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
.hover\:text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>