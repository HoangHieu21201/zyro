<template>
  <div class="user-address-wrapper pb-5 mb-5" >
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Sổ địa chỉ</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- CỘT TRÁI: SIDEBAR QUẢN LÝ -->
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <!-- CỘT PHẢI: NỘI DUNG ĐỊA CHỈ -->
          <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in pl-4 pb-5 px-3">
              <div class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h4 class="fw-bold text-c-dark dark:text-white mb-1">Sổ Địa Chỉ Của Tôi</h4>
                  <p class="text-muted small mb-0">Quản lý các địa chỉ giao nhận hàng hóa.</p>
                </div>
                <button class="btn btn-urban text-white rounded-pill px-4 py-2 fw-bold shadow-sm" @click="openModal('add')">
                  <i class="bi bi-plus-lg me-1"></i> Thêm Địa Chỉ Mới
                </button>
              </div>

              <!-- SKELETON KHI TẢI -->
              <div v-if="isLoading" class="d-flex flex-column gap-3">
                 <div v-for="i in 2" :key="i" class="p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 bg-white dark:bg-[#212529]">
                    <div class="shimmer rounded mb-2" style="width: 40%; height: 20px;"></div>
                    <div class="shimmer rounded mb-2" style="width: 80%; height: 16px;"></div>
                    <div class="shimmer rounded mb-4" style="width: 60%; height: 16px;"></div>
                    <div class="shimmer rounded" style="width: 100%; height: 1px;"></div>
                 </div>
              </div>

              <!-- DANH SÁCH ĐỊA CHỈ TRỐNG -->
              <div v-else-if="addresses.length === 0" class="text-center py-5 my-3 bg-light dark:bg-[#212529] rounded-4 border border-dashed dark:border-gray-700">
                <i class="bi bi-geo-alt fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h6 class="fw-bold text-dark dark:text-white mb-2">Bạn chưa có địa chỉ nào</h6>
                <p class="text-muted small mb-0">Thêm ngay địa chỉ để tiến hành thanh toán nhanh chóng hơn.</p>
              </div>

              <!-- DANH SÁCH ĐỊA CHỈ TỪ BACKEND -->
              <div v-else class="d-flex flex-column gap-3">
                <div v-for="addr in addresses" :key="addr.id" 
                     class="p-4 rounded-4 shadow-sm border transition-all position-relative" 
                     :class="addr.is_default ? 'active-address-card' : 'bg-white dark:bg-[#212529] border-light-subtle dark:border-gray-700'">
                  
                  <span v-if="addr.is_default" class="position-absolute top-0 end-0 m-3 badge bg-urban text-white shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> Mặc định</span>

                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="pe-5">
                      <h6 class="fw-bold text-dark dark:text-white mb-1 d-flex align-items-center flex-wrap gap-2">
                        {{ addr.customer_name }} <span class="text-muted fw-normal mx-1">|</span> <span class="text-muted font-monospace fw-normal">{{ addr.customer_phone }}</span>
                      </h6>
                      <div class="text-dark dark:text-gray-300 small lh-lg mt-2">
                        {{ addr.shipping_address }}<br>
                        {{ addr.ward }}, {{ addr.district }}, {{ addr.city }}
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 pt-3 border-top dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <button v-if="!addr.is_default" class="btn btn-sm btn-outline-secondary dark:text-gray-300 dark:border-gray-600 rounded-pill px-3 fw-semibold hover-urban-btn" @click="setDefault(addr.id)">
                      Thiết lập mặc định
                    </button>
                    <span v-else></span>

                    <div class="d-flex gap-3">
                      <button class="btn btn-link text-urban p-0 text-decoration-none fw-semibold small" @click="openModal('edit', addr)">Cập nhật</button>
                      <div class="vr text-secondary opacity-25" v-if="!addr.is_default"></div>
                      <button v-if="!addr.is_default" class="btn btn-link text-danger p-0 text-decoration-none fw-semibold small" @click="deleteAddress(addr.id)">Xóa</button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL THÊM / SỬA ĐỊA CHỈ -->
    <div class="modal fade" id="addressModal" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533]">
          <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-4">
            <h5 class="fw-bold text-dark dark:text-white mb-0">
              <i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ modalMode === 'add' ? 'Thêm Địa Chỉ Mới' : 'Cập Nhật Địa Chỉ' }}
            </h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4">
            <form @submit.prevent="saveAddress" autocomplete="off">
              <div class="row g-3">
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Họ và tên <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.customer_name" required placeholder="Nhập tên người nhận">
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control custom-input shadow-sm-hover" v-model="form.customer_phone" required placeholder="Nhập số điện thoại">
                </div>
                
                <!-- TỈNH / THÀNH PHỐ (CUSTOM DROPDOWN CÓ TÌM KIẾM) -->
                <div class="col-md-4 mb-2 position-relative">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input" 
                         v-model="searchProvince" 
                         @focus="showProvinceDrop = true"
                         @blur="handleBlur('province')"
                         placeholder="Tìm Tỉnh/Thành..." required>
                  <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>
                  
                  <ul v-if="showProvinceDrop" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                    <li v-for="c in filteredProvinces" :key="c.code">
                      <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectProvince(c)">{{ c.name }}</a>
                    </li>
                    <li v-if="filteredProvinces.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                  </ul>
                </div>

                <!-- QUẬN / HUYỆN (CUSTOM DROPDOWN CÓ TÌM KIẾM) -->
                <div class="col-md-4 mb-2 position-relative">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Quận/Huyện <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input" 
                         v-model="searchDistrict" 
                         @focus="showDistrictDrop = true"
                         @blur="handleBlur('district')"
                         placeholder="Tìm Quận/Huyện..." required :disabled="!form.city">
                  <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>

                  <ul v-if="showDistrictDrop && form.city" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                    <li v-for="d in filteredDistricts" :key="d.code">
                      <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectDistrict(d)">{{ d.name }}</a>
                    </li>
                    <li v-if="filteredDistricts.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                  </ul>
                </div>

                <!-- PHƯỜNG / XÃ (CUSTOM DROPDOWN CÓ TÌM KIẾM) -->
                <div class="col-md-4 mb-2 position-relative">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Phường/Xã <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover dropdown-search-input" 
                         v-model="searchWard" 
                         @focus="showWardDrop = true"
                         @blur="handleBlur('ward')"
                         placeholder="Tìm Phường/Xã..." required :disabled="!form.district">
                  <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.3rem; pointer-events: none; font-size: 0.8rem;"></i>

                  <ul v-if="showWardDrop && form.district" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                    <li v-for="w in filteredWards" :key="w.code">
                      <a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectWard(w)">{{ w.name }}</a>
                    </li>
                    <li v-if="filteredWards.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                  </ul>
                </div>

                <div class="col-md-12 mb-2 mt-3">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.shipping_address" required placeholder="Số nhà, ngõ, tên đường...">
                </div>
                
                <div class="col-12 mt-3" v-if="!form.is_default">
                  <div class="form-check form-switch p-3 bg-urban-soft-box rounded-3 d-flex align-items-center gap-3 border border-urban-soft">
                    <input class="form-check-input fs-4 m-0 cursor-pointer shadow-none" type="checkbox" id="setDefault" v-model="form.set_as_default">
                    <label class="form-check-label fw-bold text-urban m-0 cursor-pointer" for="setDefault">Đặt làm địa chỉ mặc định</label>
                  </div>
                </div>
              </div>

              <div class="text-end mt-4 pt-3 border-top dark:border-gray-700">
                <button type="button" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 px-4 fw-bold me-2 shadow-sm border" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-urban px-5 fw-bold text-white shadow-sm" :disabled="isSaving">
                  <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span> LƯU ĐỊA CHỈ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import UserSidebar from '@/components/client/UserSidebar.vue';

