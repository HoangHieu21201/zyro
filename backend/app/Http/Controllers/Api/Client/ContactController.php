<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Contact\StoreContactRequest;
use App\Models\Contact;
use App\Models\Admin;
use App\Mail\ContactThankYouMail;
use App\Notifications\AdminAlertNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(StoreContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        // =========================================================================
        // 0. LỚP BẢO VỆ "THÉP": CHẶN EMAIL ẢO/RÁC NGAY TẠI CỬA (ZERO-TOLERANCE)
        // =========================================================================
        if ($this->isDisposableEmail($data['email'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Hệ thống phát hiện đây là Email dùng 1 lần (ảo). Vui lòng sử dụng Email cá nhân thật của bạn!'
            ], 422);
        }

        // 1. LỚP BẢO VỆ 1: HONEYPOT TRAP (Bẫy Bot tự động)
        if ($request->filled('website_url')) {
            Contact::create(array_merge($data, ['is_spam' => true]));
            return response()->json(['success' => true, 'message' => 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất.']);
        }

        // 2. LỚP BẢO VỆ 2: GOOGLE RECAPTCHA VERIFICATION
        /** @var \Illuminate\Http\Client\Response $recaptchaResponse */
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $data['g-recaptcha-response'],
            'remoteip' => $request->ip()
        ]);

        if (!$recaptchaResponse->json('success')) {
            return response()->json([
                'success' => false, 
                'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
            ], 422);
        }

        // Loại bỏ trường dư thừa trước khi lưu Database
        unset($data['g-recaptcha-response']);
        unset($data['website_url']);

        try {
            DB::beginTransaction();

            $contact = Contact::create($data);

            // Đưa mail cảm ơn vào Queue
            Mail::to($contact->email)->queue(new ContactThankYouMail($contact->name));

            // Bắn thông báo Real-time cho Admin
            $admins = Admin::where('role_id', 1)->where('status', 'active')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminAlertNotification([
                    'type'    => 'info',
                    'title'   => 'Liên hệ mới từ: ' . $contact->name,
                    'message' => "Chủ đề: {$contact->subject}",
                    'url'     => '/admin/contacts'
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất.',
                'data'    => $contact
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Contact Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Hệ thống đang bận, vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * KIỂM TRA EMAIL ẢO (DISPOSABLE EMAIL) VỚI ĐỘ CHÍNH XÁC CỰC ĐOAN
     */
    private function isDisposableEmail($email): bool
    {
        $domain = strtolower(substr(strrchr($email, "@"), 1));

        // 1. BLACKLIST HƠN 40 DOMAIN RÁC PHỔ BIẾN NHẤT THẾ GIỚI
        $trashDomains = [
            'mailinator.com', '10minutemail.com', 'yopmail.com', 'guerrillamail.com', 'temp-mail.org', 
            'tempmail.com', 'throwawaymail.com', 'sharklasers.com', 'maildrop.cc', 'getnada.com', 
            'dispostable.com', 'trashmail.com', 'tempinbox.com', 'dropmail.me', 'crazymailing.com',
            'nada.ltd', 'fexpost.com', 'fexbox.org', 'fexbox.ru', 'generator.email', 'mailinator.net', 
            'tempmail.net', 'mohmal.com', 'my10minutemail.com', 'mailnesia.com', 'tempmailaddress.com',
            'zillyilly.com', 'mailto.plus', '10mail.org', '10minutemail.net', '10minutemail.org', 
            'guerrillamail.info', 'guerrillamail.biz', 'guerrillamail.de', 'guerrillamail.net', 
            'spam4.me', 'grr.la', 'emailondeck.com', 'inboxproxy.com', 'mailcatch.com', 'mailo.com'
        ];

        if (in_array($domain, $trashDomains)) {
            return true; // Fake 100%, chặn ngay lập tức không cần nói nhiều
        }

        // 2. GỌI KÉP 2 API QUỐC TẾ ĐỂ BẮT NHỮNG DOMAIN VỪA TẠO HÔM QUA
        try {
            /** @var \Illuminate\Http\Client\Response $kickbox */
            $kickbox = Http::timeout(3)->get("https://open.kickbox.com/v1/disposable/{$email}");
            if ($kickbox->successful() && ($kickbox->json('disposable') === true || $kickbox->json('disposable') === 'true')) {
                return true;
            }
        } catch (\Exception $e) {
            // Kickbox sập thì gọi thằng Debounce cứu viện
            try {
                /** @var \Illuminate\Http\Client\Response $debounce */
                $debounce = Http::timeout(3)->get("https://disposable.debounce.io/?email=" . urlencode($email));
                if ($debounce->successful() && $debounce->json('disposable') === 'true') {
                    return true;
                }
            } catch (\Exception $ex) {
                // Cả 2 thằng cùng sập thì đành để lọt để cứu trải nghiệm của Khách hàng thật
            }
        }

        return false;
    }
}