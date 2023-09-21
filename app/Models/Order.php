<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'summary_price',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['id'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class, 'order_id', 'id');
    }
}
