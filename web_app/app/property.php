<?php

namespace App;

// use app\Notifications\PropertyUploadSuccess;
use Illuminate\Notifications\Notifiable;
use Auth;
use App\User;
use Illuminate\Database\Eloquent\Model;

class property extends Model
{
    use Notifiable;

    protected $table = 'property';

    const CREATED_AT = "createdat";
    const UPDATED_AT = "updatedat";

    /**
     * Send the property upload notification.
     *
     * @param  string  $propertyid
     * @param string $user
     * @return void
     */
    // public function sendPasswordResetNotification($user)
    // {
    //     $this->notify(new PropertyUploadSuccess($this->id, $user));
    // }

     /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return Auth::User()->email;
    }

    public function enproperty()
    {
      return $this->hasOne('\App\proenquiry','propertyid','id');
    }

    public function enproperties()
    {
      return $this->hasMany('\App\proenquiry','propertyid','id');
    }
    
    public function imgproperty(){
        $img = $this->hasMany('\App\proimage','propertyid','id');
            
    }
    public function getPropertyType()
    
    {
      return $this->hasOne('\App\protype','id','propertytypeid');
    }
    public function getSubPropertyType()
    {
      return $this->hasOne('\App\prosubtype','id','propertysubtypeid');
    }
     public function customer()
    {
        return $this->hasOne('\App\customer','id','userid');
    }
}
