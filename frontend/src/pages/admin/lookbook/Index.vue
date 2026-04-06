<!-- File: frontend/src/pages/admin/lookbook/Index.vue -->
<template>
  <div class="lookbook-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải bộ sưu tập...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản Lý Lookbook</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
          <router-link :to="{ name: 'admin-lookbooks-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all">
            <i class="bi bi-plus-circle-fill me-1"></i> Thêm Lookbook Mới
          </router-link>
        </div>
      </div>

      <div class="mb-4">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ lookbooks.filter(c => !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'published' }" @click.prevent="switchTab('published')">
              <i class="bi bi-eye-fill me-2 text-success"></i> Đang xuất bản
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'published'}">{{ lookbooks.filter(c => c.status === 'published' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'draft' }" @click.prevent="switchTab('draft')">
              <i class="bi bi-file-earmark-text me-2 text-secondary"></i> Bản nháp
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'draft'}">{{ lookbooks.filter(c => c.status === 'draft' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-warning"></i> Đang ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'hidden'}">{{ lookbooks.filter(c => c.status === 'hidden' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ lookbooks.filter(c => c.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-3 px-4 rounded-top-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
              <i class="bi bi-images me-2 text-urban fs-5"></i> Danh sách Bộ sưu tập (Lookbooks)
              <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
            </h6>
            <div class="search-box position-relative" style="width: 280px; max-width: 100%;">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên bộ sưu tập...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>

          <!-- BỘ LỌC ĐA CHIỀU CHO LOOKBOOK -->
          <div class="d-flex align-items-center flex-wrap gap-2 bg-light dark:bg-[#212529] p-2 rounded-3 border dark:border-gray-700">
            <div class="d-flex align-items-center small fw-semibold text-muted me-2"><i class="bi bi-funnel me-1"></i> Bộ lọc:</div>
            
            <select class="form-select form-select-sm border-0 bg-white dark:bg-[#1a2533] dark:text-white shadow-sm" style="width: 160px;" v-model="filterGender">
              <option value="">Tất cả Giới tính</option>
              <option value="Unisex">Unisex</option>
              <option value="Men">Nam (Men)</option>
              <option value="Women">Nữ (Women)</option>
              <option value="Kids">Trẻ em (Kids)</option>
            </select>
            
            <div class="input-group input-group-sm shadow-sm" style="width: 280px;">
              <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-muted"><i class="bi bi-calendar-range"></i></span>
              <input type="date" class="form-control border-0 bg-white dark:bg-[#1a2533] dark:text-white" v-model="filterDateFrom" title="Từ ngày">
              <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-muted">-</span>
              <input type="date" class="form-control border-0 bg-white dark:bg-[#1a2533] dark:text-white" v-model="filterDateTo" title="Đến ngày">
            </div>
            
            <button class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 shadow-sm" @click="clearFilters" v-if="hasActiveFilters">
              <i class="bi bi-x-circle"></i> Xóa lọc
            </button>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 35%;">Bộ sưu tập</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Chi tiết Set đồ</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 20%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 20%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="displayLookbooks.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="lb in displayLookbooks" :key="lb.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': lb.deleted_at || lb.status === 'hidden'}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="position-relative me-3 border shadow-sm rounded-3 overflow-hidden dark:border-gray-600 flex-shrink-0 cursor-zoom-in" style="width: 80px; height: 100px;" @click="openImageZoom(getImageUrl(lb.main_image))">
                         <img :src="getImageUrl(lb.main_image)" @error="handleImageError" class="w-100 h-100 object-fit-cover transition-transform hover-zoom">
                         <span class="badge bg-danger position-absolute bottom-0 end-0 m-1 px-1 py-0 shadow-sm" title="Số lượng ghim (sản phẩm)">
                            <i class="bi bi-pin-angle-fill" style="font-size: 0.6rem;"></i> {{ lb.items_count || 0 }}
                         </span>
                      </div>
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate" :title="lb.name">{{ lb.name }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace"><i class="bi bi-link-45deg"></i> {{ lb.slug }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4">
                    <div class="text-dark dark:text-gray-300 small mb-1"><span class="text-muted">Dành cho:</span> <strong class="text-urban">{{ lb.gender || 'Unisex' }}</strong></div>
                    <div class="text-dark dark:text-gray-300 small mb-1"><span class="text-muted">Ước tính cả Set:</span> <strong class="text-danger dark:text-red-400">{{ formatCurrency(lb.total_price_estimate) }}</strong></div>
                  </td>

                  <td class="px-4 text-center">
                    <span v-if="lb.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200" 
                              style="width: 115px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(lb.localStatus || lb.status)"
                              v-model="lb.localStatus"
                              @change="checkStatusChange(lb)"
                              :disabled="lb.isUpdatingStatus">
                        <option value="published">Xuất bản</option>
                        <option value="draft">Bản nháp</option>
                        <option value="hidden">Đang ẩn</option>
                      </select>
                      
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="lb.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;" role="status"></div>
                        <template v-else-if="lb.isStatusChanged">
                          <button @click="saveStatus(lb)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(lb)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border" title="Xem nhanh Lookbook" @click="openQuickView(lb)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!lb.deleted_at">
                        <router-link :to="{ name: 'admin-lookbooks-edit', params: { id: lb.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Sửa & Ghim ảnh">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(lb.id, lb.name)" title="Đưa vào thùng rác">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreLookbook(lb.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i>
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
            <div v-if="displayLookbooks.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else v-for="lb in displayLookbooks" :key="lb.id" class="card border-0 shadow-sm mb-3 rounded-4 dark:bg-[#212529]" :class="{'opacity-75': lb.deleted_at || lb.status === 'hidden'}">
              <div class="card-body p-3">
                <div class="d-flex mb-3 border-bottom dark:border-gray-700 pb-3">
                  <div class="position-relative me-3 border shadow-sm rounded-3 overflow-hidden dark:border-gray-600 flex-shrink-0 cursor-zoom-in" style="width: 70px; height: 90px;" @click="openImageZoom(getImageUrl(lb.main_image))">
                     <img :src="getImageUrl(lb.main_image)" @error="handleImageError" class="w-100 h-100 object-fit-cover transition-transform hover-zoom">
                     <span class="badge bg-danger position-absolute bottom-0 end-0 m-1 px-1 py-0 shadow-sm" style="font-size: 0.6rem;">
                        <i class="bi bi-pin-angle-fill"></i> {{ lb.items_count || 0 }}
                     </span>
                  </div>
                  <div class="overflow-hidden w-100">
                    <h6 class="mb-1 fw-bold dark:text-gray-200 text-truncate">{{ lb.name }}</h6>
                    <div class="text-muted dark:text-gray-400 small text-truncate mt-1">Giới tính: {{ lb.gender || 'Unisex' }}</div>
                    <strong class="text-danger dark:text-red-400 mt-1 d-block">{{ formatCurrency(lb.total_price_estimate) }}</strong>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                   <span v-if="lb.deleted_at" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                   <span v-else-if="lb.status === 'published'" class="text-success small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Xuất bản</span>
                   <span v-else-if="lb.status === 'draft'" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Bản nháp</span>
                   <span v-else class="text-warning small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Đang ẩn</span>
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border flex-grow-1 shadow-sm" @click="openQuickView(lb)"><i class="bi bi-eye"></i> Xem</button>
                  <template v-if="!lb.deleted_at">
                    <router-link :to="{ name: 'admin-lookbooks-edit', params: { id: lb.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm"><i class="bi bi-pencil-square"></i> Ghim</router-link>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border shadow-sm" style="width: 50px;" @click="confirmDelete(lb.id, lb.name)"><i class="bi bi-trash"></i></button>
                  </template>
                  <template v-else>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold shadow-sm" @click="restoreLookbook(lb.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1">
        <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedLookbooks.length) }}</span>
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
    <!-- POPUP QUICK VIEW (ĐÃ FIX NỬA TRÊN / NỬA DƯỚI)             -->
    <!-- ========================================================= -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-images text-urban me-2"></i>Chi Tiết Lookbook</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4" v-if="selectedLookbook">
            
            <!-- NỬA TRÊN: HÌNH ẢNH & THÔNG TIN CƠ BẢN -->
            <div class="row mb-4 pb-4 border-bottom dark:border-gray-700">
              <div class="col-md-5 text-center mb-4 mb-md-0 position-relative">
                <!-- Vùng ảnh hiển thị cả Ghim (Pins) -->
                <div class="position-relative d-inline-block rounded-4 shadow-sm border border-light-subtle dark:border-gray-600 bg-white dark:bg-[#121416] overflow-hidden cursor-zoom-in" style="max-height: 400px;" @click="openImageZoom(getImageUrl(selectedLookbook.main_image))">
                  <img :src="getImageUrl(selectedLookbook.main_image)" class="img-fluid object-fit-contain" style="max-height: 400px; width: auto;" @error="handleImageError">
                  <!-- Render Pins lên ảnh -->
                  <div v-for="(item, idx) in selectedLookbook.parsed_items" :key="idx" 
                       class="position-absolute translate-middle"
                       :style="{ top: item.coords.y + '%', left: item.coords.x + '%' }">
                     <span class="badge bg-danger rounded-circle shadow border border-white d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; opacity: 0.9;">{{ idx + 1 }}</span>
                  </div>
                </div>
              </div>

              <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h4 class="fw-bold mb-1 dark:text-white">{{ selectedLookbook.name }}</h4>
                    <p class="text-muted dark:text-gray-400 small mb-0 font-monospace">/{{ selectedLookbook.slug }}</p>
                  </div>
                  <span class="badge px-3 py-2 rounded-pill shadow-sm" :class="selectedLookbook.deleted_at ? 'bg-secondary text-white' : (selectedLookbook.status === 'published' ? 'bg-success text-white' : (selectedLookbook.status === 'draft' ? 'bg-secondary text-white' : 'bg-warning text-dark'))">
                    {{ selectedLookbook.deleted_at ? 'Đã xóa' : (selectedLookbook.status === 'published' ? 'Đang xuất bản' : (selectedLookbook.status === 'draft' ? 'Bản nháp' : 'Đang ẩn')) }}
                  </span>
                </div>

                <div class="bg-light dark:bg-[#212529] p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100">
                  <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-gender-ambiguous text-urban me-1"></i>Dành cho:</span>
                       <span class="fw-bold text-dark dark:text-gray-200">{{ selectedLookbook.gender || 'Unisex' }}</span>
                    </div>
                    <div class="col-sm-6">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-cash-stack text-urban me-1"></i>Giá trị Set ước tính:</span>
                       <span class="fw-bold text-danger fs-6">{{ formatCurrency(selectedLookbook.total_price_estimate) }}</span>
                    </div>
                    <div class="col-12">
                       <span class="text-muted dark:text-gray-400 d-block"><i class="bi bi-info-circle text-urban me-1"></i>Mô tả:</span>
                       <span class="fw-bold text-dark dark:text-gray-200 fst-italic">{{ selectedLookbook.description || 'Chưa có mô tả.' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- NỬA DƯỚI: BẢNG SẢN PHẨM ĐÃ GHIM -->
            <div class="row">
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="text-dark dark:text-white fw-bold fs-5"><i class="bi bi-bag-check-fill text-urban me-2"></i>Sản Phẩm Trong Set Đồ ({{ selectedLookbook.parsed_items?.length || 0 }})</span>
                  <button v-if="!selectedLookbook.deleted_at" type="button" @click="goToEditLookbook(selectedLookbook.id)" class="btn btn-sm btn-outline-urban rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa & Ghim thêm
                  </button>
                </div>
                
                <div v-if="!selectedLookbook.parsed_items || selectedLookbook.parsed_items.length === 0" class="text-muted small fst-italic bg-light dark:bg-[#212529] p-4 text-center rounded-4 border dark:border-gray-700">
                  Lookbook này chưa được ghim sản phẩm nào.
                </div>
                
                <div v-else class="table-responsive custom-scrollbar-y rounded-3 border dark:border-gray-700" style="max-height: 300px;">
                  <table class="table table-bordered table-hover mb-0 align-middle small text-center dark:border-gray-700">
                    <thead class="bg-light dark:bg-[#2b3035] sticky-top" style="z-index: 10;">
                      <tr>
                        <th class="dark:text-gray-300" style="width: 60px;">Ghim</th>
                        <th class="dark:text-gray-300" style="width: 80px;">Ảnh SP</th>
                        <th class="dark:text-gray-300 text-start">Tên Sản Phẩm</th>
                        <th class="dark:text-gray-300 text-start" style="width: 150px;">Mã Slug</th>
                        <th class="text-danger" style="width: 150px;">Giá Tham Khảo</th>
                      </tr>
                    </thead>
                    <tbody class="dark:bg-[#1a2533]">
                      <tr v-for="(item, idx) in selectedLookbook.parsed_items" :key="idx">
                        <td class="fw-bold text-danger">#{{ idx + 1 }}</td>
                        <td>
                          <img v-if="item.product" :src="getImageUrl(item.product.thumbnail_image)" class="rounded border object-fit-cover dark:border-gray-600 mx-auto cursor-zoom-in" style="width: 40px; height: 40px;" @error="handleImageError" @click="openImageZoom(getImageUrl(item.product.thumbnail_image))">
                          <i v-else class="bi bi-question-square text-muted fs-4"></i>
                        </td>
                        <td class="text-start fw-bold text-dark dark:text-gray-200">
                          {{ item.product ? item.product.name : 'Sản phẩm đã bị xóa' }}
                        </td>
                        <td class="text-start font-monospace text-muted dark:text-gray-400">
                          {{ item.product ? item.product.slug : 'N/A' }}
                        </td>
                        <td class="fw-bold text-danger">
                          {{ item.product ? formatCurrency(item.product.base_price) : '0 ₫' }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
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

    <!-- MODAL PHÓNG TO ẢNH (DÙNG CHUNG) -->
    <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
          <div class="modal-header border-0 pb-0 justify-content-end">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
          </div>
          <div class="modal-body text-center p-0">
            <img :src="zoomedImageUrl" class="img-fluid rounded shadow-lg" style="max-height: 80vh; object-fit: contain;">
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
import defaultImage from '@/assets/images/defaults/placeholder.png'; 

const route = useRoute();
const router = useRouter();

const lookbooks = ref([]);
const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 
const isRefreshing = ref(false);

const searchQuery = ref('');
const activeTab = ref('all');
const filterGender = ref('');
const filterDateFrom = ref('');
const filterDateTo = ref('');
const currentPage = ref(1);
const itemsPerPage = 12;

const selectedLookbook = ref(null);
const isQuickViewLoading = ref(false);
let quickViewModalInstance = null;

// Biến Zoom ảnh
const zoomedImageUrl = ref('');
let imageZoomModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary bg-opacity-10 text-primary', 5: 'bg-success bg-opacity-10 text-success' };
  return map[level] || 'bg-secondary';
};

const fetchData = async (isSilent = false) => {
  if (isSilent) isRefreshing.value = true; else isLoading.value = true;
  try {
    const [resLB, resModules] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/lookbooks', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() })
    ]);
    
    const rawData = Array.isArray(resLB.data.data?.data) ? resLB.data.data.data : (Array.isArray(resLB.data.data) ? resLB.data.data : []);
    lookbooks.value = rawData.map(l => ({ ...l, localStatus: l.status, isStatusChanged: false, isUpdatingStatus: false }));
    
    const module = resModules.data.data.find(m => m.module_code === (route.meta?.moduleCode || 'admin_lookbooks'));
    if (module) currentPageLevel.value = module.required_level;
  } catch (err) { console.error(err); } finally { isLoading.value = false; isFirstLoad.value = false; isRefreshing.value = false; }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.lookbooks').listen('.LookbookEvent', () => fetchData(true));
  }
};

