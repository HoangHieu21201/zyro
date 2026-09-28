<template>
  <div class="info-navigation h-100">
    <div class="d-none d-lg-block h-100">
      <div class="sticky-top transition-all" :style="{ top: isHeaderHidden ? '30px' : '160px', zIndex: 10, transition: 'top 0.3s ease' }">
        <h6 class="fw-bold mb-4 text-uppercase tracking-widest" style="color: var(--color-c-dark);"><i class="bi bi-list-stars text-urban me-2"></i>Dịch vụ Khách hàng</h6>
        <ul class="nav flex-column zyro-sidebar-nav gap-2">
          <li class="nav-item">
            <router-link to="/about-us" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/about-us' }">
              <i class="bi bi-info-circle-fill text-primary me-2"></i> Về ZYRO
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/contact" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/contact' }">
              <i class="bi bi-telephone-fill text-success me-2"></i> Liên hệ
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/shipping-policy" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/shipping-policy' }">
              <i class="bi bi-truck text-warning me-2"></i> Chính sách vận chuyển
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/return-policy" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/return-policy' }">
              <i class="bi bi-arrow-repeat text-danger me-2"></i> Chính sách đổi trả
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/privacy-policy" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/privacy-policy' }">
              <i class="bi bi-shield-lock-fill text-info me-2"></i> Chính sách bảo mật
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/terms-of-service" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/terms-of-service' }">
              <i class="bi bi-file-earmark-text-fill text-secondary me-2"></i> Điều khoản dịch vụ
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/faq" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center" :class="{ 'router-link-active': currentRoute === '/faq' }">
              <i class="bi bi-question-circle-fill text-primary me-2"></i> Câu hỏi thường gặp (FAQ)
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
const isHeaderHidden = ref(false);
let lastScrollPosition = 0;
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

const handleScroll = () => {
  const currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
  if (currentScrollPosition <= 0) { isHeaderHidden.value = false; return; }
  if (Math.abs(currentScrollPosition - lastScrollPosition) < 2) return;
  if (currentScrollPosition > lastScrollPosition && currentScrollPosition > 100) {
    isHeaderHidden.value = true;
  } else if (currentScrollPosition < lastScrollPosition) {
    isHeaderHidden.value = false;
  }
  lastScrollPosition = currentScrollPosition;
};


onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
});
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

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