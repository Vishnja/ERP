<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplierPrice extends Model
{
    protected $table = 'product_supplier_price';

    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier');
    }

    /**
     * Scopes
     */
    public function scopeInit($query)
    {
        $query = $query->select(
                'product_supplier_price.*',
                'p.name as product_name', 'p.price as price', 's.name as supplier_name'
            )
            ->join('products AS p', 'product_supplier_price.product_id', '=', 'p.id')
            ->join('suppliers AS s', 'product_supplier_price.supplier_id', '=', 's.id');


        return $query;
    }

    public function scopeSearch($query, $search)
    {
        // generate brackets for search condition
        $query = $query->where( function($query) use ($search) {
            $query->where('p.name', 'LIKE', "%{$search}%")
                  ->orWhere('s.name', 'LIKE', "%{$search}%")
                  ->orWhere('p.price', 'LIKE', "%{$search}%")
                  ->orWhere('product_supplier_price.purchase_price', 'LIKE', "%{$search}%");
        });

        return $query;
    }
}
