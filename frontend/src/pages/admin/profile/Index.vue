<!-- File: frontend/src/pages/admin/profile/Index.vue -->
<template>
  <div class="admin-profile-wrapper pb-5 mb-5">
    <!-- Hiệu ứng Header nền trang -->
    <div class="page-header-bg d-none d-md-block"></div>

    <div class="container-fluid py-4 position-relative" style="z-index: 2;" v-if="!isLoading">
      <div class="mb-4">
        <h3 class="fw-bold text-dark dark:text-white mb-1">Cài Đặt Tài Khoản</h3>
      </div>

      <div class="row g-4">
        <!-- CỘT TRÁI: TỔNG QUAN & MENU DỌC -->
        <div class="col-xl-4 col-lg-5">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden sticky-top" style="top: 20px;">
            <!-- Cover Banners -->
            <div class="profile-cover" style="height: 120px; background: linear-gradient(135deg, var(--color-c-hover) 0%, #2b5876 100%);"></div>
            
            <div class="card-body px-4 pb-4 pt-0 text-center position-relative">
              <!-- Avatar có Overlay Upload -->
              <div class="avatar-upload-container mx-auto mb-3 shadow-sm rounded-circle bg-white dark:bg-[#1a2533]" style="width: 130px; height: 130px; margin-top: -65px; z-index: 10; padding: 4px;">
                <div class="position-relative w-100 h-100 rounded-circle overflow-hidden">
                  <img :src="previewAvatar" class="w-100 h-100 object-fit-cover">
                  <!-- Lớp phủ khi hover -->
                  <div class="avatar-overlay" @click="triggerUpload">
                    <i class="bi bi-camera-fill text-white fs-3"></i>
                  </div>
                </div>
                <!-- Nút gỡ ảnh (Chỉ hiện khi có ảnh thật) -->
                <button v-if="hasOldAvatar || formInfo.avatar" @click="removeAvatar" type="button" class="btn btn-danger btn-sm rounded-circle position-absolute shadow-sm btn-remove-avatar" title="Gỡ ảnh">
                  <i class="bi bi-x"></i>
                </button>
                <input type="file" ref="fileInput" @change="onFileChange" class="d-none" accept="image/*">
              </div>
              <div class="text-danger small mt-1 mb-2 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>

              <h4 class="fw-bold text-dark dark:text-white mb-1">{{ formInfo.fullname || 'Chưa cập nhật' }}</h4>
              <p class="text-muted small mb-3 font-monospace">{{ formInfo.email }}</p>
              
              <!-- ĐÃ FIX: Hiển thị đúng màu và chữ theo Role của tài khoản -->
              <div class="d-inline-block px-3 py-1.5 rounded-pill fw-semibold small mb-4 shadow-sm border" :class="currentAdminData.role?.badge_class || 'bg-secondary text-white'">
                <i class="bi bi-patch-check-fill me-1"></i> {{ currentAdminData.role?.label || 'Chưa gán quyền' }}
              </div>

              <!-- Menu Dọc -->
              <div class="nav flex-column nav-pills text-start profile-nav gap-2" role="tablist">
                <button class="nav-link text-start py-3 px-4 rounded-4 fw-semibold transition-all d-flex align-items-center" 
                        :class="{'active-nav shadow-sm': activeTab === 'profile'}" 
                        @click="activeTab = 'profile'">
                  <div class="icon-box me-3 d-flex align-items-center justify-content-center rounded-circle" :class="activeTab === 'profile' ? 'bg-white text-urban' : 'bg-light text-muted dark:bg-[#2b3035]'">
                    <i class="bi bi-person-lines-fill fs-5"></i>
                  </div>
                  Thông tin cá nhân
                </button>

                <button class="nav-link text-start py-3 px-4 rounded-4 fw-semibold transition-all d-flex align-items-center" 
                        :class="{'active-nav shadow-sm': activeTab === 'security'}" 
                        @click="activeTab = 'security'">
                  <div class="icon-box me-3 d-flex align-items-center justify-content-center rounded-circle" :class="activeTab === 'security' ? 'bg-white text-danger' : 'bg-light text-muted dark:bg-[#2b3035]'">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                  </div>
                  Bảo mật & Mật khẩu
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- CỘT PHẢI: NỘI DUNG CHI TIẾT -->
        <div class="col-xl-8 col-lg-7">
          
          <!-- TAB 1: THÔNG TIN CÁ NHÂN -->
          <div v-show="activeTab === 'profile'" class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 animation-fade-in">
            <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
              <h5 class="fw-bold text-dark dark:text-white mb-1">Chi Tiết Hồ Sơ</h5>
              <p class="text-muted small mb-0">Cập nhật thông tin liên hệ của bạn để đội ngũ tiện liên lạc.</p>
            </div>

            <form @submit.prevent="saveProfileInfo" autocomplete="off">
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase" style="letter-spacing: 0.5px;">Họ và tên</label>
                  <div class="input-group input-group-lg shadow-sm-hover">
                    <span class="input-group-text bg-light dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="formInfo.fullname" :class="{'is-invalid': errors.fullname}" placeholder="Nhập tên của bạn">
                    <div class="invalid-feedback">{{ errors.fullname?.[0] }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase" style="letter-spacing: 0.5px;">Số điện thoại</label>
                  <div class="input-group input-group-lg shadow-sm-hover">
                    <span class="input-group-text bg-light dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="formInfo.phone" :class="{'is-invalid': errors.phone}" placeholder="VD: 0987xxxxxx">
                    <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase" style="letter-spacing: 0.5px;">Email liên hệ</label>
                  <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-envelope-at-fill text-muted fs-4 me-3"></i>
                      <div>
                        <div class="fw-semibold text-dark dark:text-white font-monospace">{{ formInfo.email }}</div>
                        <div class="small text-muted">Email định danh dùng để đăng nhập.</div>
                      </div>
                    </div>
                    <i class="bi bi-lock-fill text-muted opacity-50" title="Không thể thay đổi"></i>
                  </div>
                </div>

                <!-- ĐỊA CHỈ DROPDOWN -->
                <div class="col-12 mt-5">
                  <h6 class="fw-bold text-dark dark:text-white mb-3"><i class="bi bi-geo-alt-fill text-urban me-2"></i>Khu vực sinh sống</h6>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <select class="form-select form-select-lg bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="addressHelper.province" @change="onProvinceChange" :disabled="loadingProvinces">
                        <option value="">{{ loadingProvinces ? '⏳ Đang tải...' : '-- Tỉnh/Thành --' }}</option>
                        <option v-for="p in provinces" :key="p.code" :value="p.name">{{ p.name }}</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select class="form-select form-select-lg bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="addressHelper.district" @change="onDistrictChange" :disabled="!addressHelper.province || loadingDistricts">
                        <option value="">{{ loadingDistricts ? '⏳ Đang tải...' : '-- Quận/Huyện --' }}</option>
                        <option v-for="d in districts" :key="d.code" :value="d.name">{{ d.name }}</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select class="form-select form-select-lg bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="addressHelper.ward" :disabled="!addressHelper.district || loadingWards">
                        <option value="">{{ loadingWards ? '⏳ Đang tải...' : '-- Phường/Xã --' }}</option>
                        <option v-for="w in wards" :key="w.code" :value="w.name">{{ w.name }}</option>
                      </select>
                    </div>
                    <div class="col-12 mt-3">
                      <div class="input-group input-group-lg shadow-sm-hover">
                        <span class="input-group-text bg-light dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-house"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="addressHelper.detail" placeholder="Số nhà, tên đường, khu phố...">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-5 pt-4 border-top dark:border-gray-700 text-end">
                <button type="submit" class="btn btn-urban btn-lg text-white px-5 fw-bold shadow" :disabled="isSavingInfo">
                  <span v-if="isSavingInfo" class="spinner-border spinner-border-sm me-2"></span> 
                  <i class="bi bi-floppy2-fill me-2" v-else></i> Lưu Thay Đổi
                </button>
              </div>
            </form>
          </div>

          <!-- TAB 2: ĐỔI MẬT KHẨU (API RIÊNG) -->
          <div v-show="activeTab === 'security'" class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 animation-fade-in">
            <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
              <h5 class="fw-bold text-dark dark:text-white mb-1">Đổi Mật Khẩu</h5>
              <p class="text-muted small mb-0">Đảm bảo tài khoản của bạn đang sử dụng mật khẩu mạnh và an toàn.</p>
            </div>

            <form @submit.prevent="savePassword" autocomplete="off">
              <input style="display:none" type="text" name="fakeusernameremembered"/>
              <input style="display:none" type="password" name="fakepasswordremembered"/>

              <div class="row g-4">
                <div class="col-12">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase" style="letter-spacing: 0.5px;">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                  <div class="input-group input-group-lg shadow-sm-hover">
                    <span class="input-group-text bg-light dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-unlock"></i></span>
                    <input :type="showPass1 ? 'text' : 'password'" autocomplete="new-password" class="form-control border-start-0 border-end-0 bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="formPass.current_password" :class="{'is-invalid': errorsPass.current_password}" placeholder="Nhập mật khẩu đang sử dụng">
                    <button class="btn btn-light dark:bg-[#212529] border border-start-0 dark:border-gray-700 text-muted" type="button" @click="showPass1 = !showPass1">
                      <i class="bi" :class="showPass1 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                    <div class="invalid-feedback">{{ errorsPass.current_password?.[0] }}</div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 my-2">
                    <div class="row g-4">
                      <div class="col-md-6">
                        <label class="form-label fw-bold text-primary small text-uppercase" style="letter-spacing: 0.5px;">Mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm-hover">
                          <span class="input-group-text bg-white dark:bg-[#1a2533] border-end-0 text-primary"><i class="bi bi-key"></i></span>
                          <input :type="showPass2 ? 'text' : 'password'" autocomplete="new-password" class="form-control border-start-0 border-end-0 bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-700" v-model="formPass.password" :class="{'is-invalid': errorsPass.password}" placeholder="Tối thiểu 6 ký tự">
                          <button class="btn bg-white dark:bg-[#1a2533] border border-start-0 dark:border-gray-700 text-muted" type="button" @click="showPass2 = !showPass2">
                            <i class="bi" :class="showPass2 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                          </button>
                          <div class="invalid-feedback">{{ errorsPass.password?.[0] }}</div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label fw-bold text-primary small text-uppercase" style="letter-spacing: 0.5px;">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm-hover">
                          <span class="input-group-text bg-white dark:bg-[#1a2533] border-end-0 text-primary"><i class="bi bi-shield-check"></i></span>
                          <input :type="showPass3 ? 'text' : 'password'" autocomplete="new-password" class="form-control border-start-0 border-end-0 bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-700" v-model="formPass.password_confirmation" :class="{'is-invalid': errorsPass.password_confirmation}" placeholder="Nhập lại mật khẩu mới">
                          <button class="btn bg-white dark:bg-[#1a2533] border border-start-0 dark:border-gray-700 text-muted" type="button" @click="showPass3 = !showPass3">
                            <i class="bi" :class="showPass3 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                          </button>
                          <div class="invalid-feedback">{{ errorsPass.password_confirmation?.[0] }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-5 pt-4 border-top dark:border-gray-700 text-end">
                <button type="submit" class="btn btn-danger btn-lg text-white px-5 fw-bold shadow" :disabled="isSavingPass">
                  <span v-if="isSavingPass" class="spinner-border spinner-border-sm me-2"></span> 
                  <i class="bi bi-arrow-repeat me-2" v-else></i> Cập Nhật Mật Khẩu
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
    
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <div class="spinner-border text-urban mb-3" style="width: 3rem; height: 3rem;"></div>
      <p class="text-muted fw-bold">Đang tải hồ sơ...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const activeTab = ref('profile');
const isLoading = ref(true);

const isSavingInfo = ref(false);
const isSavingPass = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);
const showPass3 = ref(false);

const hasOldAvatar = ref(false);
const errors = ref({});
const errorsPass = ref({});

const previewAvatar = ref(defaultAvatar);
const fileInput = ref(null);

const currentAdminData = ref({});
const currentAdminRole = ref({});

// Data Dropdown địa chỉ
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const loadingProvinces = ref(false);
const loadingDistricts = ref(false);
const loadingWards = ref(false);
const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

// 2 form riêng biệt
const formInfo = ref({ fullname: '', email: '', phone: '', address: '', avatar: null, remove_avatar: false });
const formPass = ref({ current_password: '', password: '', password_confirmation: '' });

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
    console.error('Lỗi API Tỉnh thành:', err);
  } finally {
    loadingProvinces.value = false;
  }
};

