<template>
  <div class="user-profile-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/"
                class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile"
                class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Hồ sơ cá nhân</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <div class="col-lg-9">
            <div
              class="roadmap-wrapper p-3 p-md-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 font-sans-vn mb-4 animation-fade-in position-relative"
              style="z-index: 10;" v-if="!isLoading && displayTiers.length > 0">
              <div class="position-absolute top-0 start-0 w-100 h-100 pe-none z-0 roadmap-gradient-overlay rounded-4"
                :style="{ '--glow-color': currentTierColor }"></div>

              <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 position-relative z-1 gap-2">
                <h5 class="fw-bold m-0 d-flex align-items-center text-dark dark:text-white font-decor"
                  style="letter-spacing: 0.5px;">
                  <i class="bi bi-stars text-warning me-2 fs-5"></i> Đặc Quyền Thành Viên
                </h5>
                <div
                  class="badge bg-white dark:bg-[#212529] text-dark dark:text-white rounded-pill px-3 py-2 border border-light-subtle dark:border-gray-600 d-flex align-items-center shadow-sm">
                  <span class="text-muted fw-normal me-2 font-sans-vn" style="font-size: 0.85rem;">Tích lũy:</span>
                  <span class="fw-bold font-monospace text-urban" style="font-size: 0.95rem;">{{
                    formatCurrency(totalSpent) }}</span>
                  <span v-if="nextTier" class="text-muted fw-normal ms-1 font-monospace" style="font-size: 0.85rem;"> /
                    {{ formatCurrency(nextTier.min_spent) }}</span>
                </div>
              </div>

              <div class="mb-4 position-relative z-1" v-if="currentTierData && currentTierData.id !== 'default_0'">
                <div
                  class="d-flex flex-wrap gap-3 gap-md-4 text-dark dark:text-gray-300 font-sans-vn bg-light dark:bg-[#212529] px-3 py-2 rounded-3 border border-secondary border-opacity-10 align-items-center"
                  style="font-size: 0.85rem;">
                  <div class="fw-bold d-flex align-items-center gap-2">
                    Hạng của bạn:
                    <span class="badge rounded-pill px-3 py-1.5 shadow-sm"
                      :style="{ backgroundColor: currentTierColor, color: getContrastYIQ(currentTierColor) }">{{
                      currentTierData.name }}</span>
                  </div>
                  <div class="vr d-none d-md-block dark:bg-gray-600 opacity-50"></div>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-primary"
                      :style="{ color: currentTierColor + ' !important' }"></i>
                    <span class="text-muted">Chiết khấu:</span>
                    <span class="fw-bold" :style="{ color: currentTierColor }">{{ currentTierData.discount_percent || 0
                      }}%</span>
                  </div>
                  <div class="vr d-none d-md-block dark:bg-gray-600 opacity-50"></div>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-primary"
                      :style="{ color: currentTierColor + ' !important' }"></i>
                    <span class="text-muted">Bảo hành:</span>
                    <span class="fw-bold" :style="{ color: currentTierColor }">{{ currentTierData.yearly_service_quota
                      || 0 }} lần/năm</span>
                  </div>
                </div>
              </div>

              <div v-else
                class="alert bg-urban-effect border border-urban border-opacity-10 rounded-3 p-2 px-3 mb-4 text-center dark:bg-[#212529] position-relative z-1 shadow-sm small">
                <span class="m-0 text-urban dark:text-gray-300 font-sans-vn fw-medium"><i
                    class="bi bi-stars text-warning me-1"></i> Mua sắm ngay để mở khóa những đặc quyền hấp dẫn!</span>
              </div>

              <div class="roadmap-track-wrapper position-relative z-1 w-100 mt-4 pt-4 pb-5 px-2 px-md-4 mb-1">
                <div class="position-relative w-100" style="height: 6px;">
                  <div class="position-absolute w-100 rounded-pill"
                    style="height: 6px; background-color: #e2e8f0; top: 0; left: 0; z-index: 1;"></div>
                  <div class="position-absolute rounded-pill transition-all" :style="{
                    width: roadmapProgress + '%',
                    height: '6px',
                    top: '0',
                    left: '0',
                    background: `linear-gradient(90deg, #e2e8f0, ${currentTierColor})`,
                    boxShadow: `0 0 8px ${currentTierColor}`,
                    zIndex: 2
                  }">
                  </div>

                  <div v-for="(tier, index) in displayTiers" :key="tier.id || index"
                    class="roadmap-node position-absolute flex-column align-items-center group-tooltip"
                    :class="isEssentialTier(index) ? 'd-flex' : 'd-none d-md-flex'"
                    :style="{ left: getTierPosition(index) + '%', top: '3px', transform: 'translate(-50%, -50%)', zIndex: 3 }">

                    <div
                      class="tooltip-box shadow-sm bg-white dark:bg-[#212529] font-sans-vn text-center rounded-3 py-2 px-3 border border-light-subtle dark:border-gray-600">
                      <div class="fw-bold text-dark dark:text-white" style="font-size: 0.9rem;">{{ tier.name }}</div>
                      <div class="text-urban fw-bold mt-1" style="font-size: 0.8rem;">Mốc: {{
                        formatCurrency(tier.min_spent) }}</div>
                      <div v-if="getTierStatus(index) === 'future' || getTierStatus(index) === 'next'"
                        class="text-danger small border-top dark:border-gray-700 pt-2 mt-2 fw-bold"
                        style="font-size: 0.75rem;">
                        Cần thêm: {{ formatCurrency(tier.min_spent - totalSpent) }}
                      </div>
                      <div v-else-if="getTierStatus(index) === 'current'"
                        class="text-success small border-top dark:border-gray-700 pt-2 mt-2 fw-bold"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> Đang đạt hạng này
                      </div>
                      <div v-else class="text-muted small border-top dark:border-gray-700 pt-2 mt-2 fw-semibold"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-check2-all me-1"></i> Đã vượt qua
                      </div>
                    </div>

                    <div v-if="getTierStatus(index) === 'current' && index > 0" class="position-absolute transition-all"
                      style="top: -32px; left: 50%; transform: translateX(-50%); z-index: 10;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffc107"
                        viewBox="0 0 256 256" style="filter: drop-shadow(0 3px 6px rgba(255,193,7,0.6));">
                        <path
                          d="M235.25,83.56a16,16,0,0,0-18.77-5.18L174,94.24l-38.16-57.26a16,16,0,0,0-26.6,0L71.1,94.24,28.51,78.38a16,16,0,0,0-18.77,5.18,16.21,16.21,0,0,0-2.48,19.06L38,168.39A24,24,0,0,0,59.3,184H185.69a24,24,0,0,0,21.32-15.61l30.72-65.77A16.21,16.21,0,0,0,235.25,83.56Z">
                        </path>
                      </svg>
                    </div>

                    <div class="node-circle transition-all" :class="[getTierStatus(index)]"
                      :style="{ '--tier-color': getTierColor(tier.name, index) }">
                    </div>

                    <div class="node-label position-absolute mt-3 transition-all" :style="{
                      top: '10px',
                      width: '130px',
                      left: index === 0 ? '-8px' : (index === displayTiers.length - 1 ? 'auto' : '50%'),
                      right: index === displayTiers.length - 1 ? '-8px' : 'auto',
                      transform: (index === 0 || index === displayTiers.length - 1) ? 'none' : 'translateX(-50%)',
                      textAlign: index === 0 ? 'left' : (index === displayTiers.length - 1 ? 'right' : 'center')
                    }">
                      <h6 class="fw-bold mb-0 line-clamp-1 transition-all"
                        :style="{ color: getTierStatus(index) === 'future' || getTierStatus(index) === 'next' ? '#9ca3af' : getTierColor(tier.name, index), fontSize: '0.85rem' }">
                        {{ tier.name }}</h6>
                      <div v-if="tier.min_spent > 0 || index === 0" class="text-muted font-monospace mt-1"
                        style="font-size: 0.75rem; letter-spacing: -0.5px; white-space: nowrap;">{{
                        formatCurrency(tier.min_spent) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in pl-4 pb-5 px-3 position-relative"
              style="z-index: 5;">
              <div class="mb-4 pb-3 border-bottom dark:border-gray-700 mt-2">
                <h4 class="fw-bold text-c-dark dark:text-white mb-1 font-sans-vn">Hồ Sơ Của Tôi</h4>
              </div>

              <div v-if="isLoading" class="row g-5 flex-column-reverse flex-md-row">
                <div class="col-md-8">
                  <div class="row g-4">
                    <div class="col-12">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-12">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-sm-6">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                    <div class="col-sm-6">
                      <div class="shimmer rounded w-100" style="height: 50px;"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 text-center border-start-md dark:border-gray-700">
                  <div class="shimmer rounded-circle mx-auto mb-3" style="width: 150px; height: 150px;"></div>
                  <div class="shimmer rounded-pill mx-auto" style="width: 100px; height: 35px;"></div>
                </div>
              </div>

              <form v-else @submit.prevent="updateProfile" autocomplete="off" class="font-sans-vn">
                <div class="row g-5 flex-column-reverse flex-md-row">
                  <div class="col-md-8">
                    <div class="row g-4">
                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Họ và tên <span
                            class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="form.full_name" required
                          placeholder="Nhập tên của bạn">
                      </div>

                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Email đăng nhập</label>
                        <div class="d-flex align-items-center gap-2">
                          <input type="email"
                            class="form-control custom-input bg-light dark:bg-[#121416] text-muted font-monospace pe-none w-100"
                            :value="maskEmail(form.email)" readonly tabindex="-1"
                            title="Email bảo mật không thể thay đổi">
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Số điện thoại</label>
                        <input type="tel" class="form-control custom-input" v-model="form.phone"
                          placeholder="Thêm số điện thoại">
                      </div>

                      <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Giới tính</label>
                        <select class="form-select custom-input fw-semibold" v-model="form.gender">
                          <option value="">-- Chọn --</option>
                          <option value="Nam">Nam</option>
                          <option value="Nữ">Nữ</option>
                          <option value="Khác">Khác</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Ngày sinh</label>
                        <input type="date" class="form-control custom-input fw-semibold" v-model="form.birthday">
                      </div>

                      <div class="col-12 mt-4">
                        <div
                          class="p-3 bg-c-effect dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3">
                          <h6 class="fw-bold text-c-dark dark:text-white mb-3 d-flex align-items-center">
                            <i class="bi bi-rulers text-c-hover me-2"></i> Chỉ số cơ thể
                          </h6>
                          <div class="row g-3">
                            <div class="col-sm-6">
                              <label class="form-label small fw-bold text-muted text-uppercase mb-2">Chiều cao
                                (cm)</label>
                              <input type="number" class="form-control custom-input" v-model="form.height_cm"
                                placeholder="VD: 170" min="0">
                            </div>
                            <div class="col-sm-6">
                              <label class="form-label small fw-bold text-muted text-uppercase mb-2">Cân nặng
                                (kg)</label>
                              <input type="number" class="form-control custom-input" v-model="form.weight_kg"
                                placeholder="VD: 65" min="0" step="0.1">
                            </div>
                          </div>
                          <p class="small text-muted mt-3 mb-0 fst-italic d-none d-md-block"><i
                              class="bi bi-info-circle me-1"></i>Hệ thống sẽ dựa vào chỉ số này để gợi ý kích cỡ phù hợp
                            nhất với bạn.</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 text-center border-start-md dark:border-gray-700 mb-4 mb-md-0">
                    <div class="d-flex flex-column align-items-center mt-md-2">
                      <div class="position-relative d-inline-block mb-3 avatar-wrapper" :class="{ 'vip-glow': true }"
                        :style="{ '--tier-color': currentTierColor }">
                        <img :src="previewAvatar" @error="handleImageError"
                          class="rounded-circle object-fit-cover shadow-sm border border-4 bg-white dark:bg-[#1a2533]"
                          :style="{ borderColor: currentTierColor + ' !important' }"
                          style="width: 150px; height: 150px; position: relative; z-index: 2;">
                        <button type="button" @click="triggerUpload"
                          class="btn btn-c-dark rounded-circle position-absolute shadow-sm border border-2 border-white d-flex align-items-center justify-content-center hover-transform"
                          style="width: 35px; height: 35px; bottom: 10px; right: 5px; z-index: 3;"
                          title="Cập nhật ảnh đại diện">
                          <i class="bi bi-camera-fill text-white small"></i>
                        </button>
                      </div>

                      <span
                        class="badge rounded-pill px-3 py-2 shadow-sm font-sans-vn mt-1 mb-3 text-uppercase tracking-wide"
                        :style="{ backgroundColor: currentTierColor, color: getContrastYIQ(currentTierColor) }">
                        <i class="bi bi-stars me-1 fs-6 align-middle"></i> {{ currentTierData?.name || 'Member' }}
                      </span>
                    </div>

                    <input type="file" ref="fileInput" @change="onFileChange" class="d-none"
                      accept="image/jpeg, image/png, image/webp">

                    <div class="d-flex flex-column align-items-center">
                      <button type="button"
                        class="btn btn-outline-secondary dark:text-gray-300 dark:border-gray-600 rounded-pill px-4 py-1.5 fw-semibold small shadow-sm hover-c-dark"
                        @click="triggerUpload">Đổi Ảnh</button>
                      <p class="text-muted mt-3 mb-0 d-none d-md-block" style="font-size: 0.75rem;">Dung lượng tối đa
                        5MB.<br>Định dạng: .JPEG, .PNG, .WEBP</p>
                    </div>

                    <div class="mt-4 pt-4 border-top dark:border-gray-700">
                      <button type="submit"
                        class="btn btn-c-dark btn-lg px-5 fw-bold shadow-lg hover-transform rounded-pill text-uppercase tracking-wide w-100 w-md-auto"
                        :disabled="isSaving">
                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                        <template v-else>Lưu Thay Đổi</template>
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import defaultAvatar from '@/assets/images/defaults/client_placeholder.png';
import UserSidebar from '@/components/client/UserSidebar.vue';

const isLoading = ref(true);
const isSaving = ref(false);
const fileInput = ref(null);
const previewAvatar = ref(defaultAvatar);

const allTiers = ref([]);
const totalSpent = ref(0);

const form = ref({
  full_name: '', email: '', phone: '', gender: '', birthday: '', height_cm: null, weight_kg: null, avatar: null
});

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN').format(val || 0) + 'đ';

const getContrastYIQ = (hexcolor) => {
  if (!hexcolor) return '#ffffff';
  hexcolor = hexcolor.replace("#", "");
  if (hexcolor.length === 3) hexcolor = hexcolor.split('').map(c => c + c).join('');
  const r = parseInt(hexcolor.substr(0, 2), 16);
  const g = parseInt(hexcolor.substr(2, 2), 16);
  const b = parseInt(hexcolor.substr(4, 2), 16);
  const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
  return (yiq >= 128) ? '#111111' : '#ffffff';
};

const getTierColor = (name, index) => {
  if (!name) return '#9ca3af';
  const lName = name.toLowerCase();

  // Mốc mặc định
  if (lName.includes('khởi đầu') || lName.includes('member')) return '#9ca3af';
  
  // Các mốc cơ bản
  if (lName.includes('fan cứng') || lName.includes('bronze')) return '#3b82f6';
  if (lName.includes('đồng')) return '#f59e0b';
  
  // Bạc: Bạc Tuyết Neon cực kỳ chói lọi, khác biệt hoàn toàn với màu xám tro
  if (lName.includes('bạc') || lName.includes('silver')) return '#00d2ff';
  
  // Các mốc VIP
  if (lName.includes('vàng') || lName.includes('gold')) return '#ffc107';
  if (lName.includes('bạch kim') || lName.includes('platinum')) return '#d946ef';
  if (lName.includes('kim cương') || lName.includes('diamond')) return '#8b5cf6';
  if (lName.includes('vip') || lName.includes('master')) return '#f43f5e';

  // THUẬT TOÁN TẠO MÀU DỰ PHÒNG RỰC RỠ 
  // (Sử dụng Hash kết hợp HSL: Saturation 90%, Lightness 55%)
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const h = Math.abs(hash) % 360;
  return `hsl(${h}, 90%, 55%)`;
};

const displayTiers = computed(() => {
  const tiers = [...allTiers.value];
  if (tiers.length === 0) return [];

  if (tiers[0].min_spent > 0) {
    tiers.unshift({
      id: 'default_0',
      name: 'Member',
      min_spent: 0,
      discount_percent: 0,
      yearly_service_quota: 0
    });
  }
  return tiers;
});

const currentTierData = computed(() => {
  if (displayTiers.value.length === 0) return null;
  let current = displayTiers.value[0];
  for (let i = 0; i < displayTiers.value.length; i++) {
    if (totalSpent.value >= displayTiers.value[i].min_spent) {
      current = displayTiers.value[i];
    }
  }
  return current;
});

const nextTier = computed(() => {
  return displayTiers.value.find(t => t.min_spent > totalSpent.value) || null;
});

const currentTierColor = computed(() => {
  if (displayTiers.value.length === 0) return '#9ca3af';
  let currentIndex = 0;
  for (let i = 0; i < displayTiers.value.length; i++) {
    if (totalSpent.value >= displayTiers.value[i].min_spent) {
      currentIndex = i;
    }
  }
  return getTierColor(currentTierData.value?.name, currentIndex);
});

const getTierPosition = (index) => {
  if (displayTiers.value.length <= 1) return 0;
  return (index / (displayTiers.value.length - 1)) * 100;
};

const getTierStatus = (index) => {
  let currentTierIndex = 0;
  for (let i = 0; i < displayTiers.value.length; i++) {
    if (totalSpent.value >= displayTiers.value[i].min_spent) {
      currentTierIndex = i;
    }
  }

  if (index < currentTierIndex) return 'passed';
  if (index === currentTierIndex) return 'current';
  if (index === currentTierIndex + 1) return 'next';
  return 'future';
};

const isEssentialTier = (index) => {
  const status = getTierStatus(index);
  const isFirst = index === 0;
  const isLast = index === displayTiers.value.length - 1;
  const isCurrent = status === 'current';

  return isFirst || isLast || isCurrent;
};

const roadmapProgress = computed(() => {
  const tiers = displayTiers.value;
  if (!tiers || tiers.length === 0) return 0;

  const spent = totalSpent.value;
  const numTiers = tiers.length;

  if (spent >= tiers[numTiers - 1].min_spent) return 100;

  let currentIndex = 0;
  for (let i = 0; i < numTiers; i++) {
    if (spent >= tiers[i].min_spent) currentIndex = i;
    else break;
  }

  const currentTier = tiers[currentIndex];
  const targetTier = tiers[currentIndex + 1];

  if (!targetTier) return 100;

  const segmentSize = 100 / (numTiers - 1);
  const segmentProgress = (spent - currentTier.min_spent) / (targetTier.min_spent - currentTier.min_spent);

  return (currentIndex * segmentSize) + (segmentProgress * segmentSize);
});

const handleImageError = (e) => { e.target.src = defaultAvatar; };
const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0]; const domain = parts[1];
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  return name.substring(0, 3) + '***@' + domain;
};

