<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReplyMail;
use App\Http\Requests\Admin\AdminReplyContactRequest;
use Illuminate\Support\Facades\DB;

class AdminContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        // Đếm số lượng cho Tabs
        $countQuery = clone $query;
        $rawCounts = $countQuery->select('status', DB::raw('count(*) as total'))
                                ->groupBy('status')
                                ->pluck('total', 'status')
                                ->toArray();

        $counts = [
            'all'     => array_sum($rawCounts),
            'pending' => $rawCounts['pending'] ?? 0,
            'replied' => $rawCounts['replied'] ?? 0,
        ];

        // Lọc theo Tab (Status)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Tìm kiếm
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('subject', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        // Sắp xếp
        $sort = $request->query('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $contacts = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $contacts,
            'counts'  => $counts
        ]);
    }

    public function show($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        return response()->json(['success' => true, 'data' => $contact]);
    }

    public function reply(AdminReplyContactRequest $request, $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        try {
            // ĐÃ FIX: Chuyển từ ->queue() sang ->send()
            // Bắt buộc gửi đồng bộ để nếu cấu hình .env bị sai, nó sẽ bung lỗi thẳng ra cho Admin biết.
            Mail::to($contact->email)->send(new ReplyMail($contact, $request->reply_message));

            $contact->update([
                'status'        => 'replied',
                'reply_message' => $request->reply_message,
                'replied_at'    => now(),
                'replied_by'    => $request->user()->id
            ]);

            return response()->json(['success' => true, 'message' => 'Đã gửi Email phản hồi cho khách hàng thành công.']);
        } catch (\Exception $e) {
            // Lỗi SMTP sẽ hiển thị thẳng lên màn hình qua Toast
            return response()->json(['success' => false, 'message' => 'Lỗi cấu hình gửi Mail: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa liên hệ thành công.']);
    }
}