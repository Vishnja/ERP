<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * Relationships
     */
    public function cash()
    {
        return $this->morphOne('App\Models\Cash', 'contractor');
    }

    /**
     * Getters
     */
    public function getFullnameAttribute()
    {
        return $this->surname . ' ' . $this->name;
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        // generate brackets for search condition
        // e.g.: ... WHERE status_id NOT IN (7, 8) AND ('serial' like "%...%" OR ...)
        $query = $query->where( function($query) use ($search) {
            $query->where(DB::raw("CONCAT(surname, ' ', name)"), 'LIKE', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(name, ' ', surname)"), 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('NP_number', 'LIKE', "%{$search}%");
        });

        return $query;
    }
}
