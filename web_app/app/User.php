<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\UserPasswordReset;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    const CREATED_AT = "createdat";
    const UPDATED_AT = "updatedat";

    //public $timestamps = false;

    /*protected $map = [
        'updatedat' => 'updated_at',
        'createdat' => 'created_at',
    ];

    protected $fields = [
        'updated_at' =>[
            'column' => 'updatedat',
        ],
        'created_at' => [
            'column' => 'createdat',
        ],
    ];*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fullname', 'email','roleid','franchiseid','otherfranchise', 'password','contactno','activationcode','otpcode',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserPasswordReset($token, $this));
    }

    public function role()
    {
        return $this->hasOne('\App\Userrole','id','roleid');
    }

    public function propetries()
    {
        return $this->hasMany('\App\property','userid','id');
    }
}
