<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Lookbook;

class UpdateLookbookLimitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usage_limit' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];
    }

    public function messages()
    {
        return [
            'usage_limit.integer' => 'Giới hạn phải là số nguyên.',
            'usage_limit.min'     => 'Giới hạn không được nhỏ hơn 0.',
            'usage_limit.max'     => 'Giới hạn tối đa không được vượt quá 10.000.',
        ];
    }

    /**
     * BỨC TƯỜNG LỬA CHẶN LỖI LOGIC (BUSINESS LOGIC VALIDATION)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newLimit = $this->input('usage_limit');
            
            // Nếu sếp chọn Không giới hạn (Null) thì hoàn toàn hợp lệ
            if ($newLimit === null) return;

            $lookbookId = $this->route('id');
            $lookbook = Lookbook::find($lookbookId);

            if ($lookbook) {
                // Lấy số lượt khách đã mua (Nếu Model chưa có cột usage_count thì mặc định là 0)
                $currentUsage = $lookbook->usage_count ?? 0;

                // Nếu cố tình đặt giới hạn mới NHỎ HƠN số lượng đã bán -> Báo lỗi
                if ($newLimit < $currentUsage) {
                    $validator->errors()->add(
                        'usage_limit', 
                        "Lỗi nghiệp vụ: Bộ sưu tập này đã được bán ra {$currentUsage} bộ. Không thể đặt giới hạn mới ({$newLimit}) nhỏ hơn số lượng đã bán!"
                    );
                }
            }
        });
    }
}