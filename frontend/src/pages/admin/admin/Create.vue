<!-- File: frontend/src/pages/admin/admin/Create.vue -->
<template>
  <div class="admin-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-admins' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Tạo Tài Khoản Mới</h3>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4">
            <form @submit.prevent="saveAdmin">
              <div class="row g-4">
                <div class="col-md-6 text-center text-md-start">
                   <label class="form-label fw-bold text-dark dark:text-gray-200">Ảnh đại diện</label>
                   <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-4">
                      <div class="position-relative">
                         <img :src="previewAvatar" class="rounded-circle border shadow-sm object-fit-cover" style="width: 100px; height: 100px;">
                         <button type="button" @click="triggerUpload" class="btn btn-sm btn-urban rounded-circle position-absolute bottom-0 end-0 p-1 shadow">
                            <i class="bi bi-camera-fill text-white px-1"></i>
                         </button>
                      </div>
                      <div class="text-start">
                         <small class="text-muted d-block mb-2">Định dạng: JPG, PNG, WEBP (Max 5MB)</small>
                         <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3" @click="triggerUpload">Chọn ảnh</button>
                         <input type="file" ref="fileInput" @change="onFileChange" class="d-none" accept="image/*">
                      </div>
                   </div>
                   <div class="text-danger small mt-2 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Họ và tên <span class="text-danger">*</span></label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.fullname" :class="{'is-invalid': errors.fullname}" placeholder="Nhập họ tên đầy đủ">
                  <div class="invalid-feedback">{{ errors.fullname?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Email công việc <span class="text-danger">*</span></label>
                  <input type="email" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.email" :class="{'is-invalid': errors.email}" placeholder="example@zyro.vn">
                  <div class="invalid-feedback">{{ errors.email?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPass ? 'text' : 'password'" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự">
                    <button class="btn btn-outline-secondary border-start-0" type="button" @click="showPass = !showPass"><i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                    <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- FIX: Thêm trường xác nhận mật khẩu -->
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPass ? 'text' : 'password'" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu">
                    <button class="btn btn-outline-secondary border-start-0" type="button" @click="showPass = !showPass"><i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Số điện thoại</label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.phone" :class="{'is-invalid': errors.phone}" placeholder="09xx xxx xxx">
                  <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Chức vụ (Role) <span class="text-danger">*</span></label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.role_id" :class="{'is-invalid': errors.role_id}">
                    <option value="">Chọn một chức vụ...</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.label }} (Cấp {{ role.level }})</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.role_id?.[0] }}</div>
                </div>

                <!-- ĐỊA CHỈ DROPDOWN 3 CẤP -->
                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Địa chỉ liên hệ</label>
                  <div class="row g-2 mb-2">
                    <div class="col-md-4">
                      <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.province" @change="onProvinceChange" :disabled="loadingProvinces">
                        <option value="">{{ loadingProvinces ? '⏳ Đang tải...' : '-- Chọn Tỉnh/Thành --' }}</option>
                        <option v-for="p in provinces" :key="p.code" :value="p.name">{{ p.name }}</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.district" @change="onDistrictChange" :disabled="!addressHelper.province || loadingDistricts">
                        <option value="">{{ loadingDistricts ? '⏳ Đang tải...' : '-- Chọn Quận/Huyện --' }}</option>
                        <option v-for="d in districts" :key="d.code" :value="d.name">{{ d.name }}</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.ward" :disabled="!addressHelper.district || loadingWards">
                        <option value="">{{ loadingWards ? '⏳ Đang tải...' : '-- Chọn Phường/Xã --' }}</option>
                        <option v-for="w in wards" :key="w.code" :value="w.name">{{ w.name }}</option>
                      </select>
                    </div>
                  </div>
                  <input type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.detail" placeholder="Số nhà, tên đường, tòa nhà...">
                </div>
              </div>

              <hr class="my-4 dark:border-gray-700">
              <div class="text-end">
                <router-link :to="{ name: 'admin-admins' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm fw-bold text-decoration-none border">Hủy</router-link>
                <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
                  <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Tài Khoản
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h6 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Quy chuẩn tài khoản</h6>
              <ul class="list-unstyled small text-muted dark:text-gray-400">
                <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2"></i> Email dùng để đăng nhập và không thể trùng lặp.</li>
                <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2"></i> Tài khoản mới mặc định trạng thái "Hoạt động".</li>
                <li class="mb-3 d-flex"><i class="bi bi-check2-circle text-success me-2"></i> Chức vụ sẽ quyết định các trang mà nhân sự được phép truy cập.</li>
              </ul>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const router = useRouter();
const roles = ref([]);
const previewAvatar = ref(defaultAvatar);
const fileInput = ref(null);
const isSaving = ref(false);
const showPass = ref(false);
const errors = ref({});

// Logic Dropdown Địa chỉ
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const loadingProvinces = ref(false);
const loadingDistricts = ref(false);
const loadingWards = ref(false);
const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

const form = ref({ 
  fullname: '', email: '', password: '', password_confirmation: '', 
  role_id: '', phone: '', address: '', avatar: null 
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const fetchProvinces = async () => {
  loadingProvinces.value = true;
  try {
    console.log('🔄 Fetching provinces...');
    const res = await axios.get('https://provinces.open-api.vn/api/p/', { timeout: 5000 });
    if (Array.isArray(res.data) && res.data.length > 0) {
      provinces.value = res.data;
      console.log('✅ Provinces loaded:', res.data.length);
    } else {
      throw new Error('Invalid data format');
    }
  } catch (err) { 
    console.error("❌ Lỗi tải Tỉnh thành:", err.message);
  } finally {
    loadingProvinces.value = false;
  }
};

const onProvinceChange = async () => {
  addressHelper.district = ''; addressHelper.ward = ''; districts.value = []; wards.value = [];
  const p = provinces.value.find(i => i.name === addressHelper.province);
  if (p) {
    loadingDistricts.value = true;
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`, { timeout: 5000 });
      if (res.data && res.data.districts) {
        districts.value = res.data.districts;
      }
    } catch (err) {
      console.error("❌ Lỗi tải Quận huyện:", err.message);
    } finally {
      loadingDistricts.value = false;
    }
  }
};

const onDistrictChange = async () => {
  addressHelper.ward = ''; wards.value = [];
  const d = districts.value.find(i => i.name === addressHelper.district);
  if (d) {
    loadingWards.value = true;
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`, { timeout: 5000 });
      if (res.data && res.data.wards) {
        wards.value = res.data.wards;
      }
    } catch (err) {
      console.error("❌ Lỗi tải Phường xã:", err.message);
    } finally {
      loadingWards.value = false;
    }
  }
};

const fetchData = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/roles', { headers: getHeaders() });
    roles.value = res.data.data.filter(r => !r.deleted_at);
    fetchProvinces();
  } catch (err) { console.error(err); }
};

const triggerUpload = () => fileInput.value.click();
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không được vượt quá 5MB', 'error'); return; }
  form.value.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};

const saveAdmin = async () => {
  // Gom địa chỉ
  const fullAddr = [addressHelper.detail, addressHelper.ward, addressHelper.district, addressHelper.province]
                    .filter(Boolean).join(', ');
  form.value.address = fullAddr;

  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/admins', formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã tạo tài khoản nhân sự mới', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-admins' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Không thể tạo tài khoản', 'error');
  } finally { isSaving.value = false; }
};

onMounted(fetchData);
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.hover\:text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.2) !important; }
</style>