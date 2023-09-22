<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'summary_price',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = $order->runningOrderNumber();
        });
    }

    public function getFullname()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

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

    public function runningOrderNumber()
    {
        $lastOrder = Order::latest('order_number')->first();
        $runningNumber = 1;

        if ($lastOrder) {
            $lastOrderNumber = intval(substr($lastOrder->order_number, 4));
            $runningNumber = $lastOrderNumber + 1;
        }

        return 'ORD-' . str_pad($runningNumber, 5, '0', STR_PAD_LEFT);
    }
}
