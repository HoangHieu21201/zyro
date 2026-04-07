<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Http\Requests\Banner\StoreBannerRequest;
use App\Http\Requests\Banner\UpdateBannerRequest;
use App\Events\BannerEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    // Hàm tự sinh số thứ tự
    private function getNextSortOrder($position): int
    {
        $max = Banner::where('position', $position)->max('sort_order');
        return is_numeric($max) ? $max + 1 : 1;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Banner::withTrashed()
                ->orderBy('position', 'asc')
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderBy('id', 'desc');

            if ($request->has('position') && $request->position !== '') {
                $query->where('position', $request->position);
            }

            $banners = $query->get();
            return response()->json(['success' => true, 'data' => $banners]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['click_count'] = 0;
            
            // Nếu public thì cấp số thứ tự
            if ($data['status'] === 'active') {
                $data['sort_order'] = $this->getNextSortOrder($data['position']);
            }

            if ($request->hasFile('image_desktop')) {
                $data['image_desktop'] = $request->file('image_desktop')->store('banners/desktop', 'public');
            }
            if ($request->hasFile('image_mobile')) {
                $data['image_mobile'] = $request->file('image_mobile')->store('banners/mobile', 'public');
            }

            $banner = Banner::create($data);
            broadcast(new BannerEvent('created', $banner))->toOthers();

            return response()->json(['success' => true, 'message' => 'Thêm Banner thành công!', 'data' => $banner], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $banner = Banner::withTrashed()->findOrFail($id);
            return response()->json(['success' => true, 'data' => $banner]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy Banner.'], 404);
        }
    }

    public function update(UpdateBannerRequest $request, $id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $data = $request->validated();

            // Nếu đổi Vị trí (Position) hoặc trạng thái thì cấp lại thứ tự
            if (($data['position'] !== $banner->position) || ($data['status'] !== $banner->status)) {
                $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder($data['position']) : null;
            }

            // Xử lý Ảnh Desktop
            if ($request->hasFile('image_desktop')) {
                if ($banner->image_desktop) Storage::disk('public')->delete($banner->image_desktop);
                $data['image_desktop'] = $request->file('image_desktop')->store('banners/desktop', 'public');
            }

            // Xử lý Ảnh Mobile
            if ($request->hasFile('image_mobile')) {
                if ($banner->image_mobile) Storage::disk('public')->delete($banner->image_mobile);
                $data['image_mobile'] = $request->file('image_mobile')->store('banners/mobile', 'public');
            } elseif (isset($data['remove_mobile']) && filter_var($data['remove_mobile'], FILTER_VALIDATE_BOOLEAN)) {
                if ($banner->image_mobile) Storage::disk('public')->delete($banner->image_mobile);
                $data['image_mobile'] = null;
            }

            $banner->update($data);
            broadcast(new BannerEvent('updated', $banner))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật Banner thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,hidden']);
        try {
            $banner = Banner::findOrFail($id);
            $sortOrder = ($request->status === 'active') ? $this->getNextSortOrder($banner->position) : null;
            
            $banner->update(['status' => $request->status, 'sort_order' => $sortOrder]);
            
            broadcast(new BannerEvent('updated', $banner))->toOthers();
            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->sort_order = null; // Xóa thứ tự để nhường chỗ
            $banner->save();
            $banner->delete();

            broadcast(new BannerEvent('deleted', $banner))->toOthers();
            return response()->json(['success' => true, 'message' => 'Đã đưa Banner vào thùng rác.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $banner = Banner::withTrashed()->findOrFail($id);
            if ($banner->status === 'active') {
                $banner->sort_order = $this->getNextSortOrder($banner->position);
            }
            $banner->save();
            $banner->restore();

            broadcast(new BannerEvent('restored', $banner))->toOthers();
            return response()->json(['success' => true, 'message' => 'Khôi phục Banner thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khôi phục: ' . $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => 'required|exists:banners,id',
            'banners.*.sort_order' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->banners as $item) {
                    Banner::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
                }
            });
            return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự hiển thị thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}