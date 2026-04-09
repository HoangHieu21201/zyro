<template>
  <div class="not-found-wrapper d-flex align-items-center justify-content-center bg-white dark:bg-[#121416]">
    <div class="zyro-container text-center px-4">
      
      <div class="position-relative d-inline-block mx-auto mb-4">
        <h1 class="display-1 fw-black text-c-effect dark:text-[#1a2533] m-0" style="font-size: 15vw; line-height: 1; letter-spacing: -5px; user-select: none;">404</h1>
        <div class="position-absolute top-50 start-50 translate-middle w-100">
          <h2 class="fw-bold text-c-dark dark:text-white text-uppercase tracking-widest bg-white dark:bg-[#121416] d-inline-block px-3 py-1">
            Lạc Đường Rồi
          </h2>
        </div>
      </div>

      <div class="animation-fade-up">
        <p class="text-muted dark:text-gray-400 mb-5 fs-5 mx-auto" style="max-width: 600px;">
          Có vẻ như bạn đã đi lạc khỏi xu hướng. Trang bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc đã chuyển sang bộ sưu tập khác.
        </p>

        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
          <router-link to="/" class="btn btn-outline-secondary dark:border-gray-600 dark:text-gray-300 rounded-pill px-5 py-3 fw-bold text-uppercase tracking-wide hover-c-dark transition-all">
            <i class="bi bi-house me-2"></i> Về Trang Chủ
          </router-link>
          <router-link to="/category" class="btn btn-c-dark rounded-pill px-5 py-3 fw-bold text-uppercase tracking-wide shadow-sm hover-transform transition-all">
            Khám Phá Sản Phẩm <i class="bi bi-arrow-right ms-2"></i>
          </router-link>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.not-found-wrapper {
  min-height: 80vh;
  width: 100%;
}

.fw-black { font-weight: 900; }
.text-c-effect { color: var(--color-c-effect, #EBF1F5); }
.text-c-dark { color: var(--color-c-dark); }
html.dark .text-c-dark { color: #f8f9fa !important; }

.btn-c-dark { background-color: var(--color-c-dark); color: white; border: none; transition: 0.2s; }
.btn-c-dark:hover { background-color: var(--color-c-hover); color: white; }

.hover-c-dark:hover { border-color: var(--color-c-dark) !important; color: var(--color-c-dark) !important; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important; }

.tracking-widest { letter-spacing: 3px; }
.tracking-wide { letter-spacing: 1px; }

.animation-fade-up { animation: fadeUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>