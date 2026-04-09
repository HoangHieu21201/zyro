<!-- File: frontend/src/pages/client/user/Address.vue -->
<template>
  <div class="user-address-wrapper pb-5 mb-5">
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
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in p-4 p-md-5">
              <div class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h4 class="fw-bold text-c-dark dark:text-white mb-1">Sổ Địa Chỉ Của Tôi</h4>
                  <p class="text-muted small mb-0">Quản lý các địa chỉ giao nhận hàng hóa.</p>
                </div>
                <button class="btn btn-urban text-white rounded-pill px-4 py-2 fw-bold shadow-sm" @click="openModal('add')">
                  <i class="bi bi-plus-lg me-1"></i> Thêm Địa Chỉ Mới
                </button>
              </div>

              <!-- Danh sách địa chỉ -->
              <div v-if="addresses.length === 0" class="text-center py-5 my-3 bg-light dark:bg-[#212529] rounded-4 border border-dashed dark:border-gray-700">
                <i class="bi bi-geo-alt fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h6 class="fw-bold text-dark dark:text-white mb-2">Bạn chưa có địa chỉ nào</h6>
                <p class="text-muted small mb-0">Thêm ngay địa chỉ để tiến hành thanh toán nhanh chóng hơn.</p>
              </div>

              <div v-else class="d-flex flex-column gap-3">
                <div v-for="addr in addresses" :key="addr.id" 
                     class="p-4 rounded-4 shadow-sm border transition-all position-relative" 
                     :class="addr.is_default ? 'active-address-card' : 'bg-white dark:bg-[#212529] border-light-subtle dark:border-gray-700'">
                  
                  <span v-if="addr.is_default" class="position-absolute top-0 end-0 m-3 badge bg-urban text-white shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> Mặc định</span>

                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h6 class="fw-bold text-dark dark:text-white mb-1 d-flex align-items-center flex-wrap gap-2">
                        {{ addr.name }} <span class="text-muted fw-normal mx-1">|</span> <span class="text-muted font-monospace fw-normal">{{ addr.phone }}</span>
                      </h6>
                      <div class="text-dark dark:text-gray-300 small lh-lg mt-2">
                        {{ addr.detail }}<br>
                        {{ addr.ward }}, {{ addr.district }}, {{ addr.city }}
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 pt-3 border-top dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <button v-if="!addr.is_default" class="btn btn-sm btn-outline-secondary dark:text-gray-300 dark:border-gray-600 rounded-pill px-3 fw-semibold hover-urban-btn" @click="setDefault(addr.id)">
                      Thiết lập mặc định
                    </button>
                    <span v-else></span> <!-- Placeholder để căn phải -->

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
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.name" required placeholder="Nhập tên người nhận">
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control custom-input shadow-sm-hover" v-model="form.phone" required placeholder="Nhập số điện thoại">
                </div>
                
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                  <select class="form-select custom-input shadow-sm-hover fw-semibold" v-model="form.city" @change="loadDistricts" required>
                    <option value="" disabled>-- Chọn --</option>
                    <option v-for="c in mockCities" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Quận/Huyện <span class="text-danger">*</span></label>
                  <select class="form-select custom-input shadow-sm-hover fw-semibold" v-model="form.district" @change="loadWards" required :disabled="!form.city">
                    <option value="" disabled>-- Chọn --</option>
                    <option v-for="d in mockDistricts" :key="d" :value="d">{{ d }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Phường/Xã <span class="text-danger">*</span></label>
                  <select class="form-select custom-input shadow-sm-hover fw-semibold" v-model="form.ward" required :disabled="!form.district">
                    <option value="" disabled>-- Chọn --</option>
                    <option v-for="w in mockWards" :key="w" :value="w">{{ w }}</option>
                  </select>
                </div>

                <div class="col-md-12 mb-2">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                  <input type="text" class="form-control custom-input shadow-sm-hover" v-model="form.detail" required placeholder="Số nhà, ngõ, tên đường...">
                </div>
                
                <!-- ĐÃ FIX: Khối đặt làm mặc định loại bỏ hoàn toàn lỗi màu đặc xịt -->
                <div class="col-12 mt-3" v-if="!form.is_default">
                  <div class="form-check form-switch p-3 bg-urban-soft-box rounded-3 d-flex align-items-center gap-3 border border-urban-soft">
                    <input class="form-check-input fs-4 m-0 cursor-pointer" type="checkbox" id="setDefault" v-model="form.set_as_default">
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
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import UserSidebar from '@/components/client/UserSidebar.vue';

// Mock Data
const addresses = ref([
  { id: 1, name: 'Alex Nguyễn', phone: '0987654321', city: 'Hà Nội', district: 'Quận Cầu Giấy', ward: 'Phường Dịch Vọng', detail: 'Số 12 ngõ 34 phố ABC', is_default: true },
  { id: 2, name: 'Alex Công Ty', phone: '0912345678', city: 'Hà Nội', district: 'Quận Thanh Xuân', ward: 'Phường Trung Hòa', detail: 'Tòa nhà văn phòng XYZ, số 56 đường DEF', is_default: false }
]);

const mockCities = ['Hà Nội', 'TP. Hồ Chí Minh'];
const mockDistricts = ref([]);
const mockWards = ref([]);

const isSaving = ref(false);
const modalMode = ref('add');
let modalInstance = null;

const form = ref({ id: null, name: '', phone: '', city: '', district: '', ward: '', detail: '', is_default: false, set_as_default: false });

const loadDistricts = () => { mockDistricts.value = ['Quận A', 'Quận B']; form.value.district = ''; form.value.ward = ''; };
const loadWards = () => { mockWards.value = ['Phường X', 'Phường Y']; form.value.ward = ''; };

const openModal = (mode, addr = null) => {
  modalMode.value = mode;
  if (mode === 'add') {
    form.value = { id: null, name: '', phone: '', city: '', district: '', ward: '', detail: '', is_default: false, set_as_default: false };
    mockDistricts.value = []; mockWards.value = [];
  } else {
    form.value = { ...addr, set_as_default: false };
    mockDistricts.value = [addr.district]; mockWards.value = [addr.ward]; // Fake load
  }
  if (!modalInstance) modalInstance = new window.bootstrap.Modal(document.getElementById('addressModal'));
  modalInstance.show();
};

const saveAddress = () => {
  isSaving.value = true;
  setTimeout(() => {
    isSaving.value = false;
    modalInstance.hide();
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu địa chỉ thành công', showConfirmButton: false, timer: 1500 });
  }, 800);
};

const deleteAddress = (id) => {
  Swal.fire({ title: 'Xóa địa chỉ?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Đồng ý' }).then((result) => {
    if (result.isConfirmed) {
      addresses.value = addresses.value.filter(a => a.id !== id);
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã xóa', showConfirmButton: false, timer: 1500 });
    }
  });
};

const setDefault = (id) => {
  addresses.value.forEach(a => a.is_default = (a.id === id));
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã cập nhật mặc định', showConfirmButton: false, timer: 1500 });
};

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.user-address-wrapper { width: 100%; }

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

select.custom-input {
  cursor: pointer; appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23547792' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat; background-position: right 1rem center; background-size: 16px 12px; padding-right: 2.5rem;
}
html.dark select.custom-input { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); }

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.transition-all { transition: all 0.3s ease; }
.cursor-pointer { cursor: pointer; }
</style>