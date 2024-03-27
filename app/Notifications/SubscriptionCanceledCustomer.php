<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class SubscriptionCanceledCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    private $bookings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($bookings = [])
    {
        $this->bookings = $bookings;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $bookings = [];

        foreach ($this->bookings as $b)
        {
            $balance = $b->total_net_price_original - ($b->paid_amount ?? 0) ;
            if ($balance > 0)
            {
                $bookings[] = "Prenotazione del " . Carbon::parse($b->date)->format('d/m/Y') . ": da pagare " . $balance . ' €';
            }
        }

        return (new MailMessage)
            ->subject(__("Abbonamento cancellato"))
            ->line("Il tuo abbonamento è stato cancellato.")
            ->line("Se hai cambiato abbonamento, ignora questa email.")
            ->line("Queste prenotazioni prevedono un servizio compreso nell'abbonamento che hai annullato. Pertanto, per usufruire del servizio, dovrai pagare in store.")
            ->lines($bookings)
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
            'title' => __("Annullamento abbonamento")
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
            'header' => __("Annullamento abbonamento"),
            'body' => __("il tuo abbonamento è stato annullato")
        ];
    }
}
