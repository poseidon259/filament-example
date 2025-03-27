<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'product_code',
        'product_type',
        'qty',
        'price',
        'weight',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->where('price', '>', 0)->where('qty', '>', 0);
    }
}
