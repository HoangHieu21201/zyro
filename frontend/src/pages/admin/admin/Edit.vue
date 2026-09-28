<template>
  <div class="admin-edit-wrapper">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
          <router-link :to="{ name: 'admin-admins' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
            <i class="bi bi-arrow-left-circle fs-3"></i>
          </router-link>
          <h3 class="fw-bold text-dark dark:text-white mb-0">Thiết Lập Tài Khoản</h3>
        </div>
        
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
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5">
            <form @submit.prevent="saveAdmin" autocomplete="off">
              <input style="display:none" type="text" name="fakeusernameremembered"/>
              <input style="display:none" type="password" name="fakepasswordremembered"/>

              <div class="row g-4">
                
                <!-- CỘT TRÁI: AVATAR & THÔNG TIN TÓM TẮT -->
                <div class="col-md-4 col-xl-3 d-flex flex-column align-items-center border-end-md dark:border-gray-700 pe-md-4">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 mb-3">Ảnh đại diện</label>
                  
                  <!-- AVATAR TƯƠNG TÁC MỚI -->
                  <div class="position-relative mb-2">
                    <!-- Khối ảnh có hiệu ứng hover -->
                    <div class="avatar-wrapper position-relative rounded-circle shadow-sm cursor-pointer mx-auto border border-4 border-white dark:border-gray-700" 
                         style="width: 140px; height: 140px;" 
                         @click="triggerUpload"
                         title="Nhấn để đổi ảnh">
                      <img :src="previewAvatar" class="w-100 h-100 rounded-circle object-fit-cover">
                      
                      <!-- Lớp phủ tối màu khi hover -->
                      <div class="avatar-overlay rounded-circle d-flex justify-content-center align-items-center">
                        <i class="bi bi-camera-fill text-white fs-2"></i>
                      </div>
                    </div>

                    <!-- Nút X góc trên bên phải để xóa ảnh -->
                    <button v-if="hasOldAvatar || form.avatar" 
                            type="button" 
                            @click.stop="removeAvatar"
                            class="btn btn-danger rounded-circle position-absolute d-flex justify-content-center align-items-center p-0 shadow" 
                            style="width: 28px; height: 28px; top: 0; right: 0; z-index: 2;" 
                            title="Gỡ ảnh">
                      <i class="bi bi-x fs-5 text-white"></i>
                    </button>

                    <!-- Chấm trạng thái góc dưới bên phải -->
                    <span class="position-absolute bottom-0 border border-white border-3 rounded-circle p-2 shadow-sm" 
                          :class="form.status === 'active' ? 'bg-success' : 'bg-warning'" 
                          :title="form.status === 'active' ? 'Đang hoạt động' : 'Bị khóa'" 
                          style="width: 25px; height: 25px; right: 5px; transform: translateY(-5px); z-index: 1;"></span>
                  </div>

                  <input type="file" ref="fileInput" @change="onFileChange" class="d-none" accept="image/*">
                  <div class="text-danger small fw-bold text-center mb-4 mt-2" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                  <!-- END AVATAR -->

                  <div class="w-100 p-3 bg-light dark:bg-[#212529] rounded-4 text-start border border-dashed dark:border-gray-700 mt-auto">
                    <div class="text-center mb-3">
                      <h6 class="fw-bold dark:text-white mb-1">{{ form.fullname || 'Họ và tên' }}</h6>
                      <p class="text-muted small font-monospace mb-0 text-truncate">{{ maskEmail(form.email) || 'email@example.com' }}</p>
                    </div>
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

                <!-- CỘT PHẢI: FORM NHẬP LIỆU -->
                <div class="col-md-8 col-xl-9 ps-md-4">
                  
                  <div v-show="activeTab === 'profile'">
                    <div class="row g-4">
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input id="floating_fullname" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.fullname" :class="{'is-invalid': errors.fullname}" placeholder="...">
                          <label for="floating_fullname" class="text-muted fw-bold">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                        </div>
                        <div class="invalid-feedback d-block" v-if="errors.fullname">{{ errors.fullname[0] }}</div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-floating">
                          <input id="floating_email" type="text" class="form-control bg-light text-secondary border-0 dark:bg-[#2b3035] dark:text-gray-400" :value="maskEmail(form.email)" readonly placeholder="...">
                          <label for="floating_email" class="text-muted fw-bold">EMAIL (Định danh, không thể đổi)</label>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-floating">
                          <select id="floating_role" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.role_id" :class="{'is-invalid': errors.role_id}" :disabled="adminId === 1">
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.label }}</option>
                          </select>
                          <label for="floating_role" class="text-muted fw-bold">CHỨC VỤ <span class="text-danger">*</span></label>
                        </div>
                        <small v-if="adminId === 1" class="text-danger d-block mt-1">Super Admin gốc không thể đổi quyền.</small>
                      </div>

                      <div class="col-md-6">
                        <div class="form-floating">
                          <select id="floating_status" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" 
                                  v-model="form.status" 
                                  :class="{'is-invalid': errors.status}" 
                                  :disabled="adminId === 1 || adminId === currentUserId">
                            <option value="active">Hoạt động</option>
                            <option value="locked">Bị khóa</option>
                          </select>
                          <label for="floating_status" class="text-muted fw-bold">TRẠNG THÁI <span class="text-danger">*</span></label>
                        </div>
                        <div class="invalid-feedback d-block" v-if="errors.status">{{ errors.status[0] }}</div>
                        <small v-if="adminId === 1" class="text-danger d-block mt-1">Không thể khóa Super Admin gốc.</small>
                        <small v-else-if="adminId === currentUserId" class="text-warning d-block mt-1">Bạn không thể tự khóa chính mình.</small>
                      </div>

                      <div class="col-12">
                        <div class="form-floating">
                          <input id="floating_phone" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.phone" :class="{'is-invalid': errors.phone}" placeholder="...">
                          <label for="floating_phone" class="text-muted fw-bold">SỐ ĐIỆN THOẠI LIÊN HỆ</label>
                        </div>
                        <div class="invalid-feedback d-block" v-if="errors.phone">{{ errors.phone[0] }}</div>
                      </div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top dark:border-gray-700">
                  <h6 class="fw-bold text-dark dark:text-gray-200 mb-3">Địa chỉ liên hệ</h6>
                  <AddressSelector 
                     show-location-button location-button-class="btn-outline-urban" @location-detail="val => addressHelper.detail = val"
                     v-model:city="addressHelper.province"
                     v-model:district="addressHelper.district"
                     v-model:ward="addressHelper.ward"
                  />
                  <div class="form-floating shadow-sm-hover mt-3">
                    <input type="text" class="form-control bg-white dark:bg-[#212529] dark:text-white dark:border-gray-700" id="floatingAddress" v-model="addressHelper.detail" placeholder="Số nhà, tên đường, khu phố...">
                    <label for="floatingAddress" class="text-muted"><i class="bi bi-house me-2"></i>Số nhà, tên đường, khu phố...</label>
                  </div>
                </div>
                  </div>

                  <div v-show="activeTab === 'security'">
                    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 dark:bg-yellow-900/20 dark:text-yellow-200">
                      <i class="bi bi-info-circle-fill me-2"></i>
                      Chỉ nhập vào các ô bên dưới nếu bạn thực sự muốn <strong>thay đổi mật khẩu đăng nhập</strong> của nhân sự này.
                    </div>
                    <div class="row g-4">
                      <div class="col-md-6">
                        <div class="form-floating position-relative">
                          <input :type="showPass ? 'text' : 'password'" id="floating_pass" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 pe-5" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="...">
                          <label for="floating_pass" class="text-muted fw-bold">MẬT KHẨU MỚI</label>
                          <button class="btn border-0 position-absolute top-50 end-0 translate-middle-y text-muted" type="button" @click="showPass = !showPass" style="z-index: 5;">
                            <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
                          </button>
                        </div>
                        <div class="invalid-feedback d-block" v-if="errors.password">{{ errors.password[0] }}</div>
                      </div>
                      
                      <div class="col-md-6">
                        <div class="form-floating position-relative">
                          <input :type="showPass ? 'text' : 'password'" id="floating_pass_confirm" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 pe-5" v-model="form.password_confirmation" placeholder="...">
                          <label for="floating_pass_confirm" class="text-muted fw-bold">XÁC NHẬN MẬT KHẨU <span class="text-danger" v-if="form.password">*</span></label>
                          <button class="btn border-0 position-absolute top-50 end-0 translate-middle-y text-muted" type="button" @click="showPass = !showPass" style="z-index: 5;">
                            <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <hr class="my-5 dark:border-gray-700">
              <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc nhập</p>
                <div class="text-end">
                  <!-- Đã đổi nút sang rounded-2 (bo góc nhẹ) và thêm padding -->
                  <router-link :to="{ name: 'admin-admins' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-3 px-4 py-2 shadow-sm border fw-bold text-decoration-none rounded-2">Hủy bỏ</router-link>
                  <button type="submit" class="btn btn-urban text-white px-5 py-2 fw-bold shadow-sm rounded-2" :disabled="isSaving">
                    <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Cập Nhật Ngay
                  </button>
                </div>
              </div>
            </form>
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
import { ref, onMounted, reactive, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import AddressSelector from '@/components/shared/AddressSelector.vue';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const router = useRouter();
const route = useRoute();
const adminId = parseInt(route.params.id);

const currentAdmin = JSON.parse(localStorage.getItem('admin_info') || '{}');
const currentUserId = currentAdmin.id;

const activeTab = ref('profile'); 
const isLoading = ref(true);
const isSaving = ref(false);
const showPass = ref(false);
const hasOldAvatar = ref(false);
const errors = ref({});

const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0];
  const domain = parts[1];
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  return name.substring(0, 3) + '***@' + domain;
};