const addresses = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);

const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const modalMode = ref('add');
let modalInstance = null;

const form = ref({ id: null, customer_name: '', customer_phone: '', city: '', district: '', ward: '', shipping_address: '', is_default: false, set_as_default: false });

// =====================================
// LOGIC SEARCHABLE DROPDOWN
// =====================================
const searchProvince = ref('');
const searchDistrict = ref('');
const searchWard = ref('');

const showProvinceDrop = ref(false);
const showDistrictDrop = ref(false);
const showWardDrop = ref(false);

// Hàm chuẩn hóa tiếng Việt (Xóa dấu) để tìm kiếm linh hoạt (vd: gõ "da" ra "Đà Nẵng")
const removeAccents = (str) => {
  if (!str) return '';
  return str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
};

const filteredProvinces = computed(() => {
  if (!searchProvince.value) return provinces.value;
  const q = removeAccents(searchProvince.value);
  return provinces.value.filter(p => removeAccents(p.name).includes(q));
});

const filteredDistricts = computed(() => {
  if (!searchDistrict.value) return districts.value;
  const q = removeAccents(searchDistrict.value);
  return districts.value.filter(d => removeAccents(d.name).includes(q));
});

const filteredWards = computed(() => {
  if (!searchWard.value) return wards.value;
  const q = removeAccents(searchWard.value);
  return wards.value.filter(w => removeAccents(w.name).includes(q));
});

