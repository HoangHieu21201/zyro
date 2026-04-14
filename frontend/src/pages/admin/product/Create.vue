<!-- File: frontend/src/pages/admin/product/Create.vue -->
<template>
  <div class="product-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isPageLoading">

      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center">
          <router-link :to="{ name: 'admin-products' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0">Thêm Sản phẩm Mới</h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1">Khởi tạo sản phẩm gốc và các biến thể phân loại (3 Bước)</p>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533]">
        <div class="card-header bg-white dark:bg-[#1a2533] pt-4 pb-0 border-bottom-0 rounded-top-4">
          <ul class="nav nav-underline custom-scrollbar-x flex-nowrap border-bottom dark:border-gray-700">
            <li class="nav-item">
              <a class="nav-link py-3 px-4 fw-bold custom-tab" :class="{ 'active-tab': currentStep === 1 }" href="#" @click.prevent="currentStep = 1">
                <span class="step-circle me-2">1</span> Thông tin cơ bản
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link py-3 px-4 fw-bold custom-tab" :class="{ 'active-tab': currentStep === 2, 'disabled opacity-50 pe-none': !canProceedToStep2 }" href="#" @click.prevent="proceedIfValid(2)">
                <span class="step-circle me-2">2</span> Biến thể & Kho
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link py-3 px-4 fw-bold custom-tab" :class="{ 'active-tab': currentStep === 3, 'disabled opacity-50 pe-none': variants.length === 0 }" href="#" @click.prevent="proceedIfValid(3)">
                <span class="step-circle me-2">3</span> Thư viện ảnh
              </a>
            </li>
          </ul>
        </div>

        <div class="card-body p-4 p-md-5">
          <form @submit.prevent="submitProduct" id="productForm" autocomplete="off">

            <!-- BƯỚC 1: THÔNG TIN CƠ BẢN -->
            <div v-show="currentStep === 1" class="row g-4 animation-fade-in">
              <div class="col-lg-8">
                <div class="p-4 bg-light dark:bg-[#212529] rounded-4 border dark:border-gray-700 h-100">
                  <h6 class="fw-bold mb-4 text-urban text-uppercase"><i class="bi bi-card-text me-2"></i>Dữ liệu cơ sở</h6>
                  
                  <div class="row g-3">
                    <div class="col-md-12">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Tên sản phẩm <span class="text-danger">*</span></label>
                      <input type="text" class="form-control form-control-lg bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600 shadow-sm-hover" 
                             v-model="form.name" @input="generateSlug" required placeholder="VD: Áo thun nam Cotton Basic">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Đường dẫn (Slug)</label>
                      <input type="text" class="form-control bg-light-subtle dark:bg-[#2b3035] text-muted dark:text-gray-400 font-monospace border-secondary-subtle dark:border-gray-600" 
                             v-model="form.slug" readonly>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Danh mục <span class="text-danger">*</span></label>
                      <select class="form-select border-urban fw-semibold text-urban bg-white dark:bg-[#1a2533] shadow-sm-hover" v-model="form.category_id" required>
                        <option value="" disabled>-- Chọn danh mục --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                      </select>
                      <small v-if="categories.length === 0" class="text-danger d-block mt-1">Hệ thống chưa có danh mục nào đang hoạt động!</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Thương hiệu</label>
                      <select class="form-select fw-semibold bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600 shadow-sm-hover" v-model="form.brand_id">
                        <option value="">-- Không có (No Brand) --</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Giới tính (Gender)</label>
                      <select class="form-select bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600" v-model="form.gender">
                        <option value="Unisex">Unisex</option>
                        <option value="Men">Nam</option>
                        <option value="Women">Nữ</option>
                        <option value="Kids">Trẻ em</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Kiểu dáng (Fit Type)</label>
                      <select class="form-select bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600" v-model="form.fit_type">
                        <option value="">-- Không chọn --</option>
                        <option value="Regular Fit">Regular Fit (Tiêu chuẩn)</option>
                        <option value="Slim Fit">Slim Fit (Ôm dáng)</option>
                        <option value="Loose Fit">Loose Fit (Rộng rãi)</option>
                        <option value="Oversize">Oversize (Quá khổ)</option>
                        <option value="Skinny">Skinny (Bó sát)</option>
                        <option value="Straight">Straight (Ống đứng)</option>
                      </select>
                    </div>

                    <!-- ĐÃ FIX NÂNG CẤP: Format chấm VNĐ trực tiếp lúc gõ, bỏ chữ đỏ bên dưới -->
                    <div class="col-md-4">
                      <label class="form-label fw-bold text-dark dark:text-gray-200">Giá cơ sở tham khảo <span class="text-danger">*</span></label>
                      <div class="input-group shadow-sm-hover">
                        <input type="text" class="form-control border-secondary-subtle dark:border-gray-600 bg-white dark:bg-[#1a2533] dark:text-white fw-bold border-end-0" 
                               :value="formatThousand(form.base_price)" @input="e => updateNumber(e, form, 'base_price')" required>
                        <span class="input-group-text bg-white dark:bg-[#1a2533] text-muted border-secondary-subtle dark:border-gray-600 border-start-0 fw-bold">₫</span>
                      </div>
                    </div>

                    <div class="col-12 mt-3">
                      <div class="p-3 border border-secondary-subtle dark:border-gray-600 rounded-3 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                          <h6 class="fw-bold text-dark dark:text-white mb-1">Sản phẩm Nổi bật (Featured)</h6>
                          <p class="text-muted small mb-0">Ưu tiên hiển thị trên trang chủ và danh sách.</p>
                        </div>
                        <div class="form-check form-switch fs-4 m-0">
                          <input class="form-check-input cursor-pointer" type="checkbox" id="featureSwitchCreate" v-model="form.is_featured">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="p-4 bg-light dark:bg-[#212529] rounded-4 border dark:border-gray-700 text-center h-100">
                  <h6 class="fw-bold mb-4 text-start text-urban text-uppercase"><i class="bi bi-image me-2"></i>Ảnh & Chi Tiết <span class="text-danger">*</span></h6>
                  
                  <div class="mb-3 position-relative border border-dashed dark:border-gray-600 rounded-4 overflow-hidden bg-white dark:bg-[#1a2533]" style="height: 200px;">
                    <img v-if="thumbnailPreview" :src="thumbnailPreview" class="w-100 h-100 object-fit-contain p-2">
                    <div v-else class="d-flex flex-column justify-content-center align-items-center h-100 text-muted dark:text-gray-500">
                      <i class="bi bi-image fs-1 mb-2 opacity-50"></i>
                      <span class="small fw-semibold">Bắt buộc tải ảnh nền</span>
                    </div>
                  </div>
                  <input type="file" class="d-none" id="thumbUpload" accept="image/*" @change="handleThumbUpload">
                  <label for="thumbUpload" class="btn btn-outline-urban rounded-pill w-100 fw-semibold shadow-sm cursor-pointer"><i class="bi bi-upload me-1"></i> Chọn ảnh chính</label>
                  
                  <div class="text-start mt-4">
                    <label class="form-label fw-bold text-dark dark:text-gray-200">Mô tả ngắn</label>
                    <textarea class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600 mb-3" rows="3" v-model="form.description"></textarea>

                    <label class="form-label fw-bold text-dark dark:text-gray-200">Hướng dẫn bảo quản</label>
                    <textarea class="form-control bg-white dark:bg-[#1a2533] dark:text-white border-secondary-subtle dark:border-gray-600" rows="2" v-model="form.care_instructions" placeholder="VD: Giặt tay, không dùng thuốc tẩy..."></textarea>
                  </div>
                </div>
              </div>

              <div class="col-12 text-end border-top dark:border-gray-700 pt-4 mt-4">
                <button type="button" class="btn btn-urban px-5 fw-bold text-white rounded-pill shadow py-2" @click="proceedToStep2" :disabled="!canProceedToStep2 || isProcessingSchema">
                  <span v-if="isProcessingSchema" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isProcessingSchema ? 'Đang phân tích lưới...' : 'Tiếp tục: Cấu hình Biến thể' }} <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- BƯỚC 2: CẤU HÌNH BIẾN THỂ -->
            <div v-show="currentStep === 2" class="animation-fade-in">
              <div class="card border border-secondary-subtle dark:border-gray-700 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white dark:bg-[#212529] border-bottom dark:border-gray-700 p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                  <h6 class="fw-bold mb-0 text-urban d-flex align-items-center">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i> LƯỚI QUẢN LÝ KHO & GIÁ
                  </h6>

                  <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm shadow-sm">
                      <select class="form-select border-secondary-subtle dark:border-gray-600 dark:bg-[#1a2533] dark:text-white fw-bold text-secondary" v-model="selectedAttrToAdd" style="min-width: 150px;">
                        <option value="">+ Chọn thuộc tính</option>
                        <template v-if="systemAttributes.length > 0">
                          <option v-for="attr in systemAttributes" :key="attr.id" :value="attr.id" :disabled="activeAttributes.includes(attr.id.toString())">
                            {{ attr.name }}
                          </option>
                        </template>
                      </select>
                      <button type="button" class="btn btn-success px-3 fw-bold" title="Thêm cột vào bảng" @click="addAttributeColumn"><i class="bi bi-plus-lg"></i></button>
                    </div>

                    <div class="vr mx-1 text-secondary opacity-25"></div>

                    <button type="button" class="btn btn-sm btn-outline-primary border-0 fw-bold" @click="openModal('createAttrModal')">
                      <i class="bi bi-tag-fill me-1"></i> Tạo mới
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary dark:text-gray-400 dark:border-gray-600 border-0 fw-bold" @click="openModal('manageAttrModal')">
                      <i class="bi bi-gear-fill me-1"></i> Quản lý
                    </button>
                  </div>
                </div>

                <div class="card-body p-0 bg-white dark:bg-[#1a2533]">
                  <div class="table-responsive custom-scrollbar-x" style="min-height: 450px; padding-bottom: 120px;">
                    <table class="table table-bordered mb-0 variant-table w-100 dark:border-gray-700">
                      <thead class="bg-light dark:bg-[#2b3035]">
                        <tr>
                          <th style="width: 60px;" class="dark:text-gray-300">Ảnh</th>
                          <th style="min-width: 130px;" class="dark:text-gray-300">Mã SKU</th>
                          
                          <th v-for="attrId in activeAttributes" :key="attrId" style="min-width: 160px; background-color: var(--color-c-hover) !important; color: white !important;" class="align-middle position-relative border-urban">
                            {{ getAttributeName(attrId) }}
                            <button type="button" class="btn-close btn-close-white position-absolute top-50 end-0 translate-middle-y me-2" style="font-size: 0.6rem;" @click="removeAttributeColumn(attrId)"></button>
                          </th>
                          
                          <th style="width: 140px;" class="bg-secondary bg-opacity-10 text-dark dark:text-gray-300">Giá vốn (Nhập) <span class="text-danger">*</span></th>
                          <th style="width: 140px;" class="bg-warning bg-opacity-10 text-dark dark:text-gray-300">Giá bán ra <span class="text-danger">*</span></th>
                          <th style="width: 140px;" class="dark:text-gray-300">Giá Khuyến mãi</th>
                          <th style="width: 100px;" class="dark:text-gray-300">Tồn kho <span class="text-danger">*</span></th>
                          <th style="width: 80px;">Thao tác</th>
                        </tr>
                      </thead>
                      <tbody class="dark:border-gray-700">
                        <tr v-if="variants.length === 0">
                          <td :colspan="7 + activeAttributes.length" class="text-center py-5 dark:bg-[#1a2533] text-muted">Chưa có dòng biến thể nào. Nhấn "Thêm dòng biến thể" phía dưới.</td>
                        </tr>

                        <tr v-else v-for="(v, index) in variants" :key="index" class="variant-row dark:bg-[#1a2533]" :class="{ 'row-error': v.hasDuplicateError }">
                          <td class="text-center position-relative align-middle">
                            <label class="cursor-pointer d-block m-0">
                              <img :src="v.preview || 'https://placehold.co/40x40/213448/FFFFFF?text=UP'" class="img-preview-sm dark:border-gray-600">
                              <input type="file" class="d-none" accept="image/*" @change="handleVariantImage(index, $event)">
                            </label>
                          </td>

                          <td class="align-middle">
                            <input type="text" class="form-control form-control-sm font-monospace bg-light dark:bg-[#2b3035] dark:text-white dark:border-gray-600" v-model="v.sku" placeholder="Tự sinh" required>
                          </td>

                          <!-- DROPDOWN CUSTOM VUE CHUẨN Z-INDEX 1000 ĐỂ KHÔNG ĐÈ SWEETALERT -->
                          <td v-for="attrId in activeAttributes" :key="attrId" class="align-middle" style="min-width: 160px;">
                            <div class="position-relative custom-vue-dropdown" @click.stop>
                              <button class="btn btn-sm w-100 text-start d-flex justify-content-between align-items-center bg-light dark:bg-[#2b3035] dark:text-white dark:border-gray-600 border"
                                      :class="{ 'is-invalid border-danger': v.attrError }"
                                      type="button" 
                                      @click="toggleDropdown(index, attrId)">
                                <span class="text-truncate pe-2">{{ getSelectedValueName(attrId, v.attributes[attrId]) }}</span>
                                <i class="bi text-muted" style="font-size: 0.65rem;" :class="activeDropdown === `${index}-${attrId}` ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                              </button>
                              
                              <transition name="fade">
                                <div v-show="activeDropdown === `${index}-${attrId}`" 
                                     class="position-absolute shadow-lg border rounded-3 p-3 bg-white dark:bg-[#1a2533] dark:border-gray-600" 
                                     style="width: 300px; z-index: 1000; top: 100%; left: 0; margin-top: 4px; cursor: default;">
                                  <div class="row g-2">
                                    <div class="col-6">
                                      <h6 class="small text-muted fw-bold border-bottom dark:border-gray-700 pb-1 mb-2">Chữ/Ký tự</h6>
                                      <div class="d-flex flex-wrap gap-1 custom-scrollbar-y" style="max-height: 150px; overflow-y: auto;">
                                         <div v-for="val in getSortedValues(attrId).alpha" :key="val.id"
                                              class="badge border d-flex align-items-center p-0 shadow-sm transition-all"
                                              :class="v.attributes[attrId] == val.id ? 'bg-urban text-white border-urban' : 'bg-white text-dark dark:bg-[#212529] dark:text-gray-200 dark:border-gray-600 hover-border-urban'">
                                            <span class="cursor-pointer px-2 py-1 flex-grow-1 text-center" @click="selectAttrValue(index, attrId, val.id)">{{ val.value }}</span>
                                            <div class="border-start dark:border-gray-600 px-1 cursor-pointer text-danger hover-danger d-flex align-items-center justify-content-center" style="height: 100%;" @click.stop="deleteAttributeValue(val.id, attrId)" title="Xóa rác">
                                              <i class="bi bi-x fs-6"></i>
                                            </div>
                                         </div>
                                         <span v-if="getSortedValues(attrId).alpha.length === 0" class="text-muted small fst-italic">Trống</span>
                                      </div>
                                    </div>
                                    <div class="col-6 border-start dark:border-gray-700">
                                      <h6 class="small text-muted fw-bold border-bottom dark:border-gray-700 pb-1 mb-2">Chữ Số</h6>
                                      <div class="d-flex flex-wrap gap-1 custom-scrollbar-y" style="max-height: 150px; overflow-y: auto;">
                                         <div v-for="val in getSortedValues(attrId).numeric" :key="val.id"
                                              class="badge border d-flex align-items-center p-0 shadow-sm transition-all"
                                              :class="v.attributes[attrId] == val.id ? 'bg-urban text-white border-urban' : 'bg-white text-dark dark:bg-[#212529] dark:text-gray-200 dark:border-gray-600 hover-border-urban'">
                                            <span class="cursor-pointer px-2 py-1 flex-grow-1 text-center" @click="selectAttrValue(index, attrId, val.id)">{{ val.value }}</span>
                                            <div class="border-start dark:border-gray-600 px-1 cursor-pointer text-danger hover-danger d-flex align-items-center justify-content-center" style="height: 100%;" @click.stop="deleteAttributeValue(val.id, attrId)" title="Xóa rác">
                                              <i class="bi bi-x fs-6"></i>
                                            </div>
                                         </div>
                                         <span v-if="getSortedValues(attrId).numeric.length === 0" class="text-muted small fst-italic">Trống</span>
                                      </div>
                                    </div>
                                  </div>
                                  <hr class="dark:border-gray-700 my-2">
                                  <button type="button" class="btn btn-sm btn-light dark:bg-[#2b3035] text-success w-100 fw-bold border-dashed dark:border-gray-600" @click.stop="openCreateValueModal(attrId, index); activeDropdown = null">
                                    <i class="bi bi-plus-lg"></i> Thêm giá trị mới
                                  </button>
                                </div>
                              </transition>
                            </div>
                          </td>

                          <!-- ĐÃ FIX NÂNG CẤP: Dùng Input Type Text và Format hàng ngàn, không dùng Small Text bên dưới nữa -->
                          <td class="align-middle">
                            <input type="text" class="form-control form-control-sm text-end fw-bold dark:bg-[#212529] dark:border-gray-600 dark:text-white" 
                                   :value="formatThousand(v.cost_price)" @input="e => updateNumber(e, v, 'cost_price')" required>
                          </td>
                          <td class="align-middle">
                            <input type="text" class="form-control form-control-sm text-end fw-bold text-urban dark:bg-[#212529] dark:border-gray-600" 
                                   :class="{ 'is-invalid': v.priceError }" 
                                   :value="formatThousand(v.price)" @input="e => { updateNumber(e, v, 'price'); validateRow(index); }" required>
                          </td>
                          <td class="align-middle">
                            <input type="text" class="form-control form-control-sm text-end text-danger fw-semibold dark:bg-[#212529] dark:border-gray-600" 
                                   :class="{ 'is-invalid': v.saleError }" 
                                   :value="formatThousand(v.promotional_price)" @input="e => { updateNumber(e, v, 'promotional_price', true); validateRow(index); }">
                          </td>
                          <!-- Tồn kho giữ nguyên type number -->
                          <td class="align-middle">
                            <input type="number" class="form-control form-control-sm text-center fw-bold dark:bg-[#212529] dark:border-gray-600 dark:text-white" v-model.number="v.stock_quantity" min="0" required>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-1">
                              <button type="button" class="btn btn-sm text-primary hover-opacity border-0" @click="duplicateVariantRow(index)" title="Nhân bản dòng này"><i class="bi bi-copy fs-6"></i></button>
                              <button type="button" class="btn btn-sm text-secondary hover-danger border-0" @click="removeVariantRow(index)" title="Xóa dòng"><i class="bi bi-x-lg fs-6"></i></button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="card-footer bg-light dark:bg-[#212529] py-3 d-flex justify-content-between align-items-center border-top dark:border-gray-700">
                  <button type="button" class="btn btn-outline-urban fw-bold px-3 btn-sm rounded-pill shadow-sm" @click="addVariantRow">
                    <i class="bi bi-plus-circle-dotted me-2"></i>Thêm dòng biến thể
                  </button>
                </div>
              </div>

              <div class="d-flex justify-content-between pt-2 border-top dark:border-gray-700 mt-4 pt-4">
                <button type="button" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 px-4 border fw-semibold rounded-pill" @click="currentStep = 1">
                  <i class="bi bi-arrow-left me-1"></i> Trở lại Bước 1
                </button>
                <button type="button" class="btn btn-urban text-white px-5 py-2 fw-bold shadow-sm rounded-pill" @click="proceedToStep3" :disabled="variants.length === 0">
                  Tiếp tục: Thư viện ảnh <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- BƯỚC 3: THƯ VIỆN ẢNH VÀ XUẤT BẢN -->
            <div v-show="currentStep === 3" class="animation-fade-in">
              <div class="row g-4">
                
                <div class="col-lg-8">
                  <div class="card border-0 shadow-sm rounded-4 h-100 dark:bg-[#1a2533]">
                    <div class="card-body p-4 p-md-5">
                      <h5 class="fw-bold mb-1 text-dark dark:text-white"><i class="bi bi-images me-2 text-urban"></i>Bộ sưu tập (Gallery)</h5>
                      <p class="text-muted small mb-4">Tải lên tối đa 8 ảnh các góc chụp của sản phẩm. Định dạng JPG, PNG, WEBP.</p>
                      
                      <div class="d-flex flex-wrap gap-3">
                        <div class="gallery-upload-box d-flex flex-column justify-content-center align-items-center border border-dashed border-2 rounded-4 text-muted cursor-pointer hover-bg-light dark:border-gray-600 dark:text-gray-400 bg-white dark:bg-[#212529]" 
                             @click="$refs.galleryInput.click()" 
                             style="width: 120px; height: 120px;">
                          <i class="bi bi-cloud-plus fs-2"></i>
                          <span class="small fw-semibold mt-1">Thêm ảnh</span>
                          <input type="file" ref="galleryInput" @change="handleGalleryUpload" class="d-none" accept="image/*" multiple>
                        </div>

                        <div class="position-relative border rounded-4 overflow-hidden shadow-sm dark:border-gray-600" 
                             style="width: 120px; height: 120px;" 
                             v-for="(img, index) in galleryPreviews" :key="index">
                          <img :src="img.url" class="w-100 h-100 object-fit-cover">
                          <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center opacity-75 hover-opacity-100" 
                                  style="width: 24px; height: 24px;" 
                                  @click.stop="removeGalleryImage(index)">
                            <i class="bi bi-x"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-4">
                   <div class="card border-0 shadow-sm rounded-4 h-100 dark:bg-[#1a2533]">
                     <div class="card-body p-4 d-flex flex-column">
                       <h6 class="fw-bold mb-3 text-dark dark:text-white"><i class="bi bi-check-circle me-2 text-success"></i>Trạng thái hiển thị</h6>
                       
                       <div class="form-check form-switch p-3 bg-light dark:bg-[#212529] rounded-3 border dark:border-gray-700 d-flex align-items-center justify-content-between mb-auto shadow-sm">
                          <div>
                            <label class="form-check-label fw-bold text-dark dark:text-white mb-1" for="publishSwitch" style="user-select: none;">Xuất bản công khai</label>
                            <div class="text-muted" style="font-size: 0.75rem;">Bật để hiện trên Web/App</div>
                          </div>
                          <input class="form-check-input fs-4 m-0 cursor-pointer" type="checkbox" id="publishSwitch" v-model="form.isPublished">
                       </div>
                       
                       <hr class="dark:border-gray-700 my-4">

                       <button type="submit" class="btn btn-urban btn-lg text-white w-100 fw-bold shadow-sm rounded-pill d-flex align-items-center justify-content-center" :disabled="isSaving">
                          <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                          <i class="bi bi-floppy2-fill me-2" v-else></i> LƯU HOÀN TẤT
                       </button>
                     </div>
                   </div>
                </div>

              </div>
              <div class="pt-4 mt-4 border-top dark:border-gray-700 text-start">
                <button type="button" class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 px-4 border fw-semibold rounded-pill" @click="currentStep = 2"><i class="bi bi-arrow-left"></i> Quay lại Lưới biến thể</button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- MODAL TẠO THUỘC TÍNH MỚI (CỘT) -->
    <div class="modal fade" id="createAttrModal" tabindex="-1">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header py-3 bg-urban text-white">
            <h6 class="modal-title fw-bold"><i class="bi bi-tag-fill me-2"></i>Tạo thuộc tính (Cột)</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-4">
              <label class="form-label small fw-bold dark:text-gray-200">Tên thuộc tính hệ thống</label>
              <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-none" v-model="newAttrForm.name" placeholder="VD: Chất liệu, Size..." @keydown.enter.prevent="submitCreateAttribute">
            </div>
            <button type="button" class="btn btn-urban w-100 fw-bold rounded-pill shadow-sm" @click="submitCreateAttribute" :disabled="!newAttrForm.name">Lưu Thuộc Tính</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL TẠO GIÁ TRỊ THUỘC TÍNH (BULK CREATE DẤU PHẨY) -->
    <div class="modal fade" id="createValueModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header py-3 bg-success text-white">
            <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Thêm nhanh giá trị biến thể</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-4">
              <label class="form-label small fw-bold dark:text-gray-200">
                Nhập các giá trị cho <span class="text-success text-uppercase fs-6">{{ currentOperatingAttr ? currentOperatingAttr.name : '' }}</span>
              </label>
              <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-none mb-2" 
                     v-model="newValueForm.value" 
                     placeholder="VD: S, M, L, XL, XXL..." 
                     @keydown.enter.prevent="submitCreateValue" ref="newValueInputRef">
              <small class="text-muted fst-italic">Mẹo: Gõ nhiều giá trị cách nhau bằng dấu phẩy (,) để tạo hàng loạt cùng lúc.</small>
            </div>
            <button type="button" class="btn btn-success w-100 fw-bold rounded-pill shadow-sm" @click="submitCreateValue" :disabled="!newValueForm.value">Lưu Giá Trị</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL QUẢN LÝ THUỘC TÍNH VÀ GIÁ TRỊ (XÓA RÁC - GRID 2 CỘT CHỮ VÀ SỐ) -->
    <div class="modal fade" id="manageAttrModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header py-3 bg-secondary text-white">
            <h6 class="modal-title fw-bold"><i class="bi bi-gear-fill me-2"></i>Quản lý Thuộc tính & Giá trị</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label small fw-bold dark:text-gray-200">Chọn thuộc tính cần dọn dẹp/sửa:</label>
              <select class="form-select dark:bg-[#212529] dark:text-white dark:border-gray-700 fw-semibold" v-model="selectedAttrToManage">
                <option value="">-- Chọn thuộc tính --</option>
                <template v-if="systemAttributes.length > 0">
                  <option v-for="attr in systemAttributes" :key="attr.id" :value="attr.id">{{ attr.name }}</option>
                </template>
              </select>
            </div>

            <!-- Khu vực chỉnh sửa khi đã chọn 1 thuộc tính -->
            <div v-if="selectedAttrToManage" class="mt-4 pt-3 border-top dark:border-gray-700 animation-fade-in">
              <div class="mb-3">
                <label class="form-label small fw-bold dark:text-gray-200">Tên hiển thị mới:</label>
                <div class="input-group shadow-sm">
                  <input type="text" class="form-control dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="manageAttrName" @keydown.enter.prevent="updateAttribute(selectedAttrToManage)">
                  <button type="button" class="btn btn-primary px-3 fw-bold" @click="updateAttribute(selectedAttrToManage)" :disabled="!manageAttrName">Cập nhật</button>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label small fw-bold dark:text-gray-200 d-block">Danh sách giá trị (Nhấn 'x' để xóa rác):</label>
                <div class="row g-2 p-2 bg-light dark:bg-[#212529] rounded-3 border dark:border-gray-700">
                  <div class="col-6">
                     <h6 class="small text-muted fw-bold text-center border-bottom pb-1 mb-2">Chữ cái / Ký tự</h6>
                     <div class="d-flex flex-column gap-2 pe-1 custom-scrollbar-y" style="max-height: 200px; overflow-y: auto;">
                        <div v-for="val in sortedAttrValues.alpha" :key="val.id" class="d-flex justify-content-between align-items-center bg-white dark:bg-[#1a2533] border dark:border-gray-600 rounded px-2 py-1 shadow-sm">
                           <span class="fw-semibold text-dark dark:text-gray-200 small text-truncate" :title="val.value">{{ val.value }}</span>
                           <button type="button" class="btn btn-sm text-danger p-0 border-0 ms-2 flex-shrink-0" @click="deleteAttributeValueModal(val.id, selectedAttrToManage)" title="Xóa rác">
                              <i class="bi bi-x-circle-fill fs-6"></i>
                           </button>
                        </div>
                        <div v-if="sortedAttrValues.alpha.length === 0" class="text-center text-muted small fst-italic py-2">Trống</div>
                     </div>
                  </div>
                  <div class="col-6 border-start dark:border-gray-600">
                     <h6 class="small text-muted fw-bold text-center border-bottom pb-1 mb-2">Chữ Số</h6>
                     <div class="d-flex flex-column gap-2 pe-1 custom-scrollbar-y" style="max-height: 200px; overflow-y: auto;">
                        <div v-for="val in sortedAttrValues.numeric" :key="val.id" class="d-flex justify-content-between align-items-center bg-white dark:bg-[#1a2533] border dark:border-gray-600 rounded px-2 py-1 shadow-sm">
                           <span class="fw-semibold text-dark dark:text-gray-200 small text-truncate" :title="val.value">{{ val.value }}</span>
                           <button type="button" class="btn btn-sm text-danger p-0 border-0 ms-2 flex-shrink-0" @click="deleteAttributeValueModal(val.id, selectedAttrToManage)" title="Xóa rác">
                              <i class="bi bi-x-circle-fill fs-6"></i>
                           </button>
                        </div>
                        <div v-if="sortedAttrValues.numeric.length === 0" class="text-center text-muted small fst-italic py-2">Trống</div>
                     </div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-start pt-2">
                <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-semibold" @click="deleteAttribute(selectedAttrToManage)">
                  <i class="bi bi-trash me-1"></i> Xóa toàn bộ cột thuộc tính
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const isPageLoading = ref(true);
const isSaving = ref(false);
const isProcessingSchema = ref(false);
const currentStep = ref(1);

