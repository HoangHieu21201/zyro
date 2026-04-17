<template>
  <div class="voucher-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải danh sách khuyến mãi...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Mã Khuyến Mãi (Vouchers)</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-vouchers-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all">
            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Mã Mới
          </router-link>
        </div>
      </div>

      <!-- TABS PHÂN LOẠI -->
      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ vouchers.filter(c => !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'active' }" @click.prevent="switchTab('active')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đang hoạt động
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'active'}">{{ vouchers.filter(c => c.status === 'active' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'expired' }" @click.prevent="switchTab('expired')">
              <i class="bi bi-clock-history me-2 text-secondary"></i> Đã hết hạn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'expired'}">{{ vouchers.filter(c => c.status === 'expired' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-warning"></i> Đang ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'hidden'}">{{ vouchers.filter(c => c.status === 'hidden' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ vouchers.filter(c => c.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- BẢNG DỮ LIỆU & BỘ LỌC -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-3 px-4 rounded-top-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi bi-ticket-perforated me-2 text-urban fs-5"></i> Kho Voucher
              <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
            
            <div class="search-box position-relative" style="width: 280px; max-width: 100%;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm mã code, tên voucher...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>

          <!-- ĐÃ BỔ SUNG: BỘ LỌC THEO PHẠM VI ÁP DỤNG -->
          <div class="d-flex align-items-center flex-wrap gap-2 bg-light dark:bg-[#212529] p-2 rounded-3 border dark:border-gray-700">
            <div class="d-flex align-items-center small fw-semibold text-muted me-2"><i class="bi bi-funnel me-1"></i> Bộ lọc:</div>
            
            <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 180px;" v-model="filterApplyType" @change="currentPage = 1">
              <option value="">Tất cả phạm vi</option>
              <option value="all">Toàn bộ gian hàng</option>
              <option value="specific_products">Sản phẩm chỉ định</option>
              <option value="specific_categories">Danh mục chỉ định</option>
            </select>
            
            <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 shadow-sm" @click="clearFilters" v-if="filterApplyType">
              <i class="bi bi-x-circle"></i> Xóa lọc
            </button>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Mã / Tên</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Mức giảm</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Thời hạn & Lượt dùng</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 20%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="displayVouchers.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="v in displayVouchers" :key="v.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': v.deleted_at || v.status === 'hidden' || v.status === 'expired'}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light dark:bg-[#2b3035] rounded p-2 me-3 text-center border dark:border-gray-600 shadow-sm flex-shrink-0" style="width: 50px; height: 50px;">
                         <i class="bi bi-ticket-detailed text-urban fs-4"></i>
                      </div>
                      <div class="overflow-hidden">
                        <h6 class="mb-1 fw-bold text-urban text-truncate font-monospace" :title="v.code">{{ v.code }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block text-truncate" :title="v.name">{{ v.name }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4">
                    <div class="fw-bold text-danger dark:text-red-400 mb-1">
                       Giảm {{ v.discount_type === 'percent' ? v.discount_value + '%' : formatCurrency(v.discount_value) }}
                    </div>
                    <div class="text-muted dark:text-gray-400 small text-truncate">
                       Đơn tối thiểu: {{ formatCurrency(v.min_spend) }}
                    </div>
                  </td>
                  
                  <td class="px-4 small">
                    <!-- ĐÃ FIX HIỂN THỊ THỜI GIAN THEO LOGIC MỚI -->
                    <div class="mb-1 text-dark dark:text-gray-200">
                       HSD: <strong :class="v.end_time ? '' : 'text-success'">{{ formatDateTime(v.end_time) }}</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                       <div class="progress flex-grow-1 bg-light dark:bg-[#2b3035]" style="height: 6px;">
                          <div class="progress-bar bg-urban" :style="{ width: v.usage_limit ? (v.usage_count / v.usage_limit * 100) + '%' : (v.usage_count > 0 ? '100%' : '0%') }"></div>
                       </div>
                       <span class="text-muted fw-bold" style="font-size: 0.7rem;">{{ v.usage_count }} / {{ v.usage_limit || '∞' }}</span>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <span v-if="v.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200" 
                              style="width: 110px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(v.localStatus || v.status)"
                              v-model="v.localStatus"
                              @change="checkStatusChange(v)"
                              :disabled="v.isUpdatingStatus">
                        <option value="active">Hoạt động</option>
                        <option value="hidden">Đang ẩn</option>
                        <option value="expired">Hết hạn</option>
                      </select>
                      
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="v.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;" role="status"></div>
                        <template v-else-if="v.isStatusChanged">
                          <button @click="saveStatus(v)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(v)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border" title="Xem nhanh" @click="openQuickView(v)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!v.deleted_at">
                        <router-link :to="{ name: 'admin-vouchers-edit', params: { id: v.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Sửa">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(v.id, v.code)" title="Đưa vào thùng rác">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreVoucher(v.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="displayVouchers.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else v-for="v in displayVouchers" :key="v.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': v.deleted_at || v.status === 'hidden' || v.status === 'expired'}">
              <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3 border-bottom dark:border-gray-700 pb-3">
                  <div class="bg-light dark:bg-[#2b3035] rounded p-2 me-3 text-center border dark:border-gray-600 shadow-sm flex-shrink-0" style="width: 45px; height: 45px;">
                     <i class="bi bi-ticket-detailed text-urban fs-5"></i>
                  </div>
                  <div class="overflow-hidden w-100">
                    <h6 class="mb-1 fw-bold text-urban text-truncate font-monospace">{{ v.code }}</h6>
                    <div class="text-muted dark:text-gray-400 small text-truncate">{{ v.name }}</div>
                  </div>
                </div>
                
                <div class="mb-3 small">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Mức giảm:</span>
                    <strong class="text-danger">{{ v.discount_type === 'percent' ? v.discount_value + '%' : formatCurrency(v.discount_value) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Hết hạn:</span>
                    <!-- ĐÃ FIX MOBILE -->
                    <span class="fw-bold" :class="v.end_time ? '' : 'text-success'">{{ formatDateTime(v.end_time) }}</span>
                  </div>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border shadow-sm" @click="openQuickView(v)"><i class="bi bi-eye"></i></button>
                  <template v-if="!v.deleted_at">
                    <router-link :to="{ name: 'admin-vouchers-edit', params: { id: v.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm"><i class="bi bi-pencil-square"></i></router-link>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border shadow-sm" style="width: 50px;" @click="confirmDelete(v.id, v.code)"><i class="bi bi-trash"></i></button>
                  </template>
                  <template v-else>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold shadow-sm" @click="restoreVoucher(v.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
        <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedVouchers.length) }}</span>
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

    <!-- ========================================================= -->
    <!-- POPUP QUICK VIEW (ĐÃ ĐƯỢC NÂNG CẤP HIỂN THỊ PHẠM VI)     -->
    <!-- ========================================================= -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-ticket-perforated text-urban me-2"></i>Chi Tiết Voucher</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4" v-if="selectedVoucher">
            
            <div class="text-center mb-4 pb-3 border-bottom dark:border-gray-700">
               <div class="d-inline-block border border-dashed border-2 border-urban rounded-3 px-4 py-2 mb-2 bg-light dark:bg-[#212529]">
                  <h3 class="fw-bold text-urban font-monospace m-0" style="letter-spacing: 2px;">{{ selectedVoucher.code }}</h3>
               </div>
               <p class="text-muted dark:text-gray-400 mb-2">{{ selectedVoucher.name }}</p>
               <span class="badge rounded-pill shadow-sm px-3" :class="selectedVoucher.is_public ? 'bg-success text-white' : 'bg-dark text-white border border-secondary'">
                 <i class="bi" :class="selectedVoucher.is_public ? 'bi-globe-americas' : 'bi-lock-fill'"></i>
                 {{ selectedVoucher.is_public ? ' Mã Công Khai' : ' Mã Bí Mật' }}
               </span>
            </div>

            <div class="row g-3">
              <!-- Box Giá Trị -->
              <div class="col-md-6">
                <div class="bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100">
                  <div class="row mb-2">
                    <div class="col-5 text-muted dark:text-gray-400">Mức giảm:</div>
                    <div class="col-7 fw-bold text-danger text-end">{{ selectedVoucher.discount_type === 'percent' ? selectedVoucher.discount_value + '%' : formatCurrency(selectedVoucher.discount_value) }}</div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-5 text-muted dark:text-gray-400">Giảm tối đa:</div>
                    <div class="col-7 fw-bold text-end dark:text-gray-200">{{ selectedVoucher.max_discount_amount ? formatCurrency(selectedVoucher.max_discount_amount) : 'Không giới hạn' }}</div>
                  </div>
                  <div class="row mb-2 border-top dark:border-gray-600 pt-2 mt-2">
                    <div class="col-5 text-muted dark:text-gray-400">Đơn tối thiểu:</div>
                    <div class="col-7 fw-bold text-end text-urban">{{ formatCurrency(selectedVoucher.min_spend) }}</div>
                  </div>
                </div>
              </div>

              <!-- Box Thời gian & Lượt -->
              <div class="col-md-6">
                <div class="bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100">
                  <div class="row mb-2">
                    <div class="col-5 text-muted dark:text-gray-400">Bắt đầu:</div>
                    <div class="col-7 fw-bold text-end dark:text-gray-200">{{ formatDateTime(selectedVoucher.start_time) }}</div>
                  </div>
                  <div class="row mb-2">
                    <div class="col-5 text-muted dark:text-gray-400">Kết thúc:</div>
                    <div class="col-7 fw-bold text-end text-danger" :class="selectedVoucher.end_time ? '' : 'text-success'">{{ formatDateTime(selectedVoucher.end_time) }}</div>
                  </div>
                  <div class="row mb-2 border-top dark:border-gray-600 pt-2 mt-2">
                    <div class="col-5 text-muted dark:text-gray-400">Đã dùng:</div>
                    <div class="col-7 fw-bold text-end dark:text-gray-200">{{ selectedVoucher.usage_count }} / {{ selectedVoucher.usage_limit || 'Vô cực' }}</div>
                  </div>
                  <div class="row">
                    <div class="col-8 text-muted dark:text-gray-400">Giới hạn / 1 Khách:</div>
                    <div class="col-4 fw-bold text-end dark:text-gray-200">{{ selectedVoucher.usage_limit_per_user }} lượt</div>
                  </div>
                </div>
              </div>
              
              <!-- ĐÃ BỔ SUNG: Box Phạm vi Áp Dụng Rõ Ràng -->
              <div class="col-12">
                <div class="bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small">
                  <div class="text-muted dark:text-gray-400 fw-bold mb-2 text-uppercase" style="letter-spacing: 0.5px;">Phạm vi áp dụng:</div>
                  
                  <div v-if="selectedVoucher.apply_type === 'all'" class="d-flex align-items-center p-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 text-success fw-bold">
                    <i class="bi bi-shop me-2 fs-5"></i> Áp dụng cho Toàn bộ gian hàng
                  </div>
                  
                  <template v-else>
                    <span class="badge bg-urban mb-2 px-3 py-2 fs-6 shadow-sm">
                      <i class="bi" :class="selectedVoucher.apply_type === 'specific_products' ? 'bi-box-seam' : 'bi-tags'"></i>
                      {{ selectedVoucher.apply_type === 'specific_products' ? 'Sản phẩm chỉ định' : 'Danh mục chỉ định' }}
                    </span>
                    
                    <div class="d-flex flex-wrap gap-2 mt-2 p-2 bg-white dark:bg-[#1a2533] rounded border dark:border-gray-600 custom-scrollbar-y" style="max-height: 180px; overflow-y: auto;">
                      
                      <div v-for="item in selectedVoucher.mappedConditions" :key="item.id" 
                           class="badge bg-light text-dark dark:bg-[#2b3035] dark:text-gray-200 border border-secondary-subtle dark:border-gray-600 d-flex align-items-center p-1 pe-3 shadow-sm transition-all hover-urban">
                        <div class="bg-white rounded p-1 shadow-sm border dark:border-gray-600 me-2" style="width: 32px; height: 32px;">
                          <img :src="getImageUrl(item.thumbnail_image || item.thumbnail)" @error="handleImageError" class="w-100 h-100 object-fit-cover rounded">
                        </div>
                        <div class="text-start">
                          <span class="d-block fw-bold text-truncate" style="max-width: 200px;">{{ item.name }}</span>
                          <span v-if="item.base_price" class="text-danger fw-bold" style="font-size: 0.7rem;">{{ formatCurrency(item.base_price) }}</span>
                        </div>
                      </div>
                      
                      <span v-if="!selectedVoucher.mappedConditions || selectedVoucher.mappedConditions.length === 0" class="text-muted small fst-italic w-100 text-center py-3">
                        Không tìm thấy dữ liệu (Danh mục/Sản phẩm này có thể đã bị xóa hoặc ẩn).
                      </span>
                    </div>
                  </template>
                </div>
              </div>
            </div>

          </div>
          <div v-else class="p-5 text-center">
            <span class="spinner-border text-urban mb-2"></span>
            <p class="text-muted">Đang lấy dữ liệu chi tiết...</p>
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
import defaultImage from '@/assets/images/defaults/placeholder.png'; // Add default placeholder

const route = useRoute();
const router = useRouter();

const vouchers = ref([]);
const systemModules = ref([]);
const allCategories = ref([]); // Đã thêm để mapping
const allProducts = ref([]); // Đã thêm để mapping

const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 
const isRefreshing = ref(false);

const searchQuery = ref('');
const activeTab = ref('all');
const filterApplyType = ref(''); // Bộ lọc mới theo Phạm vi

const currentPage = ref(1);
const itemsPerPage = 10;

const selectedVoucher = ref(null);
let quickViewModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

// ĐÃ CẬP NHẬT LOGIC THỜI GIAN
const formatDateTime = (dateString) => {
  if(!dateString) return 'Vô thời hạn'; // Trả về text đẹp khi null
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary bg-opacity-10 text-primary', 5: 'bg-success bg-opacity-10 text-success' };
  return map[level] || 'bg-secondary';
};

// Nâng cấp Fetch Data để lấy thêm ds Product và Category dùng cho Mapping
const fetchData = async (isSilent = false) => {
  if (isSilent) isRefreshing.value = true;
  else if (!isFirstLoad.value) isLoading.value = true;
  
  try {
    const [resData, resModules, resCats, resProds] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/vouchers', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/products?status=published', { headers: getHeaders() })
    ]);

    const payloadData = resData.data.data;
    const rawData = Array.isArray(payloadData?.data) ? payloadData.data : (Array.isArray(payloadData) ? payloadData : []);
    vouchers.value = rawData.map(v => ({
      ...v, localStatus: v.status, isStatusChanged: false, isUpdatingStatus: false
    }));
    
    // Lưu tạm Categories & Products vào bộ nhớ để QuickView xài
    allCategories.value = Array.isArray(resCats.data?.data) ? resCats.data.data : [];
    const prods = resProds.data?.data;
    allProducts.value = Array.isArray(prods?.data) ? prods.data : (Array.isArray(prods) ? prods : []);

    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_vouchers'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;
    
  } catch (err) { console.error('Lỗi khi tải dữ liệu', err); } 
  finally { isLoading.value = false; isFirstLoad.value = false; isRefreshing.value = false; }
};

