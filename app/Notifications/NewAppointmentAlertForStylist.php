<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class NewAppointmentAlertForStylist extends Notification implements ShouldQueue
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
        $day = Carbon::parse($this->booking->date)->format('d M Y');
        $hour = Carbon::parse($this->booking->start)->format('H:i');
        $services = $this->booking->slots->pluck('service.title')->toArray();
        return (new MailMessage)
            ->subject("Nuovo appuntamento")
            ->greeting('Nuovo appuntamento')
            ->line('ciao ' . $name . ' stylist, ti è stato assegnato un appuntamento il giorno '. $day . ' alle ore ' . $hour . ' per:')
            ->lines($services)
            ->salutation('Saluti');
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
