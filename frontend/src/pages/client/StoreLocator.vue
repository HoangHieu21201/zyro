<!-- File: frontend/src/pages/client/StoreLocator.vue -->
<template>
  <div class="store-locator-wrapper pb-5 mb-5">
    
    <!-- ĐÃ FIX: Tăng khoảng cách đẩy nội dung xuống dưới Header -->
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-4">
        
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">Hệ thống cửa hàng</li>
          </ol>
        </nav>

        <div class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
          <div>
            <h2 class="fw-bold text-dark dark:text-white m-0 text-uppercase tracking-widest" style="letter-spacing: 2px;">Hệ Thống Cửa Hàng ZYRO</h2>
            <p class="text-muted mt-2 mb-0">Khám phá không gian mua sắm hiện đại tại {{ stores.length }} chi nhánh trên toàn quốc.</p>
          </div>
        </div>

        <div class="row g-4 h-100">
          <!-- ĐÃ FIX: CỘT TRÁI TĂNG LÊN COL-5 ĐỂ RỘNG RÃI HƠN -->
          <div class="col-lg-5 col-xl-5 d-flex flex-column">
            <!-- Box Filter -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-3 animation-fade-in">
              <div class="mb-3">
                <div class="input-group shadow-sm-hover border border-secondary-subtle dark:border-gray-600 rounded-pill overflow-hidden bg-light dark:bg-[#212529]">
                  <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                  <input type="text" class="form-control border-0 bg-transparent shadow-none dark:text-white" v-model="searchQuery" placeholder="Tìm theo tên, địa chỉ...">
                </div>
              </div>
              <div>
                <select class="form-select form-select-sm border-secondary-subtle dark:border-gray-600 rounded-pill px-3 shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white fw-medium py-2" v-model="selectedCity">
                  <option value="">Tất cả Tỉnh/Thành phố</option>
                  <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                </select>
              </div>
            </div>

            <!-- Danh sách Cửa hàng -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] overflow-hidden flex-grow-1 animation-fade-in">
              <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-4">
                <h6 class="fw-bold m-0 text-urban d-flex align-items-center"><i class="bi bi-shop me-2"></i> Danh sách chi nhánh ({{ filteredStores.length }})</h6>
              </div>
              <!-- ĐÃ FIX: Giảm chiều cao list để cân đối với bản đồ mới -->
              <div class="card-body p-0 custom-scrollbar-y" style="height: 500px; overflow-y: auto;">
                <div v-if="filteredStores.length === 0" class="text-center py-5 text-muted fst-italic">
                  <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-25"></i>
                  Không tìm thấy cửa hàng nào.
                </div>
                <div v-else class="list-group list-group-flush">
                  <button v-for="store in filteredStores" :key="store.id" 
                          class="list-group-item list-group-item-action p-4 border-bottom dark:border-gray-700 bg-transparent transition-all store-item"
                          :class="{'active-store': selectedStore && selectedStore.id === store.id}"
                          @click="focusStore(store)">
                    <h6 class="fw-bold mb-2 text-dark dark:text-white">{{ store.name }}</h6>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ store.address }}</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone-fill text-urban me-2"></i>{{ store.phone }}</p>
                    <p class="text-muted small mb-3"><i class="bi bi-clock-fill text-urban me-2"></i>{{ store.hours }}</p>
                    
                    <div class="d-flex gap-2">
                      <a :href="getDirectionUrl(store)" target="_blank" class="btn btn-outline-urban btn-sm rounded-pill fw-semibold flex-grow-1" @click.stop>
                        <i class="bi bi-cursor-fill me-1"></i> Chỉ đường Google Maps
                      </a>
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- ĐÃ FIX: CỘT PHẢI GIẢM XUỐNG COL-7 ĐỂ BẢN ĐỒ KHÔNG QUÁ TO -->
          <div class="col-lg-7 col-xl-7 mb-4 mb-lg-0">
            <!-- ĐÃ FIX: Giảm min-height để bản đồ vừa vặn trong màn hình -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative h-100 animation-fade-in" style="min-height: 550px; height: 100%;">
              <div id="store-map" class="w-100 h-100 position-absolute top-0 start-0 z-1 bg-light"></div>
              
              <!-- Loading Overlay -->
              <div v-if="isMapLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-75 dark:bg-[#1a2533] dark:bg-opacity-75 z-index-2">
                  <div class="spinner-border text-urban mb-2" style="width: 3rem; height: 3rem;"></div>
                  <div class="fw-bold text-urban">Đang kết nối vệ tinh...</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const searchQuery = ref('');
