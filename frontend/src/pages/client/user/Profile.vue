<template>
  <div class="user-profile-wrapper pb-5 mb-5" >

    <div class="pt-5 mt-4">
      <div class="zyro-container">

        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/"
                class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile"
                class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Hồ sơ cá nhân</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in pl-4 pb-5 px-3 ">

              <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
                <h4 class="fw-bold text-c-dark dark:text-white mb-1">Hồ Sơ Của Tôi</h4>
                <p class="text-muted small mb-0 d-none d-md-block">Quản lý thông tin hồ sơ để bảo mật tài khoản và nhận
                  gợi ý size chính xác.</p>
              </div>

              <div v-if="isLoading" class="row g-5 flex-column-reverse flex-md-row">
                <div class="col-md-8">
                  <div class="row g-4">
                    <div class="col-12">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-12">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-12">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-sm-6">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-sm-6">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 text-center border-start-md dark:border-gray-700">
                  <div class="shimmer rounded-circle mx-auto mb-3" style="width: 150px; height: 150px;"></div>
                  <div class="shimmer rounded-pill mx-auto" style="width: 100px; height: 35px;"></div>
                </div>
              </div>

              <form v-else @submit.prevent="updateProfile" autocomplete="off">
                <div class="row g-5 flex-column-reverse flex-md-row">

                  <div class="col-md-8">
                    <div class="row g-4">
                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Họ và tên <span
                            class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="form.full_name" required
                          placeholder="Nhập tên của bạn">
                      </div>

                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Email đăng nhập</label>
                        <div class="d-flex align-items-center gap-2">
                          <input type="email"
                            class="form-control custom-input bg-light dark:bg-[#121416] text-muted font-monospace pe-none w-100"
                            :value="maskEmail(form.email)" readonly tabindex="-1"
                            title="Email bảo mật không thể thay đổi">
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Số điện thoại</label>
                        <input type="tel" class="form-control custom-input" v-model="form.phone"
                          placeholder="Thêm số điện thoại">
                      </div>

                      <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Giới tính</label>
                        <select class="form-select custom-input fw-semibold" v-model="form.gender">
                          <option value="">-- Chọn --</option>
                          <option value="Nam">Nam</option>
                          <option value="Nữ">Nữ</option>
                          <option value="Khác">Khác</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Ngày sinh</label>
                        <input type="date" class="form-control custom-input fw-semibold" v-model="form.birthday">
                      </div>

                      <div class="col-12 mt-4">
                        <div
                          class="p-3 bg-c-effect dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3">
                          <h6 class="fw-bold text-c-dark dark:text-white mb-3 d-flex align-items-center">
                            <i class="bi bi-rulers text-c-hover me-2"></i> Chỉ số cơ thể
                          </h6>
                          <div class="row g-3">
                            <div class="col-sm-6">
                              <label class="form-label small fw-bold text-muted text-uppercase mb-2">Chiều cao
                                (cm)</label>
                              <input type="number" class="form-control custom-input" v-model="form.height_cm"
                                placeholder="VD: 170" min="0">
                            </div>
                            <div class="col-sm-6">
                              <label class="form-label small fw-bold text-muted text-uppercase mb-2">Cân nặng
                                (kg)</label>
                              <input type="number" class="form-control custom-input" v-model="form.weight_kg"
                                placeholder="VD: 65" min="0" step="0.1">
                            </div>
                          </div>
                          <p class="small text-muted mt-3 mb-0 fst-italic d-none d-md-block"><i
                              class="bi bi-info-circle me-1"></i>Hệ thống sẽ dựa vào chỉ số này để gợi ý kích cỡ (Size)
                            phù hợp nhất với bạn.</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 text-center border-start-md dark:border-gray-700 mb-4 mb-md-0">
                    <div class="position-relative d-inline-block mt-md-4">
                      <img :src="previewAvatar" @error="handleImageError"
                        class="rounded-circle object-fit-cover shadow-sm border border-4 border-white dark:border-gray-600 mb-3 d-block mx-auto"
                        style="width: 150px; height: 150px;">
                      <button type="button" @click="triggerUpload"
                        class="btn btn-c-dark rounded-circle position-absolute shadow-sm border border-2 border-white d-flex align-items-center justify-content-center hover-transform"
                        style="width: 35px; height: 35px; bottom: 15px; right: 5px;" title="Cập nhật ảnh đại diện">
                        <i class="bi bi-camera-fill text-white small"></i>
                      </button>
                    </div>
                    <input type="file" ref="fileInput" @change="onFileChange" class="d-none"
                      accept="image/jpeg, image/png, image/webp">

                    <div class="d-flex flex-column align-items-center">
                      <button type="button"
                        class="btn btn-outline-secondary dark:text-gray-300 dark:border-gray-600 rounded-pill px-4 py-1.5 fw-semibold small shadow-sm hover-c-dark mt-2"
                        @click="triggerUpload">
                        Chọn Ảnh
                      </button>
                      <p class="text-muted mt-3 mb-0 d-none d-md-block" style="font-size: 0.75rem;">
                        Dung lượng file tối đa 5MB.<br>Định dạng: .JPEG, .PNG, .WEBP
                      </p>
                    </div>
                    <div class="mt-5 pt-4 border-top dark:border-gray-700">
                      <button type="submit"
                        class="btn btn-c-dark btn-lg px-5 fw-bold shadow-lg hover-transform rounded-pill text-uppercase tracking-wide w-100 w-md-auto"
                        :disabled="isSaving">
                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                        <template v-else>Lưu Thay Đổi</template>
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import defaultAvatar from '@/assets/images/defaults/client_placeholder.png';
import UserSidebar from '@/components/client/UserSidebar.vue';