const categories = ref([]);
const systemAttributes = ref([]);
const brands = ref([]);

const form = ref({
  category_id: '', brand_id: '', name: '', slug: '', base_price: 0, description: '', care_instructions: '', gender: 'Unisex', fit_type: '', is_featured: false, isPublished: false
});

const thumbnailFile = ref(null);
const thumbnailPreview = ref(null);

const activeAttributes = ref([]);
const selectedAttrToAdd = ref('');
const variants = ref([]);

const galleryFiles = ref([]); 
const galleryPreviews = ref([]); 

let createAttrModalObj = null;
let createValueModalObj = null;
let manageAttrModalObj = null;

const newAttrForm = ref({ name: '' });
const newValueForm = ref({ value: '' });
const currentOperatingAttr = ref(null);
const currentOperatingRowIndex = ref(null);
const newValueInputRef = ref(null);

const selectedAttrToManage = ref('');
const manageAttrName = ref('');

// LOGIC CUSTOM VUE DROPDOWN
const activeDropdown = ref(null);
const toggleDropdown = (rowIndex, attrId) => {
  const id = `${rowIndex}-${attrId}`;
  activeDropdown.value = activeDropdown.value === id ? null : id;
};
const closeDropdowns = () => {
  activeDropdown.value = null;
};

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

