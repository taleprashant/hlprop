<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PropertyUploadSuccess extends Notification
{
    use Queueable;

    public $propertyid;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($propertyid, $user)
    {
        $this->propertyid = $propertyid;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('info@homelandsproperties.com')
            ->subject('Homeland Properties - property uploaded successfully.')
            ->greeting(sprintf('Hello %s', $this->user->fullname))
            ->line('Greetings, your property is uploaded successfully on Homeland Properties.')
            ->line('It will be posted on Homeland Properties after successfull review.')
            ->action('Check Property', route('props.show','iiphl-'. $this->propertyid .'-property'))
            ->line('You will be informed once the review is done.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
