<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';

    /**
     * Mass assign
     */
    protected $guarded = [];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'changes' => 'array'
    ];
}
