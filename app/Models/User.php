<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Relationships
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Getters
     */
    public function getFullnameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ?
               asset('/img/user_avatars/' . $this->photo) :
               asset('/img/user.jpg');
    }

    /**
     * ACL
     */
    public function canAccess($capability){
        $capabilities = $this->role->capabilities;
        if ($capabilities[$capability] == 'true') return true;
        return false;
    }

    public function isSelf(){
         return auth()->user()->id == $this->id;
    }

}
