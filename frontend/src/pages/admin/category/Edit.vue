<template>
  <div class="category-edit-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-categories' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Cập Nhật Danh Mục</h3>
      </div>

      <form @submit.prevent="saveCategory" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái: Thông tin chính -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h5>
              
              <div class="row g-4">
                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Tên danh mục <span class="text-danger">*</span></label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.name" :class="{'is-invalid': errors.name}" placeholder="VD: Áo thun nam">
                  <div class="invalid-feedback">{{ errors.name?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Danh mục cha</label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.parent_id" :class="{'is-invalid': errors.parent_id}">
                    <option value="">-- Là danh mục Gốc --</option>
                    <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.parent_id?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Thứ tự hiển thị</label>
                  <div class="p-2 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 d-flex align-items-center justify-content-between h-100" style="min-height: 42px;">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-sort-numeric-down text-urban me-2 fs-5"></i>
                      <span class="fw-bold text-dark dark:text-white">{{ form.sort_order ? '#' + form.sort_order : 'Chưa cấp phát' }}</span>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-normal" title="Được hệ thống tự động quản lý">Tự động</span>
                  </div>
                  <small class="text-muted mt-1 d-block fst-italic"><i class="bi bi-info-circle me-1"></i>Sử dụng thao tác Kéo/Thả ngoài trang Danh sách để thay đổi.</small>
                </div>

                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Mô tả danh mục</label>
                  <textarea class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                            rows="3" v-model="form.description" placeholder="Nhập mô tả ngắn về danh mục này..."></textarea>
                </div>

                <!-- BOX NHẬP THUỘC TÍNH SCHEMA XỊN XÒ -->
                <div class="col-12 mt-4">
                  <div class="p-4 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-4">
                    <label class="form-label fw-bold text-urban"><i class="bi bi-tags-fill me-2"></i>Cấu hình Thuộc tính (Schema)</label>
                    <p class="text-muted small mb-3">Thêm các thuộc tính biến thể mà danh mục này sẽ có (VD: Kích cỡ, Màu sắc, Chất liệu...).</p>
                    
                    <div class="input-group shadow-sm mb-3">
                      <span class="input-group-text bg-white dark:bg-[#1a2533] border-end-0 text-muted"><i class="bi bi-tag"></i></span>
                      <!-- ĐÃ FIX: Dùng keydown.enter.prevent thay vì keyup để chặn Form bị Submit -->
                      <input type="text" class="form-control border-start-0 py-2 dark:bg-[#1a2533] dark:text-white dark:border-gray-700" 
                             v-model="newAttribute" @keydown.enter.prevent="addAttribute" placeholder="VD: Màu, Size (ngăn cách bằng dấu phẩy)">
                      <button class="btn btn-urban px-4 fw-bold" type="button" @click.prevent="addAttribute">Thêm</button>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                      <div v-for="(attr, index) in attributesList" :key="index" class="badge bg-white dark:bg-[#1a2533] border dark:border-gray-600 text-dark dark:text-gray-200 shadow-sm py-2 px-3 d-flex align-items-center gap-2">
                        <span style="font-size: 0.85rem;">{{ attr }}</span>
                        <i class="bi bi-x-circle-fill text-danger cursor-pointer hover-zoom" @click="removeAttribute(index)"></i>
                      </div>
                      <span v-if="attributesList.length === 0" class="text-muted small fst-italic mt-1">Chưa có thuộc tính nào được thêm.</span>
                    </div>
                  </div>
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

                <!-- Thumbnail -->
                <div class="mb-4 text-center p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 d-block mb-3">Ảnh đại diện</label>
                  <img :src="previewThumbnail" class="rounded-4 object-fit-cover shadow-sm mb-3 bg-white dark:bg-[#1a2533] p-1" style="width: 100%; height: 160px;">
                  <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3 fw-semibold flex-grow-1" @click="$refs.thumbnailInput.click()">
                      <i class="bi bi-cloud-upload"></i> Thay ảnh
                    </button>
                    <button v-if="hasOldThumbnail || form.thumbnail" type="button" class="btn btn-sm btn-light text-danger border dark:border-gray-600 rounded-pill px-3" @click="removeImage('thumbnail')">
                      Xóa
                    </button>
                  </div>
                  <input type="file" ref="thumbnailInput" @change="onThumbnailChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.thumbnail">{{ errors.thumbnail[0] }}</div>
                </div>

                <!-- Size Guide -->
                <div class="text-center p-3 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 d-block mb-3">Bảng size (Size Guide)</label>
                  <img :src="previewSizeGuide" class="rounded-4 object-fit-cover shadow-sm mb-3 bg-white dark:bg-[#1a2533] p-1" style="width: 100%; height: 160px;">
                  <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3 fw-semibold flex-grow-1" @click="$refs.sizeGuideInput.click()">
                      <i class="bi bi-rulers"></i> Thay ảnh
                    </button>
                    <button v-if="hasOldSizeGuide || form.size_guide_image" type="button" class="btn btn-sm btn-light text-danger border dark:border-gray-600 rounded-pill px-3" @click="removeImage('size_guide_image')">
                      Xóa
                    </button>
                  </div>
                  <input type="file" ref="sizeGuideInput" @change="onSizeGuideChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.size_guide_image">{{ errors.size_guide_image[0] }}</div>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-categories' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy</router-link>
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
const categoryId = parseInt(route.params.id);

const isLoading = ref(true);
const isSaving = ref(false);
const parentCategories = ref([]);
const errors = ref({});

// Logic quản lý hình ảnh
const previewThumbnail = ref(defaultImage);
const previewSizeGuide = ref(defaultImage);
const thumbnailInput = ref(null);
const sizeGuideInput = ref(null);
const hasOldThumbnail = ref(false);
const hasOldSizeGuide = ref(false);

// Logic Schema Tag
const newAttribute = ref('');
const attributesList = ref([]);

const form = ref({ 
  name: '', parent_id: '', description: '', sort_order: '', status: 'active',
  thumbnail: null, remove_thumbnail: false,
  size_guide_image: null, remove_size_guide: false
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const fetchData = async () => {
  try {
    const [resCats, resDetail] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get(`http://127.0.0.1:8000/api/v1/admin/categories/${categoryId}`, { headers: getHeaders() })
    ]);

    const allCats = Array.isArray(resCats.data.data) ? resCats.data.data : [];
    parentCategories.value = allCats.filter(c => !c.deleted_at && !c.parent_id && c.id !== categoryId);

    const cat = resDetail.data.data;
    form.value.name = cat.name;
    form.value.parent_id = cat.parent_id || '';
    form.value.description = cat.description;
    form.value.sort_order = cat.sort_order;
    form.value.status = cat.status;

    if (cat.attributes_schema && Array.isArray(cat.attributes_schema)) {
      attributesList.value = cat.attributes_schema;
    }

    if (cat.thumbnail) {
       previewThumbnail.value = `http://127.0.0.1:8000/storage/${cat.thumbnail}`;
       hasOldThumbnail.value = true;
    }
    if (cat.size_guide_image) {
       previewSizeGuide.value = `http://127.0.0.1:8000/storage/${cat.size_guide_image}`;
       hasOldSizeGuide.value = true;
    }

  } catch (err) { 
    Swal.fire('Lỗi', 'Không tìm thấy danh mục', 'error'); 
    router.push({ name: 'admin-categories' }); 
  } finally { isLoading.value = false; }
};

// Xử lý File
const handleImageSelect = (e, formKey, previewRef, removeKey, hasOldRef) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  form.value[formKey] = file;
  form.value[removeKey] = false;
  const reader = new FileReader();
  reader.onload = (e) => { previewRef.value = e.target.result; };
  reader.readAsDataURL(file);
};

const onThumbnailChange = (e) => handleImageSelect(e, 'thumbnail', previewThumbnail, 'remove_thumbnail', hasOldThumbnail);
const onSizeGuideChange = (e) => handleImageSelect(e, 'size_guide_image', previewSizeGuide, 'remove_size_guide', hasOldSizeGuide);

const removeImage = (type) => {
  if (type === 'thumbnail') {
    previewThumbnail.value = defaultImage; form.value.thumbnail = null; form.value.remove_thumbnail = true; hasOldThumbnail.value = false;
  } else {
    previewSizeGuide.value = defaultImage; form.value.size_guide_image = null; form.value.remove_size_guide = true; hasOldSizeGuide.value = false;
  }
};

// ĐÃ FIX: Hỗ trợ chẻ Tags bằng dấu phẩy (,)
const addAttribute = () => {
  const val = newAttribute.value.trim();
  if (!val) return;
  
  // Tách chuỗi bằng dấu phẩy, dọn dẹp khoảng trắng 2 đầu và bỏ các phần tử rỗng
  const newAttrs = val.split(',').map(item => item.trim()).filter(item => item !== '');
  
  newAttrs.forEach(attr => {
      // Chỉ push vào nếu mảng chưa có tag này
      if (!attributesList.value.includes(attr)) {
          attributesList.value.push(attr);
      }
  });
  
  newAttribute.value = '';
};

const removeAttribute = (index) => {
  attributesList.value.splice(index, 1);
};

const saveCategory = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT');

  Object.keys(form.value).forEach(key => { 
    // Tuyệt đối KHÔNG append sort_order để hệ thống Backend tự xử lý
    if(key !== 'sort_order' && form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]); 
    }
  });

  attributesList.value.forEach((attr, index) => {
    formData.append(`attributes_schema[${index}]`, attr);
  });

  try {
    await axios.post(`http://127.0.0.1:8000/api/v1/admin/categories/${categoryId}`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật danh mục thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-categories' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};

onMounted(fetchData);
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.hover\:text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.cursor-pointer { cursor: pointer; }
.hover-zoom:hover { transform: scale(1.15); transition: 0.2s; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>