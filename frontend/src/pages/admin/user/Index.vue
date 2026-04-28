<template>
  <div class="user-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải khách hàng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản Lý Khách Hàng</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-users-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all">
            <i class="bi bi-person-plus-fill me-1"></i> Thêm Khách Hàng
          </router-link>
        </div>
      </div>

      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-people-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ users.filter(c => !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'active' }" @click.prevent="switchTab('active')">
              <i class="bi bi-person-check-fill me-2 text-success"></i> Đang hoạt động
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'active'}">{{ users.filter(c => c.status === 'active' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'locked' }" @click.prevent="switchTab('locked')">
              <i class="bi bi-lock-fill me-2 text-warning"></i> Bị khóa
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'locked'}">{{ users.filter(c => c.status === 'locked' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ users.filter(c => c.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
            <i class="bi bi-list-ul me-2"></i> Danh sách khách hàng
            <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
          </h6>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="search-box position-relative" style="width: 280px; max-width: 100%;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, email, sđt...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 30%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 15%;">Hạng thành viên</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Thông tin phụ</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 20%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="displayUsers.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="user in displayUsers" :key="user.id" 
                    :class="{'bg-light opacity-75 dark:bg-[#121416]': user.deleted_at || user.status === 'locked'}"
                    :style="getRowStyle(user)">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      
                      <!-- AVATAR FRAME & GLOW -->
                      <div class="position-relative flex-shrink-0 avatar-wrapper me-3" 
                           :class="{'vip-glow': isTopTier(getCalculatedTier(user)?.name)}" 
                           :style="{'--tier-color': getTierColor(getCalculatedTier(user)?.name)}">
                        <img :src="getImageUrl(user.avatar_url)" @error="handleImageError" 
                             class="rounded-circle object-fit-cover shadow-sm bg-white dark:bg-[#212529]" 
                             :style="getAvatarStyle(getCalculatedTier(user)?.name)"
                             style="width: 48px; height: 48px; position: relative; z-index: 2;">
                      </div>

                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ user.full_name }}</h6>
                        <!-- DATA MASKING: Ẩn Email -->
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace">
                           <i class="bi bi-envelope me-1"></i>{{ maskEmail(user.email) }}
                        </small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4">
                    <span class="badge border py-1.5 px-3 rounded-pill shadow-sm" 
                          :style="getBadgeStyle(getCalculatedTier(user)?.name)">
                      <i class="bi bi-star-fill me-1" v-if="getCalculatedTier(user)"></i> {{ getCalculatedTier(user)?.name || 'Mặc định (Member)' }}
                    </span>
                  </td>
                  
                  <td class="px-4 text-muted dark:text-gray-400 small text-truncate">
                    <div class="mb-1"><i class="bi bi-telephone text-urban me-1"></i>{{ user.phone || 'N/A' }}</div>
                    <div><i class="bi bi-geo-alt text-urban me-1"></i>{{ user.addresses_count || 0 }} địa chỉ lưu</div>
                  </td>

                  <td class="px-4 text-center">
                    <span v-if="user.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200 bg-white" 
                              style="width: 110px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(user.localStatus || user.status)"
                              v-model="user.localStatus"
                              @change="checkStatusChange(user)"
                              :disabled="user.isUpdatingStatus">
                        <option value="active">Hoạt động</option>
                        <option value="locked">Bị Khóa</option>
                      </select>
                      
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="user.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;" role="status"></div>
                        <template v-else-if="user.isStatusChanged">
                          <button @click="saveUserStatus(user)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(user)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border bg-white" title="Xem nhanh" @click="openQuickView(user)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!user.deleted_at">
                        <router-link :to="{ name: 'admin-users-edit', params: { id: user.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border bg-white" title="Chỉnh sửa & Địa chỉ">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border bg-white" @click="confirmDelete(user.id, user.full_name)" title="Xóa tài khoản">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border bg-white" @click="restoreUser(user.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE: CARD VIEW -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="displayUsers.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="user in displayUsers" :key="user.id" 
                   class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]" 
                   :class="{'opacity-75': user.deleted_at || user.status === 'locked'}"
                   :style="getRowStyle(user)">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center mb-3">
                    
                    <div class="position-relative flex-shrink-0 avatar-wrapper me-3" 
                         :class="{'vip-glow': isTopTier(getCalculatedTier(user)?.name)}" 
                         :style="{'--tier-color': getTierColor(getCalculatedTier(user)?.name)}">
                      <img :src="getImageUrl(user.avatar_url)" @error="handleImageError" 
                           class="rounded-circle object-fit-cover shadow-sm bg-white dark:bg-[#212529]" 
                           :style="getAvatarStyle(getCalculatedTier(user)?.name)"
                           style="width: 50px; height: 50px; position: relative; z-index: 2;">
                    </div>

                    <div class="overflow-hidden w-100">
                      <h6 class="mb-0 fw-bold dark:text-gray-200 text-truncate">{{ user.full_name }}</h6>
                      <small class="text-muted dark:text-gray-400 d-block text-truncate font-monospace mt-1"><i class="bi bi-envelope me-1"></i> {{ maskEmail(user.email) }}</small>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-center mb-3 border-top dark:border-gray-700 pt-3 gap-2">
                     <span class="badge border py-1 px-3 rounded-pill text-truncate shadow-sm" style="max-width: 55%;" 
                           :style="getBadgeStyle(getCalculatedTier(user)?.name)">
                       {{ getCalculatedTier(user)?.name || 'Thành viên' }}
                     </span>
                     
                     <span v-if="user.deleted_at" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                     <span v-else-if="user.status === 'active'" class="text-success small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Hoạt động</span>
                     <span v-else class="text-warning small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Đang khóa</span>
                  </div>

                  <div class="d-flex gap-2">
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border flex-grow-1 shadow-sm bg-white" @click="openQuickView(user)"><i class="bi bi-eye"></i></button>
                    <template v-if="!user.deleted_at">
                      <router-link :to="{ name: 'admin-users-edit', params: { id: user.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm bg-white"><i class="bi bi-pencil-square"></i></router-link>
                      <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border flex-grow-1 shadow-sm bg-white" @click="confirmDelete(user.id, user.full_name)"><i class="bi bi-trash"></i></button>
                    </template>
                    <template v-else>
                      <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold shadow-sm bg-white" @click="restoreUser(user.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
        <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedUsers.length) }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage--"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
              <button class="page-link dark:border-gray-600" :class="currentPage === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="currentPage = page">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="currentPage++"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- POPUP QUICK VIEW CHUẨN ZYRO ĐÃ UPDATE SKELETON -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-person-vcard text-urban me-2"></i>Hồ Sơ Khách Hàng</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4">
            
            <!-- ĐÃ BỔ SUNG: KHUNG SKELETON LÚC CHỜ API DỮ LIỆU -->
            <div v-if="isQuickViewLoading" class="row pe-none animation-fade-in">
               <div class="col-md-5 text-center border-end dark:border-gray-700 mb-4 mb-md-0 mt-3">
                  <div class="shimmer rounded-circle mx-auto mb-3" style="width: 140px; height: 140px;"></div>
                  <div class="shimmer rounded mx-auto mb-2" style="width: 150px; height: 28px;"></div>
                  <div class="shimmer rounded-pill mx-auto mt-2" style="width: 100px; height: 30px;"></div>
               </div>
               <div class="col-md-7">
                  <div class="bg-light dark:bg-[#212529] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 h-100 d-flex flex-column gap-4">
                     <div v-for="i in 5" :key="i" class="d-flex justify-content-between border-bottom dark:border-gray-700 pb-3">
                        <div class="shimmer rounded" style="width: 100px; height: 20px;"></div>
                        <div class="shimmer rounded" style="width: 150px; height: 20px;"></div>
                     </div>
                     <div class="mt-auto pt-3">
                        <div class="shimmer rounded-pill w-100" style="height: 45px;"></div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- NỘI DUNG CHÍNH (Đã Tải Xong) -->
            <div v-else-if="selectedUser" class="row animation-fade-in">
              <div class="col-md-5 text-center border-end dark:border-gray-700 mb-4 mb-md-0 mt-3">
                <div class="position-relative d-inline-block mx-auto mb-3 avatar-wrapper" :class="{'vip-glow': isTopTier(getCalculatedTier(selectedUser)?.name)}" :style="{'--tier-color': getTierColor(getCalculatedTier(selectedUser)?.name)}">
                   <img :src="getImageUrl(selectedUser.avatar_url)" @error="handleImageError" 
                        class="rounded-circle object-fit-cover bg-white dark:bg-[#212529] position-relative" 
                        :style="getLargeAvatarStyle(getCalculatedTier(selectedUser)?.name)"
                        style="width: 140px; height: 140px; z-index: 2;">
                   <span class="position-absolute bottom-0 end-0 p-2 border border-light dark:border-gray-800 rounded-circle shadow-sm" :class="selectedUser.deleted_at ? 'bg-secondary' : (selectedUser.status === 'active' ? 'bg-success' : 'bg-warning')" style="width: 18px; height: 18px; right: 8px !important; bottom: 8px !important; z-index: 3;"></span>
                </div>
                <h5 class="fw-bold mb-1 dark:text-white fs-4 font-sans-vn">{{ selectedUser.full_name }}</h5>
                <span class="badge rounded-pill mt-2 px-3 py-2 shadow-sm font-sans-vn text-uppercase tracking-wide" 
                      :style="getBadgeStyle(getCalculatedTier(selectedUser)?.name)">
                  <i class="bi bi-star-fill me-1" v-if="getCalculatedTier(selectedUser)"></i> {{ getCalculatedTier(selectedUser)?.name || 'Mặc định (Member)' }}
                </span>
              </div>
              
              <div class="col-md-7">
                <div class="bg-light dark:bg-[#212529] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100 d-flex flex-column">
                  
                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700">
                    <span class="text-muted dark:text-gray-400 fw-semibold d-block mb-2"><i class="bi bi-envelope text-urban me-1"></i>Email bảo mật:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold font-monospace fs-6">{{ maskEmail(selectedUser.email) }}</span>
                  </div>
                  
                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-telephone text-urban me-1"></i>Số điện thoại:</span>
                    <div class="d-flex align-items-center gap-2">
                      <span class="text-dark dark:text-gray-200 fw-bold">{{ selectedUser.phone || 'Chưa cập nhật' }}</span>
                      <button v-if="selectedUser.phone" @click="copyPhone(selectedUser.phone)" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 border rounded py-0 px-2 shadow-sm" title="Sao chép SĐT">
                        <i class="bi bi-copy text-urban small"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-gender-ambiguous text-urban me-1"></i>Giới tính / Tuổi:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ selectedUser.gender || 'N/A' }} <span v-if="selectedUser.birthday">({{ calculateAge(selectedUser.birthday) }}T)</span></span>
                  </div>

                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-rulers text-urban me-1"></i>Chỉ số cơ thể (H/W):</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">
                      <span v-if="selectedUser.height_cm || selectedUser.weight_kg">
                        {{ selectedUser.height_cm ? selectedUser.height_cm + ' cm' : '--' }} / {{ selectedUser.weight_kg ? selectedUser.weight_kg + ' kg' : '--' }}
                      </span>
                      <span v-else class="text-muted fst-italic fw-normal">Chưa cập nhật</span>
                    </span>
                  </div>

                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-calendar-date text-urban me-1"></i>Ngày tham gia:</span>
                    <span class="text-dark dark:text-gray-300 fw-bold">{{ formatDateTime(selectedUser.created_at) }}</span>
                  </div>

                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-bold"><i class="bi bi-wallet2 text-urban me-1"></i>Tổng chi tiêu:</span>
                    <span class="text-danger fw-bold fs-6">{{ formatCurrency(selectedUser.total_spent || 0) }}</span>
                  </div>

                  <div class="mb-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-journal-text text-urban me-1"></i>Sổ địa chỉ ({{ selectedUser.addresses?.length || 0 }}):</span>
                    </div>
                    
                    <div v-if="!selectedUser.addresses || selectedUser.addresses.length === 0" class="text-muted small fst-italic bg-white dark:bg-[#1a2533] p-2 rounded border dark:border-gray-700">
                      Chưa có địa chỉ nào được lưu.
                    </div>
                    
                    <div v-else class="custom-scrollbar-y pe-2" style="max-height: 120px; overflow-y: auto;">
                      <div v-for="addr in selectedUser.addresses" :key="addr.id" class="p-2 mb-2 border dark:border-gray-600 rounded bg-white dark:bg-[#1a2533] position-relative">
                        <span v-if="addr.is_default" class="badge bg-urban text-white position-absolute top-0 end-0 m-1 shadow-sm" style="font-size: 0.6rem;">Mặc định</span>
                        <div class="fw-bold text-dark dark:text-white small mb-1 pe-5">{{ addr.customer_name }} - {{ addr.customer_phone }}</div>
                        <div class="text-muted dark:text-gray-400 small" style="font-size: 0.75rem;">
                          {{ addr.shipping_address }}, {{ [addr.ward, addr.district, addr.city].filter(Boolean).join(', ') }}
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mt-4 pt-3 border-top dark:border-gray-700" v-if="!selectedUser.deleted_at">
                    <button type="button" @click="goToEditUser(selectedUser.id)" class="btn btn-urban rounded-pill px-4 py-2.5 fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center transition-all">
                      <i class="bi bi-pencil-square me-2 fs-5"></i> Thiết lập & Cập nhật thêm
                    </button>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/avatar1.png'; 

