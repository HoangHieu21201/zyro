<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;

use App\Models\FlashSaleItem;
use App\Models\LookbookItem;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'base_price',
        'promotional_price',
        'description',
        'thumbnail_image',
        'gender',
        'fit_type',
        'size_guide_url',
        'care_instructions',
        'specifications',
        'is_featured',
        'status',
        'view_count',
        'sales_count',
        'review_count',
        'rating_avg'
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'base_price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'specifications' => 'array',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'sales_count' => 'integer',
            'review_count' => 'integer',
            'rating_avg' => 'decimal:2',
        ];
    }

    /**
     * ========================================================
     * BỨC TƯỜNG LỬA (HOOKS): TỰ ĐỘNG DỌN DẸP DỮ LIỆU RÁC
     * ========================================================
     */
    protected static function booted()
    {
        $cleanupRelations = function ($model) {
            // ĐÃ KIỂM TRA KỸ: Tuyệt đối không có chữ 'clone' ở đây
            $variantIds = $model->variants()->withTrashed()->pluck('id')->toArray();
            
            if (!empty($variantIds)) {
                FlashSaleItem::whereIn('variant_id', $variantIds)->delete();
            }

            LookbookItem::where('product_id', $model->id)->delete();
        };

        static::saved(function ($model) use ($cleanupRelations) {
            // Chỉ dọn dẹp Flash Sale/Lookbook khi trạng thái vừa bị chuyển thành Nháp/Ẩn
            if ($model->wasChanged('status') && $model->status !== 'published') {
                $cleanupRelations($model);
            }

            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });

        static::deleted(function ($model) use ($cleanupRelations) {
            // Sản phẩm bị xóa -> Chắc chắn phải dọn dẹp
            $cleanupRelations($model);

            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}