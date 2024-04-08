<?php

namespace App\Notifications\Admin;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification
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
        $date = ($this->booking->date && $this->booking->start)
            ? Carbon::parse($this->booking->date)->setTimeFromTimeString($this->booking->start)->format('d/m/Y H:i')
            : '-';

        return (new MailMessage)
            ->subject(__("Nuovo appuntamento"))
            ->line("Utente: " . $this->booking?->customer?->full_name)
            ->line("Store: " . $this->booking?->store?->name)
            ->line("Data e ora: " . $date)
            ->action('Vai alla dashboard', url(route('schedule.appointment.index')))
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