const onProvinceChange = async () => {
  loadingDistricts.value = true;
  try {
    addressHelper.district = ''; addressHelper.ward = ''; wards.value = [];
    const p = provinces.value.find(i => i.name === addressHelper.province);
    if (p) { 
      const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`, { timeout: 5000 }); 
      if (res.data && res.data.districts) {
        districts.value = res.data.districts;
      }
    }
  } catch (err) {
    console.error('Lỗi API Quận huyện:', err);
  } finally {
    loadingDistricts.value = false;
  }
};

const onDistrictChange = async () => {
  loadingWards.value = true;
  try {
    addressHelper.ward = '';
    const d = districts.value.find(i => i.name === addressHelper.district);
    if (d) { 
      const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`, { timeout: 5000 }); 
      if (res.data && res.data.wards) {
        wards.value = res.data.wards;
      }
    }
  } catch (err) {
    console.error('Lỗi API Phường xã:', err);
  } finally {
    loadingWards.value = false;
  }
};

const fetchData = async () => {
  try {
    const resAdmin = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/me`, { headers: getHeaders() });
    fetchProvinces();
    
    const admin = resAdmin.data.data;
    currentAdminData.value = admin;
    currentAdminRole.value = admin.role;

    formInfo.value.fullname = admin.fullname;
    formInfo.value.email = admin.email;
    formInfo.value.phone = admin.phone;
    formInfo.value.address = admin.address;

    if (admin.address) {
       const parts = admin.address.split(', ').map(p => p.trim());
       if (parts.length >= 4) {
          addressHelper.province = parts[parts.length - 1]; await onProvinceChange();
          addressHelper.district = parts[parts.length - 2]; await onDistrictChange();
          addressHelper.ward = parts[parts.length - 3];
          addressHelper.detail = parts.slice(0, parts.length - 3).join(', ');
       } else { addressHelper.detail = admin.address; }
    }

    if (admin.avatar_url) { previewAvatar.value = `http://127.0.0.1:8000/storage/${admin.avatar_url}`; hasOldAvatar.value = true; }
  } catch (err) { Swal.fire('Lỗi', 'Không thể tải hồ sơ', 'error'); } finally { isLoading.value = false; }
};