const route = useRoute();
const router = useRouter();

const users = ref([]);
const systemModules = ref([]);
const systemTiers = ref([]); 
const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 
const isRefreshing = ref(false);
const isQuickViewLoading = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');
const currentPage = ref(1);
const itemsPerPage = 10;

const selectedUser = ref(null);
let quickViewModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

// ===============================================
// THUẬT TOÁN TỰ PHÂN HẠNG (AUTO-TIERING FALLBACK)
// ===============================================
const getCalculatedTier = (user) => {
  if (user && user.tier) return user.tier;
  
  if (user && user.total_spent !== undefined && systemTiers.value.length > 0) {
     let matchedTier = null;
     // Ép kiểu Number để đảm bảo so sánh chuỗi tiền chính xác
     const spent = Number(user.total_spent) || 0;
     for (let i = 0; i < systemTiers.value.length; i++) {
        if (spent >= Number(systemTiers.value[i].min_spent)) {
           matchedTier = systemTiers.value[i];
        }
     }
     return matchedTier;
  }
  
  return null; 
};

const getTierColor = (name) => {
  if (!name) return '#9ca3af'; 
  const lName = name.toLowerCase();
  
  if (lName.includes('khởi đầu')) return '#9ca3af';
  if (lName.includes('fan cứng') || lName.includes('đồng')) return '#3b82f6'; 
  if (lName.includes('bạc') || lName.includes('silver')) return '#94a3b8'; 
  if (lName.includes('vàng') || lName.includes('gold')) return '#eab308'; 
  if (lName.includes('kim cương') || lName.includes('diamond')) return '#06b6d4'; 
  if (lName.includes('bạch kim') || lName.includes('platinum')) return '#8b5cf6'; 

  return '#547792'; 
};