const triggerUpload = () => { fileInput.value.click(); };
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) { ZyroSwal.toastError('Chỉ hỗ trợ file hình ảnh!'); return; }
  if (file.size > 5 * 1024 * 1024) { ZyroSwal.toastError('Ảnh tối đa 5MB'); return; }

  form.value.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewAvatar.value = e.target.result; };
  reader.readAsDataURL(file);
};

const fetchProfile = async () => {
  try {
    const res = await api.get('/client/user/profile');
    if (res.data.success) {
      const u = res.data.data;

      allTiers.value = res.data.all_tiers || [];
      totalSpent.value = parseFloat(u.total_spent) || 0;

      form.value = {
        full_name: u.full_name || '', email: u.email || '', phone: u.phone || '', gender: u.gender || '',
        birthday: u.birthday ? u.birthday.split('T')[0] : '', height_cm: u.height_cm || null, weight_kg: u.weight_kg || null, avatar: null
      };

      if (u.avatar_url) previewAvatar.value = import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + u.avatar_url;

      setTimeout(() => {
        let currentTierIndex = 0;
        for (let i = 0; i < displayTiers.value.length; i++) {
          if (totalSpent.value >= displayTiers.value[i].min_spent) {
            currentTierIndex = i;
          }
        }

        let localU = JSON.parse(localStorage.getItem('user_info')) || {};
        localU.tier_name = currentTierData.value?.name || 'Member';
        localStorage.setItem('user_info', JSON.stringify(localU));

        window.dispatchEvent(new CustomEvent('user-profile-updated', {
          detail: {
            tierName: currentTierData.value?.name || 'Member',
            tierColor: getTierColor(currentTierData.value?.name, currentTierIndex),
            isTopTier: !nextTier.value
          }
        }));
      }, 50);
    }
  } catch (error) {
    ZyroSwal.toastError('Không lấy được thông tin hồ sơ.');
  } finally { isLoading.value = false; }
};

