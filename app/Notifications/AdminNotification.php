<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $user_type, $singleton_id, $details)
    {
        $this->user         = $user;
        $this->user_type    = $user_type;
        $this->singleton_id = $singleton_id;
        $this->details      = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'user_id'   => $this->user->id,
            'user_type' => $this->user->user_type,
            'name'      => $this->user->name,
            'email'     => $this->user->email,
            'title'     => $this->details['title'],
            'msg'       => $this->user->name.' '.$this->details['msg'],
            'datetime'  => date('Y-m-d h:i:s'),
        ];
    }
}
