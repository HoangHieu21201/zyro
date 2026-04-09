<!-- File: frontend/src/pages/client/user/Password.vue -->
<template>
  <div class="user-password-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Đổi mật khẩu</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- CỘT TRÁI: SIDEBAR QUẢN LÝ -->
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <!-- CỘT PHẢI: NỘI DUNG ĐỔI MẬT KHẨU -->
          <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in p-4 p-md-5">
              <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
                <h4 class="fw-bold text-c-dark dark:text-white mb-1">Đổi Mật Khẩu</h4>
                <p class="text-muted small mb-0">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác.</p>
              </div>

              <div class="row">
                <div class="col-md-10 col-lg-8 col-xl-7">
                  <form @submit.prevent="updatePassword" autocomplete="off">
                    <input style="display:none" type="text" name="fakeusernameremembered"/>
                    <input style="display:none" type="password" name="fakepasswordremembered"/>

                    <div class="mb-4">
                      <label class="form-label small fw-bold text-muted text-uppercase mb-2">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input :type="showPass1 ? 'text' : 'password'" class="form-control custom-input border-end-0" v-model="form.current_password" required placeholder="Nhập mật khẩu đang sử dụng">
                        <button class="btn bg-white dark:bg-[#1a2533] dark:text-gray-400 border border-start-0 border-custom text-muted px-3 transition-color" type="button" @click="showPass1 = !showPass1">
                          <i class="bi" :class="showPass1 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label small fw-bold text-muted text-uppercase mb-2">Mật khẩu mới <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input :type="showPass2 ? 'text' : 'password'" autocomplete="new-password" class="form-control custom-input border-end-0" v-model="form.new_password" required placeholder="Tối thiểu 6 ký tự">
                        <button class="btn bg-white dark:bg-[#1a2533] dark:text-gray-400 border border-start-0 border-custom text-muted px-3 transition-color" type="button" @click="showPass2 = !showPass2">
                          <i class="bi" :class="showPass2 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                      </div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label small fw-bold text-muted text-uppercase mb-2">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input :type="showPass3 ? 'text' : 'password'" autocomplete="new-password" class="form-control custom-input border-end-0" v-model="form.new_password_confirmation" required placeholder="Nhập lại mật khẩu mới">
                        <button class="btn bg-white dark:bg-[#1a2533] dark:text-gray-400 border border-start-0 border-custom text-muted px-3 transition-color" type="button" @click="showPass3 = !showPass3">
                          <i class="bi" :class="showPass3 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                      </div>
                      <div class="text-danger small mt-2 fw-bold" v-if="errorMsg">{{ errorMsg }}</div>
                    </div>

                    <div class="mt-5 pt-3 border-top dark:border-gray-700">
                      <button type="submit" class="btn btn-urban btn-lg px-5 fw-bold shadow-sm rounded-pill text-white hover-transform" :disabled="isSaving">
                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
                        <template v-else>Xác Nhận Đổi</template>
                      </button>
                      <router-link to="/user/profile" class="btn btn-link text-muted text-decoration-none fw-semibold ms-3">Quay lại</router-link>
                    </div>
                  </form>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import UserSidebar from '@/components/client/UserSidebar.vue';

const isSaving = ref(false);
const errorMsg = ref('');

const showPass1 = ref(false);
const showPass2 = ref(false);
const showPass3 = ref(false);

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const updatePassword = () => {
  errorMsg.value = '';
  if (form.value.new_password !== form.value.new_password_confirmation) {
    errorMsg.value = 'Xác nhận mật khẩu mới không trùng khớp!';
    return;
  }
  if (form.value.new_password.length < 6) {
    errorMsg.value = 'Mật khẩu mới phải chứa ít nhất 6 ký tự!';
    return;
  }

  isSaving.value = true;
  // Giả lập gọi API
  setTimeout(() => {
    isSaving.value = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đổi mật khẩu thành công', showConfirmButton: false, timer: 2000 });
    form.value = { current_password: '', new_password: '', new_password_confirmation: '' };
    showPass1.value = false; showPass2.value = false; showPass3.value = false;
  }, 1000);
};

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.user-password-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark, #213448) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }

.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important; }

/* INPUT CHUẨN MƯỢT */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  padding: 0.65rem 1rem; 
  font-size: 0.95rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important; 
}
.border-custom { border-color: var(--color-c-light); transition: all 0.2s ease-in-out; }
html.dark .custom-input, html.dark .border-custom { background-color: #1a2533; border-color: #373b3e; color: white; }

.input-group:focus-within .custom-input, .input-group:focus-within .border-custom {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
}
html.dark .input-group:focus-within .custom-input, html.dark .input-group:focus-within .border-custom {
  background-color: #212529; border-color: #495057 !important;
}

.input-group:focus-within .border-custom {
  box-shadow: 2px 0 0 1px rgba(148, 180, 193, 0.2) inset !important; /* Mẹo fake shadow cho viền phải */
}

.transition-color { transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>