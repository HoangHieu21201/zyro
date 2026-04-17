<template>
  <div class="auth-wrapper d-flex min-vh-100 bg-white dark:bg-[#121416]">
    <div class="d-none d-lg-flex col-lg-6 position-relative p-0 overflow-hidden auth-cover">
      <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=1200&auto=format&fit=crop" class="w-100 h-100 object-fit-cover zoom-anim" alt="ZYRO Fashion" />
      <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
      
      <div class="position-absolute top-50 start-50 translate-middle text-center w-100 p-4 z-index-2">
         <h2 class="text-white display-4 fw-bold font-script text-shadow-lg mb-3">ZYRO Security</h2>
         <p class="text-white fs-5 text-shadow fw-medium">Bảo vệ tài khoản và phong cách của bạn.</p>
      </div>

      <router-link to="/login" class="position-absolute top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-bold hover-opacity text-shadow z-index-2">
         <i class="bi bi-arrow-left me-2 fs-5"></i> Quay lại Đăng nhập
      </router-link>
    </div>
    
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5 position-relative">
       <div class="w-100" style="max-width: 420px;">
          
          <router-link to="/login" class="d-lg-none text-dark dark:text-white text-decoration-none d-inline-flex align-items-center fw-bold mb-4 hover-text-urban transition-all">
             <i class="bi bi-arrow-left me-2 fs-5"></i> Về Đăng nhập
          </router-link>

          <div class="text-center mb-5 pb-2">
             <h1 class="fw-black tracking-widest text-dark dark:text-white mb-4" style="font-size: 3rem;">ZYRO.</h1>
             <h5 class="fw-bold text-uppercase tracking-widest text-urban mb-2">Khôi Phục Mật Khẩu</h5>
             
             <p class="text-muted small" v-if="step === 1">Nhập email đã đăng ký để nhận mã xác nhận.</p>
             <p class="text-muted small" v-if="step === 2">Vui lòng kiểm tra email <strong class="text-dark dark:text-white">{{ form.email }}</strong></p>
             <p class="text-muted small" v-if="step === 3">Tạo mật khẩu mới cho tài khoản của bạn.</p>
          </div>

          <!-- BƯỚC 1: NHẬP EMAIL -->
          <form @submit.prevent="handleSendOtp" v-if="step === 1" class="animation-fade-in">
             <div class="custom-auth-group mb-5">
               <input type="email" class="form-control zyro-auth-input" id="emailInput" v-model="form.email" placeholder=" " required />
               <label for="emailInput" class="zyro-auth-label text-muted fw-semibold small text-uppercase tracking-wide">
                 Email đăng ký <span class="text-danger">*</span>
               </label>
               <div class="zyro-auth-icon text-urban">
                 <i class="bi bi-envelope"></i>
               </div>
             </div>

             <button type="submit" class="btn btn-c-dark w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-lg hover-transform" :disabled="isLoading">
               <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> NHẬN MÃ OTP
             </button>
          </form>

          <!-- BƯỚC 2: NHẬP OTP -->
          <form @submit.prevent="handleVerifyOtp" v-if="step === 2" class="animation-fade-in">
             <div class="d-flex justify-content-between mb-4 gap-2" dir="ltr">
               <input
                 v-for="(digit, index) in otpArray"
                 :key="index"
                 type="text"
                 class="form-control text-center fw-bold fs-3 rounded-4 shadow-sm-hover otp-box"
                 maxlength="1"
                 v-model="otpArray[index]"
                 @input="handleOtpInput(index, $event)"
                 @keydown="handleOtpKeydown(index, $event)"
                 @paste="handleOtpPaste"
                 :ref="el => { if (el) otpInputs[index] = el }"
                 autocomplete="one-time-code"
                 inputmode="numeric"
               />
             </div>

             <div class="d-flex justify-content-between align-items-center mb-5">
                <span class="text-muted small cursor-pointer hover-text-urban fw-medium" @click="goBackToStep1"><i class="bi bi-pencil-square me-1"></i>Đổi email khác</span>
                <span class="text-muted small fw-medium">Hết hạn sau: <strong class="text-danger fs-6 ms-1">{{ countdown }}s</strong></span>
             </div>

             <button type="submit" class="btn btn-c-dark w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-lg hover-transform" :disabled="isLoading || form.otp.length < 6">
               <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> XÁC NHẬN OTP
             </button>
             
             <div class="text-center mt-4">
                <button type="button" class="btn btn-link text-decoration-none text-muted fw-bold small p-0 hover-text-urban" :class="{'text-urban': countdown === 0}" :disabled="countdown > 0 || isResending" @click="handleSendOtp">
                  <span v-if="isResending" class="spinner-border spinner-border-sm me-1"></span> 
                  GỬI LẠI MÃ {{ countdown > 0 ? `(${countdown}s)` : '' }}
                </button>
             </div>
          </form>

          <!-- BƯỚC 3: ĐẶT LẠI MẬT KHẨU -->
          <form @submit.prevent="handleResetPassword" v-if="step === 3" class="animation-fade-in">
             <div class="custom-auth-group mb-4 position-relative">
               <input :type="showPass1 ? 'text' : 'password'" class="form-control zyro-auth-input pe-5" id="newPass" v-model="form.password" placeholder=" " required />
               <label for="newPass" class="zyro-auth-label text-muted fw-semibold small text-uppercase tracking-wide">
                 Mật khẩu mới <span class="text-danger">*</span>
               </label>
               <div class="zyro-auth-icon text-urban"><i class="bi bi-key"></i></div>
               <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-text-urban px-3" style="z-index: 5;" @click="showPass1 = !showPass1">
                 <i class="bi" :class="showPass1 ? 'bi-eye-slash' : 'bi-eye'"></i>
               </button>
             </div>

             <div class="custom-auth-group mb-5 position-relative">
               <input :type="showPass2 ? 'text' : 'password'" class="form-control zyro-auth-input pe-5" id="confirmPass" v-model="form.password_confirmation" placeholder=" " required />
               <label for="confirmPass" class="zyro-auth-label text-muted fw-semibold small text-uppercase tracking-wide">
                 Xác nhận mật khẩu <span class="text-danger">*</span>
               </label>
               <div class="zyro-auth-icon text-urban"><i class="bi bi-check2-all"></i></div>
               <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 text-muted hover-text-urban px-3" style="z-index: 5;" @click="showPass2 = !showPass2">
                 <i class="bi" :class="showPass2 ? 'bi-eye-slash' : 'bi-eye'"></i>
               </button>
             </div>

             <button type="submit" class="btn btn-c-dark w-100 rounded-pill py-3 fw-bold text-uppercase tracking-widest shadow-lg hover-transform" :disabled="isLoading">
               <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> ĐỔI MẬT KHẨU
             </button>
          </form>

       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const router = useRouter();
