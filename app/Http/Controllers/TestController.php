<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\Stripe;
use App\Models\Log;
use Stripe\Invoice;
use App\Models\User;
use App\Api\TandaApi;
use App\Models\Order;
use App\Models\Store;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use App\Api\IpraticoApi;
use App\Models\Discount;
use Carbon\CarbonPeriod;
use Stripe\StripeClient;
use Spatie\Period\Period;
use Stripe\PaymentIntent;
use Carbon\CarbonInterval;
use App\Models\HairService;
use App\Models\PlanPricing;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Spatie\Period\Precision;
use App\Models\CheckoutError;
use Spatie\Period\Boundaries;
use App\Services\TandaService;
use Illuminate\Support\Carbon;
use App\Services\StripeService;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Jobs\BookingCheckBalance;
use App\Services\IpraticoService;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AvailabilityService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Session;
use Stripe\Service\PaymentIntentService;
use App\Notifications\BookingReminderCustomer;
use App\Notifications\Admin\BookingConfirmation;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Admin\SubscriptionConfirmation;
use App\Notifications\AppointmentConfirmationCustomer;
use App\Notifications\BookingReminderCustomerWhatsapp;
use App\Notifications\SubscriptionConfirmationCustomer;

class TestController extends Controller
{
    public function test()
    {
        
        // $orders = Order::query()
        //         ->whereHas('payments')
        //         ->where('status', Order::STATUS_PENDING)
        //         ->whereDate('created_at', '>', now()->subMonths(4))
        //         ->get();

        //     foreach ($orders as $order)
        //     {

        //         $data = json_decode($order->data, true);

        //         $booking_for = now();

            
        //         $last_payment = $order->payments->first();

        //         CheckoutError::create([
        //             'user_id' => $order->user_id,
        //             'store_id' => 1,
        //             'order_id' => $order->id,
        //             'booking_for' => $booking_for,
        //             'paid_at' => $last_payment->created_at,
        //             'total' => 99,
        //             'resolved' => false
        //         ]);
        //     }

        // dd('stop');
    }

    public function ipratico()
    {
//        $api = new IpraticoApi(
//            "18823:61ee5235-1b8e-4de5-a3c5-edfad478a806",
//            "https://apicb.ipraticocloud.com/api/public/"
//        );
//
//        dd($api->getOrders('order:e9f7c2b6-6944-4c76-92eb-ccbe5e781407'));
    }

    public function availability(Store $store)
    {
        $start = now()->startOfDay();
        $end = $start->copy()->addWeeks(AvailabilityService::getWeeksWindow());
        $store_availability_all = AvailabilityService::flow($store,$start, $end, null, true);

        dd(
            '===> Store', $store->name,
            '===> Store availability', $store_availability_all
        );
    }
}