// ĐÃ THAY ĐỔI: Format nghìn (Thêm dấu chấm) dành cho Input Type Text
const formatThousand = (val) => {
  if (val === null || val === undefined || val === '') return '';
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// ĐÃ THAY ĐỔI: Cập nhật biến số thô khi người dùng gõ
const updateNumber = (e, targetObj, key, allowEmpty = false) => {
  let rawValue = e.target.value.replace(/\D/g, ''); // Loại bỏ mọi thứ không phải là số
  if (rawValue === '') {
    targetObj[key] = allowEmpty ? null : 0;
    e.target.value = '';
  } else {
    let numValue = parseInt(rawValue, 10);
    targetObj[key] = numValue;
    e.target.value = formatThousand(numValue);
  }
};

// ĐÃ THAY ĐỔI: Cập nhật hàm này để triệt tiêu phần thập phân '.00' vô nghĩa
const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val);
};

const canProceedToStep2 = computed(() => {
  return form.value.name && form.value.category_id && form.value.base_price >= 0 && thumbnailFile.value;
});

const proceedIfValid = (step) => {
  if (step === 2 && canProceedToStep2.value) proceedToStep2();
  if (step === 3 && variants.value.length > 0) proceedToStep3();
};

const generateSlug = () => {
  let s = form.value.name.toLowerCase();
  s = s.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
  s = s.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
  s = s.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
  s = s.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
  s = s.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
  s = s.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
  s = s.replace(/đ/gi, 'd');
  form.value.slug = s.replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '').replace(/\-\-+/g, '-');
};

