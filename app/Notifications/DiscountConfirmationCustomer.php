<?php

namespace App\Notifications;

use App\Models\Discount;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class DiscountConfirmationCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    private $discount;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Discount $discount)
    {
        $this->discount = $discount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', WhatsappChannel::class];
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
            ->subject(__("Sconto erogato"))
            ->cc(config('app.info_email'))
            ->line("E' stato creato uno sconto per te.")
            ->line("Codice: ")
            ->line(new HtmlString('<strong>' . ($this->discount->code ?? '') . '</strong>'))
            ->action('Vai al sito', url('/'))
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
            'title' => __("Buono sconto erogato")
        ];
    }

    /**
     * Get whatsapp representation
     *
     * @param $notifiable
     * @return array
     */
    public function toWhatsapp($notifiable)
    {

        return [
            'header' => __("Buono sconto erogato"),
            'body' => __("il buono sconto è stato erogato") . '. ' . config('app.whatsapp_end_message', '')
        ];
    }
}