const triggerUpload = () => fileInput.value.click();
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh tối đa 5MB', 'error'); return; }
  formInfo.value.avatar = file; formInfo.value.remove_avatar = false;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; }; reader.readAsDataURL(file);
};
const removeAvatar = () => { previewAvatar.value = defaultAvatar; formInfo.value.avatar = null; formInfo.value.remove_avatar = true; hasOldAvatar.value = false; };

const saveProfileInfo = async () => {
  const fullAddr = [addressHelper.detail, addressHelper.ward, addressHelper.district, addressHelper.province].filter(Boolean).join(', ');
  formInfo.value.address = fullAddr;

  isSavingInfo.value = true; errors.value = {};
  const formData = new FormData();
  
  Object.keys(formInfo.value).forEach(key => { 
    if(formInfo.value[key] !== null && formInfo.value[key] !== '') { formData.append(key, formInfo.value[key]); }
  });

  try {
    const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/profile/info`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Đã lưu thông tin', timer: 1500, showConfirmButton: false });
    localStorage.setItem('admin_info', JSON.stringify(res.data.data));
    window.location.reload();
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSavingInfo.value = false; }
};

const savePassword = async () => {
  isSavingPass.value = true; errorsPass.value = {};
  try {
    await axios.put(`${import.meta.env.VITE_API_BASE_URL}/admin/profile/password`, formPass.value, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Đổi mật khẩu thành công', text: 'Vui lòng sử dụng mật khẩu mới ở lần đăng nhập sau.', timer: 2000, showConfirmButton: false });
    formPass.value = { current_password: '', password: '', password_confirmation: '' };
  } catch (err) {
    if (err.response?.data?.errors) errorsPass.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSavingPass.value = false; }
};

onMounted(fetchData);
</script>

<style scoped>
/* Vùng Cover nền phía sau header */
.page-header-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 220px;
  background-color: var(--color-c-effect);
  z-index: 1;
}
html.dark .page-header-bg {
  background-color: #121416;
}

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); transform: translateY(-2px); }

/* Avatar Overlay CSS */
.avatar-upload-container {
  position: relative;
  cursor: pointer;
}
.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.avatar-upload-container:hover .avatar-overlay {
  opacity: 1;
}
.btn-remove-avatar {
  right: -5px;
  bottom: 10px;
  width: 28px;
  height: 28px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 11;
}

/* Vertical Nav CSS */
.profile-nav .nav-link {
  color: #6c757d;
  border: 1px solid transparent;
}
html.dark .profile-nav .nav-link {
  color: #adb5bd;
}
.profile-nav .nav-link:hover {
  background-color: rgba(84, 119, 146, 0.05);
  color: var(--color-c-hover);
}
.profile-nav .active-nav {
  background-color: var(--color-c-hover) !important;
  color: white !important;
}

.icon-box {
  width: 40px;
  height: 40px;
  transition: all 0.3s ease;
}

/* Form Inputs Focus */
.shadow-sm-hover {
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.shadow-sm-hover:focus-within {
  box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important;
}
.form-control:focus, .form-select:focus { 
  border-color: var(--color-c-hover, #547792); 
  box-shadow: none !important; 
}

.animation-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>