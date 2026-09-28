<template>
  <div class="user-edit-wrapper">
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
          <router-link :to="{ name: 'admin-users' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
            <i class="bi bi-arrow-left-circle fs-3"></i>
          </router-link>
          <h3 class="fw-bold text-dark dark:text-white mb-0">Hồ Sơ Khách Hàng <span class="text-muted fs-5 fw-normal">#{{ userId }}</span></h3>
        </div>

        <div class="bg-white dark:bg-[#1a2533] p-1 rounded-pill shadow-sm d-flex border dark:border-gray-700">
          <button @click="activeTab = 'info'" class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all" :class="activeTab === 'info' ? 'bg-urban text-white' : 'text-muted'">
            <i class="bi bi-person-lines-fill me-1"></i> Thông tin
          </button>
          <button @click="activeTab = 'security'" class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all" :class="activeTab === 'security' ? 'bg-danger text-white' : 'text-muted'">
            <i class="bi bi-shield-lock me-1"></i> Bảo mật
          </button>
          <button @click="activeTab = 'address'" class="btn btn-sm px-4 py-2 rounded-pill fw-bold transition-all d-flex align-items-center" :class="activeTab === 'address' ? 'bg-warning text-dark' : 'text-muted'">
            <i class="bi bi-geo-alt-fill me-1"></i> Sổ địa chỉ
            <span class="badge ms-2 rounded-pill" :class="activeTab === 'address' ? 'bg-dark text-white' : 'bg-secondary text-white opacity-50'">{{ userAddresses.length }}</span>
          </button>
        </div>
      </div>

      <div class="row">
        <!-- CỘT TRÁI: AVATAR & TRẠNG THÁI -->
        <div class="col-lg-4 col-xl-3 mb-4 mb-lg-0">
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 text-center sticky-top" style="top: 20px;">
              <div class="position-relative mx-auto mb-3 avatar-wrapper group rounded-circle shadow-sm" style="width: 140px; height: 140px;">
                 <img :src="previewAvatar" class="w-100 h-100 rounded-circle border border-4 border-white dark:border-gray-700 object-fit-cover">
                 <div class="avatar-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle d-flex justify-content-center align-items-center bg-dark bg-opacity-50 cursor-pointer" @click="$refs.avatarInput.click()">
                    <i class="bi bi-camera-fill text-white fs-3"></i>
                 </div>
                 <button v-if="hasOldAvatar || form.avatar" type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle shadow-sm" style="width: 32px; height: 32px; z-index: 10;" @click="removeAvatar">
                    <i class="bi bi-x"></i>
                 </button>
                 <input type="file" ref="avatarInput" @change="onAvatarChange" class="d-none" accept="image/*">
              </div>
              <div class="text-danger small mb-3 fw-bold" v-if="errors.avatar">{{ errors.avatar[0] }}</div>
              
              

              <h5 class="fw-bold dark:text-white mb-1">{{ form.full_name || 'Khách hàng' }}</h5>
              <p class="text-muted mb-4 small font-monospace">{{ maskEmail(form.email) }}</p>
              
              <div class="text-start">
                <div class="form-floating shadow-sm-hover">
                  <select id="floating_status" class="form-select fw-bold shadow-sm dark:bg-[#212529] dark:border-gray-700" v-model="form.status" :class="form.status === 'active' ? 'text-success border-success bg-success bg-opacity-10' : 'text-warning border-warning bg-warning bg-opacity-10'">
                    <option value="active" class="text-success fw-bold">Hoạt động (Active)</option>
                    <option value="locked" class="text-warning fw-bold">Khóa (Locked)</option>
                  </select>
                  <label for="floating_status" class="fw-bold text-muted" style="font-size: 0.85rem;">TRẠNG THÁI TÀI KHOẢN</label>
                </div>
              </div>
           </div>
        </div>

        <!-- CỘT PHẢI: CHI TIẾT THEO TAB -->
        <div class="col-lg-8 col-xl-9">
          
          <!-- TAB 1: THÔNG TIN CHUNG -->
          <div v-show="activeTab === 'info'" class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 animation-fade-in">
            <div class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="fw-bold text-dark dark:text-white mb-1">Thông Tin Chung</h5>
                <p class="text-muted small mb-0">Cập nhật hồ sơ cá nhân của khách hàng.</p>
              </div>
            </div>

            <form @submit.prevent="updateUser" autocomplete="off">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_0bm67gb2h" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.full_name" :class="{'is-invalid': errors.full_name}" placeholder="...">
                    <label for="floating_0bm67gb2h" class="fw-bold text-muted" style="font-size: 0.85rem;">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                  </div>
                  <div class="invalid-feedback">{{ errors.full_name?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_5tzev8wfx" type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.phone" :class="{'is-invalid': errors.phone}" @input="validatePhone" placeholder="...">
                    <label for="floating_5tzev8wfx" class="fw-bold text-muted" style="font-size: 0.85rem;">SỐ ĐIỆN THOẠI LIÊN HỆ</label>
                  </div>
                  <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <select id="floating_gender" class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.gender">
                    <option value="">-- N/A --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                  </select>
                    <label for="floating_gender" class="fw-bold text-muted" style="font-size: 0.85rem;">GIỚI TÍNH</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_f6p8dn0g2" type="date" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="form.birthday" placeholder="...">
                    <label for="floating_f6p8dn0g2" class="fw-bold text-muted" style="font-size: 0.85rem;">NGÀY SINH</label>
                  </div>
                </div>
              </div>

              <div class="mt-5 pt-4 border-top dark:border-gray-700 text-end">
                <button type="submit" class="btn btn-urban text-white px-5 py-2 fw-bold shadow-sm" :disabled="isSavingUser">
                  <span v-if="isSavingUser" class="spinner-border spinner-border-sm me-2"></span> Cập Nhật Thông Tin
                </button>
              </div>
            </form>
          </div>

          <!-- TAB 2: BẢO MẬT & MẬT KHẨU -->
          <div v-show="activeTab === 'security'" class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5 animation-fade-in">
            <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
              <h5 class="fw-bold text-dark dark:text-white mb-1">Bảo Mật Tài Khoản</h5>
              <p class="text-muted small mb-0">Thiết lập lại mật khẩu bảo mật cho khách hàng.</p>
            </div>

            <form @submit.prevent="updateUser" autocomplete="off">
              <input style="display:none" type="text" name="fakeusernameremembered"/>
              <input style="display:none" type="password" name="fakepasswordremembered"/>

              <div class="row g-4">
                <!-- ĐÃ SỬA: Chuyển Email thành Read-only và thêm Note -->
                <div class="col-12">
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-light dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                    <div class="form-floating flex-grow-1">
                      <input type="email" class="form-control border-start-0 border-end-0 bg-light dark:bg-[#212529] text-muted dark:border-gray-700 cursor-not-allowed" :value="form.email" readonly disabled id="floating_email_edit">
                      <label for="floating_email_edit" class="fw-bold text-muted" style="font-size: 0.85rem;">EMAIL ĐĂNG NHẬP</label>
                    </div>
                    <span class="input-group-text bg-light dark:bg-[#212529] border-start-0 text-muted" title="Không thể thay đổi"><i class="bi bi-lock-fill"></i></span>
                  </div>
                  <small class="text-muted d-block mt-2 fst-italic"><i class="bi bi-info-circle me-1"></i>Email là định danh duy nhất của tài khoản và không thể thay đổi bởi Quản trị viên.</small>
                </div>

                <div class="col-12 mt-5">
                  <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 dark:bg-yellow-900/20 dark:text-yellow-200">
                    <i class="bi bi-info-circle-fill me-2"></i> Chỉ nhập vào ô bên dưới nếu bạn muốn ép buộc cấp lại mật khẩu mới cho người dùng này.
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="input-group shadow-sm-hover">
                    <div class="form-floating flex-grow-1">
                      <input :type="showPass1 ? 'text' : 'password'" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự" id="floating_pass1_edit">
                      <label for="floating_pass1_edit" class="fw-bold text-muted" style="font-size: 0.85rem;">MẬT KHẨU MỚI</label>
                    </div>
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass1 = !showPass1"><i class="bi" :class="showPass1 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i></button>
                    <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="input-group shadow-sm-hover">
                    <div class="form-floating flex-grow-1">
                      <input :type="showPass2 ? 'text' : 'password'" autocomplete="new-password" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="form.password_confirmation" placeholder="Xác nhận lại" id="floating_pass2_edit">
                      <label for="floating_pass2_edit" class="fw-bold text-muted" style="font-size: 0.85rem;">NHẬP LẠI MẬT KHẨU MỚI <span class="text-danger" v-if="form.password">*</span></label>
                    </div>
                    <button class="btn btn-light dark:bg-[#212529] border dark:border-gray-700 text-muted" type="button" @click="showPass2 = !showPass2"><i class="bi" :class="showPass2 ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i></button>
                  </div>
                </div>
              </div>

              <div class="mt-5 pt-4 border-top dark:border-gray-700 text-end">
                <button type="submit" class="btn btn-danger text-white px-5 py-2 fw-bold shadow-sm" :disabled="isSavingUser">
                  <span v-if="isSavingUser" class="spinner-border spinner-border-sm me-2"></span> Đổi Mật Khẩu
                </button>
              </div>
            </form>
          </div>

          <!-- TAB 3: QUẢN LÝ SỔ ĐỊA CHỈ -->
          <div v-show="activeTab === 'address'" class="animation-fade-in">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold text-dark dark:text-white mb-0">Sổ Địa Chỉ Giao Hàng</h5>
              <button type="button" class="btn btn-urban rounded-pill px-4 py-2 fw-bold text-white shadow-sm transition-all" @click="openAddressModal('add')">
                <i class="bi bi-plus-lg me-1"></i> Thêm Địa Chỉ
              </button>
            </div>
            
            <div v-if="userAddresses.length === 0" class="text-center py-5 bg-white dark:bg-[#1a2533] rounded-4 shadow-sm border dark:border-gray-700">
              <div class="bg-light dark:bg-[#2b3035] rounded-circle d-inline-flex justify-content-center align-items-center shadow-sm mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-geo-alt text-muted fs-2"></i>
              </div>
              <h6 class="fw-bold text-dark dark:text-white">Chưa có địa chỉ nào</h6>
              <p class="text-muted small">Khách hàng này chưa lưu địa chỉ nhận hàng.</p>
            </div>
            
            <div v-else class="row g-3">
              <div v-for="addr in userAddresses" :key="addr.id" class="col-12">
                <div class="p-4 rounded-4 shadow-sm position-relative transition-all h-100 border" :class="addr.is_default ? 'bg-urban bg-opacity-10 border-urban border-opacity-50' : 'bg-white dark:bg-[#1a2533] border-light-subtle dark:border-gray-700'">
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-3">
                    <div>
                      <h6 class="fw-bold text-dark dark:text-white mb-1 d-flex align-items-center flex-wrap gap-2">
                        {{ addr.customer_name }}
                        <span class="text-muted fw-normal">|</span>
                        <span class="text-muted fw-normal font-monospace">{{ addr.customer_phone }}</span>
                        <span v-if="addr.is_default" class="badge bg-urban text-white ms-md-2 px-2 py-1"><i class="bi bi-check-circle me-1"></i>Mặc định</span>
                      </h6>
                    </div>
                    <!-- Nút Đặt Mặc định (Nếu chưa là mặc định) -->
                    <button v-if="!addr.is_default" @click="setDefaultAddress(addr.id)" class="btn btn-sm btn-outline-secondary dark:text-gray-400 dark:border-gray-600 fw-bold rounded-pill mt-2 mt-md-0 px-3 shadow-sm hover-urban-btn" :disabled="settingDefaultId === addr.id">
                      <span v-if="settingDefaultId === addr.id" class="spinner-border spinner-border-sm me-1"></span> Đặt mặc định
                    </button>
                  </div>
                  
                  <div class="mb-4 text-dark dark:text-gray-300 small">
                    <p class="mb-1"><i class="bi bi-house-door text-urban me-2 fs-5 align-middle"></i>{{ addr.shipping_address }}</p>
                    <p class="mb-0"><i class="bi bi-map text-urban me-2 fs-5 align-middle"></i>{{ [addr.ward, addr.district, addr.city].filter(Boolean).join(', ') }}</p>
                  </div>
                  
                  <div class="d-flex gap-2 pt-3 border-top dark:border-gray-700">
                    <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-primary dark:text-blue-400 border dark:border-gray-600 rounded-pill px-4 fw-bold shadow-sm" @click="openAddressModal('edit', addr)">
                      <i class="bi bi-pencil-square me-1"></i> Sửa
                    </button>
                    <button v-if="!addr.is_default" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-pill px-4 fw-bold shadow-sm" @click="deleteAddress(addr.id)">
                      <i class="bi bi-trash3 me-1"></i> Xóa
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-5"><span class="spinner-border text-urban"></span></div>

    <!-- MODAL ĐỊA CHỈ (DÙNG API) -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ addrModalMode === 'add' ? 'Thêm Địa Chỉ Mới' : 'Cập Nhật Địa Chỉ' }}</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveAddress">
              <div class="row g-3">
                <div class="col-md-6 mb-2">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_20lndt0s5" type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="addrForm.customer_name" required placeholder="...">
                    <label for="floating_20lndt0s5" class="fw-bold text-muted" style="font-size: 0.85rem;">TÊN NGƯỜI NHẬN <span class="text-danger">*</span></label>
                  </div>
                </div>
                <div class="col-md-6 mb-2">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_3xobl8lce" type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="addrForm.customer_phone" required placeholder="...">
                    <label for="floating_3xobl8lce" class="fw-bold text-muted" style="font-size: 0.85rem;">SỐ ĐIỆN THOẠI <span class="text-danger">*</span></label>
                  </div>
                </div>
                
                <div class="col-12 mb-2">
                  <AddressSelector 
                     show-location-button location-button-class="btn-outline-urban" @location-detail="val => addrForm.shipping_address = val"
                     v-model:city="addrForm.city"
                     v-model:district="addrForm.district"
                     v-model:ward="addrForm.ward"
                  />
                </div>
                <div class="col-md-12 mb-2">
                  <div class="form-floating shadow-sm-hover">
                    <input id="floating_c2pc9p5k7" type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="addrForm.shipping_address" placeholder="VD: Số 12, Đường ABCD" required>
                    <label for="floating_c2pc9p5k7" class="fw-bold text-muted" style="font-size: 0.85rem;">ĐỊA CHỈ CỤ THỂ (SỐ NHÀ, ĐƯỜNG) <span class="text-danger">*</span></label>
                  </div>
                </div>
                
                <div class="col-12 mt-3" v-if="!addrForm.is_default">
                  <div class="form-check form-switch p-3 bg-urban bg-opacity-10 rounded-4 d-flex align-items-center gap-3 border border-urban border-opacity-25">
                    <input class="form-check-input fs-4 m-0 cursor-pointer" type="checkbox" id="flexSwitchCheckDefault" v-model="addrForm.set_as_default">
                    <label class="form-check-label fw-bold text-urban m-0 cursor-pointer" for="flexSwitchCheckDefault">Đặt làm địa chỉ mặc định</label>
                  </div>
                </div>
              </div>
              <div class="text-end mt-4 pt-3 border-top dark:border-gray-700">
                <button type="button" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 px-4 fw-bold me-2 shadow-sm border" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-urban px-5 fw-bold text-white shadow-sm" :disabled="isSavingAddr">
                  <span v-if="isSavingAddr" class="spinner-border spinner-border-sm me-1"></span> LƯU ĐỊA CHỈ
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
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';
import AddressSelector from '@/components/shared/AddressSelector.vue';