const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

const roles = ref([]);
const previewAvatar = ref(defaultAvatar);
const fileInput = ref(null);

const form = ref({ 
  fullname: '', email: '', password: '', password_confirmation: '', 
  role_id: '', phone: '', address: '', avatar: null, remove_avatar: false, status: 'active'
});


watch(addressHelper, (val) => {
  let parts = [];
  if (val.detail) parts.push(val.detail);
  if (val.ward) parts.push(val.ward);
  if (val.district) parts.push(val.district);
  if (val.province) parts.push(val.province);
  form.value.address = parts.join(', ');
}, { deep: true });

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });


const fetchData = async () => {
  try {
    const [resRole, resAdmin] = await Promise.all([
      axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/roles`, { headers: getHeaders() }),
      axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/admins/${adminId}`, { headers: getHeaders() })]);
    
    roles.value = resRole.data.data;
    const admin = resAdmin.data.data;
    form.value.fullname = admin.fullname;
    form.value.email = admin.email;
    form.value.role_id = admin.role_id;
    form.value.phone = admin.phone;
    form.value.address = admin.address;
    form.value.status = admin.status;

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
       previewAvatar.value = `${import.meta.env.VITE_STORAGE_URL}${admin.avatar_url}`;
       hasOldAvatar.value = true;
    }
  } catch (err) { 
    Swal.fire('Lỗi', 'Không tìm thấy thông tin tài khoản', 'error'); 
    router.push({ name: 'admin-admins' }); 
  } finally { isLoading.value = false; }
};

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

const saveAdmin = async () => {
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

@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid #dee2e6;
  }
}

.form-floating > .btn.position-absolute {
    padding: 1rem 0.75rem;
}

/* CSS CHO HOVER AVATAR */
.cursor-pointer {
  cursor: pointer;
}
.avatar-wrapper {
  overflow: hidden;
}
.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}
.avatar-wrapper:hover .avatar-overlay {
  opacity: 1;
}
</style>