const handleThumbUpload = (e) => {
  const f = e.target.files[0];
  if (f) {
    if (f.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh tối đa 5MB', 'error'); return; }
    thumbnailFile.value = f;
    thumbnailPreview.value = URL.createObjectURL(f);
  }
};

const handleGalleryUpload = (e) => {
  const files = Array.from(e.target.files);
  if (galleryPreviews.value.length + files.length > 8) {
    Swal.fire('Lỗi', 'Tối đa 8 ảnh trong thư viện', 'warning'); return;
  }
  files.forEach(f => {
    if (f.size <= 5 * 1024 * 1024) {
      galleryFiles.value.push(f);
      galleryPreviews.value.push({ id: null, url: URL.createObjectURL(f), file: f });
    }
  });
  e.target.value = '';
};

const removeGalleryImage = (index) => {
  galleryFiles.value.splice(index, 1);
  galleryPreviews.value.splice(index, 1);
};

const proceedToStep2 = async () => {
  isProcessingSchema.value = true;
  const selectedCat = categories.value.find(c => c.id === form.value.category_id);

  if (selectedCat && selectedCat.attributes_schema && selectedCat.attributes_schema.length > 0) {
    let addedAnyColumn = false;
    for (const schemaName of selectedCat.attributes_schema) {
      let existingAttr = systemAttributes.value.find(a => a.name.toLowerCase() === schemaName.toLowerCase());
      let attrIdToAdd = null;

      if (existingAttr) {
        attrIdToAdd = existingAttr.id.toString();
      } else {
        try {
          const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/attributes', { name: schemaName }, { headers: getHeaders() });
          res.data.data.values = [];
          systemAttributes.value.push(res.data.data);
          attrIdToAdd = res.data.data.id.toString();
        } catch (e) { console.error('Lỗi tạo', e); }
      }

      if (attrIdToAdd && !activeAttributes.value.includes(attrIdToAdd)) {
        activeAttributes.value.push(attrIdToAdd);
        addedAnyColumn = true;
      }
    }
  }

  if (variants.value.length === 0) addVariantRow();
  else {
    variants.value.forEach(v => {
      if (!v.attributes) v.attributes = {};
      activeAttributes.value.forEach(attrId => {
        if (v.attributes[attrId] === undefined) v.attributes[attrId] = "";
      });
    });
  }
  isProcessingSchema.value = false;
  currentStep.value = 2;
};

const proceedToStep3 = () => {
  validateDuplicates();
  let hasHardError = false;
  variants.value.forEach((v, i) => {
    validateRow(i);
    if (v.priceError || v.saleError || v.attrError || v.hasDuplicateError) hasHardError = true;
  });
  if (hasHardError) {
    Swal.fire('Lỗi Biến thể', 'Vui lòng kiểm tra các dòng bị bôi đỏ (Chưa chọn thuộc tính, sai giá, trùng lặp).', 'error'); return;
  }
  currentStep.value = 3;
};

const getAttributeName = (attrId) => { const a = systemAttributes.value.find(x => x.id == attrId); return a ? a.name : 'Unknown'; };

const getSelectedValueName = (attrId, valueId) => {
  if (!valueId) return '-- Chọn --';
  const attr = systemAttributes.value.find(a => a.id === parseInt(attrId));
  if (!attr || !attr.values) return '-- Chọn --';
  const val = attr.values.find(v => v.id === parseInt(valueId));
  return val ? val.value : '-- Chọn --';
};

const getSortedValues = (attrId) => {
  const attr = systemAttributes.value.find(a => a.id === parseInt(attrId));
  if (!attr || !attr.values) return { alpha: [], numeric: [] };

  const alpha = [];
  const numeric = [];
  attr.values.forEach(v => {
    if (/^\d/.test(v.value)) numeric.push(v);
    else alpha.push(v);
  });
  return { alpha, numeric };
};

const selectAttrValue = (rowIndex, attrId, valId) => {
  variants.value[rowIndex].attributes[attrId] = valId;
  activeDropdown.value = null; // Tự đóng sau khi chọn
  validateDuplicates();
};

const addAttributeColumn = () => {
  if (!selectedAttrToAdd.value) return;
  if (!activeAttributes.value.includes(selectedAttrToAdd.value.toString())) {
    activeAttributes.value.push(selectedAttrToAdd.value.toString());
    variants.value.forEach(v => { if (!v.attributes) v.attributes = {}; v.attributes[selectedAttrToAdd.value.toString()] = ""; });
  }
  selectedAttrToAdd.value = '';
};

const removeAttributeColumn = (attrId) => {
  Swal.fire({ title: 'Gỡ cột?', text: "Các giá trị đang chọn ở cột này sẽ bị xóa. Tiếp tục?", icon: 'warning', showCancelButton: true }).then((result) => {
    if (result.isConfirmed) {
      activeAttributes.value = activeAttributes.value.filter(id => id != attrId);
      variants.value.forEach(v => { delete v.attributes[attrId]; });
      validateDuplicates();
    }
  });
};

const addVariantRow = () => {
  const randomCode = Math.floor(1000 + Math.random() * 9000);
  const prefix = form.value.slug ? form.value.slug.substring(0, 4).toUpperCase().replace(/-/g, '') : 'SKU';
  const newSku = `${prefix}${randomCode}-V${variants.value.length + 1}`;
  let rowAttrs = {}; activeAttributes.value.forEach(id => rowAttrs[id] = "");
  variants.value.push({ sku: newSku, cost_price: 0, price: form.value.base_price, promotional_price: null, stock_quantity: 10, imageFile: null, preview: null, attributes: rowAttrs, hasDuplicateError: false, attrError: false, priceError: false, saleError: false });
};

const removeVariantRow = (index) => {
  if (variants.value.length <= 1) { Swal.fire('Lưu ý', 'Sản phẩm phải có ít nhất 1 biến thể!', 'warning'); return; }
  variants.value.splice(index, 1); validateDuplicates();
};

const duplicateVariantRow = (index) => {
  const rowToCopy = variants.value[index];
  const randomCode = Math.floor(1000 + Math.random() * 9000);
  const prefix = form.value.slug ? form.value.slug.substring(0, 4).toUpperCase().replace(/-/g, '') : 'SKU';
  const newSku = `${prefix}${randomCode}-V${variants.value.length + 1}`;

  const copiedAttrs = { ...rowToCopy.attributes };

  variants.value.splice(index + 1, 0, {
    sku: newSku, 
    cost_price: rowToCopy.cost_price,
    price: rowToCopy.price,
    promotional_price: rowToCopy.promotional_price,
    stock_quantity: rowToCopy.stock_quantity,
    imageFile: rowToCopy.imageFile, 
    preview: rowToCopy.preview, 
    attributes: copiedAttrs,
    hasDuplicateError: false,
    attrError: false,
    priceError: false,
    saleError: false
  });

  validateDuplicates();
};

const handleVariantImage = (index, e) => {
  const f = e.target.files[0];
  if (f) { variants.value[index].imageFile = f; variants.value[index].preview = URL.createObjectURL(f); }
};

const validateRow = (index) => {
  const v = variants.value[index];
  v.priceError = v.price <= 0 || v.price === '';
  v.saleError = parseFloat(v.promotional_price) > parseFloat(v.price);
};

const validateDuplicates = () => {
  if (activeAttributes.value.length === 0) return false;
  const seen = new Set(); let hasDuplicate = false;
  variants.value.forEach((v, i) => {
    v.attrError = false; v.hasDuplicateError = false;
    let isFullSelected = true; let sigArray = [];
    activeAttributes.value.forEach(attrId => { const val = v.attributes[attrId]; if (!val) isFullSelected = false; sigArray.push(val); });
    if (!isFullSelected) v.attrError = true;
    else {
      const signature = sigArray.join('-');
      if (seen.has(signature)) {
        v.hasDuplicateError = true; hasDuplicate = true;
        const firstDupIdx = variants.value.findIndex(x => { let sArray = []; activeAttributes.value.forEach(a => sArray.push(x.attributes[a])); return sArray.join('-') === signature; });
        if (firstDupIdx !== -1) variants.value[firstDupIdx].hasDuplicateError = true;
      } else seen.add(signature);
    }
  });
  if (hasDuplicate) Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Có biến thể trùng lặp cấu hình!', showConfirmButton: false, timer: 3000 });
  return hasDuplicate;
};

const openModal = (id) => {
  const m = new window.bootstrap.Modal(document.getElementById(id));
  if (id === 'createAttrModal') createAttrModalObj = m;
  if (id === 'createValueModal') createValueModalObj = m;
  if (id === 'manageAttrModal') manageAttrModalObj = m;
  m.show();
};

const hideModals = () => { 
  if (createAttrModalObj) createAttrModalObj.hide(); 
  if (createValueModalObj) createValueModalObj.hide(); 
  if (manageAttrModalObj) manageAttrModalObj.hide();
};

const submitCreateAttribute = async () => {
  if (!newAttrForm.value.name) return;
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/attributes', { name: newAttrForm.value.name }, { headers: getHeaders() });
    res.data.data.values = [];
    systemAttributes.value.push(res.data.data);
    hideModals(); newAttrForm.value.name = '';
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm thuộc tính', showConfirmButton: false, timer: 2000 });
  } catch (e) { if (e.response) Swal.fire('Lỗi', e.response.data.message || 'Lỗi thêm', 'error'); }
};

