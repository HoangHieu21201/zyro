<template>
  <div class="auth-wrapper d-flex min-vh-100 bg-white dark:bg-[#121416]">
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5 position-relative">
       <div class="w-100 animation-fade-in custom-scrollbar-y" style="max-width: 450px; max-height: 100vh; overflow-y: auto;">
          
          <router-link to="/" class="d-lg-none text-dark dark:text-white text-decoration-none d-inline-flex align-items-center fw-bold mb-4 hover-text-urban transition-all mt-3">
             <i class="bi bi-arrow-left me-2 fs-5"></i> Về Trang Chủ
          </router-link>

          <div class="text-center mb-5 pb-2 mt-4 mt-lg-0">
             <h1 class="fw-black tracking-widest text-dark dark:text-white mb-4" style="font-size: 3rem;">ZYRO.</h1>
             <h5 class="fw-bold text-uppercase tracking-widest text-urban mb-2">Tạo Tài Khoản</h5>
             <p class="text-muted small">Trở thành thành viên ZYRO để nhận đặc quyền riêng.</p>
          </div>

          <form @submit.prevent="handleRegister" autocomplete="off" class="pb-4">
             <div class="form-floating mb-4">
               <input type="text" class="form-control custom-auth-input bg-transparent dark:text-white" id="nameInput" v-model="form.fullName" placeholder="Họ và tên" required>
               <label for="nameInput" class="text-muted fw-semibold small text-uppercase tracking-wide px-0">Họ và tên <span class="text-danger">*</span></label>
             </div>

             <div class="row g-3 mb-4">
               <div class="col-md-6">
                 <div class="form-floating">
                   <input type="email" class="form-control custom-auth-input bg-transparent dark:text-white" id="emailInput" v-model="form.email" placeholder="name@example.com" required>
                   <label for="emailInput" class="text-muted fw-semibold small text-uppercase tracking-wide px-0">Email <span class="text-danger">*</span></label>
                 </div>
               </div>
               <div class="col-md-6">
                 <div class="form-floating">
                   <input type="tel" class="form-control custom-auth-input bg-transparent dark:text-white" id="phoneInput" v-model="form.phone" placeholder="SĐT" required>
                   <label for="phoneInput" class="text-muted fw-semibold small text-uppercase tracking-wide px-0">Số điện thoại <span class="text-danger">*</span></label>
                 </div>
               </div>
             </div>

             <div class="form-floating mb-4 position-relative">
               <input :type="showPass1 ? 'text' : 'password'" class="form-control custom-auth-input bg-transparent dark:text-white" id="passInput" v-model="form.password" placeholder="Password" required>
               <label for="passInput" class="text-muted fw-semibold small text-uppercase tracking-wide px-0">Mật khẩu <span class="text-danger">*</span></label>
               <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-text-urban px-2" @click="showPass1 = !showPass1">
                 <i class="bi" :class="showPass1 ? 'bi-eye-slash' : 'bi-eye'"></i>
               </button>
             </div>

             <div class="form-floating mb-5 position-relative">
               <input :type="showPass2 ? 'text' : 'password'" class="form-control custom-auth-input bg-transparent dark:text-white" id="passConfirmInput" v-model="form.password_confirmation" placeholder="Confirm Password" required>
               <label for="passConfirmInput" class="text-muted fw-semibold small text-uppercase tracking-wide px-0">Xác nhận mật khẩu <span class="text-danger">*</span></label>
               <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-text-urban px-2" @click="showPass2 = !showPass2">
                 <i class="bi" :class="showPass2 ? 'bi-eye-slash' : 'bi-eye'"></i>
               </button>
             </div>

             <button type="submit" class="btn btn-c-dark w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-lg hover-transform mb-4" :disabled="isLoading">
               <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> ĐĂNG KÝ
             </button>

             <div class="text-center">
               <p class="text-muted small mb-0">Đã có tài khoản? 
                 <router-link to="/login" class="text-dark dark:text-white fw-bold text-decoration-none border-bottom border-dark dark:border-gray-300 pb-1 hover-text-urban ms-1 transition-all">ĐĂNG NHẬP NGAY</router-link>
               </p>
             </div>
          </form>

       </div>
    </div>

    <div class="d-none d-lg-flex col-lg-6 position-relative p-0 overflow-hidden auth-cover">
      <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1200&auto=format&fit=crop" class="w-100 h-100 object-fit-cover zoom-anim" alt="ZYRO Fashion">
      <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
      
      <div class="position-absolute top-50 start-50 translate-middle text-center w-100 p-4 z-index-2">
         <h2 class="text-white display-4 fw-bold font-script text-shadow-lg mb-3">Join The Club</h2>
         <p class="text-white fs-5 text-shadow fw-medium">Tận hưởng các ưu đãi đặc quyền khi mua sắm.</p>
      </div>

      <router-link to="/" class="position-absolute top-0 end-0 m-4 text-white text-decoration-none d-flex align-items-center fw-bold hover-opacity text-shadow z-index-2">
         Về Trang Chủ <i class="bi bi-arrow-right ms-2 fs-5"></i>
      </router-link>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const router = useRouter();
