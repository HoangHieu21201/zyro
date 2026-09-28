<template>
  <div class="user-address-wrapper">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/"
                class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile"
                class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
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
              <div
                class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h4 class="fw-bold text-c-dark dark:text-white mb-1">Sổ Địa Chỉ Của Tôi</h4>
                  <p class="text-muted small mb-0">Quản lý các địa chỉ giao nhận hàng hóa.</p>
                </div>
                <button class="btn btn-urban text-white rounded-pill px-4 py-2 fw-bold shadow-sm"
                  @click="openModal('add')">
                  <i class="bi bi-plus-lg me-1"></i> Thêm Địa Chỉ Mới
                </button>
              </div>

              <!-- SKELETON KHI TẢI -->
              <div v-if="isLoading" class="d-flex flex-column gap-3">
                <div v-for="i in 2" :key="i"
                  class="p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 bg-white dark:bg-[#212529]">
                  <div class="shimmer rounded mb-2" style="width: 40%; height: 20px;"></div>
                  <div class="shimmer rounded mb-2" style="width: 80%; height: 16px;"></div>
                  <div class="shimmer rounded mb-4" style="width: 60%; height: 16px;"></div>
                  <div class="shimmer rounded" style="width: 100%; height: 1px;"></div>
                </div>
              </div>

              <!-- DANH SÁCH ĐỊA CHỈ TRỐNG -->
              <div v-else-if="addresses.length === 0"
                class="text-center py-5 my-3 bg-light dark:bg-[#212529] rounded-4 border border-dashed dark:border-gray-700">
                <i class="bi bi-geo-alt fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h6 class="fw-bold text-dark dark:text-white mb-2">Bạn chưa có địa chỉ nào</h6>
                <p class="text-muted small mb-0">Thêm ngay địa chỉ để tiến hành thanh toán nhanh chóng hơn.</p>
              </div>

              <!-- DANH SÁCH ĐỊA CHỈ TỪ BACKEND -->
              <div v-else class="d-flex flex-column gap-3">
                <div v-for="addr in addresses" :key="addr.id"
                  class="p-4 rounded-4 shadow-sm border transition-all position-relative"
                  :class="addr.is_default ? 'active-address-card' : 'bg-white dark:bg-[#212529] border-light-subtle dark:border-gray-700'">

                  <span v-if="addr.is_default"
                    class="position-absolute top-0 end-0 m-3 badge bg-urban text-white shadow-sm"><i
                      class="bi bi-check-circle-fill me-1"></i> Mặc định</span>

                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="pe-5">
                      <h6 class="fw-bold text-dark dark:text-white mb-1 d-flex align-items-center flex-wrap gap-2">
                        {{ addr.customer_name }} <span class="text-muted fw-normal mx-1">|</span> <span
                          class="text-muted font-monospace fw-normal">{{ addr.customer_phone }}</span>
                      </h6>
                      <div class="text-dark dark:text-gray-300 small lh-lg mt-2">
                        {{ addr.shipping_address }}<br>
                        {{ addr.ward }}, {{ addr.district }}, {{ addr.city }}
                      </div>
                    </div>
                  </div>

                  <div
                    class="mt-3 pt-3 border-top dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <button v-if="!addr.is_default"
                      class="btn btn-sm btn-outline-secondary dark:text-gray-300 dark:border-gray-600 rounded-pill px-3 fw-semibold hover-urban-btn"
                      @click="setDefault(addr.id)">
                      Thiết lập mặc định
                    </button>
                    <span v-else></span>

                    <div class="d-flex gap-3">
                      <button class="btn btn-link text-urban p-0 text-decoration-none fw-semibold small"
                        @click="openModal('edit', addr)">Cập nhật</button>
                      <div class="vr text-secondary opacity-25" v-if="!addr.is_default"></div>
                      <button v-if="!addr.is_default"
                        class="btn btn-link text-danger p-0 text-decoration-none fw-semibold small"
                        @click="deleteAddress(addr.id)">Xóa</button>
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
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Họ và tên <span
                      class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.customer_name"
                    required placeholder="Nhập tên người nhận">
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Số điện thoại <span
                      class="text-danger">*</span></label>
                  <input type="tel" class="form-control custom-input shadow-sm-hover" v-model="form.customer_phone"
                    required placeholder="Nhập số điện thoại">
                </div>

                <div class="col-12 mb-2">
                  <AddressSelector 
                    v-model:city="form.city" 
                    v-model:district="form.district" 
                    v-model:ward="form.ward" 
                  />
                </div>

                <div class="col-md-12 mb-2 mt-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold text-dark dark:text-gray-200 small m-0">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 fw-bold font-sans-vn transition-all" @click="autoFillLocation" :disabled="isLocating">
                      <i class="bi" :class="isLocating ? 'bi-hourglass-split' : 'bi-geo-alt-fill'"></i> Lấy vị trí
                    </button>
                  </div>
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.shipping_address"
                    required placeholder="Số nhà, ngõ, tên đường...">
                </div>

                <div class="col-12 mt-3" v-if="!form.is_default">
                  <div
                    class="form-check form-switch p-3 bg-urban-soft-box rounded-3 d-flex align-items-center gap-3 border border-urban-soft">
                    <input class="form-check-input fs-4 m-0 cursor-pointer shadow-none" type="checkbox" id="setDefault"
                      v-model="form.set_as_default">
                    <label class="form-check-label fw-bold text-urban m-0 cursor-pointer" for="setDefault">Đặt làm địa
                      chỉ mặc định</label>
                  </div>
                </div>
              </div>

              <div class="text-end mt-4 pt-3 border-top dark:border-gray-700">
                <button type="button"
                  class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 px-4 fw-bold me-2 shadow-sm border"
                  data-bs-dismiss="modal">Hủy</button>
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
import AddressSelector from '@/components/shared/AddressSelector.vue';