const openCreateValueModal = (attrId, rowIndex) => {
  currentOperatingAttr.value = systemAttributes.value.find(x => x.id === parseInt(attrId));
  currentOperatingRowIndex.value = rowIndex; 
  newValueForm.value.value = '';
  openModal('createValueModal');
  nextTick(() => { if (newValueInputRef.value) newValueInputRef.value.focus(); });
};

const submitCreateValue = async () => {
  if (!newValueForm.value.value || !currentOperatingAttr.value) return;
  try {
    const valuesArray = newValueForm.value.value.split(',').map(v => v.trim()).filter(v => v !== '');
    if(valuesArray.length === 0) return;

    const promises = valuesArray.map(val => {
      const payload = { attribute_id: currentOperatingAttr.value.id, value: val };
      return axios.post('http://127.0.0.1:8000/api/v1/admin/attribute-values', payload, { headers: getHeaders() });
    });

    const results = await Promise.all(promises);

    const attrObj = systemAttributes.value.find(x => x.id == currentOperatingAttr.value.id);
    if (attrObj) { 
      if (!attrObj.values) attrObj.values = []; 
      results.forEach(res => {
         attrObj.values.push(res.data.data); 
      });
    }
    
    if (currentOperatingRowIndex.value !== null && results.length > 0) {
       variants.value[currentOperatingRowIndex.value].attributes[attrObj.id] = results[0].data.data.id;
    }
    
    hideModals(); 
    validateDuplicates();
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Đã tạo nhanh ${valuesArray.length} giá trị`, showConfirmButton: false, timer: 2000 });
  } catch (e) { 
    if (e.response) Swal.fire('Lỗi', e.response.data.message || 'Lỗi thêm', 'error'); 
  }
};

watch(selectedAttrToManage, (newId) => {
    if (newId) {
        const attr = systemAttributes.value.find(a => a.id === parseInt(newId));
        if (attr) manageAttrName.value = attr.name;
    } else { manageAttrName.value = ''; }
});

const updateAttribute = async (id) => {
    if (!manageAttrName.value || !id) return;
    try {
        await axios.put(`${import.meta.env.VITE_API_BASE_URL}/admin/attributes/${id}`, { name: manageAttrName.value }, { headers: getHeaders() });
        const attr = systemAttributes.value.find(a => a.id === parseInt(id));
        if (attr) attr.name = manageAttrName.value;
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật thành công', showConfirmButton: false, timer: 2000 });
    } catch (e) { if (e.response) Swal.fire('Lỗi', e.response.data.message || 'Lỗi cập nhật', 'error'); }
};

const deleteAttribute = async (id) => {
    if (!id) return;
    Swal.fire({ title: 'Xóa thuộc tính?', icon: 'warning', showCancelButton: true }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/attributes/${id}`, { headers: getHeaders() });
                systemAttributes.value = systemAttributes.value.filter(a => a.id !== parseInt(id));
                selectedAttrToManage.value = '';
                if (manageAttrModalObj) manageAttrModalObj.hide();
                if (activeAttributes.value.includes(id.toString())) removeAttributeColumn(id.toString());
            } catch (e) { if (e.response) Swal.fire('Lỗi', e.response.data.message || 'Không thể xóa', 'error'); }
        }
    });
};

