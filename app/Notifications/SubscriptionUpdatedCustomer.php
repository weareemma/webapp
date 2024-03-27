<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionUpdatedCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $plan = $notifiable->getFirstPlan();

        $name = '-';
        $price_duration = '-';
        $next = '-';

        if ($plan)
        {
            $subscription = $notifiable->getFirstSubscription($plan);
            $price = $plan->pricings()->where('stripe_price_id', $subscription->stripe_price)->first();
            if ($price)
            {
                $cost = round($price->price);
                switch ($price->duration)
                {
                    case '1:month':
                        $duration = 'ogni mese';
                        break;
                    case '3:month':
                        $duration = 'ogni 3 mesi';
                        break;
                    case '6:month':
                        $duration = 'ogni 6 mesi';
                        break;
                    case '1:year':
                        $duration = 'ogni anno';
                        break;
                    default:
                        $duration = '';
                        break;
                }
                $price_duration = $cost . ' € ' . $duration .'.';
            }

            $name = $plan->name;
            $stripeSubscription = $subscription->asStripeSubscription();
            $next = Carbon::parse($stripeSubscription->current_period_end)->format('d/m/Y');
        }
        return (new MailMessage)
            ->subject(__("Modifica abbonamento"))
            ->line("il tuo abbonamento è stato modificato.")
            ->line('Nome: ' . $name)
            ->line('Costo: ' . $price_duration)
            ->line('Prossimo rinnovo: ' . $next)
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
            'title' => __("Modifica abbonamento")
        ];
    }
}
