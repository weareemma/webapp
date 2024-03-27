<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\CsvExportable;
use Laravel\Cashier\Billable;
use App\Services\StripeService;
use App\Pivots\PackageUserPivot;
use App\Services\HelpersService;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\SubscriptionService;
use App\Notifications\WelcomeCustomer;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Password;
use function Illuminate\Events\queueable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmailNotification;
use App\Services\IpraticoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
  use HasApiTokens;
  use HasFactory;
  use HasProfilePhoto;
  use Notifiable;
  use TwoFactorAuthenticatable;
  use HasRoles;
  use Impersonate;
  use SoftDeletes;
  use InteractsWithMedia;
  use Billable;
  use CsvExportable;


  /**
   * Roles
   */
  const ROLE_ADMIN = 'admin';
  const ROLE_MANAGER = 'manager';
  const ROLE_OPERATOR = 'operator';
  const ROLE_STYLIST = 'stylist';
  const ROLE_CUSTOMER = 'customer';

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'name',
    'surname',
    'fiscal_code',
    'email',
    'password',
    'phone',
    'address',
    'stores',
    'tanda_code',
    'afro',
    'current_store',
    'google_id',
    'facebook_id',
    'last_notes',
    'last_notes_updated_at',
    'last_notes_by_id',
    'ipratico',
    'active'
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'name'          => null,
    'surname'       => null,
    'fiscal_code'   => null,
    'email'         => null,
    'phone'         => null,
    'address'       => null,
    'stores'        => null,
    'tanda_code'    => null,
    'afro'          => false,
    'active'        => true
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'stores' => 'array',
    'afro' => 'boolean'
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = [
    'profile_photo_url',
    'full_name',
    'full_name_reverse',
    'role',
    'last_booking',
    'last_notes_by_name'
  ];

  /**
   * Get the URL to the user's profile photo.
   *
   * @return string
   */
  public function getProfilePhotoUrlAttribute()
  {
    $url = $this->profile_photo_path
    ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
    : $this->defaultProfilePhotoUrl();

      return str_replace(':3000', ':', $url);
  }

  public function getNameAttribute($value)
  {
      return $value ?? '';
  }

  /**
   * Last notes by stylist
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function lastNotesBy()
  {
      return $this->belongsTo(User::class, 'last_notes_by_id');
  }

  /**
   * User's packages
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function packages()
  {
    return $this->belongsToMany(Package::class)
      ->using(PackageUserPivot::class)
      ->withPivot('services')
      ->withTimestamps();
  }

  /**
   * User's active packages with store option
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function activePackages($storeId = null)
  {
    $query = $this->packages();

    if ($storeId) {
      $query->where(function ($q) use ($storeId) {
        $q->whereJsonContains('stores', "{$storeId}");
        $q->orWhereJsonContains('stores', $storeId);
      });
    }

    return $query->whereDate('expired_at', '>', Carbon::today())->get();
  }

  /** User's payments
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function payments()
  {
    return $this->hasMany(Payment::class);
  }

  /**
   * Has one fiscal file
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function fiscalFile()
  {
      return $this->hasOne(FiscalFile::class);
  }

  /**
   * Sync customer data with stripe
   *
   * @return void
   */
  protected static function booted()
  {
    static::updated(queueable(function ($customer) {
      if ($customer->hasStripeId()) {
        $customer->syncStripeCustomerDetails();
      }
    }));
  }

  /**
   * Assign role to user
   *
   */
  private function assignRoleToUser($role_name)
  {
    $role = Role::where('name', $role_name)->first();
    if ($role) {
      $this->syncRoles($role);
      $this->save();
    }
  }
  public function makeAdmin()
  {
    $this->assignRoleToUser(self::ROLE_ADMIN);
  }
  public function makeManager()
  {
    $this->assignRoleToUser(self::ROLE_MANAGER);
  }
  public function makeOperator()
  {
    $this->assignRoleToUser(self::ROLE_OPERATOR);
  }
  public function makeStylist()
  {
    $this->assignRoleToUser(self::ROLE_STYLIST);
  }
  public function makeCustomer()
  {
    $this->assignRoleToUser(self::ROLE_CUSTOMER);
  }

  /**
   * Check if user is specific role
   *
   * @return bool
   */
  public function isAdmin()
  {
    return $this->hasRole(self::ROLE_ADMIN);
  }
  public function isManager()
  {
    return $this->hasRole(self::ROLE_MANAGER);
  }
  public function isOperator()
  {
    return $this->hasRole(self::ROLE_OPERATOR);
  }
  public function isStylist()
  {
    return $this->hasRole(self::ROLE_STYLIST);
  }
  public function isCustomer()
  {
    return $this->hasRole(self::ROLE_CUSTOMER);
  }

  /**
   * All users of a kind
   *
   * @return mixed
   */
  public static function managers()
  {
    return self::allUsers()->role(self::ROLE_MANAGER)->get();
  }
  public static function admins()
  {
    return self::allUsers()->role(self::ROLE_ADMIN)->get();
  }
  public static function operators()
  {
    return self::allUsers()->role(self::ROLE_OPERATOR)->get();
  }
  public static function stylists()
  {
    return self::allUsers()->role(self::ROLE_STYLIST)->get();
  }
  public static function customers()
  {
    return self::allUsers()->role(self::ROLE_CUSTOMER)->get();
  }

  /**
   * Get user role
   *
   * @return null
   */
  public function getRoleAttribute()
  {
    return $this->roles()->first() ? $this->roles()->first()->name : null;
  }

  /**
   * Set user role
   *
   * @param $role
   * @return void
   */
  private function setUserRole($role)
  {
    switch ($role) {
      case self::ROLE_ADMIN:
        $this->makeAdmin();
        break;
      case self::ROLE_MANAGER:
        $this->makeManager();
        break;
      case self::ROLE_OPERATOR:
        $this->makeOperator();
        break;
      case self::ROLE_STYLIST:
        $this->makeStylist();
        break;
      case self::ROLE_CUSTOMER:
        $this->makeCustomer();
        break;
      default:
        break;
    }
  }

  /**
   * Rela bookings
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function bookings()
  {
    $foreign_key = ($this->isStylist() ? 'stylist_id' : 'customer_id');
    return $this->hasMany(Booking::class, $foreign_key)
        ->has('customer')
        ->when($this->isCustomer(), function ($query) {
            $query->owned();
        });
  }

  public function pastBookings()
  {
      return $this->bookings()
          ->whereDate('date', '<=', now()->format('Y-m-d'))
          ->whereTime('start', '<', now()->format('H:i'))
          ->orderBy('date', 'desc')
          ->orderBy('start', 'desc');
  }

  public function nextBookings()
  {
      $nextBookings = $this->bookings()
          ->where('date', '>=', now()->format('Y-m-d'))
          ->orderBy('date')
          ->orderBy('start')
          ->get();

      return $nextBookings->where(function ($b) {
          return $b->start_date->gt(now());
      })->sortBy('start_date');
  }

  /**
   * Get full name attribute
   *
   * @return string
   */
  public function getFullNameAttribute()
  {
    return $this->name . ' ' . $this->surname;
  }

  public function getFullNameReverseAttribute()
  {
    return $this->surname . ' ' . $this->name;
  }

  /**
   * All users query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allUsers(Request $request = null)
  {
    $query = self::query()
      ->with(['subscriptions']);

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['email','name', 'surname']));

    if ($request)
    {
      if ($request->subStatus == 'subscribed')
      {
        $query->whereHas('subscriptions', function($q) use ($request) {
          $q->where('stripe_status', 'active');
          if ($request->subName) $q->where('name', $request->subName);
          if ($request->subFrom)
          {
            $from = HelpersService::parseDateString($request->get('subFrom'));
            if ($from)
            {
                $q->whereDate('ends_at', '>=', $from);
            }
          }
          
          if ($request->subTo)
          {
            $to = HelpersService::parseDateString($request->get('subTo'));
            if ($to)
            {
                $q->whereDate('ends_at', '<=', $to);
            }
          }        
        });
      }

      if ($request->subStatus == 'unsubscribed')
      {
        $query->where(function($q) {
          $q
            ->doesntHave('subscriptions')
            ->orWhere(function($q) {
              $q->whereHas('subscriptions', function($q) {
                $q->where('stripe_status', '!=', 'active');
              });
            });
        });
      }
    }

    return $query->orderByDesc('created_at');
  }

  /**
   * Store user
   *
   * @param $data
   * @return void|null
   */
  public static function store($data = [])
  {
    try {
      // Encrypt password
      if (array_key_exists('password', $data))
      {
          $send_create_password = false;
          $password = $data['password'];
      }
      else
      {
          $send_create_password = true;
          $password = Str::random(8);
      }

      $data['password'] = Hash::make($password);

      // Create user
      $user = self::create(Arr::except($data, [
        'role'
      ]));

      // Set user role and save
      $user->setUserRole($data['role']);

      // If it's customer creation send email
      if($send_create_password)
      {
          $url = route('password.reset', [
              'token' => Password::createToken($user),
              'email' => $user->email
          ]);
          $user->notify(new WelcomeCustomer($url));

          (new IpraticoService())->createOrUpdateBusinessActor($user);
      }

      return $user;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (store): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Update user
   *
   * @param User $user
   * @param $data
   * @return User|null
   */
  public static function edit(User $user, $data = [])
  {
    try {
      if (array_key_exists('password', $data) &&  !empty($data['password']) && $data['password'] !== '') {
        // Encrypt password
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $user->update(Arr::except($data, ['photo', 'role']));
      if (isset($data['photo'])) {
          $user->updateProfilePhoto($data['photo']);
      }

        if (isset($data['role'])) {
            $user->setUserRole($data['role']);
        }

      $user->save();

      return $user;
    } catch (\Exception $ex) {
      Log::error(self::class . ' (update): ' . $ex->getMessage());
      return null;
    }
  }


  /**
   * Rela shift
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function shifts()
  {
    return $this->hasMany(Shift::class);
  }

  /**
   * Delete user
   *
   * @param User $user
   * @return void
   */
  public static function deleteModel(User $user)
  {
    try {
      $user->delete();
    } catch (\Exception $ex) {
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
    }
  }

  /**
   * Override default profile photo url
   *
   * @return string
   */
  // protected function defaultProfilePhotoUrl()
  // {
  //     return '';
  // }

  /**
   * Register media collection
   *
   * @return void
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('tmp-hairService');
    $this->addMediaCollection('tmp-booking');
  }

  /**
   * Get temp collection name for model
   *
   * @param $model_name
   * @return string
   */
  public function getTempCollectionName($model_name)
  {
    return 'tmp-' . $model_name;
  }

  /**
   * Reset temp media collection
   *
   * @param $model_name
   * @return void
   */
  public function clearTempCollection($model_name)
  {
    $this->clearMediaCollection($this->getTempCollectionName($model_name));
  }

  /**
   * Get first temp media
   *
   * @param $model_name
   * @param $uid
   * @return mixed
   */
  public function getTempMedia($model_name, $uid = '')
  {
    return $this->getMedia($this->getTempCollectionName($model_name), [
      'uid' => $uid
    ]);
  }

  /**
   * Has any plan
   *
   * @return boolean
   */
  public function hasAnySubscription()
  {
    foreach (Plan::all() as $plan) if ($this->subscribed($plan->stripe_product_id)) return true;
    return false;
  }

  /**
   * Get first plan
   *
   * @return Plan|null
   */
  public function getFirstPlan()
  {
    foreach (Plan::all() as $plan) if ($this->subscribed($plan->stripe_product_id)) return $plan;
    return null;
  }

  public function getFirstPrice()
  {
      $sub = $this->subscriptions()->active()->first();
      if ($sub)
      {
          return PlanPricing::query()->where('stripe_price_id', $sub->stripe_price)->first();
      }
      return null;
  }

  public function getFirstPriceForStripe()
  {
      $sub = $this->subscriptions()->first();
      if ($sub)
      {
          return PlanPricing::query()->where('stripe_price_id', $sub->stripe_price)->first();
      }
      return null;
  }

  /**
   * Assign store to user
   *
   * @param Store $store
   * @return void
   */
  public function assignStore(Store $store)
  {
    if (!in_array($store->id, $this->stores ?? [])) {
      $stores = $this->stores ?? [];
      $stores[] = $store->id;
      $this->stores = $stores;
      $this->save();
    }
  }
  /**
   * Get first plan
   *
   * @return boolean
   */
  public function getFirstSubscription($plan = null)
  {
    $p = $plan ?? $this->getFirstPlan();
    return $p ? $this->subscription($p->stripe_product_id) : null;
  }

  public function getFirstStripeSubscription()
  {
      $p = $this->getFirstPlan();
      return $p ? $this->subscription($p->stripe_product_id)->asStripeSubscription() : null;
  }

  /**
   * Buy package
   *
   * @param User $user
   * @param Package $package
   * @return void
   */
  public static function buyPackage(User $user, Package $package)
  {
    if ($user && $package) {
      $user->packages()->syncWithPivotValues($package->id, [
        'services' => ($package->services) ? json_encode($package->services) : null
      ]);
    }
  }

  /**
   * Load customer data
   *
   * @return array
   */
  public static function loadCustomerData()
  {
      $data = [];

      if (Auth::check() && Auth::user()->isCustomer())
      {
          $customer = Auth::user();
          $data['user'] = $customer;
          $data['notifications_count'] = $customer->unreadNotifications()->count();
          $data['packages'] = $customer->activePackages();
          $data['bookings'] = $customer->bookings()->get();
          $data['last_booking'] = $customer->pastBookings()->orderByDesc('date')->orderbyDesc('start')->first();
          $data['next_booking'] = $customer->getNextBooking();
          $data['can_edit_next_booking'] = $customer->canEditNextBooking();
          $data['subscription'] = $customer->getFirstPlan();
          $data['current_price'] = $customer->getFirstPrice();
          $data['subscription_ends_at'] = $customer->currentSubscriptionEndsAt();
          $data['photo_profile_remember'] = $customer->showPhotoProfileRememberModal();
          $data['default_payment_method'] = $customer->defaultPaymentMethod();
          $data['customer_discount'] = SubscriptionService::getCurrentSubDiscount($customer);
      }

      return $data;
  }

  /**
   * Hide photo profile modal
   *
   * @return void
   */
  public function hidePhotoProfileRememberModal()
  {
      Session::put('photo_profile_remember', false);
  }

  /**
   * Show photo profile modal
   *
   * @return bool
   */
  public function showPhotoProfileRememberModal()
  {
      return $this->isCustomer()
          && is_null($this->profile_photo_path)
          && Session::get('photo_profile_remember', true);
  }

    /**
     * Check if user is model owner
     *
     * @param $model
     * @param $id_field
     * @return bool
     */
  public function isModelOwner($model, array $id_fields)
  {
      foreach ($id_fields as $field)
      {
          if ($model->{$field} == $this->id) return true;
      }
      return false;
  }

  /**
   * Get last booking
   *
   * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
   */
  public function getLastBookingAttribute()
  {
      return null;
  }

  /**
   * Get last notes by stylist - name
   *
   * @return mixed|string
   */
  public function getLastNotesByNameAttribute()
  {
      return '-';
  }

    /**
     * Get next booking
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
  public function getNextBooking()
  {
      $nextBookings = $this->bookings()
          ->with('store')
          ->where('date', '>=', now()->format('Y-m-d'))
          ->orderBy('date')
          ->orderBy('start')
          ->get();

      $nextBooking = $nextBookings->where(function ($b) {
          return $b->start_date->gt(now());
      })->sortBy('start_date')->first();

      return $nextBooking;
  }

    /**
     * Customer can edit booking till 48 hours before
     *
     * @return bool
     */
  public function canEditNextBooking()
  {
      $edit_limit = Setting::getSetting('delete_update_limit', 24);
      $nextBooking = $this->getNextBooking();
      return ($nextBooking) && $nextBooking->can_be_edited;
  }

  public function currentSubscriptionEndsAt()
  {
      $sub = $this->subscriptions()->active()->first();
      return ($sub) ? $sub->ends_at : null;
  }

  public function getLastPhotos()
  {
      $bookings = $this
          ->bookings()
          ->orderBy('date')
          ->orderBy('start')
          ->limit(4)
          ->get();

      $photos = [];

      foreach ($bookings as $booking)
      {
          foreach ($booking->photos as $photo)
          {
              $photos[] = $photo;
          }
      }
      return $photos;
  }

  /**
   * Override reset password email template
   *
   * @param $token
   * @return void
   */
  public function sendPasswordResetNotification($token)
  {
      $url = url(route('password.reset', [
          'token' => $token,
          'email' => $this->email,
      ], false));

      $this->notify(new ResetPassword($url));
  }

  /**
   * Get whatsapp contact field
   *
   * @return mixed
   */
  public function getWhatsAppContact()
  {
      return $this->phone;
  }

  /**
   * Update fiscal file
   *
   * @param User $user
   * @param $data
   * @return void
   */
  public static function updateFiscalFile(User $user, $data = [])
  {
      $fiscal = $user->fiscalFile;

      if (is_null($fiscal))
      {
          $fiscal = new FiscalFile();
      }

      $fiscal->fill($data);
      $fiscal->user_id = $user->id;
      $fiscal->save();
  }

  /**
   * Check if user has already promo sub
   *
   * @return bool
   */
  public function hasAlreadyPromo()
  {
      $plan = Plan::query()
          ->where('name', config('app.promo_name', 'PROMO'))
          ->first();

      if ($plan)
      {
          $found = $this
              ->subscriptions()
              ->where('name', $plan->stripe_product_id)
              ->first();

          return ! is_null($found);
      }

      return false;
  }

    /**
     * Update payment method
     *
     * @return void
     */
    public function updatePaymentMethod()
    {
        $methods = $this->paymentMethods();

        if ($methods && $methods->count() > 0)
        {
            $default = $methods->first();
            $this->updateDefaultPaymentMethod($default->id);
        }
    }

    public function getBookingCountAttribute()
    {
      return 0;
    }

    public function canImpersonate()
    {
        return $this->isAdmin();
    }

    public function canBeImpersonated()
    {
        return ! $this->isAdmin();
    }

    /**
     * Create discount for customer
     *
     * @param Booking $booking
     * @param null $total
     * @param null $description
     * @return void
     */
    public function createDiscountFromBooking(Booking $booking, $total = null, $description = null)
    {
        if ($total)
        {
            $discount = Discount::create([
                'code' => "SU" . $this->id . now()->format('dHi'),
                'checkout_type' => 'appointment',
                'typology' => 'fixed',
                'value' => $total,
                'minimum_charge' => 0,
                'valid_from' => now(),
                'valid_to' => now()->addMonths(6),
                'maximum_count_per_user' => 1,
                'stores' => array(strval($booking->store_id)),
                'users' => array(strval($this->id)),
                'target' => 'users',
                'description' => $description
            ]);

            // Create refund record
            Refund::create([
                'user_id' => $this->id,
                'total' => $total,
                'refundable_id' => $booking->id,
                'refundable_type' => Booking::class
            ]);

            return $discount;
        }

        return null;
    }

    /**
     * Set csv headers
     *
     * @param array $metadata
     * @return string[]
     */
    public static function setHeaders(array $metadata = [])
    {
        return [
            'Cognome',
            'Nome',
            'Email',
            'Telefono',
            'Data registrazione',
            'Abbonato',
            'Nome Abbonamento',
            'Status abbonamento',
            'Data abbonamento',
            'Data scadenza abbonamento',
            'Data ultimo appuntamento',
            'No. totale appuntamenti',
            ...array_map(fn($i) => $i['header'], $metadata['stores']),
            'No. servizio non eseguito',
            ...array_map(fn($i) => $i['header'], $metadata['primaries']),
            ...array_map(fn($i) => $i['header'], $metadata['addon']),
            'Codici sconto prodotti',
            'Codici sconto redenti',
            'Valore totale appuntamenti',
            'Valore totale transazioni',
            'Data ultimo acquisto',
            'Appuntamenti cancellati',
            'Campo note',
            'Campo note stylist',
            'No. totale note'
        ];
    }

    /**
     * Transform booking into csv line
     *
     * @param array $metadata
     * @return array
     */
    protected function toCsv(array $metadata = []): array
    {
        $current_subscription = $this->subscriptions->sortByDesc('created_at')->first();
        $bookings = $this->bookings->whereNull('deleted_at');

        // Stores
        $stores = [];
        foreach ($metadata['stores'] as $store)
        {
            $stores[] = $bookings->where('store_id', $store['id'])->count();
        }

        // Discounts
        $discount_created = 0;
        foreach ($metadata['discounts'] as $discount)
        {
            if (isset($discount['users'][0]) && $discount['users'][0] == $this->id)
            {
                $discount_created++;
            }
        }
        $discount_usage = 0;
        foreach ($bookings as $booking)
        {
            if ($booking->additional_data && isset($booking->additional_data['discount']))
            {
                $discount_usage++;
            }
        }

        // Last stylist note
        $stylist_note = null;
        $booking = $bookings->whereNotNull('stylist_notes')->sortByDesc('date')->sortByDesc('start')->first();
        if ($booking)
        {
            $stylist_note = $booking->stylist_notes;
        }

        // Primaries
        $primaries = [];
        foreach ($metadata['primaries'] as $service)
        {
            $count = 0;
            foreach ($bookings as $booking)
            {
                if (isset($booking->slots))
                {
                    foreach ($booking->slots as $slot)
                    {
                        if (isset($slot['service']['id']) && $slot['service']['id'] == $service['id'])
                        {
                            $count++;
                        }
                    }
                }
            }

            $primaries[] = $count;
        }

        // addon
        $addon = [];
        foreach ($metadata['addon'] as $service)
        {
            $count = 0;
            foreach ($bookings as $booking)
            {
                if (isset($booking->slots))
                {
                    foreach ($booking->slots as $slot)
                    {
                        if (isset($slot['service']['id']) && $slot['service']['id'] == $service['id'])
                        {
                            $count++;
                        }
                    }
                }
            }

            $addon[] = $count;
        }

        // Plan
        $plan_name = '-';
        if ($current_subscription)
        {
          $plan = collect($metadata['plans'])->where('stripe_product_id', $current_subscription->name)->first();
          $plan_name = ($plan) ? $plan['name'] : '-';
        }

        return [
            $this->surname,
            $this->name,
            $this->email,
            $this->phone,
            $this->created_at->format($this->datetime_format),
            ($this->subscriptions->count() > 0) ? 'Si' : 'No',
            $plan_name,
            ($current_subscription) ? $current_subscription->stripe_status : '-',
            $current_subscription?->created_at->format($this->datetime_format),
            ($current_subscription && $current_subscription->ends_at)
                ? $current_subscription->ends_at->format($this->datetime_format)
                : '-',
            $bookings->sortByDesc('date')->sortBy('start')->first()?->date->format($this->date_format) ?? '-',
            $bookings->count(),
            ...$stores,
            $bookings->whereNull('stylist_started_at')->count(),
            ...$primaries,
            ...$addon,
            $discount_created,
            $discount_usage,
            $bookings->sum('total_net_price'),
            $this->payments->where('refunded', '!=', 1)->sum('total'),
            $this->payments->sortBy('created_at')->first()?->created_at?->format($this->datetime_format) ?? '-',
            $this->bookings->whereNotNull('deleted_at')->count(),
            $this->last_notes,
            $stylist_note,
            $bookings->whereNotNull('stylist_notes')->count()
        ];
    }

    /**
     * Load csv data
     *
     * @param array $request
     * @return Collection
     */
    protected static function loadData(array $request = []): Collection
    {
        $query = User::query()
          ->role(self::ROLE_CUSTOMER)
          ->with(['bookings' => fn($q) => $q->withTrashed(), 'subscriptions', 'payments']);

          if (isset($request['subStatus']) && $request['subStatus'] == 'subscribed')
          {
            $query->whereHas('subscriptions', function($q) use ($request) {
              $q->where('stripe_status', 'active');
              if ($request['subName'] ?? false) $q->where('name', $request['subName']);

              if (isset($request['subFrom']))
              {
                $from = HelpersService::parseDateString($request['subFrom']);
                if ($from)
                {
                    $q->whereDate('ends_at', '>=', $from);
                }
              }
              
              if (isset($request['subTo']))
              {
                $to = HelpersService::parseDateString($request['subTo']);
                if ($to)
                {
                    $q->whereDate('ends_at', '<=', $to);
                }
              }
            });
          }
      
          if (isset($request['subStatus']) && $request['subStatus'] == 'unsubscribed')
          {
            $query->where(function($q) {
              $q
                ->doesntHave('subscriptions')
                ->orWhere(function($q) {
                  $q->whereHas('subscriptions', function($q) {
                    $q->where('stripe_status', '!=', 'active');
                  });
                });
            });
          }

        return $query
            ->latest()
            ->get();
    }

    /**
     * Set csv file name
     *
     * @return string
     */
    protected static function setFileName()
    {
        return 'export_clienti_'. now()->format('d-m-Y-Hi') . '.csv';
    }

    /**
     * Load meta data for building csv
     *
     * @return array|array[]
     */
    public static function loadMetadata()
    {
        $meta = [
            'stores' => [],
            'primaries' => [],
            'addon' => [],
            'discounts' => []
        ];

        // Fetch stores
        foreach (Store::all() as $store)
        {
            $meta['stores'][] = [
                'header' => 'No. ' . $store->name,
                ...$store->getAttributes()
            ];
        }

        // Fetch primary services
        foreach (HairService::query()->primary()->get() as $service)
        {
            $meta['primaries'][] = [
                'header' => 'No. ' . $service->title,
                ...$service->getAttributes()
            ];
        }

        // Fetch addons
        foreach (HairService::query()->addon()->get() as $service)
        {
            $meta['addon'][] = [
                'header' => 'No. ' . $service->title,
                ...$service->getAttributes()
            ];
        }

        // Fetch discounts
        foreach (Discount::all() as $discount)
        {
            $meta['discounts'][] = [
                ...$discount->getAttributes()
            ];
        }

        // Fetch plans
        foreach (Plan::all() as $plan)
        {
            $meta['plans'][] = [
                ...$plan->getAttributes()
            ];
        }

        return $meta;
    }
}
