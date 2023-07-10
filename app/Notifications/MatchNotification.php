<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MatchNotification extends Notification
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
                    ->greeting(__('msg.Hi').'!')
                    ->line($this->user->name.' '.__('msg.has Sent you a Match Request.'));
                    // ->line(__('msg.To See His/Her Profile, Click on the Link Below'))
                    // ->action(__('msg.Click Here'), url('/'));
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
            'title'     => __('msg.Match Request'),
            'msg'       => $this->user->name.' '.$this->user->lname.' '.__('msg.has Sent you a Match Request.'),
            'datetime'  => date('Y-m-d h:i:s'),
        ];
    }
}