const route = useRoute();
const router = useRouter();
const userId = parseInt(route.params.id);

const isLoading = ref(true);
const isSavingUser = ref(false);
const errors = ref({});

const activeTab = ref('info'); 

const previewAvatar = ref(defaultAvatar);
const avatarInput = ref(null);
const hasOldAvatar = ref(false);

const showPass1 = ref(false);
const showPass2 = ref(false);

const form = ref({ 
  full_name: '', email: '', phone: '', gender: '', birthday: '', status: 'active',
  password: '', password_confirmation: '', avatar: null, remove_avatar: false 
});

// Địa chỉ Data
const userAddresses = ref([]);
const isSavingAddr = ref(false);
const settingDefaultId = ref(null);
const addrModalMode = ref('add');
let addressModalInstance = null;

const addrForm = ref({ 
  id: null, customer_name: '', customer_phone: '', shipping_address: '', 
  city: '', district: '', ward: '', is_default: 0, set_as_default: false 
});

// Dropdown API Data

const getHeaders = () => ({ 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  return (parts[0].length <= 2 ? parts[0].charAt(0) : parts[0].substring(0, 3)) + '***@' + parts[1];
};

const validatePhone = (e) => { form.value.phone = e.target.value.replace(/\D/g, '').slice(0, 11); };

// ================= API CALLS =================



const fetchUser = async () => {
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${userId}`, { headers: getHeaders() });
    const u = res.data.data;
    
    form.value.full_name = u.full_name;
    form.value.email = u.email;
    form.value.phone = u.phone;
    form.value.status = u.status;
    form.value.gender = u.gender || '';
    form.value.birthday = u.birthday ? u.birthday.split('T')[0] : '';
    
    if (u.avatar_url) {
      previewAvatar.value = `${import.meta.env.VITE_STORAGE_URL}${u.avatar_url}`;
      hasOldAvatar.value = true;
    }
    
    userAddresses.value = u.addresses || [];
  } catch (err) { 
      Swal.fire('Lỗi', 'Không tải được hồ sơ khách hàng', 'error');
      router.push({ name: 'admin-users' });
  } finally { isLoading.value = false; }
};

// ================= USER CRUD =================
const onAvatarChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không vượt quá 5MB', 'error'); return; }
  form.value.avatar = file; form.value.remove_avatar = false;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};
const removeAvatar = () => { previewAvatar.value = defaultAvatar; form.value.avatar = null; form.value.remove_avatar = true; hasOldAvatar.value = false; };

const updateUser = async () => {
  isSavingUser.value = true; errors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT'); 
  
  Object.keys(form.value).forEach(key => {
    if (key === 'password' && !form.value.password) return; 
    if (key === 'password_confirmation' && !form.value.password_confirmation) return; 
    if (form.value[key] !== null && form.value[key] !== '') formData.append(key, form.value[key]);
  });

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${userId}`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật hồ sơ thành công', timer: 1500, showConfirmButton: false });
    form.value.password = ''; form.value.password_confirmation = '';
    fetchUser();
  } catch (err) { 
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors;
        if(errors.value.password) activeTab.value = 'security';
      } else Swal.fire('Lỗi', err.response?.data?.message || 'Không thể cập nhật hồ sơ', 'error');
  } finally { isSavingUser.value = false; }
};

