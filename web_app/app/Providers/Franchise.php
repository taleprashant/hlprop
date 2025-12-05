<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\Franchise as Authenticatable;
use App\Notifications\UserPasswordReset;

class Franchise extends Authenticatable
{
    use Notifiable;

    protected $table = 'franchise';

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
        'franchisename', 'contactno','email','city','location', 'address','state','country',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    
}
