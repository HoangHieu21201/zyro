<!-- File: frontend/src/pages/admin/banner/Edit.vue -->
<template>
  <div class="banner-edit-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-banners' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Cập Nhật Banner</h3>
      </div>

      <form @submit.prevent="saveBanner" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái: Thông tin -->
          <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Nội dung & Vị trí</h5>
              
              <div class="row g-4">
                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Tiêu đề Banner <span class="text-danger">*</span></label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.title" :class="{'is-invalid': errors.title}">
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
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Đường dẫn đích (URL)</label>
                  <div class="input-group shadow-sm-hover">
                    <span class="input-group-text bg-light dark:bg-[#2b3035] dark:text-gray-400 border-secondary-subtle dark:border-gray-700"><i class="bi bi-link-45deg"></i></span>
                    <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.target_url">
                  </div>
                </div>

                <!-- Thời gian -->
                <div class="col-md-12 mt-4 pt-3 border-top dark:border-gray-700">
                  <h6 class="fw-bold text-urban mb-3"><i class="bi bi-clock-history me-2"></i>Hẹn giờ hiển thị</h6>
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
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold text-dark dark:text-gray-200 m-0"><i class="bi bi-pc-display me-2"></i>Ảnh Desktop</label>
                    <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3 fw-semibold" @click="$refs.desktopInput.click()">Thay ảnh</button>
                  </div>
                  
                  <div class="bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden shadow-sm border dark:border-gray-600 mx-auto d-flex align-items-center justify-content-center" 
                       style="width: 100%; height: 180px; padding: 4px;">
                    <!-- ĐÃ CẬP NHẬT: Thêm class img-zoomable và sự kiện click phóng to -->
                    <img v-if="previewDesktop" :src="previewDesktop" class="object-fit-cover w-100 h-100 rounded-2 img-zoomable" @click="openImageZoom(previewDesktop)" @error="handleImageError">
                  </div>
                  <input type="file" ref="desktopInput" @change="e => onImageChange(e, 'image_desktop')" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold text-center" v-if="errors.image_desktop">{{ errors.image_desktop[0] }}</div>
                </div>

                <!-- Mobile Image -->
                <div class="p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold text-dark dark:text-gray-200 m-0"><i class="bi bi-phone me-2"></i>Ảnh Mobile</label>
                    <div class="d-flex gap-1">
                      <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-2 fw-semibold" @click="$refs.mobileInput.click()" title="Thay ảnh"><i class="bi bi-upload"></i></button>
                      <button v-if="hasOldMobile || form.image_mobile" type="button" class="btn btn-sm btn-light text-danger border dark:border-gray-600 rounded-pill px-2" @click="removeMobileImage" title="Gỡ ảnh"><i class="bi bi-trash3"></i></button>
                    </div>
                  </div>

                  <div class="bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden shadow-sm border dark:border-gray-600 mx-auto d-flex align-items-center justify-content-center" 
                       style="width: 140px; height: 220px; padding: 4px;">
                    <!-- ĐÃ CẬP NHẬT: Thêm class img-zoomable và sự kiện click phóng to -->
                    <img v-if="previewMobile" :src="previewMobile" class="object-fit-cover w-100 h-100 rounded-2 img-zoomable" @click="openImageZoom(previewMobile)" @error="handleImageError">
                    <div v-else class="text-muted text-center small px-2 fst-italic">Cắt từ Desktop</div>
                  </div>
                  <input type="file" ref="mobileInput" @change="e => onImageChange(e, 'image_mobile')" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold text-center" v-if="errors.image_mobile">{{ errors.image_mobile[0] }}</div>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-banners' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy bỏ</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Cập Nhật Ngay
          </button>
        </div>
      </form>
    </div>
    
    <div v-else class="text-center py-5"><span class="spinner-border text-urban"></span></div>

    <!-- ĐÃ BỔ SUNG: MODAL PHÓNG TO ẢNH (KÈM HIỆU ỨNG BLUR KÍNH) -->
    <div class="modal fade glass-modal" id="imageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
          <div class="modal-header border-0 pb-0 justify-content-end position-absolute top-0 end-0 w-100" style="z-index: 2;">
            <button type="button" class="btn-close btn-close-white m-3" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
          </div>
          <div class="modal-body text-center p-0 position-relative">
            <img :src="zoomedImageUrl" class="img-fluid rounded-4 shadow-lg border border-secondary-subtle dark:border-gray-600" style="max-height: 85vh; object-fit: contain; background-color: var(--color-c-effect);">
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const bannerId = parseInt(route.params.id);

const isLoading = ref(true);
const isSaving = ref(false);
const errors = ref({});

const previewDesktop = ref(null);
const previewMobile = ref(null);
const desktopInput = ref(null);
const mobileInput = ref(null);

const hasOldDesktop = ref(false);
const hasOldMobile = ref(false);

// State cho tính năng Phóng to ảnh
const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

const form = ref({ 
  title: '', target_url: '', position: '', 
  start_time: '', end_time: '', status: 'active',
  image_desktop: null, image_mobile: null, remove_mobile: false 
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const handleImageError = (e) => { e.target.style.display = 'none'; };

const formatDateForInput = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0,16); 
};

const fetchData = async () => {
  try {
    const resDetail = await axios.get(`http://127.0.0.1:8000/api/v1/admin/banners/${bannerId}`, { headers: getHeaders() });
    const b = resDetail.data.data;
    
    form.value.title = b.title;
    form.value.target_url = b.target_url || '';
    form.value.position = b.position;
    form.value.status = b.status;
    form.value.start_time = formatDateForInput(b.start_time);
    form.value.end_time = formatDateForInput(b.end_time);

    if (b.image_desktop) {
       previewDesktop.value = `http://127.0.0.1:8000/storage/${b.image_desktop}`;
       hasOldDesktop.value = true;
    }
    if (b.image_mobile) {
       previewMobile.value = `http://127.0.0.1:8000/storage/${b.image_mobile}`;
       hasOldMobile.value = true;
    }
  } catch (err) { 
    Swal.fire('Lỗi', 'Không tìm thấy Banner', 'error'); 
    router.push({ name: 'admin-banners' }); 
  } finally { isLoading.value = false; }
};

const onImageChange = (e, formKey) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không vượt quá 5MB', 'error'); return; }
  
  form.value[formKey] = file;
  if (formKey === 'image_mobile') form.value.remove_mobile = false;

  const reader = new FileReader();
  reader.onload = (e) => { 
      if (formKey === 'image_desktop') previewDesktop.value = e.target.result;
      else previewMobile.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeMobileImage = () => {
  previewMobile.value = null; 
  form.value.image_mobile = null; 
  form.value.remove_mobile = true; 
  hasOldMobile.value = false;
};

// Hàm mở Popup phóng to ảnh
const openImageZoom = (url) => {
  if (!url) return;
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) {
    imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  }
  imageZoomModalInstance.show();
};

const saveBanner = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT');

  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]); 
    }
  });

  try {
    await axios.post(`http://127.0.0.1:8000/api/v1/admin/banners/${bannerId}`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật Banner thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-banners' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};

onMounted(fetchData);

// Cleanup Modal chống rò rỉ bộ nhớ
onBeforeUnmount(() => {
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});
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

/* CSS cho ảnh phóng to */
.img-zoomable { cursor: zoom-in; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.img-zoomable:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

/* Class bọc ngoài Modal để làm mờ nền */
.glass-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-color: rgba(0, 0, 0, 0.4);
}
</style>