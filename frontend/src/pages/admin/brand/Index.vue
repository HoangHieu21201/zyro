<!-- File: frontend/src/pages/admin/brand/Index.vue -->
<template>
  <div class="brand-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải thương hiệu...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Thương Hiệu Sản Phẩm</h3>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-brands-create' }" class="btn btn-urban px-4 py-2 fw-bold shadow-sm text-white rounded-pill transition-all" v-if="!isReorderMode">
            <i class="bi bi-plus-circle-fill me-1"></i> Thêm Thương Hiệu
          </router-link>
        </div>
      </div>

      <div class="mb-4" :class="{'opacity-50 pe-none': isReorderMode}">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-wrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ brands.filter(c => !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'active' }" @click.prevent="switchTab('active')">
              <i class="bi bi-eye-fill me-2 text-success"></i> Hiển thị
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'active'}">{{ brands.filter(c => c.status === 'active' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-warning"></i> Đang ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'hidden'}">{{ brands.filter(c => c.status === 'hidden' && !c.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Thùng rác
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ brands.filter(c => c.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all" :class="{'border border-warning border-2': isReorderMode}">
        <div class="card-header bg-white dark:bg-[#1a2533] border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 rounded-top-4">
          <h6 class="fw-bold mb-0 text-dark dark:text-white d-flex align-items-center">
            <i class="bi me-2" :class="isReorderMode ? 'bi-arrows-move text-warning fs-5' : 'bi-list-ul'"></i> 
            {{ isReorderMode ? 'Kéo thả hoặc nhấn nút để thay đổi thứ tự ưu tiên' : 'Danh sách hiển thị' }}
            <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban ms-3" role="status"></span>
          </h6>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <template v-if="!searchQuery">
              <button class="btn btn-sm px-3 py-2 fw-bold shadow-sm transition-all d-flex align-items-center" 
                      :class="isReorderMode ? 'btn-warning text-dark' : 'btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 border text-dark'"
                      @click="toggleReorderMode">
                <i class="bi me-1" :class="isReorderMode ? 'bi-x-circle' : 'bi-arrows-move'"></i> 
                {{ isReorderMode ? 'Hủy Sắp Xếp' : 'Sắp xếp thứ tự' }}
              </button>
            </template>

            <button v-if="isReorderMode" class="btn btn-sm btn-urban text-white fw-bold px-4 shadow-sm py-2 d-flex align-items-center" @click="saveReorder" :disabled="isSavingOrder">
              <span v-if="isSavingOrder" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-floppy-fill me-1" v-else></i> LƯU THỨ TỰ
            </button>

            <div class="search-box position-relative" style="width: 280px; max-width: 100%;" v-show="!isReorderMode">
              <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light dark:bg-[#212529] dark:text-gray-200 border-0 py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, slug thương hiệu...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" :class="{'table-reorder': isReorderMode}" style="table-layout: fixed; width: 100%; min-width: 900px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th v-if="isReorderMode" class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 50px;"></th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 80px;">Thứ tự</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 35%;">Thương hiệu</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0" style="width: 25%;">Mô tả ngắn</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 20%;">{{ isReorderMode ? 'Điều hướng' : 'Thao tác' }}</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="displayBrands.length === 0">
                  <td :colspan="isReorderMode ? 6 : 6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="(brand, index) in displayBrands" :key="brand.id" 
                    :class="{'bg-light opacity-75 dark:bg-[#121416]': brand.deleted_at || brand.status === 'hidden', 'drag-item': isReorderMode, 'dragging': draggedIndex === index, 'drag-over': dragOverIndex === index}"
                    :draggable="isReorderMode"
                    @dragstart="onDragStart(index, $event)"
                    @dragover.prevent="onDragOver(index)"
                    @dragenter.prevent="onDragEnter(index)"
                    @dragleave="onDragLeave(index)"
                    @drop="onDrop(index)"
                    @dragend="onDragEnd">
                  
                  <td v-if="isReorderMode" class="px-4 text-muted cursor-move text-center">
                    <i class="bi bi-grip-vertical fs-5 text-warning"></i>
                  </td>
                  
                  <td class="px-4 fw-bold text-center" :class="isReorderMode ? 'text-warning' : 'text-muted dark:text-gray-400'">
                    {{ isReorderMode ? index + 1 : (brand.sort_order ?? '-') }}
                  </td>

                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getImageUrl(brand.logo)" @error="handleImageError" class="rounded-3 object-fit-contain me-3 border shadow-sm pe-none dark:border-gray-600 bg-white" style="width: 50px; height: 50px; padding: 2px;">
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate" :title="brand.name">{{ brand.name }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace"><i class="bi bi-link-45deg"></i> {{ brand.slug }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-4 text-muted dark:text-gray-400 small text-truncate" style="max-width: 250px;" :title="brand.description">
                    {{ brand.description || 'Chưa có mô tả.' }}
                  </td>

                  <td class="px-4 text-center">
                    <span v-if="brand.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                    <div v-else class="d-flex align-items-center justify-content-center gap-1">
                      <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0 dark:bg-[#212529] dark:text-gray-200" 
                              style="width: 110px; font-size: 0.75rem;"
                              :class="getStatusSelectClass(brand.localStatus || brand.status)"
                              v-model="brand.localStatus"
                              @change="checkStatusChange(brand)"
                              :disabled="brand.isUpdatingStatus || isReorderMode">
                        <option value="active">Hiển thị</option>
                        <option value="hidden">Đang ẩn</option>
                      </select>
                      
                      <div class="d-flex align-items-center" style="min-width: 50px;">
                        <div v-if="brand.isUpdatingStatus" class="spinner-border text-urban ms-1" style="width: 1rem; height: 1rem; border-width: 0.15em;" role="status"></div>
                        <template v-else-if="brand.isStatusChanged">
                          <button @click="saveBrandStatus(brand)" class="btn btn-sm btn-success rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Lưu">
                            <i class="bi bi-check-lg" style="font-size: 0.7rem;"></i>
                          </button>
                          <button @click="cancelStatusChange(brand)" class="btn btn-sm btn-light dark:bg-[#2b3035] text-danger border dark:border-gray-600 rounded-circle p-0 ms-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px;" title="Hủy">
                            <i class="bi bi-x-lg" style="font-size: 0.7rem;"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-1" v-if="!isReorderMode">
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border" title="Xem chi tiết" @click="openQuickView(brand)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <template v-if="!brand.deleted_at">
                        <router-link :to="{ name: 'admin-brands-edit', params: { id: brand.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border" title="Chỉnh sửa">
                          <i class="bi bi-pencil-square"></i>
                        </router-link>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border" @click="confirmDelete(brand.id, brand.name)" title="Đưa vào thùng rác">
                          <i class="bi bi-trash"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success shadow-sm border" @click="restoreBrand(brand.id)" title="Khôi phục">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>
                      </template>
                    </div>

                    <div class="d-flex justify-content-center gap-1" v-else>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 shadow-sm border px-2" @click="moveToTop(index)" :disabled="index === 0" title="Lên đầu tiên"><i class="bi bi-chevron-bar-up"></i></button>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 shadow-sm border px-2" @click="moveUp(index)" :disabled="index === 0" title="Lên 1 bậc"><i class="bi bi-chevron-up"></i></button>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 shadow-sm border px-2" @click="moveDown(index)" :disabled="index === reorderList.length - 1" title="Xuống 1 bậc"><i class="bi bi-chevron-down"></i></button>
                      <button class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 shadow-sm border px-2" @click="moveToBottom(index)" :disabled="index === reorderList.length - 1" title="Xuống cuối cùng"><i class="bi bi-chevron-bar-down"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="displayBrands.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="(brand, index) in displayBrands" :key="brand.id" 
                   class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]" 
                   :class="{'opacity-75': brand.deleted_at || brand.status === 'hidden', 'border border-warning': isReorderMode && draggedIndex === index}"
                   :draggable="isReorderMode"
                   @dragstart="onDragStart(index, $event)"
                   @dragover.prevent="onDragOver(index)"
                   @dragenter.prevent="onDragEnter(index)"
                   @dragleave="onDragLeave(index)"
                   @drop="onDrop(index)"
                   @dragend="onDragEnd">
                <div class="card-body p-3">
                  <div v-if="isReorderMode" class="text-center mb-2 pb-2 border-bottom border-warning border-opacity-50 cursor-move">
                    <i class="bi bi-grip-horizontal fs-4 text-warning"></i>
                    <span class="ms-2 fw-bold text-warning">Vị trí #{{ index + 1 }}</span>
                  </div>

                  <div class="d-flex align-items-center mb-3">
                    <img :src="getImageUrl(brand.logo)" @error="handleImageError" class="rounded-3 object-fit-contain me-3 border shadow-sm dark:border-gray-600 bg-white" style="width: 50px; height: 50px; padding: 2px;">
                    <div class="overflow-hidden w-100">
                      <h6 class="mb-0 fw-bold dark:text-gray-200 text-truncate">{{ brand.name }}</h6>
                      <small class="text-muted dark:text-gray-400 d-block text-truncate font-monospace mt-1"><i class="bi bi-link-45deg"></i> {{ brand.slug }}</small>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-center mb-3 border-top dark:border-gray-700 pt-3 gap-2">
                     <span v-if="brand.deleted_at" class="text-secondary small fw-bold flex-shrink-0"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                     <span v-else-if="brand.status === 'active'" class="text-success small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Hiển thị</span>
                     <span v-else class="text-warning small fw-bold flex-shrink-0"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Đang ẩn</span>
                  </div>

                  <div class="d-flex gap-2" v-if="!isReorderMode">
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info border flex-grow-1 shadow-sm" @click="openQuickView(brand)"><i class="bi bi-eye"></i></button>
                    <template v-if="!brand.deleted_at">
                      <router-link :to="{ name: 'admin-brands-edit', params: { id: brand.id } }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary border flex-grow-1 shadow-sm"><i class="bi bi-pencil-square"></i></router-link>
                      <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger border flex-grow-1 shadow-sm" @click="confirmDelete(brand.id, brand.name)"><i class="bi bi-trash"></i></button>
                    </template>
                    <template v-else>
                      <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 text-success border flex-grow-1 fw-bold shadow-sm" @click="restoreBrand(brand.id)"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                    </template>
                  </div>

                  <div class="d-flex gap-2 mt-2 pt-2 border-top dark:border-gray-700" v-else>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 border flex-grow-1 shadow-sm" @click="moveToTop(index)" :disabled="index === 0" title="Lên đầu tiên"><i class="bi bi-chevron-bar-up"></i></button>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 border flex-grow-1 shadow-sm" @click="moveUp(index)" :disabled="index === 0" title="Lên 1 bậc"><i class="bi bi-chevron-up"></i></button>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 border flex-grow-1 shadow-sm" @click="moveDown(index)" :disabled="index === reorderList.length - 1" title="Xuống 1 bậc"><i class="bi bi-chevron-down"></i></button>
                    <button class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-300 border flex-grow-1 shadow-sm" @click="moveToBottom(index)" :disabled="index === reorderList.length - 1" title="Xuống cuối cùng"><i class="bi bi-chevron-bar-down"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="totalPages > 1 && !isReorderMode">
        <span class="text-muted dark:text-gray-400 small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedBrands.length) }}</span>
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

    <!-- POPUP QUICK VIEW (ĐÃ FIX: Layout 2 cột cực kỳ sang trọng và đồng bộ với Category) -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-tag-fill text-urban me-2"></i>Chi Tiết Thương Hiệu</h5>
            <button type="button" class="btn-close dark:filter dark:invert" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" v-if="selectedBrand">
            <div class="row">
              <!-- Cột Trái: Ảnh Logo và Trạng thái -->
              <div class="col-md-5 text-center border-end dark:border-gray-700 mb-4 mb-md-0">
                <div class="bg-white p-3 rounded-4 shadow-sm border border-light-subtle dark:border-gray-600 mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 100%; max-height: 250px; aspect-ratio: 1/1;">
                  <img :src="getImageUrl(selectedBrand.logo)" @error="handleImageError" class="object-fit-contain w-100 h-100">
                </div>
                <h5 class="fw-bold mb-1 dark:text-white">{{ selectedBrand.name }}</h5>
                <p class="text-muted dark:text-gray-400 small mb-3 font-monospace">/{{ selectedBrand.slug }}</p>
                <span class="badge px-3 py-2 rounded-pill shadow-sm" :class="selectedBrand.deleted_at ? 'bg-secondary text-white' : (selectedBrand.status === 'active' ? 'bg-success text-white' : 'bg-warning text-dark')">
                  {{ selectedBrand.deleted_at ? 'Đã xóa' : (selectedBrand.status === 'active' ? 'Đang hiển thị' : 'Đang ẩn') }}
                </span>
              </div>
              
              <!-- Cột Phải: Thông tin chi tiết & Điều hướng -->
              <div class="col-md-7">
                <div class="bg-light dark:bg-[#212529] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 small h-100 d-flex flex-column">
                  
                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700">
                    <span class="text-muted dark:text-gray-400 fw-semibold d-block mb-2"><i class="bi bi-card-text text-urban me-1"></i>Mô tả thương hiệu:</span>
                    <span class="text-dark dark:text-gray-300 fst-italic">{{ selectedBrand.description || 'Chưa có mô tả chi tiết cho thương hiệu này.' }}</span>
                  </div>
                  
                  <div class="mb-3 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-sort-numeric-down text-urban me-1"></i>Thứ tự ưu tiên:</span>
                    <span class="badge bg-dark text-white fs-6">{{ selectedBrand.sort_order ?? 'Chưa cấp' }}</span>
                  </div>
                  
                  <div class="mb-auto d-flex justify-content-between align-items-center">
                    <span class="text-muted dark:text-gray-400 fw-semibold"><i class="bi bi-calendar-date text-urban me-1"></i>Ngày tạo:</span>
                    <span class="text-dark dark:text-gray-300 fw-bold">{{ formatDateTime(selectedBrand.created_at) }}</span>
                  </div>
                  
                  <!-- Nút Thiết lập đẩy xuống góc dưới cùng cột phải -->
                  <div class="mt-4 pt-3 border-top dark:border-gray-700" v-if="!selectedBrand.deleted_at">
                    <button type="button" @click="goToEditBrand(selectedBrand.id)" class="btn btn-urban rounded-pill px-4 py-2.5 fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center transition-all">
                      <i class="bi bi-pencil-square me-2 fs-5"></i> Thiết lập thương hiệu
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
import defaultImage from '@/assets/images/defaults/placeholder.png'; 

const route = useRoute();
const router = useRouter();

const brands = ref([]);
const systemModules = ref([]);
const currentPageLevel = ref(null);
const isLoading = ref(true);
const isFirstLoad = ref(true); 
const isRefreshing = ref(false);
const searchQuery = ref('');
const activeTab = ref('all');
const currentPage = ref(1);
const itemsPerPage = 10;

const selectedBrand = ref(null);
let quickViewModalInstance = null;

const isReorderMode = ref(false);
const isSavingOrder = ref(false);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);
const reorderList = ref([]); 

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

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

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const fetchData = async (isSilent = false) => {
  if (isReorderMode.value) return;

  if (isSilent) isRefreshing.value = true;
  else if (!isFirstLoad.value) isLoading.value = true;
  
  try {
    const [resBrands, resModules] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/brands', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/modules', { headers: getHeaders() })
    ]);

    const rawData = Array.isArray(resBrands.data.data) ? resBrands.data.data : [];
    
    brands.value = rawData.map(b => ({
      ...b, localStatus: b.status, isStatusChanged: false, isUpdatingStatus: false
    }));
    
    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_brands'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;
    
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
    window.Echo.private('admin.brands')
      .listen('.BrandEvent', () => {
        fetchData(true); 
      });
  }
};

