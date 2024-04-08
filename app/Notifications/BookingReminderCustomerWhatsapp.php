<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\WhatsappChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderCustomerWhatsapp extends Notification
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
        return [WhatsappChannel::class];
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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

    /**
     * Get whatsapp representation
     *
     * @param $notifiable
     * @return array
     */
    public function toWhatsapp($notifiable)
    {
        $name = $notifiable->full_name;
        $date = Carbon::parse($this->booking->date)->format('d/m/Y');
        $time = Carbon::parse($this->booking->start)->format('H:i');
        $address = ($this->booking->store) ? $this->booking->store->address : '--';
        return [
            'header' => __("Reminder appuntamento"),
            'body' => 
                "Ciao {$name} ti ricordiamo il tuo appuntamento del {$date} alle {$time} presso il nostro salone di {$address}." .
                " " .
                config('app.whatsapp_end_message', '')
        ];
    }
}