const selectedCity = ref('');
const selectedStore = ref(null);
const isMapLoading = ref(true);

let map = null;
let leafletMarkers = [];

// MOCK DATA CÁC CỬA HÀNG CHUẨN MỰC
const stores = ref([
  { id: 1, name: 'ZYRO Flagship Vincom Bà Triệu', address: '191 Bà Triệu, Lê Đại Hành, Hai Bà Trưng, Hà Nội', phone: '024 1234 5678', hours: '09:00 - 22:00', lat: 21.0118, lng: 105.8497, city: 'Hà Nội' },
  { id: 2, name: 'ZYRO Thái Hà', address: '123 Thái Hà, Đống Đa, Hà Nội', phone: '024 8765 4321', hours: '09:00 - 22:00', lat: 21.0135, lng: 105.8213, city: 'Hà Nội' },
  { id: 3, name: 'ZYRO Quận 1', address: '45 Lê Lợi, Bến Nghé, Quận 1, TP. Hồ Chí Minh', phone: '028 1111 2222', hours: '09:30 - 22:30', lat: 10.7743, lng: 106.7018, city: 'TP. Hồ Chí Minh' },
  { id: 4, name: 'ZYRO Gò Vấp', address: 'Vincom Plaza Gò Vấp, 12 Phan Văn Trị, TP. Hồ Chí Minh', phone: '028 3333 4444', hours: '09:30 - 22:30', lat: 10.8265, lng: 106.6888, city: 'TP. Hồ Chí Minh' },
  { id: 5, name: 'ZYRO Đà Nẵng', address: 'Vincom Center Đà Nẵng, 910A Ngô Quyền, Sơn Trà, Đà Nẵng', phone: '0236 555 666', hours: '09:00 - 22:00', lat: 16.0668, lng: 108.2312, city: 'Đà Nẵng' },
  { id: 6, name: 'ZYRO Cần Thơ', address: 'Vincom Xuân Khánh, 209 30 Tháng 4, Xuân Khánh, Ninh Kiều, Cần Thơ', phone: '0292 777 888', hours: '09:00 - 22:00', lat: 10.0267, lng: 105.7725, city: 'Cần Thơ' }
]);

const cities = computed(() => {
  const citySet = new Set(stores.value.map(s => s.city));
  return Array.from(citySet).sort();
});

const filteredStores = computed(() => {
  return stores.value.filter(s => {
    const matchCity = selectedCity.value === '' || s.city === selectedCity.value;
    const matchSearch = searchQuery.value === '' || 
                        s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                        s.address.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchCity && matchSearch;
  });
});

const getDirectionUrl = (store) => {
  return `https://www.google.com/maps/dir/?api=1&destination=${store.lat},${store.lng}`;
};

// ==========================================
// LOGIC BẢN ĐỒ (Leaflet)
// ==========================================
const loadLeafletScript = () => {
  return new Promise((resolve) => {
    if (window.L) return resolve();
    const link = document.createElement('link');
    link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => resolve();
    document.head.appendChild(script);
  });
};

const initMap = async () => {
  await loadLeafletScript();
  isMapLoading.value = false;
  
  if (!map) {
    map = window.L.map('store-map').setView([16.0471, 108.2068], 6); // Trọng tâm VN
    window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);
  }
  
  updateMapMarkers();
};

