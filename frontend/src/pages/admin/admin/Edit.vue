<!-- File: frontend/src/pages/admin/admin/Edit.vue -->
<template>
  <div class="admin-edit-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
          <router-link :to="{ name: 'admin-admins' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
            <i class="bi bi-arrow-left-circle fs-3"></i>
          </router-link>
          <h3 class="fw-bold text-dark dark:text-white mb-0">Thiết Lập Tài Khoản</h3>
        </div>
        
        <!-- Tabs chuyển đổi chuyên nghiệp -->
        <div class="bg-white dark:bg-[#1a2533] p-1 rounded-pill shadow-sm d-flex border dark:border-gray-700">
          <button @click="activeTab = 'profile'" 
                  class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all"
                  :class="activeTab === 'profile' ? 'bg-urban text-white' : 'text-muted'">
            <i class="bi bi-person-circle me-1"></i> Hồ sơ
          </button>
          <button @click="activeTab = 'security'" 
                  class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all"
                  :class="activeTab === 'security' ? 'bg-danger text-white' : 'text-muted'">
            <i class="bi bi-shield-lock me-1"></i> Bảo mật
          </button>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4">
            
            <form @submit.prevent="saveAdmin" autocomplete="off">
              <input style="display:none" type="text" name="fakeusernameremembered"/>
              <input style="display:none" type="password" name="fakepasswordremembered"/>

              <!-- TAB 1: THÔNG TIN CƠ BẢN -->
              <div v-show="activeTab === 'profile'">
                <div class="row g-4">
                  <!-- Avatar Section -->
                  <div class="col-12 text-center text-md-start mb-2">
                     <label class="form-label fw-bold text-dark dark:text-gray-200">Ảnh đại diện</label>
                     <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-4">
                        <div class="position-relative">
                           <img :src="previewAvatar" class="rounded-circle border shadow-sm object-fit-cover" style="width: 100px; height: 100px;">
                           <button type="button" @click="triggerUpload" class="btn btn-sm btn-urban rounded-circle position-absolute bottom-0 end-0 p-1 shadow">
                              <i class="bi bi-camera-fill text-white px-1"></i>
                           </button>
                        </div>
                        <div class="text-start">
                           <button type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3 mb-2" @click="triggerUpload">Thay đổi ảnh</button>
                           <button v-if="hasOldAvatar || form.avatar" type="button" class="btn btn-sm btn-light text-danger border d-block w-100 rounded-pill px-3" @click="removeAvatar">Gỡ ảnh</button>
                           <input type="file" ref="fileInput" @change="onFileChange" class="d-none" accept="image/*">
                        </div>
                     </div>
                     <div class="text-danger small mt-2 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.fullname" :class="{'is-invalid': errors.fullname}">
                    <div class="invalid-feedback">{{ errors.fullname?.[0] }}</div>
                  </div>

                  <div class="col-md-6">
                    <!-- ĐÃ FIX BẢO MẬT: Không binding v-model để ẩn mail đi, dùng :value để hiển thị data đã mask -->
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Email</label>
                    <input type="text" class="form-control py-2 bg-light dark:bg-[#2b3035] dark:text-gray-400" :value="maskEmail(form.email)" readonly>
                    <small class="text-muted fst-italic">Email là định danh, không thể thay đổi.</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Chức vụ <span class="text-danger">*</span></label>
                    <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.role_id" :class="{'is-invalid': errors.role_id}" :disabled="adminId === 1">
                      <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.label }}</option>
                    </select>
                    <small v-if="adminId === 1" class="text-danger">Super Admin gốc không thể đổi quyền.</small>
                  </div>

                  <!-- BOX EDIT TRẠNG THÁI -->
                  <div class="col-md-6">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Trạng thái hoạt động <span class="text-danger">*</span></label>
                    <select class="form-select py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                            v-model="form.status" 
                            :class="{'is-invalid': errors.status}" 
                            :disabled="adminId === 1 || adminId === currentUserId">
                      <option value="active">Hoạt động</option>
                      <option value="locked">Bị khóa</option>
                    </select>
                    <div class="invalid-feedback">{{ errors.status?.[0] }}</div>
                    <small v-if="adminId === 1" class="text-danger d-block mt-1">Không thể khóa Super Admin gốc.</small>
                    <small v-else-if="adminId === currentUserId" class="text-warning d-block mt-1">Bạn không thể tự khóa chính mình.</small>
                  </div>

                  <!-- ĐỊA CHỈ DROPDOWN 3 CẤP -->
                  <div class="col-12">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Số điện thoại liên hệ</label>
                    <input type="text" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.phone" :class="{'is-invalid': errors.phone}">
                    <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Địa chỉ thường trú</label>
                    <div class="row g-2">
                      <div class="col-md-4">
                        <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.province" @change="onProvinceChange">
                          <option value="">-- Chọn Tỉnh/Thành --</option>
                          <option v-for="p in provinces" :key="p.code" :value="p.name">{{ p.name }}</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.district" @change="onDistrictChange" :disabled="!addressHelper.province">
                          <option value="">-- Chọn Quận/Huyện --</option>
                          <option v-for="d in districts" :key="d.code" :value="d.name">{{ d.name }}</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.ward" :disabled="!addressHelper.district">
                          <option value="">-- Chọn Phường/Xã --</option>
                          <option v-for="w in wards" :key="w.code" :value="w.name">{{ w.name }}</option>
                        </select>
                      </div>
                      <div class="col-12 mt-2">
                        <input type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.detail" placeholder="Số nhà, tên đường...">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TAB 2: ĐỔI MẬT KHẨU -->
              <div v-show="activeTab === 'security'">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 dark:bg-yellow-900/20 dark:text-yellow-200">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  Chỉ nhập vào các ô bên dưới nếu bạn thực sự muốn thay đổi mật khẩu đăng nhập của nhân sự này.
                </div>
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Mật khẩu mới</label>
                    <div class="input-group">
                      <input :type="showPass ? 'text' : 'password'" autocomplete="new-password" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự">
                      <button class="btn btn-outline-secondary border-start-0" type="button" @click="showPass = !showPass"><i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                      <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Nhập lại mật khẩu mới <span class="text-danger" v-if="form.password">*</span></label>
                    <div class="input-group">
                      <input :type="showPass ? 'text' : 'password'" autocomplete="new-password" class="form-control py-2 dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password_confirmation" placeholder="Xác nhận lại mật khẩu">
                      <button class="btn btn-outline-secondary border-start-0" type="button" @click="showPass = !showPass"><i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                    </div>
                  </div>
                </div>
              </div>

              <hr class="my-4 dark:border-gray-700">
              <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc nhập</p>
                <div class="text-end">
                  <router-link :to="{ name: 'admin-admins' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-2 px-4 shadow-sm border fw-bold text-decoration-none">Hủy bỏ</router-link>
                  <button type="submit" class="btn btn-urban text-white px-5 fw-bold shadow-sm" :disabled="isSaving">
                    <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Cập Nhật Ngay
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        
        <!-- SIDEBAR INFO -->
        <div class="col-lg-4 mt-4 mt-lg-0">
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 text-center sticky-top" style="top: 20px; z-index: 1;">
              <div class="mb-4 position-relative d-inline-block">
                 <img :src="previewAvatar" class="rounded-circle border border-4 border-white dark:border-gray-700 shadow object-fit-cover" style="width: 140px; height: 140px;">
                 <span class="position-absolute bottom-0 end-0 border border-white border-3 rounded-circle p-2" 
                       :class="form.status === 'active' ? 'bg-success' : 'bg-warning'" 
                       :title="form.status === 'active' ? 'Tài khoản đang hoạt động' : 'Tài khoản đang bị khóa'" 
                       style="width: 25px; height: 25px;"></span>
              </div>
              <h5 class="fw-bold dark:text-white mb-1">{{ form.fullname || 'Họ và tên' }}</h5>
              <!-- ĐÃ FIX BẢO MẬT: Mask Email Sidebar -->
              <p class="text-muted mb-4 small font-monospace">{{ maskEmail(form.email) || 'email@example.com' }}</p>
              
              <div class="p-3 bg-light dark:bg-[#212529] rounded-4 text-start border border-dashed dark:border-gray-700">
                 <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Cấp quyền:</span>
                    <span class="fw-bold text-urban small">Cấp {{ roles.find(r => r.id === form.role_id)?.level || 'N/A' }}</span>
                 </div>
                 <div class="d-flex justify-content-between">
                    <span class="small text-muted">ID Hệ thống:</span>
                    <span class="font-monospace small">#{{ adminId }}</span>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
    
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <div class="spinner-border text-urban mb-3" style="width: 3rem; height: 3rem;"></div>
      <p class="text-muted fw-bold">Đang tải hồ sơ nhân sự...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const router = useRouter();