const isLoading = ref(true);
const isSaving = ref(false);
const fileInput = ref(null);
const previewAvatar = ref(defaultAvatar);

const form = ref({
  full_name: '',
  email: '',
  phone: '',
  gender: '',
  birthday: '',
  height_cm: null,
  weight_kg: null,
  avatar: null
});

const handleImageError = (e) => {
  e.target.src = defaultAvatar;
};

const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0];
  const domain = parts[1];
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  return name.substring(0, 3) + '***@' + domain;
};

const triggerUpload = () => {
  fileInput.value.click();
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    ZyroSwal.toastError('Chỉ hỗ trợ file hình ảnh!');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    ZyroSwal.toastError('Ảnh tối đa 5MB');
    return;
  }

  form.value.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};

const fetchProfile = async () => {
  try {
    const res = await api.get('/client/user/profile');
    if (res.data.success) {
      const u = res.data.data;
      form.value = {
        full_name: u.full_name || '',
        email: u.email || '',
        phone: u.phone || '',
        gender: u.gender || '',
        birthday: u.birthday ? u.birthday.split('T')[0] : '',
        height_cm: u.height_cm || null,
        weight_kg: u.weight_kg || null,
        avatar: null
      };

      if (u.avatar_url) {
        previewAvatar.value = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + u.avatar_url;
      }
    }
  } catch (error) {
    ZyroSwal.toastError('Không lấy được thông tin hồ sơ.');
  } finally {
    isLoading.value = false;
  }
};

const updateProfile = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('full_name', form.value.full_name);

    formData.append('phone', form.value.phone || '');
    formData.append('gender', form.value.gender || '');
    formData.append('birthday', form.value.birthday || '');
    formData.append('height_cm', form.value.height_cm || '');
    formData.append('weight_kg', form.value.weight_kg || '');

    if (form.value.avatar instanceof File) {
      formData.append('avatar', form.value.avatar);
    }

    const res = await api.post('/client/user/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data.success) {
      ZyroSwal.toastSuccess(res.data.message);
      const userStr = localStorage.getItem('user_info');
      if (userStr) {
        let u = JSON.parse(userStr);
        u.full_name = res.data.data.full_name;
        if (res.data.data.avatar_url) {
          u.avatar_url = res.data.data.avatar_url;
        }
        localStorage.setItem('user_info', JSON.stringify(u));
        window.dispatchEvent(new CustomEvent('user-profile-updated'));
      }
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) {
        const firstError = Object.values(errors).flat()[0];
        ZyroSwal.toastError(firstError);
      } else {
        ZyroSwal.toastError(error.response.data.message);
      }
    } else {
      ZyroSwal.toastError(error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật.');
    }
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProfile();
});
</script>

<style scoped>
.user-profile-wrapper {
  padding-top: 26px;
  width: 100%;
}

.zyro-container {
  width: 100%;
  max-width: 1310px;
  margin: 0 auto;
  padding-left: 20px;
  padding-right: 20px;
}

@media (min-width: 1400px) {
  .zyro-container {
    padding-left: 0;
    padding-right: 0;
  }
}

.text-c-dark {
  color: var(--color-c-dark) !important;
}

html.dark .text-c-dark {
  color: #f8f9fa !important;
}

.text-c-hover {
  color: var(--color-c-hover) !important;
}

.bg-c-dark {
  background-color: var(--color-c-dark) !important;
  color: white;
}

.btn-c-dark {
  background-color: var(--color-c-dark);
  color: white;
  border: none;
  transition: 0.2s ease;
}

.btn-c-dark:hover {
  background-color: var(--color-c-hover);
  color: white;
}

.bg-c-effect {
  background-color: var(--color-c-effect);
}

.hover-c-dark:hover {
  border-color: var(--color-c-dark) !important;
  color: var(--color-c-dark) !important;
}

.tracking-wide {
  letter-spacing: 1px;
}

.hover-text-dark:hover {
  color: #000 !important;
}

html.dark .hover-text-dark:hover {
  color: #fff !important;
}

.hover-transform {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-transform:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light);
  color: var(--color-c-dark);
  padding: 0.65rem 1rem;
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important;
}

html.dark .custom-input {
  background-color: #1a2533;
  border-color: #373b3e;
  color: white;
}

.custom-input:focus,
.custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  outline: none;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important;
}

html.dark .custom-input:focus {
  background-color: #212529;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important;
}

select.custom-input {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23547792' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 16px 12px;
  padding-right: 2.5rem;
}

html.dark select.custom-input {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

.custom-input:disabled,
.custom-input.pe-none {
  cursor: not-allowed;
  opacity: 0.8;
}

@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #dee2e6;
  }

  html.dark .border-start-md {
    border-color: #373b3e !important;
  }
}

.animation-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}

html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}

@keyframes placeholderShimmer {
  0% {
    background-position: -400px 0;
  }

  100% {
    background-position: 400px 0;
  }
}
</style>