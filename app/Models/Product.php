<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Getters
     */
    public function getArchiveAttributesAttribute()
    {
        return [
            'name' => $this->name,
            'vendor_code' => $this->vendor_code,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        // generate brackets for search condition
        // e.g.: ... WHERE status_id NOT IN (7, 8) AND ('serial' like "%...%" OR ...)
        $query = $query->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('vendor_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('price', 'LIKE', "%{$search}%")
                  ->orWhere('quantity_receipt', 'LIKE', "%{$search}%")
                  ->orWhere('quantity_realization', 'LIKE', "%{$search}%");
        });

        return $query;
    }
}
