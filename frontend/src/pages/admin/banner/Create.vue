<template>
  <div class="banner-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-banners' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Thêm Banner Mới</h3>
      </div>

      <form @submit.prevent="saveBanner" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái: Thông tin -->
          <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Nội dung & Vị trí</h5>
              
              <div class="row g-4">
                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Tiêu đề Banner (Cho mục đích quản lý) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.title" :class="{'is-invalid': errors.title}" placeholder="VD: Khuyến mãi Thu Đông 2026">
                  <div class="invalid-feedback">{{ errors.title?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Vị trí hiển thị <span class="text-danger">*</span></label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.position" :class="{'is-invalid': errors.position}">
                    <option value="main_slider">Slider Trang Chủ</option>
                    <option value="home_banner_1">Banner Giữa Trang 1</option>
                    <option value="home_banner_2">Banner Giữa Trang 2</option>
                    <option value="popup">Popup Khuyến Mãi (Khi vào web)</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.position?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Trạng thái phát hành <span class="text-danger">*</span></label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.status">
                    <option value="active">Hiển thị (Active)</option>
                    <option value="hidden">Đang ẩn (Hidden)</option>
                  </select>
                </div>

                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Đường dẫn đích (URL khi click)</label>
                  <div class="input-group shadow-sm-hover">
                    <span class="input-group-text bg-light dark:bg-[#2b3035] dark:text-gray-400 border-secondary-subtle dark:border-gray-700"><i class="bi bi-link-45deg"></i></span>
                    <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                           v-model="form.target_url" placeholder="VD: /category/ao-thun-nam hoặc https://zyro.vn/sale">
                  </div>
                  <small class="text-muted d-block mt-1 fst-italic">Bỏ trống nếu chỉ muốn hiển thị ảnh mà không bấm được.</small>
                </div>

                <!-- Thời gian -->
                <div class="col-md-12 mt-4 pt-3 border-top dark:border-gray-700">
                  <h6 class="fw-bold text-urban mb-3"><i class="bi bi-clock-history me-2"></i>Hẹn giờ hiển thị (Tùy chọn)</h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted text-uppercase">Bắt đầu lúc</label>
                      <input type="datetime-local" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.start_time">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted text-uppercase">Kết thúc lúc</label>
                      <input type="datetime-local" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.end_time" :class="{'is-invalid': errors.end_time}">
                      <div class="invalid-feedback">{{ errors.end_time?.[0] }}</div>
                      <small class="text-success fw-bold d-block mt-1"><i class="bi bi-infinity"></i> Bỏ trống = Vô thời hạn</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Cột Phải: Hình ảnh -->
          <div class="col-lg-5">
             <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
                <h5 class="fw-bold text-urban mb-4"><i class="bi bi-images me-2"></i>Giao Diện Thiết Bị</h5>

                <!-- Desktop Image -->
                <div class="mb-4 p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 d-block mb-3">
                    <i class="bi bi-pc-display me-2"></i>Ảnh Desktop (Máy tính) <span class="text-danger">*</span>
                  </label>
                  <!-- Khung ngang -->
                  <div class="bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden shadow-sm border dark:border-gray-600 mx-auto d-flex align-items-center justify-content-center cursor-pointer hover-opacity-75 transition-all" 
                       style="width: 100%; height: 180px; padding: 4px;"
                       @click="$refs.desktopInput.click()">
                    <img v-if="previewDesktop" :src="previewDesktop" class="object-fit-cover w-100 h-100 rounded-2">
                    <div v-else class="text-muted text-center small"><i class="bi bi-cloud-plus fs-2 d-block mb-1"></i> Bấm để chọn ảnh ngang</div>
                  </div>
                  <input type="file" ref="desktopInput" @change="e => onImageChange(e, 'image_desktop', previewDesktopRef)" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold text-center" v-if="errors.image_desktop">{{ errors.image_desktop[0] }}</div>
                </div>

                <!-- Mobile Image -->
                <div class="p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 d-block mb-3">
                    <i class="bi bi-phone me-2"></i>Ảnh Mobile (Điện thoại)
                  </label>
                  <!-- Khung dọc -->
                  <div class="bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden shadow-sm border dark:border-gray-600 mx-auto d-flex align-items-center justify-content-center cursor-pointer hover-opacity-75 transition-all" 
                       style="width: 140px; height: 220px; padding: 4px;"
                       @click="$refs.mobileInput.click()">
                    <img v-if="previewMobile" :src="previewMobile" class="object-fit-cover w-100 h-100 rounded-2">
                    <div v-else class="text-muted text-center small px-2"><i class="bi bi-cloud-plus fs-3 d-block mb-1"></i> Bấm chọn ảnh dọc</div>
                  </div>
                  <input type="file" ref="mobileInput" @change="e => onImageChange(e, 'image_mobile', previewMobileRef)" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold text-center" v-if="errors.image_mobile">{{ errors.image_mobile[0] }}</div>
                  <p class="text-muted small mt-2 text-center fst-italic">Nếu bỏ trống, hệ thống sẽ tự cắt phần giữa của Ảnh Desktop để hiển thị.</p>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-banners' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Banner
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

// Vì Vue ref không thể truyền trực tiếp vào tham số hàm và mong đợi nó mutate (nó là pass by value),
// Nên ta sẽ tạo 2 biến ref riêng biệt để hứng URL ảnh preview
const previewDesktop = ref(null);
const previewMobile = ref(null);

// refs cho thẻ input HTML
const desktopInput = ref(null);
const mobileInput = ref(null);

const form = ref({ 
  title: '', target_url: '', position: 'main_slider', 
  start_time: '', end_time: '', status: 'active',
  image_desktop: null, image_mobile: null 
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const onImageChange = (e, formKey, previewType) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  
  form.value[formKey] = file;
  const reader = new FileReader();
  reader.onload = (e) => { 
      if (formKey === 'image_desktop') previewDesktop.value = e.target.result;
      else previewMobile.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const saveBanner = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/banners', formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã tạo Banner mới', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-banners' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
.hover-opacity-75:hover { opacity: 0.75; }
.transition-all { transition: all 0.3s ease; }
.cursor-pointer { cursor: pointer; }
</style>