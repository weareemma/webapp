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

class BookingReminderCustomer extends Notification implements ShouldQueue
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
     * @return \App\Mail\Customer\BookingReminderCustomer|MailMessage
     */
    public function toMail($notifiable)
    {
        $name = $notifiable->full_name;
        $date = Carbon::parse($this->booking->date)->format('d/m/Y');
        $time = Carbon::parse($this->booking->start)->format('H:i');
        $address = ($this->booking->store) ? $this->booking->store->address : '--';
        return (new MailMessage)
            ->subject('Ricordati del tuo appuntamento')
            ->markdown('emails.customer.bookingReminder')
            ->line("Gentile {$name},")
            ->line("ci vediamo il {$date} alle {$time}")
            ->line("in {$address}")
            ->line("per il tuo appuntamento con la piega dei tuoi sogni!");
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
            'title' => __("Reminder appuntamento")
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
            'header' => __("Reminder appuntamento"),
            'body' => __("Ricordati del tuo appuntamento")
        ];
    }
}
