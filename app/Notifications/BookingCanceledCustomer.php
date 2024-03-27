<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\WhatsappChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCanceledCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    private $booking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $name = $notifiable->full_name;
        $date = Carbon::parse($this->booking->date)->format('d/m/Y');
        $time = Carbon::parse($this->booking->start)->format('H:i');
        return (new MailMessage)
            ->subject(__("Cancellazione appuntamento"))
            ->greeting("Ciao {$name}",)
            ->line("Il tuo appuntamento previsto per il " . $date . ' alle ' . $time . ', è stato cancellato.')
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
            'title' => __("Cancellazione appuntamento")
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
        $date = Carbon::parse($this->booking->date)->format('d/m/Y');
        $time = Carbon::parse($this->booking->start)->format('H:i');
        return [
            'header' => __("Cancellazione appuntamento"),
            'body' => "Il tuo appuntamento previsto per il " . $date . ' alle ' . $time . ', è stato cancellato.'
        ];
    }
}
