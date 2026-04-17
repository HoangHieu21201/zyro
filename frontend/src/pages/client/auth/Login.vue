<template>
  <div class="auth-wrapper d-flex min-vh-100 bg-white dark:bg-[#121416]">
    <div class="d-none d-lg-flex col-lg-6 position-relative p-0 overflow-hidden auth-cover">
      <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=1200&auto=format&fit=crop" class="w-100 h-100 object-fit-cover zoom-anim" alt="ZYRO Fashion">
      <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
      
      <div class="position-absolute top-50 start-50 translate-middle text-center w-100 p-4 z-index-2">
         <h2 class="text-white display-4 fw-bold font-script text-shadow-lg mb-3">Fall Collection</h2>
         <p class="text-white fs-5 text-shadow fw-medium">Định hình phong cách cá nhân của bạn.</p>
      </div>

      <router-link to="/" class="position-absolute top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-bold hover-opacity text-shadow z-index-2">
         <i class="bi bi-arrow-left me-2 fs-5"></i> Về Trang Chủ
      </router-link>
    </div>
    
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5 position-relative">
       <div class="w-100 animation-fade-in" style="max-width: 420px;">
          
          <router-link to="/" class="d-lg-none text-dark dark:text-white text-decoration-none d-inline-flex align-items-center fw-bold mb-4 hover-text-urban transition-all">
             <i class="bi bi-arrow-left me-2 fs-5"></i> Về Trang Chủ
          </router-link>

          <div class="text-center mb-5 pb-2">
             <h1 class="fw-black tracking-widest text-dark dark:text-white mb-4" style="font-size: 3rem;">ZYRO.</h1>
             <h5 class="fw-bold text-uppercase tracking-widest text-urban mb-2">Đăng Nhập</h5>
             <p class="text-muted small">Chào mừng bạn quay trở lại với ZYRO Studios.</p>
          </div>

          <form @submit.prevent="handleLogin" autocomplete="off">
             <div class="custom-auth-group mb-4">
               <input type="email" class="form-control zyro-auth-input" id="emailInput" v-model="form.email" placeholder=" " required>
               <label for="emailInput" class="zyro-auth-label text-muted fw-semibold small text-uppercase tracking-wide">
                 Email đăng nhập <span class="text-danger">*</span>
               </label>
               <div class="zyro-auth-icon text-urban">
                 <i class="bi bi-person-bounding-box"></i>
               </div>
             </div>

             <div class="custom-auth-group mb-4 position-relative">
               <input :type="showPass ? 'text' : 'password'" class="form-control zyro-auth-input pe-5" id="passInput" v-model="form.password" placeholder=" " required>
               <label for="passInput" class="zyro-auth-label text-muted fw-semibold small text-uppercase tracking-wide">
                 Mật khẩu <span class="text-danger">*</span>
               </label>
               <div class="zyro-auth-icon text-urban">
                 <i class="bi bi-fingerprint"></i>
               </div>
               <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-text-urban px-3" style="z-index: 5;" @click="showPass = !showPass">
                 <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
               </button>
             </div>

             <div class="d-flex justify-content-between align-items-center mb-5 pb-2 mt-2">
               <label class="custom-checkbox-wrapper d-flex align-items-center cursor-pointer m-0">
                 <input type="checkbox" class="d-none" v-model="form.remember">
                 <span class="custom-checkbox me-2 d-flex align-items-center justify-content-center transition-all">
                   <i class="bi bi-check-lg text-white"></i>
                 </span>
                 <span class="text-muted small fw-medium">Ghi nhớ tài khoản</span>
               </label>
               <router-link to="#" class="text-urban small fw-bold text-decoration-none hover-underline">Quên mật khẩu?</router-link>
             </div>

             <button type="submit" class="btn btn-c-dark w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-lg hover-transform mb-4" :disabled="isLoading">
               <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> ĐĂNG NHẬP
             </button>

             <div class="text-center">
               <p class="text-muted small mb-0">Bạn chưa có tài khoản? 
                 <router-link to="/register" class="text-dark dark:text-white fw-bold text-decoration-none border-bottom border-dark dark:border-gray-300 pb-1 hover-text-urban ms-1 transition-all">ĐĂNG KÝ NGAY</router-link>
               </p>
             </div>
          </form>

       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import { useCartStore } from '@/stores/cartStore';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const router = useRouter();
