<?php

namespace App\Http\Requests\FlashSale;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FlashSale;
use Carbon\Carbon;

class UpdateFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:3', 'max:255'],
            'start_time'   => ['required', 'date'], // Khi update có thể ngày bắt đầu đã qua, nên bỏ after_or_equal:now
            'end_time'     => ['required', 'date', 'after:start_time'],
            'status'       => ['required', 'in:active,hidden,ended'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'items_data'   => ['required', 'json'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Vui lòng nhập tên chiến dịch Flash Sale.',
            'start_time.required'   => 'Thời gian bắt đầu là bắt buộc.',
            'end_time.after'        => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'items_data.required'   => 'Vui lòng chọn ít nhất 1 sản phẩm tham gia Flash Sale.',
            'banner_image.image'    => 'Banner phải là định dạng hình ảnh.',
        ];
    }

    /**
     * BỨC TƯỜNG LỬA CHẶN "OVERLAP TIME FLAW" (XUNG ĐỘT THỜI GIAN)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $flashSaleId = $this->route('flash_sale') ?? $this->route('id');
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            
            $itemsData = json_decode($this->input('items_data', '[]'), true);
            if (!is_array($itemsData)) $itemsData = [];

            if (!$startTime || !$endTime || empty($itemsData) || !$flashSaleId) return;

            $variantIds = collect($itemsData)->pluck('variant_id')->filter()->unique()->toArray();
            if (empty($variantIds)) return;

            // TÌM XUNG ĐỘT (LOẠI TRỪ CHÍNH CHIẾN DỊCH ĐANG CHỈNH SỬA)
            $overlappingSales = FlashSale::where('id', '!=', $flashSaleId)
                ->where('status', '!=', 'ended')
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

            if ($overlappingSales->isNotEmpty()) {
                foreach ($overlappingSales as $overlap) {
                    $startFormat = Carbon::parse($overlap->start_time)->format('H:i d/m/Y');
                    $endFormat = Carbon::parse($overlap->end_time)->format('H:i d/m/Y');

                    foreach ($overlap->items as $overlapItem) {
                        $productName = $overlapItem->variant->product->name ?? 'Sản phẩm';
                        $sku = $overlapItem->variant->sku ?? 'N/A';
                        
                        $validator->errors()->add(
                            'items_data',
                            "Lỗi Xung Đột Thời Gian: Biến thể [{$sku}] của '{$productName}' đang được xếp lịch trong chiến dịch khác là '{$overlap->name}' (Từ {$startFormat} đến {$endFormat}). Không thể thiết lập thời gian trùng lặp!"
                        );
                    }
                }
            }
        });
    }
}