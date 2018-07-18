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
//        'verified',
//        'verification_token'
    ];

    /**
     * Create a user
     *
     * @param $name
     * @param $email
     * @param $password
     * @return User|bool
     */
    public static function createFromValues($name, $email, $password)
    {
        $user = new static;

        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->verification_token = Str::random(64);

        return $user->save() ? $user : false;
    }

    /**
     * Verify by token
     *
     * @param $token
     * @return false|User
     */
    public static function verifyByToken($token)
    {
        $user = (new static)->where(['verification_token' => $token, 'verified' => 0])->first();

        if (!$user) {
            return false;
        }

        $user->verify();

        return $user;
    }

    /**
     * Verifiy a user
     *
     * @return bool
     */
    public function verify()
    {
        $this->verification_token = null;
        $this->verified = 1;

        return $this->save();
    }
}