const step = ref(1); 
const isLoading = ref(false);
const isResending = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);

const form = ref({
  email: '',
  otp: '',
  reset_token: '',
  password: '',
  password_confirmation: ''
});

const otpArray = ref(['', '', '', '', '', '']);
const otpInputs = ref([]); 

watch(otpArray, (newVal) => {
  form.value.otp = newVal.join('');
}, { deep: true });

const handleOtpInput = (index, event) => {
  let val = event.target.value;
  if (val && !/^\d+$/.test(val)) {
     otpArray.value[index] = '';
     return;
  }
  if (val && index < 5) otpInputs.value[index + 1].focus();
};

const handleOtpKeydown = (index, event) => {
  if (event.key === 'Backspace' && !otpArray.value[index] && index > 0) {
    otpInputs.value[index - 1].focus();
  }
};

const handleOtpPaste = (event) => {
  event.preventDefault();
  const pasteData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
  if (pasteData) {
    for (let i = 0; i < pasteData.length; i++) otpArray.value[i] = pasteData[i];
    const nextIndex = Math.min(pasteData.length, 5);
    otpInputs.value[nextIndex].focus();
  }
};

const goBackToStep1 = () => {
  step.value = 1;
  otpArray.value = ['', '', '', '', '', ''];
  form.value.otp = '';
  form.value.reset_token = '';
  form.value.password = '';
  form.value.password_confirmation = '';
  if (timer) clearInterval(timer);
};

const countdown = ref(0);
let timer = null;

const startCountdown = () => {
  countdown.value = 60;
  if (timer) clearInterval(timer);
  timer = setInterval(() => {
    if (countdown.value > 0) countdown.value--;
    else clearInterval(timer);
  }, 1000);
};

