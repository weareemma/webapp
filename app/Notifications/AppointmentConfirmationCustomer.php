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

class AppointmentConfirmationCustomer extends Notification implements ShouldQueue
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
        $multiple_title = ($this->booking->multiple) ? "La tua prenotazione multipla per " : "La tua prenotazione per ";

        return (new MailMessage)
            ->subject("Conferma appuntamento")
            ->greeting("Evviva! Il tuo appuntamento è confermato")
            ->line("Gentile {$name},")
            ->line($multiple_title)
            ->lines($services)
            ->line("è confermata.")
            ->line("Ti aspettiamo il giorno " . $date . ' alle ' . $time ." nel nostro store di " . $this->booking->store->address . ".")
            ->line("Se è la tua prima volta ti suggeriamo di arrivare 5 minuti prima per poter eseguire una consulenza dettagliata.")
            ->line("Ti ricordiamo inoltre che potrai modificare o annullare la tua prenotazione fino a 24 ore prima del tuo appuntamento.")
            ->salutation("Grazie per averci scelto e ti aspettiamo in salone!")
            ->action("Scopri il mondo We Are Emma", url('/'));
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
            'title' => __("Conferma appuntamento")
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
            'header' => __("Conferma appuntamento"),
            'body' => __("Il tuo appuntamento è confermato per il ") .
                $date .
                __(" alle ") .
                $time .
                ". " . 
                config('app.whatsapp_end_message', '')
        ];
    }
}
