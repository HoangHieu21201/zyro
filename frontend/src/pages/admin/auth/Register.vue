<template>
  <div class="min-h-screen flex items-center justify-center bg-c-effect px-4 py-10">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
      
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold tracking-widest text-c-dark uppercase mb-2">ZYRO.</h1>
        <p class="text-sm text-gray-500">Khởi tạo tài khoản Quản trị viên</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5" autocomplete="off">
        
        <div v-if="successMessage" class="p-3 bg-green-50 text-green-700 text-sm rounded border border-green-200">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="p-3 bg-red-50 text-red-600 text-sm rounded border border-red-200">
          {{ errorMessage }}
        </div>

        <div>
          <label class="block text-sm font-bold text-c-dark mb-1">Họ và Tên</label>
          <input 
            v-model="form.fullname" 
            type="text" 
            required 
            autocomplete="off"
            class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-c-hover"
          >
        </div>

        <div>
          <label class="block text-sm font-bold text-c-dark mb-1">Địa chỉ Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            autocomplete="off"
            class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-c-hover"
          >
        </div>

        <div>
          <label class="block text-sm font-bold text-c-dark mb-1">Mật khẩu</label>
          <div class="relative">
            <input 
              v-model="form.password" 
              :type="showPassword ? 'text' : 'password'" 
              required 
              minlength="8" 
              autocomplete="new-password"
              class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-c-hover pr-10"
            >
            <button 
              type="button" 
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-c-dark focus:outline-none"
            >
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-bold text-c-dark mb-1">Xác nhận Mật khẩu</label>
          <div class="relative">
            <input 
              v-model="password_confirmation" 
              :type="showPasswordConfirm ? 'text' : 'password'" 
              required 
              minlength="8" 
              autocomplete="new-password"
              class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:border-c-hover pr-10"
            >
            <button 
              type="button" 
              @click="showPasswordConfirm = !showPasswordConfirm"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-c-dark focus:outline-none"
            >
              <svg v-if="!showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
        </div>

        <input type="hidden" v-model="form.role_id">
        <input type="hidden" v-model="form.status">

        <button 
          type="submit" 
          :disabled="isLoading"
          class="w-full mt-4 bg-c-dark text-white font-bold py-3 px-4 rounded hover:bg-c-hover transition-colors flex justify-center items-center uppercase tracking-wider text-sm disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isLoading ? 'Đang tạo...' : 'Khởi Tạo Tài Khoản' }}
        </button>

        <div class="text-center mt-4 border-t border-gray-100 pt-4">
          <router-link to="/admin/login" class="text-sm text-c-hover hover:text-c-dark font-medium">Quay lại trang Đăng nhập</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = reactive({
  fullname: '',
  email: '',
  password: '',
  role_id: 1, 
  status: 'active'
});

const password_confirmation = ref('');
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const handleRegister = async () => {
  if (form.password !== password_confirmation.value) {
    errorMessage.value = 'Mật khẩu xác nhận không trùng khớp!';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const payload = {
      ...form,
      password_confirmation: password_confirmation.value
    };

    const response = await axios.post('http://127.0.0.1:8000/api/v1/admin/register', payload);
    
    if (response.data.success) {
      successMessage.value = 'Tạo tài khoản thành công! Tự động chuyển trang...';
      setTimeout(() => {
        router.push('/admin/login');
      }, 2000);
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      errorMessage.value = Object.values(errors).flat().join(' ');
    } else if (error.response && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Không thể kết nối máy chủ.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>