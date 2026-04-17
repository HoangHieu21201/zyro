<template>
  <div class="user-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-users' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Thêm Khách Hàng</h3>
      </div>

      <form @submit.prevent="saveUser" autocomplete="off">
        <div class="row g-4">
          <!-- Cột Trái: Thông tin chính -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h5>
              
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Họ và tên <span class="text-danger">*</span></label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.full_name" :class="{'is-invalid': errors.full_name}" placeholder="VD: Nguyễn Văn A">
                  <div class="invalid-feedback">{{ errors.full_name?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Số điện thoại</label>
                  <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.phone" :class="{'is-invalid': errors.phone}" @input="validatePhone" placeholder="VD: 098xxxxxxx">
                  <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                </div>

                <div class="col-md-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Email đăng nhập <span class="text-danger">*</span></label>
                  <input type="email" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.email" :class="{'is-invalid': errors.email}" placeholder="example@gmail.com">
                  <div class="invalid-feedback">{{ errors.email?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Mật khẩu <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input :type="showPass1 ? 'text' : 'password'" autocomplete="new-password" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự">
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass1 = !showPass1">
                      <i class="bi" :class="showPass1 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                    <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input :type="showPass2 ? 'text' : 'password'" autocomplete="new-password" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu">
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass2 = !showPass2">
                      <i class="bi" :class="showPass2 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Giới tính</label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.gender">
                    <option value="">-- N/A --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Ngày sinh</label>
                  <input type="date" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.birthday">
                </div>

                <!-- BOX ĐỊA CHỈ DEFAULT -->
                <div class="col-12 mt-4">
                  <div class="p-4 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-4">
                    <label class="form-label fw-bold text-urban"><i class="bi bi-geo-alt-fill me-2"></i>Địa chỉ mặc định (Tùy chọn)</label>
                    <div class="row g-3 mt-1">
                      <div class="col-md-4">
                        <select class="form-select bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-600 shadow-sm" v-model="addressHelper.province" @change="onProvinceChange" :disabled="loadingProvinces">
                          <option value="">{{ loadingProvinces ? '⏳ Đang tải...' : '-- Chọn Tỉnh/Thành --' }}</option>
                          <option v-for="p in provinces" :key="p.code" :value="p.name">{{ p.name }}</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select class="form-select bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-600 shadow-sm" v-model="addressHelper.district" @change="onDistrictChange" :disabled="!addressHelper.province || loadingDistricts">
                          <option value="">{{ loadingDistricts ? '⏳ Đang tải...' : '-- Chọn Quận/Huyện --' }}</option>
                          <option v-for="d in districts" :key="d.code" :value="d.name">{{ d.name }}</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select class="form-select bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-600 shadow-sm" v-model="addressHelper.ward" :disabled="!addressHelper.district || loadingWards">
                          <option value="">{{ loadingWards ? '⏳ Đang tải...' : '-- Chọn Phường/Xã --' }}</option>
                          <option v-for="w in wards" :key="w.code" :value="w.name">{{ w.name }}</option>
                        </select>
                      </div>
                      <div class="col-12">
                        <input type="text" class="form-control bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-600 shadow-sm" v-model="form.shipping_address" placeholder="Số nhà, tên đường cụ thể...">
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          
          <!-- Cột Phải: Hình ảnh & Trạng thái -->
          <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
                <h5 class="fw-bold text-urban mb-4"><i class="bi bi-image me-2"></i>Avatar & Trạng thái</h5>

                <div class="mb-4 text-center p-4 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <img :src="previewAvatar" class="rounded-circle object-fit-cover shadow-sm mb-3 border border-3 border-white dark:border-gray-600" style="width: 140px; height: 140px;">
                  <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-semibold w-100" @click="$refs.avatarInput.click()">
                    <i class="bi bi-camera-fill me-1"></i> Tải ảnh lên
                  </button>
                  <input type="file" ref="avatarInput" @change="onAvatarChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-bold text-dark dark:text-gray-200">Trạng thái tài khoản</label>
                  <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.status">
                    <option value="active">Hoạt động bình thường</option>
                    <option value="locked">Bị khóa (Locked)</option>
                  </select>
                </div>
             </div>
          </div>
        </div>
        
        <hr class="my-4 dark:border-gray-700">
        <div class="text-end">
          <router-link :to="{ name: 'admin-users' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy bỏ</router-link>
          <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Tài Khoản
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/avatar1.png';

const router = useRouter();
const isSaving = ref(false);
const errors = ref({});

const showPass1 = ref(false);
const showPass2 = ref(false);

const previewAvatar = ref(defaultImage);
const avatarInput = ref(null);

// Data Dropdown Địa chỉ
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const loadingProvinces = ref(false);
const loadingDistricts = ref(false);
const loadingWards = ref(false);
const addressHelper = reactive({ province: '', district: '', ward: '' });

const form = ref({ 
  full_name: '', email: '', password: '', password_confirmation: '', phone: '',
  gender: '', birthday: '', status: 'active', avatar: null,
  shipping_address: '', city: '', district: '', ward: ''
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const fetchProvinces = async () => {
  loadingProvinces.value = true;
  try { 
    const res = await axios.get('https://provinces.open-api.vn/api/p/', { timeout: 5000 }); 
    if (Array.isArray(res.data) && res.data.length > 0) {
      provinces.value = res.data;
    } else {
      throw new Error('Invalid format');
    }
  } catch (err) {
    console.error('Lỗi API:', err);
  } finally {
    loadingProvinces.value = false;
  }
};

const onProvinceChange = async () => {
  loadingDistricts.value = true;
  try {
    addressHelper.district = ''; addressHelper.ward = ''; districts.value = []; wards.value = [];
    form.value.city = addressHelper.province;
    const p = provinces.value.find(i => i.name === addressHelper.province);
    if (p) { 
      const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`, { timeout: 5000 }); 
      if (res.data && res.data.districts) {
        districts.value = res.data.districts;
      }
    }
  } catch (err) {
    console.error('Lỗi API Quận:', err);
  } finally {
    loadingDistricts.value = false;
  }
};

const onDistrictChange = async () => {
  loadingWards.value = true;
  try {
    addressHelper.ward = '';
    form.value.district = addressHelper.district;
    const d = districts.value.find(i => i.name === addressHelper.district);
    if (d) { 
      const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`, { timeout: 5000 }); 
      if (res.data && res.data.wards) {
        wards.value = res.data.wards;
      }
    }
  } catch (err) {
    console.error('Lỗi API Phường:', err);
  } finally {
    loadingWards.value = false;
  }
};

const validatePhone = (e) => { form.value.phone = e.target.value.replace(/\D/g, '').slice(0, 11); };

const onAvatarChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không vượt quá 5MB', 'error'); return; }
  form.value.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};

const saveUser = async () => {
  form.value.ward = addressHelper.ward;
  
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/users', formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Tạo tài khoản thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-users' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};

onMounted(fetchProvinces);
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