const toggleReorderMode = () => {
  if (activeTab.value !== 'active') {
    Swal.fire('Lưu ý', 'Chỉ có thể sắp xếp thứ tự của các thương hiệu đang ở trạng thái Hiển thị!', 'info');
    return;
  }
  
  isReorderMode.value = !isReorderMode.value;
  if (isReorderMode.value) {
    searchQuery.value = ''; 
    reorderList.value = JSON.parse(JSON.stringify(processedBrands.value));
  }
};

// Chuột
const onDragStart = (index, event) => {
  draggedIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.dropEffect = 'move';
  setTimeout(() => event.target.classList.add('opacity-50'), 0);
};
const onDragOver = (index) => { event.dataTransfer.dropEffect = 'move'; };
const onDragEnter = (index) => { if (draggedIndex.value !== index) dragOverIndex.value = index; };
const onDragLeave = (index) => { if (dragOverIndex.value === index) dragOverIndex.value = null; };
const onDrop = (index) => {
  if (draggedIndex.value !== null && draggedIndex.value !== index) {
    const draggedItem = reorderList.value[draggedIndex.value];
    reorderList.value.splice(draggedIndex.value, 1);
    reorderList.value.splice(index, 0, draggedItem);
  }
  dragOverIndex.value = null;
};
const onDragEnd = (event) => {
  event.target.classList.remove('opacity-50');
  draggedIndex.value = null;
  dragOverIndex.value = null;
};

