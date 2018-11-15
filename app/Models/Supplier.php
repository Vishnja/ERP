<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
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
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        // generate brackets for search condition
        // e.g.: ... WHERE status_id NOT IN (7, 8) AND ('serial' like "%...%" OR ...)
        $query = $query->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%");
        });

        //Log::info($query->toSql());

        return $query;
    }
}