const updateProfile = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('full_name', form.value.full_name);
    formData.append('phone', form.value.phone || '');
    formData.append('gender', form.value.gender || '');
    formData.append('birthday', form.value.birthday || '');
    formData.append('height_cm', form.value.height_cm || '');
    formData.append('weight_kg', form.value.weight_kg || '');
    if (form.value.avatar instanceof File) formData.append('avatar', form.value.avatar);

    const res = await api.post('/client/user/profile', formData, { headers: { 'Content-Type': 'multipart/form-data' } });

    if (res.data.success) {
      ZyroSwal.toastSuccess(res.data.message);
      const userStr = localStorage.getItem('user_info');
      if (userStr) {
        let u = JSON.parse(userStr);
        u.full_name = res.data.data.full_name;
        if (res.data.data.avatar_url) u.avatar_url = res.data.data.avatar_url;
        localStorage.setItem('user_info', JSON.stringify(u));
        window.dispatchEvent(new CustomEvent('user-profile-updated'));
      }
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors) ZyroSwal.toastError(Object.values(errors).flat()[0]);
      else ZyroSwal.toastError(error.response.data.message);
    } else { ZyroSwal.toastError('Có lỗi xảy ra khi cập nhật.'); }
  } finally { isSaving.value = false; }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProfile();
});
</script>