// Hàm định nghĩa xem có vẽ Hào quang hay không
const isTopTier = (name) => {
  if (!name) return false;
  const n = name.toLowerCase();
  return !n.includes('khởi đầu') && !n.includes('mới') && !n.includes('member');
};

// ĐÃ THÊM: Tạo Background màu nhạt tương ứng với Hạng của User
const getRowStyle = (user) => {
  if (user.deleted_at || user.status === 'locked') return {}; 
  
  const tier = getCalculatedTier(user);
  const color = getTierColor(tier?.name);
  
  // Nếu là màu mặc định (xám/ghi) thì không tô nền để bảng đỡ bị xỉn màu
  if (color === '#9ca3af' || color === '#547792') return {};

  // Chuyển Hex sang RGBA với Opacity 8% cho rõ hơn 1 xíu
  let r = parseInt(color.slice(1, 3), 16);
  let g = parseInt(color.slice(3, 5), 16);
  let b = parseInt(color.slice(5, 7), 16);
  
  return { backgroundColor: `rgba(${r}, ${g}, ${b}, 0.08)` };
};

const getAvatarStyle = (tierName) => {
  const color = getTierColor(tierName);
  return {
    border: `2px solid ${color}`,
    padding: '2px', 
  };
};

const getLargeAvatarStyle = (tierName) => {
  const color = getTierColor(tierName);
  return {
    border: `4px solid ${color}`,
    padding: '4px',
  };
};