const selectProvince = (p) => {
  form.value.city = p.name;
  searchProvince.value = p.name;
  showProvinceDrop.value = false;
  loadDistricts(false); 
};

const selectDistrict = (d) => {
  form.value.district = d.name;
  searchDistrict.value = d.name;
  showDistrictDrop.value = false;
  loadWards(false); 
};

const selectWard = (w) => {
  form.value.ward = w.name;
  searchWard.value = w.name;
  showWardDrop.value = false;
};

const handleBlur = (type) => {
  showProvinceDrop.value = false;
  showDistrictDrop.value = false;
  showWardDrop.value = false;
  
  if (type === 'province' && searchProvince.value !== form.value.city) {
     searchProvince.value = form.value.city; 
  } else if (type === 'district' && searchDistrict.value !== form.value.district) {
     searchDistrict.value = form.value.district;
  } else if (type === 'ward' && searchWard.value !== form.value.ward) {
     searchWard.value = form.value.ward;
  }
};

// =====================================
// API LẤY DANH SÁCH ĐỊA CHỈ (BACKEND)
// =====================================
const fetchAddresses = async () => {
  try {
    // ĐÃ SỬA API ENDPOINT (thêm /client/user/)
    const res = await api.get('/client/user/addresses');
    if (res.data.success) {
      addresses.value = res.data.data;
    }
  } catch (error) {
    console.error('Lỗi lấy Sổ địa chỉ:', error);
  } finally {
    isLoading.value = false;
  }
};

// =====================================
// API LẤY DANH SÁCH TỈNH THÀNH
// =====================================
const fetchProvinces = async () => {
  try {
    const res = await axios.get('https://provinces.open-api.vn/api/p/');
    provinces.value = res.data;
  } catch (err) {
    console.error("Lỗi lấy Tỉnh thành:", err);
  }
};

const loadDistricts = async (isEdit = false) => {
  if (!isEdit) {
     form.value.district = ''; searchDistrict.value = '';
     form.value.ward = ''; searchWard.value = '';
  }
  districts.value = []; wards.value = [];
  const p = provinces.value.find(i => i.name === form.value.city);
  if (p) {
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`);
      districts.value = res.data.districts;
    } catch (err) {}
  }
};

const loadWards = async (isEdit = false) => {
  if (!isEdit) {
     form.value.ward = ''; searchWard.value = '';
  }
  wards.value = [];
  const d = districts.value.find(i => i.name === form.value.district);
  if (d) {
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`);
      wards.value = res.data.wards;
    } catch (err) {}
  }
};

// =====================================
// ACTIONS
// =====================================
const openModal = async (mode, addr = null) => {
  modalMode.value = mode;
  if (mode === 'add') {
    form.value = { id: null, customer_name: '', customer_phone: '', city: '', district: '', ward: '', shipping_address: '', is_default: false, set_as_default: false };
    searchProvince.value = ''; searchDistrict.value = ''; searchWard.value = '';
    districts.value = []; wards.value = [];
  } else {
    // Đổ dữ liệu có sẵn vào form sửa
    form.value = { ...addr, set_as_default: false };
    searchProvince.value = addr.city;
    searchDistrict.value = addr.district;
    searchWard.value = addr.ward;
    
    await loadDistricts(true);
    await loadWards(true);
  }
  if (!modalInstance) modalInstance = new window.bootstrap.Modal(document.getElementById('addressModal'));
  modalInstance.show();
};