const deleteAttributeValueModal = async (valueId, attrId) => {
  if (!valueId) return;

  Swal.fire({
    title: 'Xóa giá trị này?',
    text: "Các sản phẩm đang dùng giá trị này sẽ bị xóa cấu hình. Bạn chắc chắn chứ?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý Xóa'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/admin/attribute-values/${valueId}`, { headers: getHeaders() });

        const attr = systemAttributes.value.find(a => a.id === parseInt(attrId));
        if (attr && attr.values) {
          attr.values = attr.values.filter(v => v.id !== valueId);
        }

        variants.value.forEach(v => {
          if (v.attributes[attrId] == valueId) {
            v.attributes[attrId] = '';
          }
        });
        
        validateDuplicates();
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã dọn dẹp giá trị rác', showConfirmButton: false, timer: 1500 });
      } catch (e) {
        if (e.response) Swal.fire('Lỗi', e.response.data.message || 'Giá trị này đang được sử dụng ở sản phẩm khác, không thể xóa!', 'error');
      }
    }
  });
};

const sortedAttrValues = computed(() => {
  if (!selectedAttrToManage.value) return { alpha: [], numeric: [] };
  const attr = systemAttributes.value.find(a => a.id === parseInt(selectedAttrToManage.value));
  if (!attr || !attr.values) return { alpha: [], numeric: [] };

  const alpha = [];
  const numeric = [];
  attr.values.forEach(v => {
    if (/^\d/.test(v.value)) numeric.push(v);
    else alpha.push(v);
  });
  return { alpha, numeric };
});