<style scoped>
.user-profile-wrapper {
  padding-top: 26px;
  width: 100%;
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

.font-sans-vn {
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important;
}

.font-decor {
  font-family: 'Times New Roman', Times, serif;
  font-style: italic;
}

.roadmap-wrapper {
  background-color: #ffffff;
}

html.dark .roadmap-wrapper {
  background-color: #1a2533;
}

.roadmap-gradient-overlay {
  background: radial-gradient(circle at 100% 0%, var(--glow-color) 0%, transparent 40%),
    radial-gradient(circle at 0% 100%, var(--glow-color) 0%, transparent 30%);
  opacity: 0.08;
  animation: breathe-bg 4s infinite alternate ease-in-out;
}

html.dark .roadmap-gradient-overlay {
  opacity: 0.15;
}

@keyframes breathe-bg {
  0% {
    opacity: 0.05;
  }

  100% {
    opacity: 0.12;
  }
}

.roadmap-nodes {
  z-index: 3;
}

.node-circle {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: #fff;
  border: 3px solid #e2e8f0;
  position: relative;
  z-index: 5;
}

html.dark .node-circle {
  background-color: #1a2533;
  border-color: #373b3e;
}

.node-circle.passed {
  background-color: var(--tier-color);
  border-color: var(--tier-color);
}

.node-circle.current {
  background-color: var(--tier-color);
  border-color: var(--tier-color);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.7), 0 0 10px var(--tier-color);
  transform: scale(1.3);
  animation: pulse-node 2s infinite;
}

