<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    //
   
    protected $table = 'user';
    public $timestamps = false;

    public function getRole()
    {
    	return $this->hasOne('\App\Userrole','id','roleid');
    }
}
