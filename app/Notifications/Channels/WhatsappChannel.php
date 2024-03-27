<?php

namespace App\Notifications\Channels;

use App\Services\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    /**
     * Send notification
     *
     * @param $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsapp($notifiable);

        WhatsappService::sendMessage(
            $notifiable->getWhatsAppContact(),
            $message['header'] ?? '',
            $message['body'] ?? ''
        );
    }
}