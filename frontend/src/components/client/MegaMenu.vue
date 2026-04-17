<template>
  <div class="mega-menu-wrapper">
    <transition name="fade">
      <div v-if="isOpen" class="mega-menu-backdrop" @click="closeMenu"></div>
    </transition>

    <transition name="mega-slide">
      <div v-if="isOpen" class="mega-menu-panel shadow-lg rounded-4 bg-white dark:bg-[#1a2533] d-flex flex-column">
        
        <div class="mega-menu-content custom-scrollbar-y px-4 px-lg-5 pt-4 pb-2 flex-grow-1">
          
          <!-- NẾU CÓ LOOKBOOK THÌ MỚI HIỂN THỊ KHU VỰC NÀY -->
          <div v-if="lookbooks && lookbooks.length > 0" class="mb-5 pb-2 border-bottom dark:border-gray-700">
            <h6 class="text-center fw-bold text-dark dark:text-white mb-4 text-uppercase tracking-widest">BỘ SƯU TẬP</h6>
            
            <div class="lookbook-auto-grid custom-scrollbar-x pb-4" 
                 :class="lookbooks.length <= 4 ? 'is-centered' : 'is-swipeable'">
              
              <div v-for="lb in lookbooks" :key="lb.id" class="lookbook-card flex-shrink-0 cursor-pointer group" @click="closeMenu">
                <div class="position-relative rounded-4 overflow-hidden mb-3" style="aspect-ratio: 16/9; width: 100%;">
                  <!-- Ảnh và Tên Lookbook -->
                  <img :src="lb.main_image || '/client_placeholder.png'" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale" @error="e => e.target.src='/client_placeholder.png'">
                  <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25 transition-opacity group-hover-opacity-50"></div>
                  
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark-bottom">
                    <h6 class="fw-bold mb-1 text-white text-truncate">{{ lb.name }}</h6>
                  </div>
                </div>
                <!-- Subtitle lấy từ description -->
                <div class="text-muted dark:text-gray-400 small text-truncate text-center px-2">{{ lb.description || 'Khám phá ngay' }}</div>
              </div>
            </div>
          </div>

          <!-- DANH MỤC -->
          <div class="pb-3">
            <!-- NẾU CÓ DATA DANH MỤC -->
            <div v-if="categories && categories.length > 0" class="category-smart-grid">
              
              <div class="category-smart-col" v-for="parent in categories" :key="parent.id">
                
                <router-link :to="`/category/${parent.slug}`" class="text-decoration-none d-inline-block mb-3" @click="closeMenu">
                  <h6 class="fw-bold text-dark dark:text-white m-0 text-uppercase tracking-widest hover-text-urban transition-color">
                    {{ parent.name }}
                  </h6>
                </router-link>
                
                <div class="d-flex flex-column gap-1">
                  <div v-for="child in parent.children" :key="child.id">
                    
                    <div class="d-flex align-items-center justify-content-between py-2 rounded cursor-pointer transition-all hover-bg-light dark-hover-bg group pe-2" 
                         @click="toggleCategory(child.id)">
                      <div class="d-flex align-items-center gap-3">
                        <img :src="child.image || '/client_placeholder.png'" class="rounded-circle object-fit-cover shadow-sm bg-light" style="width: 36px; height: 36px;" @error="e => e.target.src='/client_placeholder.png'">
                        <span class="fw-medium text-dark dark:text-gray-200 group-hover-text-urban transition-color" style="font-size: 0.9rem;">{{ child.name }}</span>
                      </div>
                      <i class="bi text-muted transition-transform" :class="isCategoryOpen(child.id) ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                    </div>

                    <!-- Sản phẩm thả xuống -->
                    <div v-show="isCategoryOpen(child.id)" class="ps-5 py-2 accordion-content animation-fade-down">
                      <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li v-for="prodName in child.products" :key="prodName">
                          <router-link to="/category" class="text-muted dark:text-gray-400 text-decoration-none small hover-text-urban transition-all" @click="closeMenu">
                            {{ prodName }}
                          </router-link>
                        </li>
                        <li v-if="!child.products || child.products.length === 0">
                           <span class="text-muted small fst-italic">Đang cập nhật...</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- NẾU ĐANG CHỜ API HOẶC RỖNG THÌ HIỆN LOADING -->
            <div v-else class="text-center py-5 w-100">
              <div class="spinner-border text-secondary mb-3" role="status" style="width: 2rem; height: 2rem;"></div>
              <p class="text-muted">Đang tải dữ liệu danh mục...</p>
            </div>

          </div>

        </div>

        <div class="mega-menu-footer text-center pb-4 pt-2 bg-white dark:bg-[#1a2533] rounded-bottom-4 shadow-sm-top z-index-1">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 shadow-sm border rounded-pill px-5 py-2 fw-bold hover-bg-effect transition-all" @click="closeMenu">
            <i class="bi bi-x-lg me-1"></i> Đóng
          </button>
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  offsetTop: { type: Number, default: 90 },
  categories: { type: Array, default: () => [] },
  lookbooks: { type: Array, default: () => [] }
});