// Nút bấm
const moveUp = (index) => {
  if (index > 0) {
    const item = reorderList.value[index];
    reorderList.value.splice(index, 1);
    reorderList.value.splice(index - 1, 0, item);
  }
};
const moveDown = (index) => {
  if (index < reorderList.value.length - 1) {
    const item = reorderList.value[index];
    reorderList.value.splice(index, 1);
    reorderList.value.splice(index + 1, 0, item);
  }
};
const moveToTop = (index) => {
  if (index > 0) {
    const item = reorderList.value[index];
    reorderList.value.splice(index, 1);
    reorderList.value.unshift(item);
  }
};
const moveToBottom = (index) => {
  if (index < reorderList.value.length - 1) {
    const item = reorderList.value[index];
    reorderList.value.splice(index, 1);
    reorderList.value.push(item);
  }
};

const saveReorder = async () => {
  isSavingOrder.value = true;
  const payload = reorderList.value.map((b, index) => ({
    id: b.id,
    sort_order: index + 1
  }));

  try {
    await axios.post('http://127.0.0.1:8000/api/v1/admin/brands/reorder', { brands: payload }, { headers: getHeaders() });
    Swal.fire({icon: 'success', title: 'Đã lưu thứ tự!', timer: 1500, showConfirmButton: false});
    isReorderMode.value = false;
    await fetchData(true); 
  } catch (err) {
    Swal.fire('Lỗi', 'Không thể cập nhật thứ tự', 'error');
  } finally {
    isSavingOrder.value = false;
  }
};

