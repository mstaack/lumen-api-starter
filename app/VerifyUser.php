<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VerifyUser extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'user_id';

    /**
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
