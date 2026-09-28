<template>
  <div class="user-create-wrapper">
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
          <div class="col-lg-8 order-lg-2">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h5 class="fw-bold text-urban mb-4"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h5>
              
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_zur5gxfqm" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.full_name" :class="{'is-invalid': errors.full_name}" placeholder="VD: Nguyễn Văn A">
                    <label for="floating_zur5gxfqm" class="fw-bold text-muted" style="font-size: 0.85rem;">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                  </div>
                  <div class="invalid-feedback">{{ errors.full_name?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_hywxs38cq" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.phone" :class="{'is-invalid': errors.phone}" @input="validatePhone" placeholder="VD: 098xxxxxxx">
                    <label for="floating_hywxs38cq" class="fw-bold text-muted" style="font-size: 0.85rem;">SỐ ĐIỆN THOẠI</label>
                  </div>
                  <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                </div>

                <div class="col-md-12">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_aq0klzsnb" type="email" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                         v-model="form.email" :class="{'is-invalid': errors.email}" placeholder="example@gmail.com">
                    <label for="floating_aq0klzsnb" class="fw-bold text-muted" style="font-size: 0.85rem;">EMAIL ĐĂNG NHẬP <span class="text-danger">*</span></label>
                  </div>
                  <div class="invalid-feedback">{{ errors.email?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <div class="input-group shadow-sm-hover">
                    <div class="form-floating flex-grow-1">
                      <input :type="showPass1 ? 'text' : 'password'" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự" id="floating_pass1">
                      <label for="floating_pass1" class="fw-bold text-muted" style="font-size: 0.85rem;">MẬT KHẨU <span class="text-danger">*</span></label>
                    </div>
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass1 = !showPass1"><i class="bi" :class="showPass1 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i></button>
                    <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="input-group shadow-sm-hover">
                    <div class="form-floating flex-grow-1">
                      <input :type="showPass2 ? 'text' : 'password'" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu" id="floating_pass2">
                      <label for="floating_pass2" class="fw-bold text-muted" style="font-size: 0.85rem;">XÁC NHẬN MẬT KHẨU <span class="text-danger">*</span></label>
                    </div>
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass2 = !showPass2"><i class="bi" :class="showPass2 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i></button>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <select id="floating_gender" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.gender">
                    <option value="">-- N/A --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                  </select>
                    <label for="floating_gender" class="fw-bold text-muted" style="font-size: 0.85rem;">GIỚI TÍNH</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_gonc1dy46" type="date" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.birthday" placeholder="...">
                    <label for="floating_gonc1dy46" class="fw-bold text-muted" style="font-size: 0.85rem;">NGÀY SINH</label>
                  </div>
                </div>

                <!-- BOX ĐỊA CHỈ DEFAULT -->
                <div class="col-12 mt-4">
                  <div class="p-4 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-4">
                    <label class="form-label fw-bold text-urban"><i class="bi bi-geo-alt-fill me-2"></i>Địa chỉ mặc định (Tùy chọn)</label>
                    <div class="row g-3 mt-3">
                      <div class="col-12">
                        <AddressSelector 
                           show-location-button location-button-class="btn-outline-urban" @location-detail="val => form.shipping_address = val"
                           v-model:city="form.city"
                           v-model:district="form.district"
                           v-model:ward="form.ward"
                        />
                      </div>
                      <div class="col-12 mt-3">
                        <div class="form-floating shadow-sm-hover">
                          <input id="floating_shipping" type="text" class="form-control bg-white dark:bg-[#1a2533] dark:text-white dark:border-gray-600 shadow-sm" v-model="form.shipping_address" placeholder="Số nhà, tên đường cụ thể...">
                          <label for="floating_shipping" class="text-muted"><i class="bi bi-house me-2"></i>Số nhà, tên đường cụ thể...</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          
          <!-- Cột Phải: Hình ảnh & Trạng thái -->
          <div class="col-lg-4 order-lg-1">
             <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 h-100">
                <h5 class="fw-bold text-urban mb-4"><i class="bi bi-image me-2"></i>Avatar & Trạng thái</h5>

                <div class="mb-4 text-center p-4 border border-dashed dark:border-gray-700 rounded-4 bg-light dark:bg-[#212529]">
                  <div class="avatar-wrapper group position-relative mx-auto rounded-circle shadow-sm mb-3" style="width: 140px; height: 140px;">
                    <img :src="previewAvatar" class="w-100 h-100 object-fit-cover rounded-circle border border-3 border-white dark:border-gray-600">
                    <div class="avatar-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-dark bg-opacity-50 cursor-pointer" @click="$refs.avatarInput.click()">
                      <i class="bi bi-camera-fill text-white fs-3"></i>
                    </div>
                    <button v-if="form.avatar" type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle shadow-sm" style="width: 32px; height: 32px; z-index: 10;" @click="removeAvatar">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                  <input type="file" ref="avatarInput" @change="onAvatarChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
                </div>

                <div class="mb-4">
                  <div class="form-floating shadow-sm-hover">
                  <select id="floating_status" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.status">
                    <option value="active">Hoạt động bình thường</option>
                    <option value="locked">Bị khóa (Locked)</option>
                  </select>
                  <label for="floating_status" class="fw-bold text-muted" style="font-size: 0.85rem;">TRẠNG THÁI TÀI KHOẢN</label>
                </div>
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/avatar1.png';
import AddressSelector from '@/components/shared/AddressSelector.vue';

const router = useRouter();
const isSaving = ref(false);
const errors = ref({});

const showPass1 = ref(false);
const showPass2 = ref(false);

const previewAvatar = ref(defaultImage);
const avatarInput = ref(null);


const form = ref({ 
  full_name: '', email: '', password: '', password_confirmation: '', phone: '',
  gender: '', birthday: '', status: 'active', avatar: null,
  shipping_address: '', city: '', district: '', ward: ''
});

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });


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

const removeAvatar = () => {
  previewAvatar.value = defaultImage;
  form.value.avatar = null;
  if (avatarInput.value) avatarInput.value.value = '';
};

const saveUser = async () => {
  isSaving.value = true; errors.value = {};
  const formData = new FormData();
  
  Object.keys(form.value).forEach(key => { 
    if(form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]); 
  });

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/users`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Tạo tài khoản thành công', timer: 1500, showConfirmButton: false });
    router.push({ name: 'admin-users' });
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi hệ thống', 'error');
  } finally { isSaving.value = false; }
};


</script>

<style scoped>
.avatar-overlay { opacity: 0; transition: opacity 0.3s ease; }
.avatar-wrapper:hover .avatar-overlay { opacity: 1; }
.cursor-pointer { cursor: pointer; }

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