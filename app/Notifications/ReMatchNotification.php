<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReMatchNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $user_type, $singleton_id)
    {
        $this->user         = $user;
        $this->user_type    = $user_type;
        $this->singleton_id = $singleton_id;
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
            'title'     => __('msg.Re-Match Request'),
            'msg'       => $this->user->name.' '.__("msg.has sent you a match request again").'.',
            'datetime'  => date('Y-m-d h:i:s'),
        ];
    }
}