html.dark .node-circle.current {
  box-shadow: 0 0 0 3px rgba(26, 37, 51, 0.7), 0 0 10px var(--tier-color);
}

.node-circle.next {
  border-color: var(--tier-color);
  border-style: dashed;
  background-color: #fff;
}

html.dark .node-circle.next {
  background-color: #1a2533;
}

.node-circle.future {
  border-color: #e2e8f0;
  background-color: #fff;
}

html.dark .node-circle.future {
  border-color: #373b3e;
  background-color: #1a2533;
}

@keyframes pulse-node {
  0% {
    box-shadow: 0 0 0 0px var(--tier-color);
  }

  70% {
    box-shadow: 0 0 0 6px rgba(255, 255, 255, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
  }
}

.drop-shadow-glow {
  filter: drop-shadow(0 0 8px currentColor);
}

.group-tooltip {
  cursor: pointer;
}

.tooltip-box {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%) translateY(10px);
  opacity: 0;
  visibility: hidden;
  min-width: max-content;
  padding: 10px 15px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  z-index: 100;
  pointer-events: none;
}

.tooltip-box::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 6px;
  border-style: solid;
  border-color: #fff transparent transparent transparent;
}

html.dark .tooltip-box::after {
  border-color: #212529 transparent transparent transparent;
}