const submitProduct = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    if (form.value.brand_id) formData.append('brand_id', form.value.brand_id);
    formData.append('name', form.value.name);
    formData.append('slug', form.value.slug);
    formData.append('base_price', form.value.base_price);
    formData.append('description', form.value.description);
    formData.append('care_instructions', form.value.care_instructions);
    
    formData.append('gender', form.value.gender || 'Unisex');
    formData.append('fit_type', form.value.fit_type);
    formData.append('is_featured', form.value.is_featured ? 1 : 0);
    formData.append('status', form.value.isPublished ? 'published' : 'draft');
    
    if (thumbnailFile.value) {
        formData.append('thumbnail_image', thumbnailFile.value);
    }

    const variantsPayload = variants.value.map(v => ({
      sku: v.sku, cost_price: v.cost_price || 0, price: v.price, promotional_price: v.promotional_price || null, stock_quantity: v.stock_quantity, attributes: v.attributes
    }));
    formData.append('variants_data', JSON.stringify(variantsPayload));

    variants.value.forEach((v, index) => {
      if (v.imageFile) formData.append(`variant_image_${index}`, v.imageFile);
    });

    galleryFiles.value.forEach((f) => {
      formData.append(`gallery_images[]`, f);
    });

    const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/products', formData, { headers: getHeaders() });
    
    Swal.fire({ icon: 'success', title: 'Tạo thành công', text: res.data.message, timer: 2000, showConfirmButton: false }).then(() => {
      router.push({ name: 'admin-products' });
    });
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
       let errorHtml = '<ul class="text-start text-danger small mt-2" style="max-height: 200px; overflow-y: auto;">';
       Object.values(e.response.data.errors).flat().forEach(msg => { errorHtml += `<li>${msg}</li>`; });
       errorHtml += '</ul>';
       Swal.fire({ title: 'Dữ liệu không hợp lệ', html: errorHtml, icon: 'error' });
    } else {
       Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi lưu sản phẩm.', 'error');
    }
  } finally { isSaving.value = false; }
};

