<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'is_sponsored', 'stock', 'company_id'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }
}