const showPass1 = ref(false);
const showPass2 = ref(false);
const isLoading = ref(false);

const form = ref({
  fullName: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
});

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
      ZyroSwal.toastError('Mật khẩu xác nhận không khớp');
      return;
  }

  isLoading.value = true;
  
  try {
    const payload = {
      fullName: form.value.fullName.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone.trim(),
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    };

    await api.post('/client/register', payload);

    ZyroSwal.toastSuccess('Đăng ký thành công! Chào mừng đến với ZYRO');
    setTimeout(() => {
        router.push('/login');
    }, 1500);

  } catch (error) {
    let errorMsg = 'Lỗi kết nối máy chủ.';
    if (error.response?.data?.errors) {
        errorMsg = Object.values(error.response.data.errors).flat()[0];
    } else if (error.response?.data?.message) {
        errorMsg = error.response.data.message;
    }
    ZyroSwal.toastError(errorMsg);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.fw-black { font-weight: 900; }
.font-script { font-family: inherit; font-style: normal; }
.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }
.text-shadow { text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
.text-shadow-lg { text-shadow: 2px 4px 10px rgba(0,0,0,0.8); }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.text-c-dark { color: var(--color-c-dark, #213448) !important; }
.btn-c-dark { background-color: var(--color-c-dark, #213448); color: white; border: none; transition: 0.3s ease; }
.btn-c-dark:hover { background-color: var(--color-c-hover, #547792); color: white; }

.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-underline:hover { text-decoration: underline !important; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }
.hover-opacity { transition: opacity 0.2s ease; }
.hover-opacity:hover { opacity: 0.7; }
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: transparent; border-radius: 10px; }
.custom-scrollbar-y:hover::-webkit-scrollbar-thumb { background: #dee2e6; }
html.dark .custom-scrollbar-y:hover::-webkit-scrollbar-thumb { background: #373b3e; }

.auth-cover { height: 100vh; position: sticky; top: 0; }
.zoom-anim { animation: bg-zoom 20s linear infinite alternate; transform-origin: center; }
@keyframes bg-zoom { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }

.custom-auth-input {
  border: none;
  border-bottom: 1.5px solid #dee2e6;
  border-radius: 0;
  padding-left: 0;
  padding-right: 40px;
  box-shadow: none !important;
  transition: border-color 0.3s ease;
}
html.dark .custom-auth-input { border-bottom-color: #373b3e; }

.custom-auth-input:focus {
  border-color: var(--color-c-dark, #213448);
}
html.dark .custom-auth-input:focus { border-color: #f8f9fa; }

.form-floating > label {
  padding-left: 0;
  color: #adb5bd;
  transition: all 0.2s ease;
}
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
  transform: scale(0.85) translateY(-1rem) translateX(0);
  color: var(--color-c-dark, #213448);
}
html.dark .form-floating > .form-control:focus ~ label,
html.dark .form-floating > .form-control:not(:placeholder-shown) ~ label {
  color: #f8f9fa;
}

.animation-fade-in { animation: fadeIn 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-contacts-auto-fill-button,
input[type="password"]::-webkit-credentials-auto-fill-button {
  display: none !important;
}
</style>