const route = useRoute();
const adminId = parseInt(route.params.id);

const currentAdmin = JSON.parse(localStorage.getItem('admin_info') || '{}');
const currentUserId = currentAdmin.id;

const activeTab = ref('profile'); // 'profile' hoặc 'security'
const isLoading = ref(true);
const isSaving = ref(false);
const showPass = ref(false);
const hasOldAvatar = ref(false);
const errors = ref({});

// Hàm che giấu Email (Data Masking)
const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0];
  const domain = parts[1];
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  return name.substring(0, 3) + '***@' + domain;
};

// Data cho Dropdown địa chỉ
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

const roles = ref([]);
const previewAvatar = ref(defaultAvatar);
const fileInput = ref(null);

// Mặc dù form.email bị ẩn một nửa trên giao diện bằng Masking, nhưng khi đẩy API vẫn lấy biến `form.email` đầy đủ
const form = ref({ 
  fullname: '', email: '', password: '', password_confirmation: '', 
  role_id: '', phone: '', address: '', avatar: null, remove_avatar: false, status: 'active'
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

// 1. FETCH ĐỊA CHÍNH (Sử dụng API Provinces Việt Nam)
const fetchProvinces = async () => {
  try {
    const res = await axios.get('https://provinces.open-api.vn/api/p/');
    provinces.value = res.data;
  } catch (err) { console.error("Lỗi API Tỉnh thành"); }
};

const onProvinceChange = async () => {
  addressHelper.district = ''; addressHelper.ward = ''; districts.value = []; wards.value = [];
  const p = provinces.value.find(i => i.name === addressHelper.province);
  if (p) {
    const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`);
    districts.value = res.data.districts;
  }
};

const onDistrictChange = async () => {
  addressHelper.ward = ''; wards.value = [];
  const d = districts.value.find(i => i.name === addressHelper.district);
  if (d) {
    const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`);
    wards.value = res.data.wards;
  }
};

// 2. FETCH DATA HỆ THỐNG
const fetchData = async () => {
  try {
    const [resRole, resAdmin] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/roles', { headers: getHeaders() }),
      axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${adminId}`, { headers: getHeaders() }),
      fetchProvinces()
    ]);
    
    roles.value = resRole.data.data;
    const admin = resAdmin.data.data;
    form.value.fullname = admin.fullname;
    form.value.email = admin.email;
    form.value.role_id = admin.role_id;
    form.value.phone = admin.phone;
    form.value.address = admin.address;
    form.value.status = admin.status;

    // Phân tách chuỗi địa chỉ
    if (admin.address) {
       const parts = admin.address.split(', ').map(p => p.trim());
       if (parts.length >= 4) {
          addressHelper.province = parts[parts.length - 1];
          await onProvinceChange();
          addressHelper.district = parts[parts.length - 2];
          await onDistrictChange();
          addressHelper.ward = parts[parts.length - 3];
          addressHelper.detail = parts.slice(0, parts.length - 3).join(', ');
       } else {
          addressHelper.detail = admin.address;
       }
    }

    if (admin.avatar_url) {
       previewAvatar.value = `http://127.0.0.1:8000/storage/${admin.avatar_url}`;
       hasOldAvatar.value = true;
    }
  } catch (err) { 
    Swal.fire('Lỗi', 'Không tìm thấy thông tin tài khoản', 'error'); 
    router.push({ name: 'admin-admins' }); 
  } finally { isLoading.value = false; }
};

