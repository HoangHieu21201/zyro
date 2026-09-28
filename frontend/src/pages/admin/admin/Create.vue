<template>
  <div class="admin-create-wrapper">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center mb-4">
        <router-link :to="{ name: 'admin-admins' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
          <i class="bi bi-arrow-left-circle fs-3"></i>
        </router-link>
        <h3 class="fw-bold text-dark dark:text-white mb-0">Tạo Tài Khoản Mới</h3>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5">
            <form @submit.prevent="saveAdmin" autocomplete="off">
              <input style="display:none" type="text" name="fakeusernameremembered" />
              <input style="display:none" type="password" name="fakepasswordremembered" />

              <div class="row g-4">
                
                <div class="col-md-4 col-xl-3 d-flex flex-column align-items-center border-end-md dark:border-gray-700 pe-md-4">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 mb-3">Ảnh đại diện</label>
                  
                  <div class="position-relative mb-2">
                    <div class="avatar-wrapper position-relative rounded-circle shadow-sm cursor-pointer mx-auto border border-4 border-white dark:border-gray-700" 
                         style="width: 140px; height: 140px;" 
                         @click="triggerUpload"
                         title="Nhấn để đổi ảnh">
                      <img :src="previewAvatar" class="w-100 h-100 rounded-circle object-fit-cover">
                      
                      <div class="avatar-overlay rounded-circle d-flex justify-content-center align-items-center">
                        <i class="bi bi-camera-fill text-white fs-2"></i>
                      </div>
                    </div>

                    <button v-if="form.avatar" 
                            type="button" 
                            @click.stop="removeAvatar"
                            class="btn btn-danger rounded-circle position-absolute d-flex justify-content-center align-items-center p-0 shadow" 
                            style="width: 28px; height: 28px; top: 0; right: 0; z-index: 2;" 
                            title="Gỡ ảnh">
                      <i class="bi bi-x fs-5 text-white"></i>
                    </button>
                  </div>

                  <input type="file" ref="fileInput" @change="onFileChange" class="d-none" accept="image/*">
                  <small class="text-muted text-center mt-2">Định dạng: JPG, PNG (Max 5MB)</small>
                  <div class="text-danger small fw-bold text-center mt-2" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                </div>

                <div class="col-md-8 col-xl-9 ps-md-4">
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
                        <input id="floating_email" type="email" autocomplete="off" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.email" :class="{'is-invalid': errors.email}" placeholder="...">
                        <label for="floating_email" class="text-muted fw-bold">EMAIL CÔNG VIỆC <span class="text-danger">*</span></label>
                      </div>
                      <div class="invalid-feedback d-block" v-if="errors.email">{{ errors.email[0] }}</div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-floating position-relative">
                        <input :type="showPass ? 'text' : 'password'" id="floating_pass" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 pe-5" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="...">
                        <label for="floating_pass" class="text-muted fw-bold">MẬT KHẨU KHỞI TẠO <span class="text-danger">*</span></label>
                        <button class="btn border-0 position-absolute top-50 end-0 translate-middle-y text-muted" type="button" @click="showPass = !showPass" style="z-index: 5;">
                          <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
                        </button>
                      </div>
                      <div class="invalid-feedback d-block" v-if="errors.password">{{ errors.password[0] }}</div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-floating position-relative">
                        <input :type="showPass ? 'text' : 'password'" id="floating_pass_confirm" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 pe-5" v-model="form.password_confirmation" placeholder="...">
                        <label for="floating_pass_confirm" class="text-muted fw-bold">XÁC NHẬN MẬT KHẨU <span class="text-danger">*</span></label>
                        <button class="btn border-0 position-absolute top-50 end-0 translate-middle-y text-muted" type="button" @click="showPass = !showPass" style="z-index: 5;">
                          <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
                        </button>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-floating">
                        <input id="floating_phone" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.phone" :class="{'is-invalid': errors.phone}" placeholder="...">
                        <label for="floating_phone" class="text-muted fw-bold">SỐ ĐIỆN THOẠI</label>
                      </div>
                      <div class="invalid-feedback d-block" v-if="errors.phone">{{ errors.phone[0] }}</div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-floating">
                        <select id="floating_role" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.role_id" :class="{'is-invalid': errors.role_id}">
                          <option value="">Chọn một chức vụ...</option>
                          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.label }} (Cấp {{ role.level }})</option>
                        </select>
                        <label for="floating_role" class="text-muted fw-bold">CHỨC VỤ (ROLE) <span class="text-danger">*</span></label>
                      </div>
                      <div class="invalid-feedback d-block" v-if="errors.role_id">{{ errors.role_id[0] }}</div>
                    </div>
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

              <hr class="my-5 dark:border-gray-700">
              <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted small mb-0"><span class="text-danger">*</span> Trường bắt buộc nhập</p>
                <div class="text-end">
                  <router-link :to="{ name: 'admin-admins' }" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 me-3 px-4 py-2 shadow-sm fw-bold text-decoration-none border rounded-2">Hủy bỏ</router-link>
                  <button type="submit" class="btn btn-urban text-white px-5 py-2 fw-bold shadow-sm rounded-2" :disabled="isSaving">
                    <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu Tài Khoản
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import AddressSelector from '@/components/shared/AddressSelector.vue';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';

const router = useRouter();
const roles = ref([]);
const previewAvatar = ref(defaultAvatar);
const fileInput = ref(null);
const isSaving = ref(false);
const showPass = ref(false);
const errors = ref({});


const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

const form = ref({ 
  fullname: '', email: '', password: '', password_confirmation: '', 
  role_id: '', phone: '', address: '', avatar: null 
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
    // Chạy song song cả API Role và Tỉnh thành
    const [resRole] = await Promise.all([
      axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/roles`, { headers: getHeaders() }).catch(e => {
        console.error("Lỗi API Roles:", e.message);
        return { data: { data: [] } };
      }),
      fetchProvinces()
    ]);
    
    // Dùng Optional Chaining đảm bảo biến mảng luôn hợp lệ trước khi dùng .filter
    const rawRoles = resRole?.data?.data || resRole?.data || [];
    roles.value = Array.isArray(rawRoles) ? rawRoles.filter(r => !r.deleted_at) : [];

  } catch (err) {
    console.error("Lỗi tải dữ liệu khởi tạo:", err.message);
  }
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

const removeAvatar = () => {
  previewAvatar.value = defaultAvatar;
  form.value.avatar = null;
};

const saveAdmin = async () => {
  const fullAddr = [addressHelper.detail, addressHelper.ward, addressHelper.district, addressHelper.province]
                    .filter(Boolean).join(', ');
  form.value.address = fullAddr;

  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/admins`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
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

@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid #dee2e6;
  }
}

.form-floating > .btn.position-absolute {
    padding: 1rem 0.75rem;
}

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