<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Channels\DatabaseChannel as IlluminateDatabaseChannel;

class DatabaseChannel extends IlluminateDatabaseChannel
{
    /**
    * Send the given notification.
    *
    * @param mixed $notifiable
    * @param \Illuminate\Notifications\Notification $notification
    * @return \Illuminate\Database\Eloquent\Model
    */
    public function send($notifiable, Notification $notification)
    {
        return $notifiable->routeNotificationFor('database')->create([
            'id'            => $notification->id,
            'type'          => get_class($notification),
            'user_type'     => $notification->user_type ?? '',
            'singleton_id'  => $notification->singleton_id ?? '',
            'data'          => $this->getData($notifiable, $notification),
            'read_at'       => Null,
        ]);
    }
}