const addresses = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);



const modalMode = ref('add');
let modalInstance = null;

const form = ref({ id: null, customer_name: '', customer_phone: '', city: '', district: '', ward: '', shipping_address: '', is_default: false, set_as_default: false });


// API LẤY DANH SÁCH ĐỊA CHỈ (BACKEND)
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


// ACTIONS
const openModal = async (mode, addr = null) => {
  modalMode.value = mode;
  if (mode === 'add') {
    form.value = { id: null, customer_name: '', customer_phone: '', city: '', district: '', ward: '', shipping_address: '', is_default: false, set_as_default: false };
  } else {
    // Đổ dữ liệu có sẵn vào form sửa
    form.value = { ...addr, set_as_default: false };
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


const isLocating = ref(false);

const autoFillLocation = () => {
  if (!navigator.geolocation) {
    ZyroSwal.toastError("Trình duyệt không hỗ trợ định vị!");
    return;
  }
  
  isLocating.value = true;
  ZyroSwal.showLoading("Đang lấy vị trí...");
  
  navigator.geolocation.getCurrentPosition(
    async (position) => {
      try {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        
        const response = await axios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&accept-language=vi`);
        const data = response.data;
        
        if (data && data.address) {
           const addr = data.address;
           
           let specific = [];
           if (addr.house_number) specific.push(addr.house_number);
           if (addr.road) specific.push(addr.road);
           if (addr.suburb && !addr.road) specific.push(addr.suburb);
           
           form.value.shipping_address = specific.join(", ");
           
           const apiProvince = addr.city || addr.state || addr.province || "";
           if (apiProvince) form.value.city = apiProvince;
           
           const apiDistrict = addr.county || addr.district || addr.town || "";
           if (apiDistrict) form.value.district = apiDistrict;
           
           const apiWard = addr.quarter || addr.neighbourhood || addr.village || addr.suburb || "";
           if (apiWard) form.value.ward = apiWard;

           ZyroSwal.close();
           ZyroSwal.toastSuccess("Đã điền vị trí hiện tại!");
        } else {
           ZyroSwal.close();
           ZyroSwal.toastError("Không thể xác định địa chỉ!");
        }
      } catch (err) {
        ZyroSwal.close();
        ZyroSwal.toastError("Lỗi kết nối dịch vụ định vị!");
      } finally {
        isLocating.value = false;
      }
    },
    (err) => {
      isLocating.value = false;
      ZyroSwal.close();
      if (err.code === 1) ZyroSwal.toastError("Vui lòng cấp quyền truy cập vị trí!");
      else ZyroSwal.toastError("Không thể lấy vị trí hiện tại!");
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchAddresses();
  
});
</script>

<style scoped>
.user-address-wrapper {
  width: 100%;
  padding-top: 26px;
}

.zyro-container {
  width: 100%;
  max-width: 1310px;
  margin: 0 auto;
  padding-left: 20px;
  padding-right: 20px;
}

@media (min-width: 1400px) {
  .zyro-container {
    padding-left: 0;
    padding-right: 0;
  }
}

.text-c-dark {
  color: var(--color-c-dark, #213448) !important;
}

html.dark .text-c-dark {
  color: #f8f9fa !important;
}

.text-urban {
  color: var(--color-c-hover, #547792) !important;
}

.bg-urban {
  background-color: var(--color-c-hover, #547792) !important;
}

/* CSS FIX: Classes riêng cho box làm mờ nền nhạt */
.bg-urban-soft-box {
  background-color: rgba(84, 119, 146, 0.08) !important;
}

html.dark .bg-urban-soft-box {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.border-urban-soft {
  border-color: rgba(84, 119, 146, 0.2) !important;
}

html.dark .border-urban-soft {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

.btn-urban {
  background-color: var(--color-c-hover, #547792);
  border: none;
  transition: 0.2s;
}

.btn-urban:hover {
  background-color: var(--color-c-dark, #213448);
  color: white;
  transform: translateY(-1px);
}

/* Lớp màu riêng cho thẻ địa chỉ mặc định */
.active-address-card {
  background-color: var(--color-c-effect);
  border-color: var(--color-c-hover) !important;
}

html.dark .active-address-card {
  background-color: rgba(84, 119, 146, 0.15);
  border-color: var(--color-c-hover) !important;
}

.hover-urban-btn:hover {
  background-color: var(--color-c-hover, #547792) !important;
  color: white !important;
  border-color: var(--color-c-hover, #547792) !important;
}

.hover-bg-effect:hover {
  background-color: var(--color-c-effect, #EBF1F5) !important;
  color: var(--color-c-hover, #547792) !important;
}

html.dark .hover-bg-effect:hover {
  background-color: #343a40 !important;
  color: #fff !important;
}

.hover-text-dark:hover {
  color: #000 !important;
}

html.dark .hover-text-dark:hover {
  color: #fff !important;
}

.border-dashed {
  border-style: dashed !important;
  border-width: 2px !important;
}

.shadow-sm-hover {
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.shadow-sm-hover:focus-within {
  box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important;
}

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

html.dark .custom-input {
  background-color: #1a2533;
  border-color: #373b3e;
  color: white;
}

.custom-input:focus,
.custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  outline: none;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important;
}

html.dark .custom-input:focus {
  background-color: #212529;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important;
}

.dropdown-search-input {
  padding-right: 2.5rem !important;
}

.animation-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* SCROLLBAR BÊN TRONG DROPDOWN LỌC TỈNH THÀNH */
.custom-scrollbar-y::-webkit-scrollbar {
  width: 5px;
}

.custom-scrollbar-y::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar-y::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 10px;
}

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

@keyframes placeholderShimmer {
  0% {
    background-position: -400px 0;
  }

  100% {
    background-position: 400px 0;
  }
}

.transition-all {
  transition: all 0.3s ease;
}

.cursor-pointer {
  cursor: pointer;
}
</style>