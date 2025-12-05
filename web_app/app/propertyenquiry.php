<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Auth;
use App\User;

class propertyenquiry extends Model
{
    use Notifiable;

    protected $table = 'propertyenquiry';

    const CREATED_AT = "createdat";
    const UPDATED_AT = "updatedat";

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
}