.group-tooltip:hover .tooltip-box {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}

.roadmap-node:last-child .tooltip-box {
  left: auto;
  right: -20px;
  transform: translateY(10px);
}

.roadmap-node:last-child .tooltip-box::after {
  left: auto;
  right: 22px;
  transform: none;
}

.roadmap-node:last-child:hover .tooltip-box {
  transform: translateY(0);
}

.roadmap-node:first-child .tooltip-box {
  left: -20px;
  transform: translateY(10px);
}

.roadmap-node:first-child .tooltip-box::after {
  left: 22px;
  transform: none;
}

.roadmap-node:first-child:hover .tooltip-box {
  transform: translateY(0);
}

@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid #dee2e6;
  }
}

.avatar-wrapper {
  position: relative;
  border-radius: 50%;
  display: inline-flex;
}

.vip-glow::before {
  content: '';
  position: absolute;
  top: -3px;
  left: -3px;
  right: -3px;
  bottom: -3px;
  border-radius: 50%;
  z-index: 1;
  background: var(--tier-color);
  filter: blur(8px);
  animation: pulse-avatar-glow 2s infinite alternate;
}

@keyframes pulse-avatar-glow {
  0% {
    opacity: 0.5;
    filter: blur(5px);
    transform: scale(0.98);
  }

  100% {
    opacity: 1;
    filter: blur(12px);
    transform: scale(1.03);
  }
}


