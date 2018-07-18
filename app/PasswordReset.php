<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'email';
}