const getStatusSelectClass = (status) => {
  const map = { 
    'active': 'text-success border-success bg-success bg-opacity-10', 
    'hidden': 'text-warning border-warning bg-warning bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const checkStatusChange = (b) => { b.isStatusChanged = (b.localStatus !== b.status); };
const cancelStatusChange = (b) => { b.localStatus = b.status; b.isStatusChanged = false; };

const saveBrandStatus = async (b) => {
  b.isUpdatingStatus = true;
  const formData = new FormData();
  formData.append('_method', 'PUT'); 
  formData.append('name', b.name);
  formData.append('status', b.localStatus); 

  try {
    await axios.post(`http://127.0.0.1:8000/api/v1/admin/brands/${b.id}`, formData, { headers: getHeaders() });
    b.status = b.localStatus; 
    b.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật trạng thái thành công', showConfirmButton: false, timer: 1500 });
  } catch (error) { 
    cancelStatusChange(b); 
    Swal.fire('Lỗi', 'Không thể cập nhật trạng thái', 'error');
  } finally { 
    b.isUpdatingStatus = false; 
  }
};

const switchTab = (tabId) => { 
  activeTab.value = tabId; 
  currentPage.value = 1; 
  isReorderMode.value = false; 
};

onBeforeUnmount(() => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const openQuickView = (b) => {
  selectedBrand.value = b;
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
};

const goToEditBrand = (id) => {
  if (quickViewModalInstance) quickViewModalInstance.hide();
  setTimeout(() => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.className = '';
    document.body.style = '';
    router.push({ name: 'admin-brands-edit', params: { id } });
  }, 300);
};

const processedBrands = computed(() => {
  let result = brands.value;
  if (activeTab.value === 'deleted') { result = result.filter(b => b.deleted_at); } 
  else {
    result = result.filter(b => !b.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(b => b.status === activeTab.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(b => (b.name?.toLowerCase().includes(q)) || (b.slug?.toLowerCase().includes(q)));
  }
  return result;
});

const totalPages = computed(() => Math.ceil(processedBrands.value.length / itemsPerPage) || 1);

const displayBrands = computed(() => {
  if (isReorderMode.value) return reorderList.value;
  const start = (currentPage.value - 1) * itemsPerPage; 
  return processedBrands.value.slice(start, start + itemsPerPage);
});

const confirmDelete = (id, name) => {
  Swal.fire({ title: 'Xóa thương hiệu?', text: `Thương hiệu "${name}" sẽ bị đưa vào thùng rác!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.delete(`http://127.0.0.1:8000/api/v1/admin/brands/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
      } catch(e) {
        isLoading.value = false;
        Swal.fire('Lỗi', e.response?.data?.message || 'Không thể xóa', 'error');
      }
    }
  });
};

const restoreBrand = (id) => {
  Swal.fire({ title: 'Khôi phục thương hiệu?', icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover, #547792)', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      isLoading.value = true;
      try {
        await axios.post(`http://127.0.0.1:8000/api/v1/admin/brands/${id}/restore`, {}, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã khôi phục', timer: 1500, showConfirmButton: false});
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
  if (window.Echo) window.Echo.leave('admin.brands');
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

.cursor-move { cursor: grab; }
.cursor-move:active { cursor: grabbing; }
.drag-item { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.drag-over { border-top: 3px solid #ffc107 !important; background-color: #fff9e6 !important; }
html.dark .drag-over { background-color: rgba(255, 193, 7, 0.1) !important; }
.dragging { opacity: 0.5; background-color: #f8f9fa; }
html.dark .dragging { background-color: #121416; }

.transition-all { transition: all 0.3s ease; }
</style>