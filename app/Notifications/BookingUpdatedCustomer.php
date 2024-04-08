<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\WhatsappChannel;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingUpdatedCustomer extends Notification implements ShouldQueue
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
        $name = $notifiable->full_name;
        $date = Carbon::parse($this->booking->date)->format('d/m/Y');
        $time = Carbon::parse($this->booking->start)->format('H:i');
        $services = BookingService::servicesForEmail($this->booking);
        $updated_by = $this->booking->updated_by ?? $this->booking->created_by;
        return (new MailMessage)
            ->subject(($updated_by == Booking::CREATOR_CUSTOMER) ? __("Modifica appuntamento") : __("Modifica appuntamento dallo staff"))
            ->greeting("Ciao {$name}",)
            ->lineIf($updated_by == Booking::CREATOR_CUSTOMER, "Il tuo appuntamento è stato modificato.")
            ->lineIf($updated_by !== Booking::CREATOR_CUSTOMER, "Il tuo appuntamento è stato modificato dallo staff.")
            ->line("Ti aspettiamo il " . $date . ' alle ' . $time)
            ->line("I servizi che hai prenotato sono:")
            ->lines($services)
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
            'title' => __("Modifica appuntamento")
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
            'header' => __("Modifica appuntamento"),
            'body' => __("Il tuo appuntamento è stato modificato. ") . config('app.whatsapp_end_message', '')
        ];
    }
}
