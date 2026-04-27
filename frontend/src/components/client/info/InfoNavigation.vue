<template>
  <div class="info-navigation">
    <div class="d-none d-lg-block">
      <div class="sticky-top" style="top: 160px; z-index: 10;">
        <h6 class="fw-bold mb-4 text-uppercase tracking-widest" style="color: var(--color-c-dark);">Dịch vụ Khách hàng</h6>
        <ul class="nav flex-column zyro-sidebar-nav gap-2">
          <li class="nav-item">
            <router-link to="/about-us" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/about-us' }">Về ZYRO</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/contact" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/contact' }">Liên hệ</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/shipping-policy" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/shipping-policy' }">Chính sách vận chuyển</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/return-policy" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/return-policy' }">Chính sách đổi trả</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/privacy-policy" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/privacy-policy' }">Chính sách bảo mật</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/terms-of-service" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/terms-of-service' }">Điều khoản dịch vụ</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/faq" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/faq' }">Câu hỏi thường gặp (FAQ)</router-link>
          </li>
          <!-- <li class="nav-item">
            <router-link to="/stores" class="nav-link rounded-3 px-3 py-2" :class="{ 'router-link-active': currentRoute === '/stores' }">Hệ thống cửa hàng</router-link>
          </li> -->
          
        </ul>
      </div>
    </div>

    <div class="d-block d-lg-none mb-3">
      <select class="form-select border-light-subtle rounded-3 py-2 bg-white fw-medium shadow-sm" :value="currentRoute" @change="navigateTo($event)">
        <option value="/about-us">Về ZYRO</option>
        <option value="/shipping-policy">Chính sách vận chuyển</option>
        <option value="/return-policy">Chính sách đổi trả</option>
        <option value="/privacy-policy">Chính sách bảo mật</option>
        <option value="/terms-of-service">Điều khoản dịch vụ</option>
        <option value="/faq">Câu hỏi thường gặp (FAQ)</option>
        <option value="/stores">Hệ thống cửa hàng</option>
        <option value="/contact">Liên hệ</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

defineProps({
  currentRoute: {
    type: String,
    required: true
  }
});

const router = useRouter();

const navigateTo = (event) => {
  router.push(event.target.value);
};
</script>

<style scoped>
.tracking-widest { letter-spacing: 0.15em; }

.zyro-sidebar-nav .nav-link {
  color: var(--color-c-hover);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 500;
  border: 1px solid transparent;
}
.zyro-sidebar-nav .nav-link:hover {
  background-color: var(--color-c-effect);
  color: var(--color-c-dark);
  padding-left: 1.5rem !important;
}
.zyro-sidebar-nav .nav-link.router-link-active {
  background-color: var(--color-c-dark);
  color: #ffffff !important;
  box-shadow: 0 4px 15px rgba(33, 52, 72, 0.15);
}

[data-bs-theme="dark"] .form-select { background-color: #121416 !important; border-color: #373b3e !important; color: #fff; }
[data-bs-theme="dark"] .zyro-sidebar-nav .nav-link { color: #adb5bd; }
[data-bs-theme="dark"] .zyro-sidebar-nav .nav-link:hover { background-color: #2b3035; color: #fff; }
[data-bs-theme="dark"] .zyro-sidebar-nav .nav-link.router-link-active { background-color: var(--color-c-hover); color: #fff !important; }
</style>