// ================= ADDRESS CRUD =================
const openAddressModal = async (mode, addr = null) => {
  addrModalMode.value = mode;
  if (mode === 'add') {
    addrForm.value = { id: null, customer_name: form.value.full_name, customer_phone: form.value.phone, shipping_address: '', city: '', district: '', ward: '', is_default: 0, set_as_default: false };
     
  } else {
    addrForm.value = { ...addr, set_as_default: false };
      }
  if (!addressModalInstance) addressModalInstance = new window.bootstrap.Modal(document.getElementById('addressModal'));
  addressModalInstance.show();
};

const saveAddress = async () => {
  isSavingAddr.value = true;
  const url = addrModalMode.value === 'add' ? `${import.meta.env.VITE_API_BASE_URL}/admin/users/${userId}/addresses` : `${import.meta.env.VITE_API_BASE_URL}/admin/addresses/${addrForm.value.id}`;
  const payload = { ...addrForm.value, is_default: addrForm.value.set_as_default ? 1 : addrForm.value.is_default };
  
  try {
    if (addrModalMode.value === 'add') await axios.post(url, payload, { headers: getHeaders() });
    else await axios.put(url, payload, { headers: getHeaders() });
    
    addressModalInstance.hide(); 
    fetchUser(); 
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu địa chỉ', timer: 1500, showConfirmButton: false }); 
  } catch (err) { Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi khi lưu địa chỉ', 'error');
  } finally { isSavingAddr.value = false; }
};

