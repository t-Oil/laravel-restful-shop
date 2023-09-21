<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    use HasFactory;

    protected $table = 'order_addresses';

    protected $fillable = [
        'order_id',
        'address',
        'type',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['id'];

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
