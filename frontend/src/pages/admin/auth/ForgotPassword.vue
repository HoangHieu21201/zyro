<template>
  <div class="min-h-screen flex items-center justify-center bg-c-effect px-4 py-10">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 transition-all duration-500">
      
      <!-- Nút quay lại -->
      <button v-if="step === 2" @click="step = 1" class="text-gray-400 hover:text-c-dark mb-4 flex items-center transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="m15 18-6-6 6-6"/></svg>
        <span class="text-sm font-medium">Quay lại</span>
      </button>

      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-c-effect text-c-dark mb-4">
          <svg v-if="step === 1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h1 class="text-2xl font-bold tracking-widest text-c-dark uppercase mb-2">
          {{ step === 1 ? 'Quên Mật Khẩu' : 'Nhập Mã Xác Nhận' }}
        </h1>
        <p class="text-sm text-gray-500">
          {{ step === 1 ? 'Vui lòng nhập địa chỉ email đã đăng ký của bạn. Chúng tôi sẽ gửi mã OTP gồm 6 chữ số để xác thực.' : `Mã xác nhận đã được gửi đến email: ${form.email}` }}
        </p>
      </div>

      <!-- Hiển thị thông báo chung -->
      <div v-if="errorMessage" class="p-3 mb-5 bg-red-50 text-red-600 text-sm rounded border border-red-200">
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="p-3 mb-5 bg-green-50 text-green-700 text-sm rounded border border-green-200">
        {{ successMessage }}
      </div>

      <!-- BƯỚC 1: NHẬP EMAIL -->
      <form v-if="step === 1" @submit.prevent="handleRequestOTP" class="space-y-6" autocomplete="off">
        <div>
          <label class="block text-sm font-bold text-c-dark mb-2">Địa chỉ Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            autocomplete="off"
            class="w-full px-4 py-3 rounded border border-gray-300 focus:outline-none focus:border-c-hover focus:ring-1 focus:ring-c-hover transition-colors"
            placeholder="admin@zyro.com"
          >
        </div>

        <button 
          type="submit" 
          :disabled="isLoading"
          class="w-full bg-c-dark text-white font-bold py-3 px-4 rounded hover:bg-c-hover transition-colors flex justify-center items-center uppercase tracking-wider text-sm disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ isLoading ? 'Đang gửi...' : 'Gửi mã OTP' }}
        </button>
      </form>

      <!-- BƯỚC 2: NHẬP OTP CHUYÊN NGHIỆP -->
      <form v-if="step === 2" @submit.prevent="handleVerifyOTP" class="space-y-6" autocomplete="off">
        <div class="flex justify-between gap-2 sm:gap-3">
          <input 
            v-for="(digit, index) in 6" :key="index"
            type="text" 
            maxlength="1"
            v-model="otpArray[index]"
            @input="handleOtpInput(index, $event)"
            @keydown="handleOtpKeydown(index, $event)"
            @paste="handleOtpPaste"
            :ref="el => otpInputs[index] = el"
            class="w-10 h-12 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold text-c-dark rounded-lg border border-gray-300 focus:outline-none focus:border-c-dark focus:ring-2 focus:ring-c-dark/20 transition-all bg-gray-50 focus:bg-white"
          >
        </div>

        <button 
          type="submit" 
          :disabled="isLoading || otpArray.join('').length !== 6"
          class="w-full bg-c-dark text-white font-bold py-3 px-4 rounded hover:bg-c-hover transition-colors flex justify-center items-center uppercase tracking-wider text-sm disabled:opacity-70 disabled:cursor-not-allowed mt-6"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ isLoading ? 'Đang xác thực...' : 'Xác nhận OTP' }}
        </button>

        <div class="text-center mt-4">
          <p class="text-sm text-gray-500">
            Chưa nhận được mã? 
            <button 
              v-if="countdown === 0" 
              type="button" 
              @click="handleRequestOTP" 
              class="text-c-dark font-bold hover:underline focus:outline-none"
            >Gửi lại</button>
            <span v-else class="text-gray-400 font-medium">Gửi lại sau {{ countdown }}s</span>
          </p>
        </div>
      </form>

      <div v-if="step === 1" class="text-center mt-6 border-t border-gray-100 pt-6">
        <router-link to="/admin/login" class="text-sm text-c-hover hover:text-c-dark font-medium transition-colors">
          Quay lại trang Đăng nhập
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, nextTick, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const step = ref(1);
const form = reactive({ email: '' });

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// --- LOGIC CHO BƯỚC 2: OTP ---
const otpArray = ref(['', '', '', '', '', '']);
const otpInputs = ref([]);
const countdown = ref(0);
let timer = null;

const startCountdown = () => {
  countdown.value = 60; // 60 giây
  if (timer) clearInterval(timer);
  timer = setInterval(() => {
    if (countdown.value > 0) countdown.value--;
    else clearInterval(timer);
  }, 1000);
};

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

// Xử lý Focus thông minh cho 6 ô OTP
const handleOtpInput = (index, event) => {
  const value = event.target.value;
  if (!/^\d*$/.test(value)) {
    otpArray.value[index] = '';
    return;
  }
  if (value && index < 5) {
    nextTick(() => {
      otpInputs.value[index + 1].focus();
    });
  }
};

const handleOtpKeydown = (index, event) => {
  if (event.key === 'Backspace' && !otpArray.value[index] && index > 0) {
    otpInputs.value[index - 1].focus();
  }
};

const handleOtpPaste = (event) => {
  event.preventDefault();
  const pastedData = event.clipboardData.getData('text').slice(0, 6).replace(/\D/g, ''); 
  if (pastedData) {
    const chars = pastedData.split('');
    chars.forEach((char, i) => {
      if (i < 6) otpArray.value[i] = char;
    });
    const focusIndex = Math.min(chars.length, 5);
    nextTick(() => {
      otpInputs.value[focusIndex].focus();
    });
  }
};

// --- GỌI API THẬT ---
const handleRequestOTP = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    // ĐÃ KÍCH HOẠT API
    const response = await axios.post('http://127.0.0.1:8000/api/v1/admin/forgot-password', form);
    
    successMessage.value = response.data.message || 'Mã xác nhận đã được gửi thành công!';
    step.value = 2;
    startCountdown();
    
    nextTick(() => {
      if (otpInputs.value[0]) otpInputs.value[0].focus();
    });

  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi gửi email.';
  } finally {
    isLoading.value = false;
  }
};

const handleVerifyOTP = async () => {
  const otpCode = otpArray.value.join('');
  if (otpCode.length !== 6) return;

  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Vì Backend xử lý việc check OTP trực tiếp ở hàm Đổi mật khẩu (resetPassword), 
    // nên ở bước này ta chỉ cần chuyển sang trang Đổi Mật Khẩu và truyền OTP theo URL.
    await new Promise(resolve => setTimeout(resolve, 500)); // Tạo độ trễ nhẹ cho mượt

    router.push({ 
      name: 'admin-reset-password', 
      query: { email: form.email, token: otpCode } 
    });

  } catch (error) {
    errorMessage.value = 'Có lỗi xảy ra.';
  } finally {
    isLoading.value = false;
  }
};
</script>