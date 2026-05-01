<?php

namespace App\Http\Requests\FlashSale;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FlashSale;
use Carbon\Carbon;

class StoreFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:3', 'max:255'],
            'start_time'   => ['required', 'date', 'after_or_equal:now'],
            'end_time'     => ['required', 'date', 'after:start_time'],
            'status'       => ['required', 'in:active,hidden,ended'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'items_data'   => ['required', 'json'], // Dữ liệu các mặt hàng sale
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Vui lòng nhập tên chiến dịch Flash Sale.',
            'start_time.required'   => 'Thời gian bắt đầu là bắt buộc.',
            'start_time.after_or_equal' => 'Thời gian bắt đầu không được nằm trong quá khứ.',
            'end_time.after'        => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'items_data.required'   => 'Vui lòng chọn ít nhất 1 sản phẩm tham gia Flash Sale.',
            'banner_image.image'    => 'Banner phải là định dạng hình ảnh.',
            'banner_image.max'      => 'Dung lượng ảnh banner không được vượt quá 5MB.',
        ];
    }

    /**
     * BỨC TƯỜNG LỬA CHẶN "OVERLAP TIME FLAW" (XUNG ĐỘT THỜI GIAN)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            
            // Xử lý an toàn dữ liệu đầu vào
            $itemsData = json_decode($this->input('items_data', '[]'), true);
            if (!is_array($itemsData)) $itemsData = [];

            if (!$startTime || !$endTime || empty($itemsData)) return;

            // Rút trích danh sách Variant ID mà Admin muốn chạy Sale
            $variantIds = collect($itemsData)->pluck('variant_id')->filter()->unique()->toArray();
            if (empty($variantIds)) return;

            // THUẬT TOÁN TÌM XUNG ĐỘT: Tìm các Flash Sale đang chứa những Variant ID này và có thời gian giao nhau
            $overlappingSales = FlashSale::where('status', '!=', 'ended')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                })
                ->whereHas('items', function ($query) use ($variantIds) {
                    $query->whereIn('variant_id', $variantIds);
                })
                ->with(['items' => function($q) use ($variantIds) {
                    $q->whereIn('variant_id', $variantIds)->with('variant.product');
                }])
                ->get();

            // Nếu tìm thấy có sự trùng lặp -> Khóa mõm ngay lập tức và in lỗi chi tiết
            if ($overlappingSales->isNotEmpty()) {
                foreach ($overlappingSales as $overlap) {
                    $startFormat = Carbon::parse($overlap->start_time)->format('H:i d/m/Y');
                    $endFormat = Carbon::parse($overlap->end_time)->format('H:i d/m/Y');

                    foreach ($overlap->items as $overlapItem) {
                        $productName = $overlapItem->variant->product->name ?? 'Sản phẩm';
                        $sku = $overlapItem->variant->sku ?? 'N/A';
                        
                        $validator->errors()->add(
                            'items_data',
                            "Lỗi Xung Đột Thời Gian: Biến thể [{$sku}] của '{$productName}' đã được lên lịch trong chiến dịch '{$overlap->name}' (Từ {$startFormat} đến {$endFormat}). Không thể thiết lập thời gian trùng lặp!"
                        );
                    }
                }
            }
        });
    }
}