const updateMapMarkers = () => {
  if (!map) return;
  
  // Xóa markers cũ
  leafletMarkers.forEach(m => map.removeLayer(m));
  leafletMarkers = [];
  
  // Tùy chỉnh Icon Marker ZYRO
  const iconHtml = `<div class="bg-dark text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center border border-2 border-white hover-transform" style="width:35px; height:35px; background-color: var(--color-c-dark) !important;"><i class="bi bi-shop"></i></div>`;
  const customIcon = window.L.divIcon({ html: iconHtml, className: '', iconSize: [35, 35], iconAnchor: [17, 35], popupAnchor: [0, -35] });

  const bounds = [];

  filteredStores.value.forEach(store => {
    const marker = window.L.marker([store.lat, store.lng], { icon: customIcon }).addTo(map);
    
    // Popup thông tin cửa hàng mượt mà
    const popupContent = `
      <div class="text-start" style="min-width: 220px; font-family: inherit;">
        <h6 class="fw-bold mb-2" style="color: var(--color-c-dark);">${store.name}</h6>
        <p class="small text-muted mb-2"><i class="bi bi-geo-alt-fill text-urban me-1"></i> ${store.address}</p>
        <p class="small mb-2 text-dark"><i class="bi bi-telephone-fill text-urban me-1"></i> ${store.phone}</p>
        <a href="${getDirectionUrl(store)}" target="_blank" class="btn btn-sm text-white w-100 rounded-pill fw-semibold mt-2" style="background-color: var(--color-c-dark); border: none;">Chỉ đường</a>
      </div>
    `;
    
    marker.bindPopup(popupContent);
    marker.storeId = store.id;
    leafletMarkers.push(marker);
    bounds.push([store.lat, store.lng]);
    
    marker.on('click', () => {
      selectedStore.value = store;
      map.setView([store.lat, store.lng], 16);
    });
  });

  // Tự động thu phóng (fit bounds) khi search hoặc load
  if (bounds.length > 0 && !selectedStore.value) {
    if (bounds.length === 1) {
      map.setView(bounds[0], 15);
    } else {
      map.fitBounds(bounds, { padding: [50, 50] });
    }
  }
};

const focusStore = (store) => {
  selectedStore.value = store;
  if (map) {
    map.setView([store.lat, store.lng], 16);
    const marker = leafletMarkers.find(m => m.storeId === store.id);
    if (marker) marker.openPopup();
  }
};

watch([filteredStores], () => {
  selectedStore.value = null; 
  updateMapMarkers();
});

onMounted(() => {
  window.scrollTo(0, 0);
  initMap();
});
</script>

<style scoped>
.store-locator-wrapper { width: 100%; }

/* CHUẨN ZYRO CONTAINER */
.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

/* Tùy chỉnh thanh cuộn */
.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* Item Cửa hàng */
.store-item { cursor: pointer; }
.store-item:hover { background-color: rgba(84, 119, 146, 0.05) !important; }
html.dark .store-item:hover { background-color: rgba(255, 255, 255, 0.05) !important; }

/* Trạng thái Store đang chọn */
.active-store {
  background-color: rgba(84, 119, 146, 0.1) !important;
  border-left: 4px solid var(--color-c-hover, #547792) !important;
}
html.dark .active-store { background-color: rgba(255, 255, 255, 0.05) !important; }

/* Override Leaflet Map Dark Mode & Popup */
html.dark #store-map { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
:deep(.leaflet-popup-content) {
  margin: 16px;
}
html.dark :deep(.leaflet-popup-content-wrapper) {
  background-color: #1a2533;
  color: #fff;
}
html.dark :deep(.leaflet-popup-tip) {
  background-color: #1a2533;
}

.tracking-widest { letter-spacing: 2px; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.z-index-2 { z-index: 2; }
</style>