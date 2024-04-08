<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PaymentLinkCustomer extends Notification implements ShouldQueue
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
      $services = $this->booking->slots->pluck('service.title')->toArray();
      $url = URL::temporarySignedRoute('verify.payment_link', now()->addDays(1), ['booking' => $this->booking->id]);
      return (new MailMessage)
          ->subject("Paga il tuo appuntamento")
          ->greeting("Ciao {$name}",)
          ->line("Clicca qui per precedere al pagamento del tuo appuntamento:")
          ->lines($services)
          ->action('Paga ora', $url)
          ->salutation('Un saluto!');
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