const emit = defineEmits(['close']);

const closeMenu = () => {
  emit('close');
};

watch(() => props.isOpen, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});

const openCategoryIds = ref([]);
const toggleCategory = (id) => {
  const index = openCategoryIds.value.indexOf(id);
  if (index > -1) {
    openCategoryIds.value.splice(index, 1);
  } else {
    openCategoryIds.value.push(id);
  }
};
const isCategoryOpen = (id) => openCategoryIds.value.includes(id);

onMounted(() => {
  window.addEventListener('popstate', closeMenu);
});
</script>

<style scoped>
.mega-menu-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
}

.mega-menu-panel {
  position: fixed;
  top: 20px; 
  left: 50%;
  transform: translateX(-50%); 
  width: 96%; 
  max-width: 1300px; 
  max-height: calc(100vh - 40px); 
  z-index: 1060; 
  overflow: hidden; 
  border: 1px solid rgba(0,0,0,0.08);
}
html.dark .mega-menu-panel { border: 1px solid rgba(255,255,255,0.05); }

.mega-menu-content { overflow-y: auto; }

.lookbook-auto-grid {
  display: flex;
  gap: 1.5rem;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.lookbook-auto-grid.is-centered { justify-content: center; }
.lookbook-auto-grid.is-swipeable { justify-content: flex-start; }

.lookbook-card {
  width: calc(25% - 1.2rem); 
  min-width: 220px; 
  max-width: 280px; 
  scroll-snap-align: start; 
}

.category-smart-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center; 
  gap: 2rem;
}

.category-smart-col {
  flex: 1; 
  min-width: 220px; 
  max-width: 280px; 
}

.bg-gradient-dark-bottom {
  background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
}
.shadow-sm-top { box-shadow: 0 -4px 10px rgba(0,0,0,0.03); }
.tracking-widest { letter-spacing: 2px; }

.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.08); }
.group-hover-opacity-50 { transition: opacity 0.3s ease; }
.group:hover .group-hover-opacity-50 { opacity: 0.1; }

.hover-bg-light:hover { background-color: rgba(84, 119, 146, 0.05); }
html.dark .dark-hover-bg:hover { background-color: rgba(255, 255, 255, 0.05); }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.group:hover .group-hover-text-urban { color: var(--color-c-hover, #547792) !important; }

.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5); color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; }

.animation-fade-down { animation: fadeDown 0.3s ease forwards; }
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 0px; display: none; } 

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.mega-slide-enter-active, .mega-slide-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mega-slide-enter-from, .mega-slide-leave-to { transform: translate(-50%, -20px) scale(0.98); opacity: 0; }

.transition-color { transition: color 0.2s ease; }
.transition-transform { transition: transform 0.3s ease; }
</style>