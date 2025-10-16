<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\NexmoMessage;

class RenewalSmsNotification extends Notification
{
    protected $message;

    public function __construct($message)
    {
        $this->message = strip_tags($message);
    }

    public function via($notifiable)
    {
        return ['nexmo'];
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage())->content($this->message);
    }

    public function toArray($notifiable)
    {
        return ['message' => $this->message];
    }
}