const getBadgeStyle = (tierName) => {
  const color = getTierColor(tierName);
  const getContrast = (hexcolor) => {
      hexcolor = hexcolor.replace("#", "");
      if (hexcolor.length === 3) hexcolor = hexcolor.split('').map(c => c + c).join('');
      const r = parseInt(hexcolor.substr(0,2),16);
      const g = parseInt(hexcolor.substr(2,2),16);
      const b = parseInt(hexcolor.substr(4,2),16);
      const yiq = ((r*299)+(g*587)+(b*114))/1000;
      return (yiq >= 128) ? '#111111' : '#ffffff';
  };
  
  if(!tierName) return { backgroundColor: '#f8f9fa', color: '#6c757d', border: '1px solid #dee2e6' };
  
  return { 
    backgroundColor: color, 
    color: getContrast(color),
    border: `1px solid ${color}` 
  };
};

const getLevelColor = (level) => {
  if(!level) return 'bg-secondary';
  const l = parseInt(level);
  switch (l) {
    case 1: return 'bg-danger text-white border-danger shadow-sm';         
    case 2: return 'bg-warning text-dark border-warning';                  
    case 3: return 'bg-info text-dark border-info';                        
    case 4: return 'bg-primary bg-opacity-10 text-primary border-primary'; 
    case 5: return 'bg-success bg-opacity-10 text-success border-success'; 
    default: return 'bg-light dark:bg-gray-700 text-secondary dark:text-gray-300 border-secondary'; 
  }
};

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')}`;
};