.text-c-dark {
  color: var(--color-c-dark) !important;
}

html.dark .text-c-dark {
  color: #f8f9fa !important;
}

.text-c-hover {
  color: var(--color-c-hover) !important;
}

.btn-c-dark {
  background-color: var(--color-c-dark);
  color: white;
  border: none;
  transition: 0.2s ease;
}

.btn-c-dark:hover {
  background-color: var(--color-c-hover);
  color: white;
}

.bg-c-effect {
  background-color: var(--color-c-effect);
}

.bg-urban-effect {
  background-color: rgba(84, 119, 146, 0.05) !important;
}

.hover-c-dark:hover {
  border-color: var(--color-c-dark) !important;
  color: var(--color-c-dark) !important;
}

.hover-text-dark:hover {
  color: #000 !important;
}

html.dark .hover-text-dark:hover {
  color: #fff !important;
}

.hover-transform {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-transform:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

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

select.custom-input {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23547792' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 16px 12px;
  padding-right: 2.5rem;
}

html.dark select.custom-input {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
}

.custom-input:disabled,
.custom-input.pe-none {
  cursor: not-allowed;
  opacity: 0.8;
}

@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #dee2e6;
  }

  html.dark .border-start-md {
    border-color: #373b3e !important;
  }
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

.transition-all {
  transition: all 0.3s ease;
}

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
</style>