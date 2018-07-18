<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
//        'password',
        'verified',
        'verification_token'
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
     * Get user by email
     *
     * @param $email
     * @return User
     */
    public static function byEmail($email)
    {
        return (new static)->where(compact('email'))->first();
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

    /**
     * Create password recovery token
     */
    public function createPasswordRecoveryToken()
    {
        $token = Str::random(64);

        $created = DB::table('password_resets')->updateOrInsert(
            ['email' => $this->email],
            ['email' => $this->email, 'token' => $token]
        );

        return $created ? $token : false;
    }

    /**
     * Restore password by token
     *
     * @param $token
     * @param $password
     * @return false|User
     */
    public static function newPasswordByResetToken($token, $password)
    {
        $record = DB::table('password_resets')->where(compact('token'))->first();

        if (!$record) {
            return false;
        }

        $user = self::byEmail($record->email);

        return $user->setPassword($password);
    }

    /**
     * Persist a new password for the user
     *
     * @param $password
     * @return bool
     */
    public function setPassword($password)
    {
        $this->password = Hash::make($password);
        return $this->save();
    }
}
