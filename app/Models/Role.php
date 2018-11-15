<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'capabilities' => 'array'
    ];

    /**
     * Relationships
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }


}
