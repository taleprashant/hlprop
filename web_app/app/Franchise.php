<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Franchise extends Model
{
   // use Notifiable;

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
        'franchisename', 'contactno','email','city','location','address','state','country',
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
