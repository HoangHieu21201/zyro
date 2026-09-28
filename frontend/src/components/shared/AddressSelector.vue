<template>
  <div class="address-selector-container position-relative">
    <div v-if="showLocationButton" class="d-flex justify-content-end mb-2">
      <button type="button" class="btn btn-sm px-3 fw-bold transition-all font-sans-vn" :class="locationButtonClass" @click="autoFillLocation" :disabled="isLocating">
        <span v-if="isLocating" class="spinner-border spinner-border-sm me-1"></span>
        <i v-else class="bi bi-geo-alt-fill me-1"></i> 
        <span v-if="!isLocating">Lấy vị trí</span>
        <span v-else>Đang định vị...</span>
      </button>
    </div>
    <div class="row g-2 g-md-3">
    <!-- TỈNH / THÀNH PHỐ -->
    <div class="col-md-4 mb-2 position-relative">
      <label class="form-label small" :class="labelClass">Tỉnh/Thành phố <span class="text-danger">*</span></label>
      <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input"
        v-model="searchProvince" @focus="showProvinceDrop = true" @blur="handleBlur('province')"
        placeholder="Tìm Tỉnh/Thành..." required>
      <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>
      <ul v-if="showProvinceDrop" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
        <li v-for="c in filteredProvinces" :key="c.id">
          <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300"
            @mousedown.prevent="selectProvince(c)">{{ c.full_name }}</a>
        </li>
        <li v-if="filteredProvinces.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
      </ul>
    </div>

    <!-- QUẬN / HUYỆN -->
    <div class="col-md-4 mb-2 position-relative">
      <label class="form-label small" :class="labelClass">Quận/Huyện <span class="text-danger">*</span></label>
      <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input"
        v-model="searchDistrict" @focus="showDistrictDrop = true" @blur="handleBlur('district')"
        placeholder="Tìm Quận/Huyện..." required :disabled="!selectedProvinceData">
      <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>
      <ul v-if="showDistrictDrop && selectedProvinceData" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
        <li v-for="d in filteredDistricts" :key="d.id">
          <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300"
            @mousedown.prevent="selectDistrict(d)">{{ d.full_name }}</a>
        </li>
        <li v-if="filteredDistricts.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
      </ul>
    </div>

    <!-- PHƯỜNG / XÃ -->
    <div class="col-md-4 mb-2 position-relative">
      <label class="form-label small" :class="labelClass">Phường/Xã <span class="text-danger">*</span></label>
      <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input"
        v-model="searchWard" @focus="showWardDrop = true" @blur="handleBlur('ward')"
        placeholder="Tìm Phường/Xã..." required :disabled="!selectedDistrictData">
      <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>
      <ul v-if="showWardDrop && selectedDistrictData" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
        <li v-for="w in filteredWards" :key="w.id">
          <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300"
            @mousedown.prevent="selectWard(w)">{{ w.full_name }}</a>
        </li>
        <li v-if="filteredWards.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
      </ul>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';


const props = defineProps({
  city: { type: String, default: '' },
  district: { type: String, default: '' },
  ward: { type: String, default: '' },
  labelClass: { type: String, default: 'fw-bold text-dark dark:text-gray-200' },
  showLocationButton: { type: Boolean, default: false },
  locationButtonClass: { type: String, default: 'btn-outline-primary' }
});

const emit = defineEmits(['update:city', 'update:district', 'update:ward', 'location-detail']);

const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const searchProvince = ref('');
const searchDistrict = ref('');
const searchWard = ref('');

const showProvinceDrop = ref(false);
const showDistrictDrop = ref(false);
const showWardDrop = ref(false);

const selectedProvinceData = ref(null);
const selectedDistrictData = ref(null);

const removeAccents = (str) => {
  if (!str) return '';
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
};

const filteredProvinces = computed(() => {
  if (!searchProvince.value) return provinces.value;
  const q = removeAccents(searchProvince.value);
  return provinces.value.filter(p => removeAccents(p.full_name).includes(q) || removeAccents(p.name).includes(q));
});

const filteredDistricts = computed(() => {
  if (!searchDistrict.value) return districts.value;
  const q = removeAccents(searchDistrict.value);
  return districts.value.filter(d => removeAccents(d.full_name).includes(q) || removeAccents(d.name).includes(q));
});

const filteredWards = computed(() => {
  if (!searchWard.value) return wards.value;
  const q = removeAccents(searchWard.value);
  return wards.value.filter(w => removeAccents(w.full_name).includes(q) || removeAccents(w.name).includes(q));
});

const handleBlur = (type) => {
  setTimeout(() => {
    if (type === 'province') showProvinceDrop.value = false;
    if (type === 'district') showDistrictDrop.value = false;
    if (type === 'ward') showWardDrop.value = false;
  }, 200);
};

const fetchProvinces = async () => {
  try {
    const res = await axios.get('https://esgoo.net/api-tinhthanh/1/0.htm');
    if (res.data.error === 0) {
      provinces.value = res.data.data;
      if (props.city) {
         syncCity(props.city);
      }
    }
  } catch (err) {
    console.error("Lỗi lấy Tỉnh thành:", err);
  }
};