const calculateAge = (birthday) => {
    if(!birthday) return '';
    const ageDifMs = Date.now() - new Date(birthday).getTime();
    const ageDate = new Date(ageDifMs);
    return Math.abs(ageDate.getUTCFullYear() - 1970);
};

const maskEmail = (email) => {
  if (!email) return '';
  const parts = email.split('@');
  if (parts.length !== 2) return email;
  const name = parts[0];
  const domain = parts[1];
  if (name.length <= 2) return name.charAt(0) + '***@' + domain;
  return name.substring(0, 3) + '***@' + domain;
};

const copyPhone = (phone) => {
  if(!phone) return;
  const copyToClipboard = async () => {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(phone);
      } else {
        const textArea = document.createElement("textarea");
        textArea.value = phone;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        document.execCommand('copy');
        textArea.remove();
      }
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã sao chép SĐT', showConfirmButton: false, timer: 1500 });
    } catch (err) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Sao chép thất bại', showConfirmButton: false, timer: 1500 });
    }
  };
  copyToClipboard();
};

const fetchData = async (isSilent = false) => {
  if (isSilent) isRefreshing.value = true;
  else if (!isFirstLoad.value) isLoading.value = true;
  
  try {
    const [resUsers, resModules, resTiers] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/users', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/tiers', { headers: getHeaders() }).catch(() => ({data: {data: []}}))
    ]);

    const rawData = Array.isArray(resUsers.data.data) ? resUsers.data.data : [];
    
    users.value = rawData.map(u => ({
      ...u, localStatus: u.status, isStatusChanged: false, isUpdatingStatus: false
    }));
    
    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_users'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;

    systemTiers.value = Array.isArray(resTiers.data.data) ? resTiers.data.data : [];
    // ĐẢM BẢO CHUỖI ĐƯỢC CHUYỂN SANG NUMBER ĐỂ SORT ĐÚNG
    systemTiers.value.sort((a, b) => Number(a.min_spent) - Number(b.min_spent));
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
    isRefreshing.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.users')
      .listen('.UserEvent', () => {
        fetchData(true); 
      });
  }
};