const deleteAddress = (id) => {
  Swal.fire({ title: 'Xóa địa chỉ?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
          await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/addresses/${id}`, { headers: getHeaders() });
          fetchUser(); Swal.fire({ toast:true, position: 'top-end', icon: 'success', title: 'Đã xóa!', timer: 1500, showConfirmButton: false });
      } catch (err) { Swal.fire('Lỗi', err.response?.data?.message || 'Không thể xóa địa chỉ này', 'error'); }
    }
  });
};

const setDefaultAddress = async (id) => {
  settingDefaultId.value = id;
  try {
    await axios.put(`${import.meta.env.VITE_API_BASE_URL}/admin/addresses/${id}/default`, {}, { headers: getHeaders() });
    fetchUser(); Swal.fire({ toast:true, position: 'top-end', icon: 'success', title: 'Đã cập nhật mặc định', timer: 1500, showConfirmButton: false });
  } catch (err) { Swal.fire('Lỗi', err.response?.data?.message || 'Lỗi cập nhật', 'error');
  } finally { settingDefaultId.value = null; }
};

onMounted(() => {  fetchUser(); });
</script>

<style scoped>
.avatar-overlay { opacity: 0; transition: opacity 0.3s ease; }
.avatar-wrapper:hover .avatar-overlay { opacity: 1; }
.cursor-pointer { cursor: pointer; }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.hover-urban-btn:hover { background-color: var(--color-c-hover, #547792) !important; color: white !important; border-color: var(--color-c-hover, #547792) !important; }
.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.cursor-pointer { cursor: pointer; }
.sticky-top { transition: all 0.3s ease; }
.cursor-not-allowed { cursor: not-allowed; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