const fetchDistricts = async (provinceId, silent = false) => {
  try {
    const res = await axios.get(`https://esgoo.net/api-tinhthanh/2/${provinceId}.htm`);
    if (res.data.error === 0) {
      districts.value = res.data.data;
      if (props.district && silent) {
         syncDistrict(props.district);
      }
    }
  } catch (err) {
    console.error("Lỗi lấy Quận huyện:", err);
  }
};

const fetchWards = async (districtId, silent = false) => {
  try {
    const res = await axios.get(`https://esgoo.net/api-tinhthanh/3/${districtId}.htm`);
    if (res.data.error === 0) {
      wards.value = res.data.data;
      if (props.ward && silent) {
         syncWard(props.ward);
      }
    }
  } catch (err) {
    console.error("Lỗi lấy Phường xã:", err);
  }
};

const selectProvince = (p) => {
  selectedProvinceData.value = p;
  searchProvince.value = p.full_name;
  emit('update:city', p.full_name);
  
  searchDistrict.value = '';
  searchWard.value = '';
  selectedDistrictData.value = null;
  districts.value = [];
  wards.value = [];
  emit('update:district', '');
  emit('update:ward', '');
  
  showProvinceDrop.value = false;
  fetchDistricts(p.id);
};

const selectDistrict = (d) => {
  selectedDistrictData.value = d;
  searchDistrict.value = d.full_name;
  emit('update:district', d.full_name);
  
  searchWard.value = '';
  wards.value = [];
  emit('update:ward', '');
  
  showDistrictDrop.value = false;
  fetchWards(d.id);
};

const selectWard = (w) => {
  searchWard.value = w.full_name;
  emit('update:ward', w.full_name);
  showWardDrop.value = false;
};

const syncCity = (cityName) => {
   if (!cityName) return;
   const cleanName = cityName.replace(/Thành phố|Tỉnh/gi, '').trim();
   const p = provinces.value.find(x => x.full_name === cityName || removeAccents(x.full_name).includes(removeAccents(cleanName)));
   if (p) {
       selectedProvinceData.value = p;
       searchProvince.value = p.full_name;
       fetchDistricts(p.id, true);
   } else {
       searchProvince.value = cityName;
   }
};

const syncDistrict = (distName) => {
   if (!distName) return;
   const cleanName = distName.replace(/Quận|Huyện|Thị xã|Thành phố/gi, '').trim();
   const d = districts.value.find(x => x.full_name === distName || removeAccents(x.full_name).includes(removeAccents(cleanName)));
   if (d) {
       selectedDistrictData.value = d;
       searchDistrict.value = d.full_name;
       fetchWards(d.id, true);
   } else {
       searchDistrict.value = distName;
   }
};

const syncWard = (wardName) => {
   if (!wardName) return;
   const cleanName = wardName.replace(/Phường|Xã|Thị trấn/gi, '').trim();
   const w = wards.value.find(x => x.full_name === wardName || removeAccents(x.full_name).includes(removeAccents(cleanName)));
   if (w) {
       searchWard.value = w.full_name;
   } else {
       searchWard.value = wardName;
   }
};

watch(() => props.city, (newVal) => {
   if (newVal !== searchProvince.value) syncCity(newVal);
});
watch(() => props.district, (newVal) => {
   if (newVal !== searchDistrict.value) syncDistrict(newVal);
});
watch(() => props.ward, (newVal) => {
   if (newVal !== searchWard.value) syncWard(newVal);
});


const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});

const isLocating = ref(false);

const autoFillLocation = () => {
  if (!navigator.geolocation) {
    Toast.fire({ icon: 'error', title: "Trình duyệt không hỗ trợ định vị!" });
    return;
  }
  
  isLocating.value = true;


  navigator.geolocation.getCurrentPosition(
    async (position) => {
      try {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&accept-language=vi`);
        const data = await response.json();
        
        if (data && data.address) {
           const addr = data.address;
           
           let specific = [];
           if (addr.house_number) specific.push(addr.house_number);
           if (addr.road) specific.push(addr.road);
           
           emit('location-detail', specific.join(", "));
           
           const apiProvince = addr.city || addr.state || addr.province || "";
           if (apiProvince) emit('update:city', apiProvince);
           
           const apiDistrict = addr.county || addr.district || addr.town || "";
           if (apiDistrict) emit('update:district', apiDistrict);
           
           const apiWard = addr.quarter || addr.neighbourhood || addr.village || addr.suburb || "";
           if (apiWard) emit('update:ward', apiWard);

           
           Toast.fire({ icon: 'success', title: 'Đã cập nhật vị trí!' });
        } else {
           Toast.fire({ icon: 'error', title: "Không thể xác định địa chỉ!" });
        }
      } catch (err) {
        console.error(err);
        Toast.fire({ icon: 'error', title: "Lỗi khi lấy dữ liệu định vị!" });
      } finally {
        isLocating.value = false;
      }
    },
    (error) => {
      isLocating.value = false;
      let msg = "Không thể lấy vị trí.";
      if (error.code === 1) msg = "Bạn đã từ chối quyền truy cập vị trí.";
      Toast.fire({ icon: 'error', title: msg });
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
};


onMounted(() => {
  fetchProvinces();
});
</script>

<style scoped>
.custom-scrollbar-y {
  overflow-y: auto;
}
.custom-scrollbar-y::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar-y::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.dropdown-search-input {
  padding-right: 2rem;
}
.hover-bg-effect:hover {
  background-color: var(--bs-light);
}
</style>