const getStatusSelectClass = (status) => {
  const map = { 
    'active': 'text-success border-success bg-success bg-opacity-10', 
    'locked': 'text-warning border-warning bg-warning bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const checkStatusChange = (u) => { u.isStatusChanged = (u.localStatus !== u.status); };
const cancelStatusChange = (u) => { u.localStatus = u.status; u.isStatusChanged = false; };

const saveUserStatus = async (u) => {
  u.isUpdatingStatus = true;
  const formData = new FormData();
  formData.append('_method', 'PUT'); 
  formData.append('full_name', u.full_name);
  formData.append('email', u.email);
  formData.append('status', u.localStatus); 

  try {
    await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${u.id}`, formData, { headers: getHeaders() });
    u.status = u.localStatus; 
    u.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã cập nhật trạng thái', showConfirmButton: false, timer: 1500 });
  } catch (error) { 
    cancelStatusChange(u); 
    Swal.fire('Lỗi', 'Không thể cập nhật trạng thái', 'error');
  } finally { 
    u.isUpdatingStatus = false; 
  }
};

const switchTab = (tabId) => { 
  activeTab.value = tabId; 
  currentPage.value = 1; 
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const openQuickView = async (u) => {
  selectedUser.value = null; // Reset
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
  
  isQuickViewLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${u.id}`, { headers: getHeaders() });
    selectedUser.value = res.data.data;
  } catch (err) {
    console.error("Lỗi tải chi tiết KH", err);
  } finally {
    isQuickViewLoading.value = false;
  }
};

const goToEditUser = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = '';
    document.body.style = '';
    router.push({ name: 'admin-users-edit', params: { id } });
  }, 300);
};

const processedUsers = computed(() => {
  let result = users.value;
  if (activeTab.value === 'deleted') { result = result.filter(u => u.deleted_at); } 
  else {
    result = result.filter(u => !u.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(u => u.status === activeTab.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(u => (u.full_name?.toLowerCase().includes(q)) || (u.email?.toLowerCase().includes(q)) || (u.phone?.includes(q)));
  }
  return result;
});

const totalPages = computed(() => Math.ceil(processedUsers.value.length / itemsPerPage) || 1);

const displayUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage; 
  return processedUsers.value.slice(start, start + itemsPerPage);
});

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Khóa & Đưa vào thùng rác?', text: `Khách hàng "${name}" sẽ bị vô hiệu hóa!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        await fetchData(); 
      } catch(e) {
        isLoading.value = false;
        Swal.fire('Lỗi', e.response?.data?.message || 'Không thể xóa', 'error');
      }
    }
  });
};

const restoreUser = (id) => {
  Swal.fire({ title: 'Khôi phục tài khoản?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover, #547792)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/users/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
        await fetchData(); 
      } catch(e) {
        isLoading.value = false;
        Swal.fire('Lỗi', e.response?.data?.message || 'Không thể khôi phục', 'error');
      }
    }
  });
};

onMounted(() => {
  fetchData();
  setupRealtime();
});

onUnmounted(() => {
  if (window.Echo) window.Echo.leave('admin.users');
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* AVATAR GLOW EFFECT DÀNH RIÊNG CHO LIST VÀ QUICKVIEW */
.avatar-wrapper { position: relative; border-radius: 50%; display: inline-flex; }
.vip-glow::before {
  content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px;
  border-radius: 50%; z-index: 1;
  background: var(--tier-color);
  filter: blur(8px);
  animation: pulse-glow 2s infinite alternate;
}
@keyframes pulse-glow {
  0% { opacity: 0.5; filter: blur(5px); transform: scale(0.98); }
  100% { opacity: 1; filter: blur(12px); transform: scale(1.05); }
}

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* SKELETON CSS */
.shimmer {
  background: #e2e8f0;
  background-image: linear-gradient(to right, #e2e8f0 0%, #f1f5f9 20%, #e2e8f0 40%, #e2e8f0 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
</style>