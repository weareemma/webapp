<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompleted extends Notification
{
    use Queueable;

    private $product;
    private $price;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($product = null, $price = null)
    {
        $this->product = $product;
        $this->price = $price;
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
        $name = $notifiable->full_name;
        return (new MailMessage)
            ->subject("Ecco la ricevuta per il tuo acquisto")
            ->greeting("Ciao {$name}",)
            ->line("Grazie per aver acquistato su We Are Emma")
            ->line("Nome prodotto: " . ($this->product ?? '-'))
            ->line("Prezzo: " . ($this->price ?? '-') . " €")
//            ->line("In allegato trovi la fattura")
            ->salutation('A presto, il team di We Are Emma');
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