const getStatusSelectClass = (status) => {
  const map = { 'published': 'text-success border-success bg-success bg-opacity-10', 'draft': 'text-secondary border-secondary bg-secondary bg-opacity-10', 'hidden': 'text-warning border-warning bg-warning bg-opacity-10' }; 
  return map[status] || 'bg-light text-secondary'; 
};

const checkStatusChange = (p) => { p.isStatusChanged = (p.localStatus !== p.status); };
const cancelStatusChange = (p) => { p.localStatus = p.status; p.isStatusChanged = false; };

const saveStatus = async (p) => {
  p.isUpdatingStatus = true;
  try {
    await axios.patch(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${p.id}/status`, { status: p.localStatus }, { headers: getHeaders() });
    p.status = p.localStatus; p.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật thành công', showConfirmButton: false, timer: 1500 });
  } catch (error) { 
    cancelStatusChange(p); 
    Swal.fire('Lỗi', error.response?.data?.message || 'Lỗi cập nhật trạng thái', 'error');
  } finally { p.isUpdatingStatus = false; }
};

const switchTab = (tabId) => { activeTab.value = tabId; currentPage.value = 1; };

const hasActiveFilters = computed(() => {
  return filterGender.value !== '' || filterDateFrom.value !== '' || filterDateTo.value !== '';
});

const clearFilters = () => {
  filterGender.value = ''; filterDateFrom.value = ''; filterDateTo.value = ''; currentPage.value = 1;
};

// ĐÃ FIX: Mở khóa Modal Quick View
const openQuickView = async (lb) => {
  selectedLookbook.value = lb; 
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
  
  isQuickViewLoading.value = true;
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${lb.id}`, { headers: getHeaders() });
    selectedLookbook.value = res.data.data;
    
    // Parse tọa độ để hiển thị điểm ghim lên ảnh QuickView
    if (selectedLookbook.value.items) {
       selectedLookbook.value.parsed_items = selectedLookbook.value.items.map(item => {
          return {
             ...item,
             coords: typeof item.pin_coordinates === 'string' ? JSON.parse(item.pin_coordinates) : item.pin_coordinates
          };
       });
    }
  } catch (err) {
    console.error("Lỗi tải chi tiết", err);
  } finally { isQuickViewLoading.value = false; }
};

