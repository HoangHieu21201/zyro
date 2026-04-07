<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'banners';

    protected $fillable = [
        'title',
        'image_desktop',
        'image_mobile',
        'target_url',
        'position',
        'sort_order',
        'start_time',
        'end_time',
        'status',
        'click_count'
    ];

    protected function casts(): array
    {
        return [
            'sort_order'  => 'integer',
            'start_time'  => 'datetime',
            'end_time'    => 'datetime',
            'click_count' => 'integer',
        ];
    }
}