const cartStore = useCartStore();
const showPass = ref(false);
const isLoading = ref(false);

const form = ref({
  email: '',
  password: '',
  remember: false
});

const handleLogin = async () => {
  isLoading.value = true;
  try {
    const payload = {
      email: form.value.email.trim(),
      password: form.value.password
    };

    const res = await api.post('/client/login', payload);

    if (res.data.access_token) {
      localStorage.setItem('access_token', res.data.access_token);
      localStorage.setItem('user_info', JSON.stringify(res.data.user));

      await cartStore.mergeCartAfterLogin();

      ZyroSwal.toastSuccess(res.data.message || 'Đăng nhập thành công');
      router.push('/');
    }
  } catch (error) {
    let errorMsg = 'Kết nối máy chủ thất bại.';
    if (error.response?.data?.errors?.email) {
        errorMsg = error.response.data.errors.email[0];
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

.auth-cover { height: 100vh; position: sticky; top: 0; }
.zoom-anim { animation: bg-zoom 20s linear infinite alternate; transform-origin: center; }
@keyframes bg-zoom { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }

.custom-auth-group {
  position: relative;
  background-color: var(--color-c-effect);
  border-radius: 8px;
  overflow: hidden; 
  border: 1.5px solid transparent;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
html.dark .custom-auth-group { background-color: #212529; }
.custom-auth-group:focus-within {
  border-color: var(--color-c-hover);
  box-shadow: 0 0 0 4px rgba(84, 119, 146, 0.15);
}

.zyro-auth-input {
  height: 60px;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  padding-left: 1.5rem; 
  font-weight: 600;
  color: var(--color-c-dark);
  transition: padding-left 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); 
}
html.dark .zyro-auth-input { color: #fff; }

.zyro-auth-label {
  position: absolute;
  top: 50%;
  left: 1.5rem;
  transform: translateY(-50%) translateX(0); 
  pointer-events: none;
  margin: 0;
  transition: all 0.4s ease; 
}

.zyro-auth-icon {
  position: absolute;
  left: 1.25rem;
  top: 150%; 
  transform: translateY(-50%);
  opacity: 0;
  font-size: 1.3rem;
  pointer-events: none;
  transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55); 
}

.zyro-auth-input:focus ~ .zyro-auth-label,
.zyro-auth-input:not(:placeholder-shown) ~ .zyro-auth-label {
  opacity: 0;
  transform: translateY(-50%) translateX(30px); 
}

.zyro-auth-input:focus ~ .zyro-auth-icon,
.zyro-auth-input:not(:placeholder-shown) ~ .zyro-auth-icon {
  top: 50%; 
  opacity: 1;
}

.zyro-auth-input:focus,
.zyro-auth-input:not(:placeholder-shown) {
  padding-left: 3.2rem; 
}

.custom-checkbox-wrapper { user-select: none; }
.custom-checkbox {
  width: 22px;
  height: 22px;
  border: 1.5px solid #ced4da;
  border-radius: 6px;
  background-color: transparent;
}
html.dark .custom-checkbox { border-color: #495057; }

.custom-checkbox i {
  font-size: 1.1rem;
  opacity: 0;
  transform: scale(0.3); 
  transition: all 0.25s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.custom-checkbox-wrapper:hover .custom-checkbox { border-color: var(--color-c-hover, #547792); }

.custom-checkbox-wrapper input:checked ~ .custom-checkbox {
  background-color: var(--color-c-dark, #213448);
  border-color: var(--color-c-dark, #213448);
}
html.dark .custom-checkbox-wrapper input:checked ~ .custom-checkbox {
  background-color: var(--color-c-hover, #547792);
  border-color: var(--color-c-hover, #547792);
}

.custom-checkbox-wrapper input:checked ~ .custom-checkbox i {
  opacity: 1;
  transform: scale(1);
}

.animation-fade-in { animation: fadeIn 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>