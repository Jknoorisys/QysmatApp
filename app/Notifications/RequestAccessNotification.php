<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestAccessNotification extends Notification
{
    use Queueable;
    private $user;
    private $user_type;
    private $singleton_id;
    private $access_code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $user_type, $singleton_id, $access_code)
    {
        $this->user         = $user;
        $this->user_type    = $user_type;
        $this->singleton_id = $singleton_id;
        $this->access_code = $access_code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
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
                    ->line($this->user->name.' '.__('msg.has Sent you an Access Request.'))
                    ->line(__('msg.Your Access Code is'))
                    ->line(__($this->access_code.'.'), url('/'));
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
            'title'     => __('msg.Access Request'),
            'msg'       => $this->user->name.' '.__('msg.has Sent you an Access Request.'),
            'datetime'  => date('Y-m-d h:i:s'),
        ];
    }
}