const setupRealtime = () => {
  if (window.Echo) window.Echo.private('admin.vouchers').listen('.VoucherEvent', () => { fetchData(true); });
};

const getStatusSelectClass = (status) => {
  const map = { 'active': 'text-success border-success bg-success bg-opacity-10', 'hidden': 'text-warning border-warning bg-warning bg-opacity-10', 'expired': 'text-secondary border-secondary bg-secondary bg-opacity-10' }; 
  return map[status] || 'bg-light text-secondary'; 
};

const checkStatusChange = (p) => { p.isStatusChanged = (p.localStatus !== p.status); };
const cancelStatusChange = (p) => { p.localStatus = p.status; p.isStatusChanged = false; };

const saveStatus = async (p) => {
  p.isUpdatingStatus = true;
  try {
    await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/vouchers/${p.id}/status`, { status: p.localStatus }, { headers: getHeaders() });
    p.status = p.localStatus; p.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật thành công', showConfirmButton: false, timer: 1500 });
  } catch (error) { 
    cancelStatusChange(p); 
    Swal.fire('Lỗi', error.response?.data?.message || 'Lỗi cập nhật trạng thái', 'error');
  } finally { p.isUpdatingStatus = false; }
};

const switchTab = (tabId) => { activeTab.value = tabId; currentPage.value = 1; };

const clearFilters = () => {
    filterApplyType.value = '';
    currentPage.value = 1;
};

// ĐÃ CẬP NHẬT LOGIC QUICK VIEW ĐỂ LOAD CHI TIẾT SẢN PHẨM/DANH MỤC
const openQuickView = (v) => {
  // Clone object để không ảnh hưởng dữ liệu thật
  selectedVoucher.value = JSON.parse(JSON.stringify(v));
  
  // Mapping Conditions (ID -> Hình ảnh, Tên)
  if (v.apply_type === 'specific_categories') {
     selectedVoucher.value.mappedConditions = allCategories.value.filter(c => v.conditions?.includes(c.id));
  } else if (v.apply_type === 'specific_products') {
     selectedVoucher.value.mappedConditions = allProducts.value.filter(p => v.conditions?.includes(p.id));
  } else {
     selectedVoucher.value.mappedConditions = [];
  }

  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = ''; document.body.style = '';
});

const processedVouchers = computed(() => {
  let result = vouchers.value;
  if (activeTab.value === 'deleted') result = result.filter(c => c.deleted_at);
  else {
    result = result.filter(c => !c.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(c => c.status === activeTab.value);
  }
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(c => (c.name?.toLowerCase().includes(q)) || (c.code?.toLowerCase().includes(q)));
  }

  // Lọc theo Loại Áp Dụng
  if (filterApplyType.value) {
     result = result.filter(c => c.apply_type === filterApplyType.value);
  }

  return result;
});

const totalPages = computed(() => Math.ceil(processedVouchers.value.length / itemsPerPage) || 1);
const displayVouchers = computed(() => processedVouchers.value.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage));

const confirmDelete = (id, code) => {
  Swal.fire({ title: 'Đưa vào thùng rác?', text: `Mã "${code}" sẽ bị vô hiệu hóa!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/vouchers/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xóa', 'error'); }
    }
  });
};

const restoreVoucher = (id) => {
  Swal.fire({ title: 'Khôi phục?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/vouchers/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi khôi phục', 'error'); }
    }
  });
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.vouchers'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.hover-urban:hover { border-color: var(--color-c-hover, #547792) !important; transform: translateY(-2px); }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
</style>