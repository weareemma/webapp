<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionConfirmationCustomer extends Notification implements ShouldQueue
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

        $user_name = $notifiable->name;

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

                if ( ! $plan->isPromo())
                {
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
                else
                {
                    $price_duration = $cost . ' € ';
                }
            }

            $name = $plan->name;
            $stripeSubscription = $subscription->asStripeSubscription();
            $next = Carbon::parse($stripeSubscription->current_period_end)->format('d/m/Y');
        }

        if ($plan && $plan->isPromo())
        {
            // Launch date
            $launch_date = Carbon::createFromFormat('d-m-Y', config('app.launch_date'))->startOfDay();
            if ($launch_date->isPast())
            {
                return (new MailMessage)
                    ->subject(__("Abbonamento acquistato"))
                    ->greeting("L'era della piega Zero Sbatti ha inizio")
                    ->line("Ciao " . $user_name . ",")
                    ->line("grazie per avere acquistato l'abbonamento mensile in promozione a 50€.")
                    ->line("Da oggi hai diritto a 30 giorni di lavaggio e piega o solo piega illimitati!")
                    ->line("Non ti resta che prenotare la tua prima piega!")
                    ->action("Prenota ora", url('/'))
                    ->line("Puoi vedere il dettaglio del tuo acquisto anche nell'area riservata.")
                    ->salutation('A presto, il team di We Are Emma');
            }
            else
            {
                return (new MailMessage)
                    ->subject(__("Abbonamento acquistato"))
                    ->greeting("L'era della piega Zero Sbatti ha inizio")
                    ->line("Ciao " . $user_name . ",")
                    ->line("grazie per avere acquistato l'abbonamento mensile in promozione a 50€.")
                    ->line("Dall'apertura avrai diritto a 30 giorni di lavaggio e piega o solo piega illimitati!")
                    ->line("Ti invieremo una mail appena sarà possibile prenotare la tua prima piega; nel frattempo puoi iniziare a farti ispirare dal nostro menu di pieghe!")
                    ->action("Scopri le pieghe", 'https://weareemma.com/servizi/le-pieghe/')
                    ->line("Puoi vedere il dettaglio del tuo acquisto anche nell'area riservata.")
                    ->salutation('A presto, il team di We Are Emma');
            }
        }

        return (new MailMessage)
            ->subject(__("Abbonamento acquistato"))
            ->line("Grazie per aver acquistato l'abbonamento.")
            ->line('Nome: ' . $name)
            ->line('Costo: ' . $price_duration)
            ->lineIf( ! $plan->isPromo(), 'Prossimo rinnovo: ' . $next)
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
            'title' => __("Abbonamento acquistato")
        ];
    }
}