// 3. XỬ LÝ FILE & AVATAR
const triggerUpload = () => fileInput.value.click();
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Dung lượng ảnh tối đa 5MB', 'warning'); return; }
  form.value.avatar = file; form.value.remove_avatar = false;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};
const removeAvatar = () => { 
  previewAvatar.value = defaultAvatar; 
  form.value.avatar = null; 
  form.value.remove_avatar = true; 
  hasOldAvatar.value = false; 
};

// 4. LƯU DỮ LIỆU
const saveAdmin = async () => {
  // Ghép địa chỉ trước khi gửi
  const fullAddr = [addressHelper.detail, addressHelper.ward, addressHelper.district, addressHelper.province]
                    .filter(Boolean).join(', ');
  form.value.address = fullAddr;

  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT');
  
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') {
      formData.append(key, form.value[key]);
    }
  });

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${adminId}`, formData, { 
      headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } 
    });
    Swal.fire({ icon: 'success', title: 'Cập nhật thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-admins' });
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors;
      // Tự chuyển tab nếu lỗi nằm ở mật khẩu
      if (errors.value.password) activeTab.value = 'security';
      else activeTab.value = 'profile';
    } else {
      Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
    }
  } finally { isSaving.value = false; }
};

onMounted(fetchData);
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); transform: translateY(-1px); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: 0 0 0 0.25rem rgba(84, 119, 146, 0.15) !important; }
.sticky-top { transition: all 0.3s ease; }
</style>