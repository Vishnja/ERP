<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $table = 'order_product';

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function productSupplierPrice()
    {
        return $this->belongsTo('App\Models\ProductSupplierPrice');
    }
}