const saveAddress = async () => {
  if (!form.value.city || !form.value.district || !form.value.ward) {
    ZyroSwal.toastError('Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện, Phường/Xã');
    return;
  }

  isSaving.value = true;
  try {
    const payload = {
      customer_name: form.value.customer_name,
      customer_phone: form.value.customer_phone,
      city: form.value.city,
      district: form.value.district,
      ward: form.value.ward,
      shipping_address: form.value.shipping_address,
      is_default: form.value.set_as_default
    };

    let res;
    // ĐÃ SỬA API ENDPOINT
    if (modalMode.value === 'add') {
      res = await api.post('/client/user/addresses', payload);
    } else {
      res = await api.put(`/client/user/addresses/${form.value.id}`, payload);
    }

    if (res.data.success) {
      ZyroSwal.toastSuccess(res.data.message);
      modalInstance.hide();
      fetchAddresses(); 
    }
  } catch (error) {
    ZyroSwal.toastError(error.response?.data?.message || 'Có lỗi xảy ra khi lưu địa chỉ');
  } finally {
    isSaving.value = false;
  }
};

const deleteAddress = (id) => {
  ZyroSwal.confirmDelete('địa chỉ này').then(async (result) => {
    if (result.isConfirmed) {
      try {
         // ĐÃ SỬA API ENDPOINT
         const res = await api.delete(`/client/user/addresses/${id}`);
         if (res.data.success) {
            ZyroSwal.toastSuccess(res.data.message);
            fetchAddresses();
         }
      } catch (error) {
         ZyroSwal.toastError('Xóa địa chỉ thất bại');
      }
    }
  });
};

const setDefault = async (id) => {
  try {
     // ĐÃ SỬA API ENDPOINT
     const res = await api.put(`/client/user/addresses/${id}/set-default`);
     if (res.data.success) {
        ZyroSwal.toastSuccess(res.data.message);
        fetchAddresses(); 
     }
  } catch (error) {
     ZyroSwal.toastError('Có lỗi xảy ra');
  }
};

onMounted(() => { 
  window.scrollTo(0, 0); 
  fetchAddresses();
  fetchProvinces();
});
</script>

<style scoped>
.user-address-wrapper { width: 100%; padding-top: 26px;}

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark, #213448) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }

/* CSS FIX: Classes riêng cho box làm mờ nền nhạt */
.bg-urban-soft-box { background-color: rgba(84, 119, 146, 0.08) !important; }
html.dark .bg-urban-soft-box { background-color: rgba(255, 255, 255, 0.05) !important; }
.border-urban-soft { border-color: rgba(84, 119, 146, 0.2) !important; }
html.dark .border-urban-soft { border-color: rgba(255, 255, 255, 0.1) !important; }

.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-1px); }

/* Lớp màu riêng cho thẻ địa chỉ mặc định */
.active-address-card { background-color: var(--color-c-effect); border-color: var(--color-c-hover) !important; }
html.dark .active-address-card { background-color: rgba(84, 119, 146, 0.15); border-color: var(--color-c-hover) !important; }

.hover-urban-btn:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }
.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5) !important; color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; }
.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.border-dashed { border-style: dashed !important; border-width: 2px !important; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }

/* INPUT CHUẨN MƯỢT */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  padding: 0.65rem 1rem; 
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important; 
}
html.dark .custom-input { background-color: #1a2533; border-color: #373b3e; color: white; }
.custom-input:focus, .custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  outline: none;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; 
}
html.dark .custom-input:focus { background-color: #212529; box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important; }

.dropdown-search-input {
  padding-right: 2.5rem !important; 
}

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* SCROLLBAR BÊN TRONG DROPDOWN LỌC TỈNH THÀNH */
.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

/* Skeleton */
.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }

.transition-all { transition: all 0.3s ease; }
.cursor-pointer { cursor: pointer; }
</style>