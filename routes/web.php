<?php

use App\Http\Controllers\ActualScheduleCotroller;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutErrorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DefaultScheduleController;
use App\Http\Controllers\SpecificScheduleController;
use App\Http\Controllers\StylistsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Models\Booking;
use App\Models\DefaultSchedule;
use App\Models\HairService;
use App\Models\SpecificSchedule;
use App\Models\Store;
use App\Models\User;
use App\Services\IpraticoService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});

// Signed routes
Route::get('/payment_link', [\App\Http\Controllers\Guest\SignedRouteController::class, 'verifyPaymentLink'])->name('verify.payment_link');

// Social login
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Auth\LoginController::class, 'redirectToProvider'])->name('social.login');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\LoginController::class, 'handleProviderCallback'])->name('social.callback');

// impersonate
Route::get('impersonate/leave', [App\Http\Controllers\UserController::class, 'leave'])->name('impersonate.leave');
Route::get('impersonate/{user}', [App\Http\Controllers\UserController::class, 'impersonate'])->name('impersonate');

// Buy Not logged
Route::prefix('buy')->name('buy.')->group(function () {
  Route::get('/subscription', [\App\Http\Controllers\BuyController::class, 'subscription'])->name('subscription');
  Route::get('/package', [\App\Http\Controllers\BuyController::class, 'package'])->name('package');
  Route::post('/newsletter', [\App\Http\Controllers\BuyController::class, 'newsletter'])->name('newsletter');
});

