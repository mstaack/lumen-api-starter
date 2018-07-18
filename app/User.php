<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'verified',
        'verification_token'
    ];

    /**
     * Create standard user
     *
     * @param $name
     * @param $email
     * @param $password
     * @return User
     */
    public static function createFromValues($name, $email, $password)
    {
        $user = (new static)->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'verification_token' => Str::random(64)
        ]);

        return $user;
    }

    /**
     * Verify a token
     *
     * @param $token
     * @return false|User
     */
    public static function verifyToken($token)
    {
        $user = (new static)->where(['verification_token' => $token, 'verified' => 0])->first();

        if (!$user) {
            return false;
        }

        $user->verification_token = '';
        $user->verified = 1;

        $user->save();

        return $user;
    }
}
