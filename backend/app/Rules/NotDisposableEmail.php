<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class NotDisposableEmail implements ValidationRule
{
    /**
     * Chạy logic kiểm tra dữ liệu.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr($value, "@"), 1));

        // 1. LỚP PHÒNG THỦ 1: BLACKLIST TĨNH (Sút văng ngay các domain rác phổ biến)
        // Bổ sung thêm rất nhiều domain rác chuyên dụng để chặn triệt để
        $blacklistedDomains = [
            'mailinator.com', '10minutemail.com', 'yopmail.com', 'guerrillamail.com',
            'temp-mail.org', 'tempmail.com', 'throwawaymail.com', 'sharklasers.com',
            'maildrop.cc', 'getnada.com', 'dispostable.com', 'trashmail.com',
            'mail.ru', 'tempinbox.com', 'dropmail.me', 'crazymailing.com',
            'nada.ltd', 'fexpost.com', 'fexbox.org', 'fexbox.ru', 'generator.email',
            'mailinator.net', 'tempmail.net', 'mohmal.com', 'my10minutemail.com',
            'mailnesia.com', 'tempmailaddress.com'
        ];

        if (in_array($domain, $blacklistedDomains)) {
            $fail('Hệ thống không chấp nhận Email dùng 1 lần (Disposable Email). Vui lòng sử dụng Email thật!');
            return; // Dừng luôn, không cần gọi API nữa
        }

        // 2. LỚP PHÒNG THỦ 2: GỌI API (Để hốt nốt những tên miền rác mới tạo ngày hôm qua)
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(3)->get('https://disposable.debounce.io/?email=' . urlencode($value));

            if ($response->status() === 200) {
                $body = json_decode($response->body(), true);
                
                if (isset($body['disposable']) && $body['disposable'] === 'true') {
                    $fail('Hệ thống không chấp nhận Email dùng 1 lần (Disposable Email). Vui lòng sử dụng Email thật!');
                }
            }
        } catch (\Exception $e) {
            // Nếu API bị lag/sập, ta vẫn cho qua vì đã có Blacklist ở trên bảo kê rồi
            // Giúp trải nghiệm của khách hàng thật không bị gián đoạn
        }
    }
}