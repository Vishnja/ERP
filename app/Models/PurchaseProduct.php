<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class PurchaseProduct extends Model
{
    protected $table = 'purchase_product';

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }

    public function productSupplierPrice()
    {
        return $this->belongsTo('App\Models\ProductSupplierPrice');
    }
}