// authenticated
Route::middleware(['auth:sanctum'])->group(function () {

  // Home
  Route::get('/', [\App\Http\Controllers\HomeController::class, 'home'])->name('home');

  Route::post('/store/change', [\App\Http\Controllers\StoreController::class, 'changeCurrentStore'])->name('store.change');

  // Media services
  Route::post('/media/upload', [\App\Http\Controllers\MediaController::class, 'uploadTempFile'])->name('media.upload');

  // Fiscal file
  Route::post('/user/fiscalFile/update', [\App\Http\Controllers\UserController::class, 'updateFiscalFile'])->name('user.fiscalFile.update');

  // Booking
  Route::prefix('booking')->name('booking.')->group(function () {

    // Admin
    Route::middleware(['role:manager|admin'])->group(function () {
      Route::get('/admin-dashboard', [\App\Http\Controllers\BookingController::class, 'adminDashboard'])->name('admin-dashboard');
      /*Route::get('/admin-dashboard', function(){
          return view('utils.maintenance');
      })->name('admin-dashboard');*/
      Route::get('/stylists/{booking}', [\App\Http\Controllers\BookingController::class, 'availableStylists'])->name('stylists');
      Route::post('/{booking}/update', [\App\Http\Controllers\BookingController::class, 'updateStylist'])->name('update.stylist');
      Route::post('/{booking}/calendar', [\App\Http\Controllers\BookingController::class, 'updateFromCalendar'])->name('update.calendar');
      Route::post('/{booking}/date', [\App\Http\Controllers\BookingController::class, 'updateDate'])->name('update.date');
      Route::get('/export', [\App\Http\Controllers\BookingController::class, 'exportCsv'])->name('export');
    });

    Route::middleware(['role:stylist|admin'])->group(function () {
      Route::post('/{booking}/notShown', [BookingController::class, 'setNotShownStatus'])->name('notShown');
    });

    // booking paid
    Route::post('/paid/{booking}', [\App\Http\Controllers\BookingController::class, 'paid'])->name('paid');
    Route::post('/success/no-stripe', [\App\Http\Controllers\BookingController::class, 'successNoStripe'])->name('success.nostripe');

    // check discount
    Route::post('/discount/check', [\App\Http\Controllers\BookingController::class, 'checkDiscount'])->name('discount.check');

    // Customer non logged
    Route::withoutMiddleware(['auth:sanctum'])->group(function () {
      // Wizard main view
      /*Route::get('/wizard', function(){
          return view('utils.maintenance');
      })->name('wizard')->middleware('bookingLocked');*/
      Route::get('/wizard', [\App\Http\Controllers\BookingController::class, 'wizard'])->name('wizard')->middleware('bookingLocked');
      // get payment infos
      Route::get('/payment-infos', [\App\Http\Controllers\BookingController::class, 'getPaymentInfos'])->name('payment-infos');
      // services
      Route::prefix('hair-services')->name('hair-services.')->group(function () {
        Route::post('/primary', [\App\Http\Controllers\BookingController::class, 'primaryHairServices'])->name('primary');
        Route::post('/addon/{primaryService}', [\App\Http\Controllers\BookingController::class, 'addonHairServices'])->name('addon');
        Route::post('/get-stylists', [\App\Http\Controllers\BookingController::class, 'stylistAvailable'])->name('stylists');
        Route::post('/check', [\App\Http\Controllers\BookingController::class, 'checkAvailability'])->name('check-availability');
      });
      // get infos
      Route::post('/infos', [\App\Http\Controllers\BookingController::class, 'getInfos'])->name('infos');
    });

      Route::post('/customer/subscribed', [\App\Http\Controllers\BookingController::class, 'customerIsSubscribed'])->name('customer.subscribed');
      Route::post('/customer/select', [\App\Http\Controllers\BookingController::class, 'customersForSelect'])->name('customer.select');
  });
  Route::resource('booking', \App\Http\Controllers\BookingController::class)->middleware('bookingLocked')->withTrashed(['show']);

  // Buy
  Route::middleware(['role:customer|admin|manager'])->prefix('buy')->name('buy.')->group(function () {
    Route::get('/subscription/checkout/{plan}', [\App\Http\Controllers\BuyController::class, 'subscriptionCheckout'])->name('subscription.checkout');
    Route::get('/package/checkout/{package}', [\App\Http\Controllers\BuyController::class, 'packageCheckout'])->name('package.checkout');
    Route::get('/success', [\App\Http\Controllers\BuyController::class, 'success'])->name('success');
    Route::get('/cancel', [\App\Http\Controllers\BuyController::class, 'cancel'])->name('cancel');
  });
  Route::post('/buy', [\App\Http\Controllers\BuyController::class, 'buy'])->name('buy');
  Route::get('/buy/subscription/after', [\App\Http\Controllers\BuyController::class, 'buyAfterSubscription'])->name('buy.subscription.after');
  Route::get('/buy/charge/after', [\App\Http\Controllers\BuyController::class, 'buyAfterCharge'])->name('buy.charge.after');
  Route::get('/buy/package/after', [\App\Http\Controllers\BuyController::class, 'buyAfterPackage'])->name('buy.package.after');
  Route::post('/swap-subscription', [\App\Http\Controllers\BuyController::class, 'swapSubscription'])->name('subscription.swap');

  // plan detail
  Route::get('/plan/detail', [\App\Http\Controllers\PlanController::class, 'detail'])->name('plan.detail');

  // Schedules
  Route::prefix('schedule')->name('schedule.')->group(function () {
    Route::get('/appointment/resources', [AppointmentScheduleController::class, 'resources'])->name('appointment.resources');
    Route::get('/appointment/events', [AppointmentScheduleController::class, 'events'])->name('appointment.events');
  });

  Route::group(['middleware' => ['role:admin']], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard/totalBookings', [DashboardController::class, 'totalBookings'])->name('admin.dashboard.totalBookings');
    Route::get('dashboard/bookingServices', [DashboardController::class, 'bookingServices'])->name('admin.dashboard.bookingServices');
    Route::get('dashboard/bookingAddonServices', [DashboardController::class, 'bookingAddon'])->name('admin.dashboard.bookingAddonServices');
    Route::get('dashboard/counters', [DashboardController::class, 'counters'])->name('admin.dashboard.counters');
    Route::get('dashboard/bookedCounters', [DashboardController::class, 'bookedCounters'])->name('admin.dashboard.bookedCounters');
    Route::get('dashboard/users', [DashboardController::class, 'users'])->name('admin.dashboard.users');
  });

  // Admin Routes
  Route::group(['middleware' => ['role:manager|admin']], function () {

    Route::post('checkAvailability', [BookingController::class, 'checkAvailabilitySingle'])->name('checkAvailability.single');

    // Checkout error
    Route::get('checkoutErrors', [CheckoutErrorController::class, 'index'])->name('checkoutError.index');
    Route::post('checkoutErrors/{checkoutError}', [CheckoutErrorController::class, 'markAsResolved'])->name('checkoutError.resolve');

    // Availability test
    Route::get('/bb-test/{store}', [TestController::class, 'availability']);

    // Log viewer
    Route::get('/bb-logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

    // Logs
    Route::get('/logs', [\App\Http\Controllers\LogController::class, 'index'])->name('logs.index');

    // Users
    Route::get('/user/export', [\App\Http\Controllers\UserController::class, 'exportCsv'])->name('user.export');
    Route::resource('user', \App\Http\Controllers\UserController::class);

    // Customers
    Route::resource('customer', \App\Http\Controllers\CustomerController::class);

    // Stores
    Route::resource('store', \App\Http\Controllers\StoreController::class);

    // Opening Times
    Route::resource('openingTime', \App\Http\Controllers\OpeningTimeController::class)->only('store', 'update', 'destroy');

    // Closing Days
    Route::resource('closingDay', \App\Http\Controllers\ClosingDayController::class)->only('store', 'update', 'destroy');

    // Exceptional  Times
    Route::resource('exceptionalTime', \App\Http\Controllers\ExceptionalTimeController::class)->only('store', 'update', 'destroy');

    // Hair services
    Route::resource('hairService', \App\Http\Controllers\HairServiceController::class)->except(['show', 'destroy']);
    Route::post('hairService/sync', [\App\Http\Controllers\HairServiceController::class, 'syncServices'])->name('hairService.sync');

    // Settings
    Route::resource('setting', \App\Http\Controllers\SettingController::class)->only('index', 'update');

    // Plans
    Route::resource('plan', \App\Http\Controllers\PlanController::class)->except('show');

    // Subscriptions
    Route::get('/subscription/export', [\App\Http\Controllers\SubscriptionController::class, 'exportCsv'])->name('subscription.export');
    Route::resource('subscription', \App\Http\Controllers\SubscriptionController::class)->only(['index', 'destroy']);

    // Packages
    Route::resource('package', \App\Http\Controllers\PackageController::class)->except('show');

    // Discounts
    Route::get('/discount/export', [\App\Http\Controllers\DiscountController::class, 'exportCsv'])->name('discount.export');
    Route::resource('discount', \App\Http\Controllers\DiscountController::class)->except('show');

    // Payments
    Route::get('/payment/export', [\App\Http\Controllers\PaymentController::class, 'exportCsv'])->name('payment.export');
    Route::resource('payment', \App\Http\Controllers\PaymentController::class)->only(['index', 'destroy']);
    Route::get('/user/{user}/invoice/{payment}', [\App\Http\Controllers\PaymentController::class, 'invoice'])->name('payment.invoice');
      Route::post('/refund/{payment}', [\App\Http\Controllers\PaymentController::class, 'refund'])->name('payment.refund');

    Route::prefix('tanda')->name('tanda.')->group(function () {
        Route::post('/shifts', [\App\Http\Controllers\TandaController::class, 'updateShifts'])->name('update.shifts');
        Route::post('/users', [\App\Http\Controllers\TandaController::class, 'updateUsers'])->name('update.users');
        Route::post('/stores', [\App\Http\Controllers\TandaController::class, 'updateStores'])->name('update.stores');
    });

    // Schedules
    Route::prefix('schedule')->name('schedule.')->group(function () {
      // Default schedule
      Route::get('/default', [DefaultScheduleController::class, 'index'])->name('default.index');
      Route::post('/default', [DefaultScheduleController::class, 'save'])->name('default.save');

      // Specific schedule
      Route::get('/specific', [SpecificScheduleController::class, 'index'])->name('specific.index');
      Route::post('/specific', [SpecificScheduleController::class, 'save'])->name('specific.save');

      // Appointments schedule
      Route::get('/appointment', [AppointmentScheduleController::class, 'index'])->name('appointment.index');
      Route::get('/appointment/past', [AppointmentScheduleController::class, 'past'])->name('appointment.past');
      Route::get('/appointment/all', [AppointmentScheduleController::class, 'allBookings'])->name('appointment.all');

      // Actual schedule
      Route::get('/actual', [ActualScheduleCotroller::class, 'index'])->name('actual.index');
      Route::get('/actual/resources', [ActualScheduleCotroller::class, 'resources'])->name('actual.resources');
      Route::get('/actual/events', [ActualScheduleCotroller::class, 'events'])->name('actual.events');
      Route::get('/actual/bookings', [ActualScheduleCotroller::class, 'bookings'])->name('actual.bookings');

      // No stylist count
      Route::post('/bookings/count', [AppointmentScheduleController::class, 'getNoStylistBookingsCount'])->name('nostylist.count');
    });

    // Payments
    Route::resource('payment', \App\Http\Controllers\PaymentController::class)->only('index');
    Route::get('/user/{user}/invoice/{payment}', [\App\Http\Controllers\PaymentController::class, 'invoice'])->name('payment.invoice');
  });

  // Customer Routes
  Route::middleware(['role:customer'])->prefix('/customers')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CustomersController::class, 'dashboard'])->name('dashboard');
    Route::post('/updatePaymentMethod', [\App\Http\Controllers\CustomersController::class, 'updatePaymentMethod'])->name('updatePaymentMethod');
    Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'indexCustomer'])->name('payments');
    Route::get('/bookings/past', [\App\Http\Controllers\CustomersController::class, 'bookingsPast'])->name('bookings.past');
    Route::get('/bookings/future', [\App\Http\Controllers\CustomersController::class, 'bookingsFuture'])->name('bookings.future');

    Route::get('/profile', [\App\Http\Controllers\CustomersController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [\App\Http\Controllers\CustomersController::class, 'profileUpdate'])->name('profile.update');
    Route::post('/profile/photo/update', [\App\Http\Controllers\CustomersController::class, 'updatePhotoProfile'])->name('profile.photo.update');

    Route::get('/buy/subscription', [\App\Http\Controllers\CustomersController::class, 'buySubscription'])->name('buy.subscription');
    Route::get('/buy/package', [\App\Http\Controllers\CustomersController::class, 'buyPackage'])->name('buy.package');

    Route::post('/photoProfile/hide', [\App\Http\Controllers\CustomersController::class, 'hidePhotoProfileModal'])->name('photoProfileModal.hide');

    Route::post('/booking/{booking}/uploadPhotos/', [\App\Http\Controllers\CustomersController::class, 'uploadBookingPhotos'])->name('booking.uploadPhotos');

    Route::post('/plan/cancel', [\App\Http\Controllers\PlanController::class, 'cancelSubscription'])->name('subscription.cancel');
    Route::get('/plan/edit', [\App\Http\Controllers\PlanController::class, 'editUserPlan'])->name('plan.edit');

    Route::get('/notifications', [\App\Http\Controllers\CustomersController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/markAsRead', [\App\Http\Controllers\CustomersController::class, 'markAsRead'])->name('notifications.markAsRead');

    Route::get('/gallery', [\App\Http\Controllers\CustomersController::class, 'gallery'])->name('gallery');

  });

  // Stylist Routes
  Route::middleware(['role:stylist'])->prefix('/stylist')->name('stylist.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StylistsController::class, 'dashboard'])->name('dashboard');

    Route::get('/appointment', [AppointmentScheduleController::class, 'index'])->name('appointment.index');
    Route::get('/appointment/details/{booking}', [\App\Http\Controllers\BookingController::class, 'showStylist'])->name('appointment.details');

    Route::get('/booking/past', [\App\Http\Controllers\StylistsController::class, 'bookingPast'])->name('booking.past');
    Route::get('/booking/future', [\App\Http\Controllers\StylistsController::class, 'bookingFuture'])->name('booking.future');
    Route::get('/booking/{booking}', [\App\Http\Controllers\StylistsController::class, 'bookingDetails'])->name('booking.details');
    Route::get('/booking/{booking}/edit', [\App\Http\Controllers\StylistsController::class, 'bookingEdit'])->name('booking.edit');
    Route::post('/booking/take', [\App\Http\Controllers\StylistsController::class, 'bookingTakeCharge'])->name('booking.take');
    Route::post('/booking/end', [\App\Http\Controllers\StylistsController::class, 'bookingEndService'])->name('booking.end');
    Route::post('/booking/{booking}/addExtra', [StylistsController::class, 'addExtra'])->name('booking.addExtra');


    Route::get('/customer/show/{customer}', [\App\Http\Controllers\StylistsController::class, 'customerShow'])->name('customer.show');
    Route::get('/profile', [\App\Http\Controllers\StylistsController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [\App\Http\Controllers\StylistsController::class, 'profileUpdate'])->name('profile.update');
  });

  // Notes
  Route::middleware(['role:stylist|admin'])->prefix('/notes')->name('notes.')->group(function () {
    Route::post('customer/{user}/notes', [UserController::class, 'saveCustomerNotes'])->name('customer')->withTrashed();
    Route::post('booking/{booking}/notes', [BookingController::class, 'saveBookingNotes'])->name('booking')->withTrashed();
  });
});


Route::get('/test', [\App\Http\Controllers\TestController::class, 'test']);
Route::get('/ipratico', [\App\Http\Controllers\TestController::class, 'ipratico']);