const handleFormErrors = (error, defaultMsg) => {
    if (error.response?.status === 422 && error.response?.data?.errors) {
        const errors = error.response.data.errors;
        const firstError = Object.values(errors)[0][0]; 
        ZyroSwal.toastError(firstError);
    } else if (error.response) {
        let errorMsg = error.response?.data?.message || defaultMsg;
        ZyroSwal.toastError(errorMsg);
    } else {
        console.error('Phát hiện lỗi Javascript hoặc Network:', error);
        ZyroSwal.toastError(defaultMsg);
    }
};

const handleSendOtp = async () => {
  if (step.value === 1) isLoading.value = true;
  else isResending.value = true;

  try {
    await api.post('/client/forgot-password/send-otp', { email: form.value.email.trim() });
    ZyroSwal.toastSuccess('Mã OTP đã được gửi đến email của bạn!');
    step.value = 2;
    nextTick(() => { if(otpInputs.value[0]) otpInputs.value[0].focus(); });
    startCountdown();
  } catch (error) {
    handleFormErrors(error, 'Lỗi gửi OTP.');
  } finally {
    isLoading.value = false;
    isResending.value = false;
  }
};

const handleVerifyOtp = async () => {
  isLoading.value = true;
  try {
    const res = await api.post('/client/forgot-password/verify-otp', {
      email: form.value.email.trim(),
      otp: form.value.otp
    });
    
    const payload = res.data || res;
    const token = payload.reset_token || payload.data?.reset_token;

    if (!token) {
        ZyroSwal.toastError('Lỗi Client: Không lấy được Token bảo mật từ Server!');
        return; 
    }
    
    form.value.reset_token = token;
    step.value = 3;
    ZyroSwal.toastSuccess('Xác thực thành công. Vui lòng tạo mật khẩu mới.');
    if (timer) clearInterval(timer);
  } catch (error) {
    handleFormErrors(error, 'Mã OTP không hợp lệ.');
    
    otpArray.value = ['', '', '', '', '', ''];
    form.value.otp = '';
    nextTick(() => { if(otpInputs.value[0]) otpInputs.value[0].focus(); });

    if (error.response?.status === 403) {
        setTimeout(() => { goBackToStep1(); }, 2000);
    }
  } finally {
    isLoading.value = false;
  }
};

const handleResetPassword = async () => {
  if (!form.value.reset_token) {
      ZyroSwal.toastError('Mã Token bảo mật bị rỗng! Vui lòng làm lại từ đầu.');
      goBackToStep1();
      return;
  }

  if (form.value.password !== form.value.password_confirmation) {
      ZyroSwal.toastError('Mật khẩu xác nhận không khớp.');
      return;
  }
  
  isLoading.value = true;
  try {
    const res = await api.post('/client/forgot-password/reset', {
      email: form.value.email.trim(),
      reset_token: form.value.reset_token,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    });
    
    ZyroSwal.toastSuccess(res.data.message || 'Đổi mật khẩu thành công!');
    router.push('/login');
  } catch (error) {
    handleFormErrors(error, 'Lỗi đặt lại mật khẩu.');

    if (error.response?.status === 403) {
        setTimeout(() => { goBackToStep1(); }, 2000);
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => { window.scrollTo(0, 0); });
onUnmounted(() => { if (timer) clearInterval(timer); });
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
.btn-c-dark:disabled { background-color: #6c757d; cursor: not-allowed; opacity: 0.8; }

.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }
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

.otp-box {
  width: 55px;
  height: 65px;
  border: 2px solid var(--color-c-effect);
  background-color: var(--color-c-effect);
  color: var(--color-c-dark);
  transition: all 0.3s ease;
  caret-color: var(--color-c-hover); 
}
.otp-box:focus {
  border-color: var(--color-c-hover);
  background-color: transparent;
  box-shadow: 0 4px 15px rgba(84, 119, 146, 0.15) !important;
  transform: translateY(-2px);
}
html.dark .otp-box {
  background-color: #212529;
  border-color: #373b3e;
  color: #fff;
}
html.dark .otp-box:focus {
  background-color: transparent;
  border-color: var(--color-c-hover);
}
.otp-box::-webkit-outer-spin-button,
.otp-box::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.animation-fade-in { animation: fadeIn 0.4s ease forwards; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateX(-10px); }
  to { opacity: 1; transform: translateX(0); }
}
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-contacts-auto-fill-button,
input[type="password"]::-webkit-credentials-auto-fill-button {
  display: none !important;
}
</style>