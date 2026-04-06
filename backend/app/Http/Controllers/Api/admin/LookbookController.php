<?php

// File: backend/app/Http/Controllers/Api/Admin/LookbookController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lookbook;
use App\Models\LookbookItem;
use App\Http\Requests\Lookbook\StoreLookbookRequest;
use App\Http\Requests\Lookbook\UpdateLookbookRequest;
use App\Events\LookbookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class LookbookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Lookbook::withTrashed()->withCount('items');

            // Hỗ trợ Frontend lọc theo giới tính (Gender)
            if ($request->has('gender') && $request->gender !== '') {
                $query->where('gender', $request->gender);
            }

            $lookbooks = $query->orderBy('id', 'desc')->paginate(12);

            return response()->json(['success' => true, 'data' => $lookbooks]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $lookbook = Lookbook::withTrashed()
                ->with(['items.product:id,name,slug,thumbnail_image,base_price,status'])
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $lookbook]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy Lookbook.'], 404);
        }
    }

    public function store(StoreLookbookRequest $request): JsonResponse
    {
        try {
            $lookbook = DB::transaction(function () use ($request) {
                // Biến $data đã chứa toàn bộ dữ liệu bao gồm trường 'gender'
                $data = $request->validated();

                if ($request->hasFile('main_image')) {
                    $data['main_image'] = $request->file('main_image')->store('lookbooks/main', 'public');
                }

                // Tạo Lookbook gốc
                $lookbook = Lookbook::create($data);

                // Xử lý các sản phẩm được ghim tọa độ
                $itemsData = json_decode($request->input('items_data'), true);
                if (is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        LookbookItem::create([
                            'lookbook_id'     => $lookbook->id,
                            'product_id'      => $item['product_id'],
                            'pin_coordinates' => $item['pin_coordinates'],
                            'sort_order'      => $item['sort_order'] ?? 0
                        ]);
                    }
                }

                return $lookbook;
            });

            $lookbook->load('items');
            broadcast(new LookbookEvent('created', $lookbook))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo Lookbook thành công!', 'data' => $lookbook], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateLookbookRequest $request, $id): JsonResponse
    {
        try {
            $lookbook = DB::transaction(function () use ($request, $id) {
                $lookbook = Lookbook::findOrFail($id);
                // Biến $data đã chứa toàn bộ dữ liệu bao gồm trường 'gender'
                $data = $request->validated();

                if ($request->hasFile('main_image')) {
                    if ($lookbook->main_image) {
                        Storage::disk('public')->delete($lookbook->main_image);
                    }
                    $data['main_image'] = $request->file('main_image')->store('lookbooks/main', 'public');
                }

                // Cập nhật Lookbook
                $lookbook->update($data);

                // Xóa toàn bộ ghim cũ và nạp ghim mới (Chống rác tọa độ)
                LookbookItem::where('lookbook_id', $lookbook->id)->delete();

                $itemsData = json_decode($request->input('items_data'), true);
                if (is_array($itemsData)) {
                    foreach ($itemsData as $item) {
                        LookbookItem::create([
                            'lookbook_id'     => $lookbook->id,
                            'product_id'      => $item['product_id'],
                            'pin_coordinates' => $item['pin_coordinates'],
                            'sort_order'      => $item['sort_order'] ?? 0
                        ]);
                    }
                }

                return $lookbook;
            });

            $lookbook->load('items');
            broadcast(new LookbookEvent('updated', $lookbook))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật Lookbook thành công!', 'data' => $lookbook]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:published,draft,hidden']);
        
        try {
            $lookbook = Lookbook::findOrFail($id);
            $lookbook->update(['status' => $request->status]);

            broadcast(new LookbookEvent('updated', $lookbook))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $lookbook = Lookbook::findOrFail($id);
            $lookbook->slug = $lookbook->slug . '-deleted-' . time();
            $lookbook->save();
            $lookbook->delete();

            broadcast(new LookbookEvent('deleted', $lookbook))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã đưa Lookbook vào thùng rác.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $lookbook = Lookbook::withTrashed()->findOrFail($id);
            
            $originalSlug = preg_replace('/-deleted-\d+$/', '', $lookbook->slug);
            if (Lookbook::where('slug', $originalSlug)->whereNull('deleted_at')->exists()) {
                return response()->json(['success' => false, 'message' => 'Đường dẫn (Slug) đã bị tài khoản khác sử dụng.'], 400);
            }

            $lookbook->slug = $originalSlug;
            $lookbook->save();
            $lookbook->restore();

            broadcast(new LookbookEvent('restored', $lookbook))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khôi phục Lookbook.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}