const fetchData = async () => {
  try {
    const [catRes, attrRes, brandRes] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/attributes', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/brands', { headers: getHeaders() })
    ]);
    
    const allCats = Array.isArray(catRes.data.data) ? catRes.data.data : [];
    categories.value = allCats.filter(c => c.status === 'active' || c.status === 'published');
    
    const allBrands = Array.isArray(brandRes.data.data) ? brandRes.data.data : [];
    brands.value = allBrands.filter(b => b.status === 'active');

    systemAttributes.value = Array.isArray(attrRes.data.data) ? attrRes.data.data : [];
  } catch (e) { console.error('Lỗi tải dữ liệu', e); } finally { isPageLoading.value = false; }
};

onMounted(() => {
  fetchData();
  document.addEventListener('click', closeDropdowns);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', closeDropdowns);
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.hover-bg-light:hover { background-color: rgba(84, 119, 146, 0.05) !important; }
html.dark .hover-bg-light:hover { background-color: rgba(255, 255, 255, 0.05) !important; }

/* Cấm bôi đen text */
.user-select-none { 
  user-select: none !important; 
  -webkit-user-select: none !important; 
  -moz-user-select: none !important; 
}

.custom-tab { color: #6c757d; border-bottom: 3px solid transparent; transition: all 0.3s; }
.custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; border-bottom-color: var(--color-c-hover, #547792); }

.step-circle { display: inline-flex; width: 24px; height: 24px; border-radius: 50%; background: #e9ecef; color: #6c757d; align-items: center; justify-content: center; font-size: 0.8rem; }
.active-tab .step-circle { background: var(--color-c-hover, #547792); color: white; }

.cursor-pointer { cursor: pointer; }

/* CSS FIX NÚT ACTIONS & XÓA RÁC */
.hover-opacity:hover { opacity: 0.7; }
.hover-danger:hover { color: #dc3545 !important; }
.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; }
.hover-scale:hover { transform: scale(1.15); transition: transform 0.2s ease; }

.variant-table th { font-size: 0.75rem; text-transform: uppercase; color: #555; vertical-align: middle; text-align: center; border-bottom: 2px solid #e9ecef; white-space: nowrap; padding: 12px; }
.variant-table th.bg-urban { background-color: var(--color-c-hover, #547792) !important; color: white !important; }
.variant-table td { vertical-align: middle; padding: 8px; }
html.dark .variant-table th { border-bottom-color: #373b3e; }
html.dark .variant-table th.bg-urban { background-color: var(--color-c-hover, #547792) !important; }

.img-preview-sm { width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; background: #fff; transition: transform 0.2s; cursor: pointer; }
.img-preview-sm:hover { transform: scale(1.1); border-color: var(--color-c-hover, #547792); }

.is-invalid { border-color: #dc3545 !important; }
.row-error td { background-color: #fff5f5 !important; }
html.dark .row-error td { background-color: rgba(220, 53, 69, 0.1) !important; }

.aspect-ratio-1 { aspect-ratio: 1 / 1; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Logo Shimmer Loading */
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* Custom Vue Dropdown Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>