// TÍNH NĂNG ZOOM ẢNH CHUYÊN NGHIỆP
const openImageZoom = (url) => {
  zoomedImageUrl.value = url;
  if (!imageZoomModalInstance) imageZoomModalInstance = new window.bootstrap.Modal(document.getElementById('imageZoomModal'));
  imageZoomModalInstance.show();
};

const goToEditLookbook = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = '';
    document.body.style = '';
    router.push({ name: 'admin-lookbooks-edit', params: { id } });
  }, 300);
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  if (imageZoomModalInstance) imageZoomModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
});

const processedLookbooks = computed(() => {
  let result = lookbooks.value;
  if (activeTab.value === 'deleted') result = result.filter(c => c.deleted_at);
  else {
    result = result.filter(c => !c.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(c => c.status === activeTab.value);
  }
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(c => (c.name?.toLowerCase().includes(q)) || (c.slug?.toLowerCase().includes(q)));
  }

  // Bộ lọc Giới tính
  if (filterGender.value) {
    result = result.filter(c => c.gender === filterGender.value);
  }
  
  // Bộ lọc Ngày
  if (filterDateFrom.value) {
    const fromDate = new Date(filterDateFrom.value).setHours(0,0,0,0);
    result = result.filter(c => new Date(c.created_at).getTime() >= fromDate);
  }
  if (filterDateTo.value) {
    const toDate = new Date(filterDateTo.value).setHours(23,59,59,999);
    result = result.filter(c => new Date(c.created_at).getTime() <= toDate);
  }

  return result;
});

const totalPages = computed(() => Math.ceil(processedLookbooks.value.length / itemsPerPage) || 1);
const displayLookbooks = computed(() => processedLookbooks.value.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage));

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Đưa vào thùng rác?', text: `Lookbook "${name}" sẽ bị ẩn đi!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xóa', 'error'); }
    }
  });
};

const restoreLookbook = (id) => {
  Swal.fire({ title: 'Khôi phục?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
        fetchData(true);
      } catch(e) { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi khôi phục', 'error'); }
    }
  });
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.lookbooks'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.cursor-zoom-in { cursor: zoom-in; }
.transition-transform { transition: transform 0.3s ease; }
.hover-zoom:hover { transform: scale(1.05); }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
</style>