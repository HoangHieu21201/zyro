<?php

// File: backend/app/Http/Controllers/Api/Admin/UserController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Events\UserEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $users = User::withTrashed()
                ->with(['tier'])
                ->withCount('addresses')
                ->orderBy('id', 'desc')
                ->get();
                
            return response()->json(['success' => true, 'data' => $users]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi tải danh sách: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $data = $request->validated();
                $data['password'] = Hash::make($data['password']);
                $data['status'] = $data['status'] ?? 'active';

                if ($request->hasFile('avatar')) {
                    $data['avatar_url'] = $request->file('avatar')->store('avatars/users', 'public');
                }

                $newUser = User::create($data);

                // Nếu có nhập địa chỉ thì tạo luôn địa chỉ mặc định
                if (!empty($data['shipping_address']) && !empty($data['city'])) {
                    $newUser->addresses()->create([
                        'customer_name'    => $newUser->full_name,
                        'customer_phone'   => $newUser->phone,
                        'shipping_address' => $data['shipping_address'],
                        'city'             => $data['city'],
                        'district'         => $data['district'] ?? null,
                        'ward'             => $data['ward'] ?? null,
                        'is_default'       => true
                    ]);
                }

                return $newUser;
            });

            $user->load('tier');
            broadcast(new UserEvent('created', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Tạo tài khoản khách hàng thành công!', 'data' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = User::withTrashed()
                ->with(['tier'])
                // Lấy danh sách địa chỉ, ưu tiên địa chỉ mặc định lên đầu
                ->with(['addresses' => function($q) {
                    $q->orderBy('is_default', 'desc')->orderBy('id', 'desc');
                }])
                ->findOrFail($id);
                
            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy khách hàng.'], 404);
        }
    }

    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $data = $request->validated();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar_url) Storage::disk('public')->delete($user->avatar_url);
                $data['avatar_url'] = $request->file('avatar')->store('avatars/users', 'public');
            } elseif (isset($data['remove_avatar']) && filter_var($data['remove_avatar'], FILTER_VALIDATE_BOOLEAN)) {
                if ($user->avatar_url) Storage::disk('public')->delete($user->avatar_url);
                $data['avatar_url'] = null;
            }

            DB::transaction(function () use ($user, $data) {
                $user->update($data);

                // Tự động đồng bộ Tên/SĐT mới vào các Địa chỉ nếu admin đổi Tên/SĐT gốc
                if (array_key_exists('full_name', $data) || array_key_exists('phone', $data)) {
                    $user->addresses()->update([
                        'customer_name'  => $user->full_name,
                        'customer_phone' => $user->phone,
                    ]);
                }
            });

            $user->load('tier');
            broadcast(new UserEvent('updated', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin thành công!', 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete(); 
            
            broadcast(new UserEvent('deleted', $user))->toOthers();

            return response()->json(['success' => true, 'message' => 'Đã khóa và chuyển khách hàng vào thùng rác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            // Chống xung đột Unique khi khôi phục
            $conflict = User::whereNull('deleted_at')
                ->where(function ($q) use ($user) {
                    $q->where('email', $user->email);
                    if ($user->phone) {
                        $q->orWhere('phone', $user->phone);
                    }
                })->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Lỗi: Email hoặc SĐT của khách hàng này đã bị tài khoản mới sử dụng trong thời gian bị xóa!'
                ], 400);
            }

            $user->restore();
            $user->load('tier');
            
            broadcast(new UserEvent('restored', $user))->toOthers();
            
            return response()->json(['success' => true, 'message' => 'Đã khôi phục tài khoản thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }
}