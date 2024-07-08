<?php

namespace App\Models;

use App\Services\HelpersService;
use App\Services\StripeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class Plan extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
        'stripe_product_id',
        'name',
        'active',
        'description',
        'hair_service_id',
        'hair_service_count',
        'ds_count',
        'discount_percentage',
  ];

  /**
   * Attributes defaults
   *
   * @var array
   */
  protected $attributes = [
    'stripe_product_id'     => null,
    'name'                  => null,
    'active'                => true,
    'description'           => null,
    'hair_service_id'       => null,
    'hair_service_count'    => 0,
    'ds_count'              => 0,
    'discount_percentage'   => 0,
  ];

  protected $appends = [
      'first_price'
  ];

  public function getFirstPriceAttribute()
  {
      return $this->pricings()->first() ? $this->pricings()->first()->price : null;
  }

  /**
   * All models query
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function allModels(Request $request = null)
  {
    $query = self::query()->with('pricings');

    if ($request) $query->where(fn ($q) => HelpersService::makeSearchQuery($q, $request->q, ['name']));

    return $query->orderByDesc('created_at');
  }

  /**
   * Plan pricings relationship
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function pricings()
  {
    return $this->hasMany(PlanPricing::class);
  }

  /**
   * Scope active
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeActive(Builder $query)
  {
      return $query->where('active', 1);
  }

  /**
   * Store model
   *
   * @param $data
   * @return null
   */
  public static function storeModel($data = [])
  {
    try {
      return self::saveModel(data: $data);
    } catch (\Exception $ex) {
      Log::error(self::class . ' (store): ' . $ex->getMessage());
      // dd('store', $ex->getMessage());
      return null;
    }
  }

  /**
   * Update model
   *
   * @param Plan $model
   * @param array $data
   * @return Plan
   */
  public static function updateModel(Plan $model, $data = [])
  {
    try {
      return self::saveModel(model: $model, data: $data);
    } catch (\Exception $ex) {
      Log::error(self::class . ' (update): ' . $ex->getMessage());
      return null;
    }
  }

  /**
   * Save model
   *
   * @param Plan $model
   * @param array $data
   * @return Plan
   */
  public static function saveModel(Plan $model = null, $data = [])
  {
    try {
      DB::beginTransaction();
      // create stripe product
      $stripe = StripeService::setupStripe();

      // product data
      $productData = [
        'name' => $data['name'],
        'active' => !!$data['active'],
        'description' => $data['description'] ?? null
      ];

      if ($model) {
        // update product
        StripeService::updateProduct($model->stripe_product_id, $productData, $stripe);

        // Update model
        $model->update($data);
      } else {
        // create product
        $product = StripeService::createProduct($productData, $stripe);

        // Store model
        $data['stripe_product_id'] = $product['id'];
        $model = self::create($data);
      }

      // create stripe pricings
      $pricings = $data['pricings'] ?? [];
      foreach ($pricings as $idx => $pricing) {

        $stripeInterval = self::transformDurationIntoStripeIntervals($pricing['duration']);
        $stripeAmount = self::transformPriceIntoStripeAmount($pricing['price']);

        if (!empty($pricing['new'])) {
          // to create

          $stripePrice = StripeService::createPrice([
            'product' => $model->stripe_product_id,
            'recurring' => $stripeInterval,
            'unit_amount' => $stripeAmount,
            'currency' => config('cashier.currency'),
            'active' => !!$pricing['active'],
          ], $stripe);

          $pricingModel = PlanPricing::create([
            'plan_id' => $model->id,
            'stripe_price_id' => $stripePrice['id'],
            'duration_type' => $stripeInterval['interval'],
            'duration_qty' => $stripeInterval['interval_count'],
            'price' => $pricing['price'],
            'active' => !!$pricing['active'],
          ]);
        } else if (!empty($pricing['deleted'])) {
          // to delete

          $pricingModel = PlanPricing::find($pricing['id']);
          if ($pricingModel) $pricingModel->delete();
        } else {
          // to update

          $pricingModel = PlanPricing::find($pricing['id']);
          if ($pricingModel) {
            StripeService::updatePrice(
              $pricingModel->stripe_price_id,
              ['active' => !!$pricing['active']],
              $stripe
            );
            $pricingModel->update([
              'active' => !!$pricing['active'],
            ]);
          }
        }

        $pricings[$idx]['id'] = $pricingModel->id;
      }

      DB::commit();
      return $model;
    } catch (\Throwable $th) {
      DB::rollback();
      throw $th;
    }
  }

  /**
   * Delete model
   *
   * @param Plan $model
   * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
   */
  public static function deleteModel(Plan $model)
  {
    try {
      DB::beginTransaction();

      // delete pricings
      foreach ($model->pricings as $pricing) $pricing->delete();

      // delete product
      StripeService::deleteProduct($model->stripe_product_id);
      $model->delete();

      DB::commit();
      return $model;
    } catch (InvalidRequestException $ex) {
      DB::rollBack();
      Log::error(self::class . ' (delete): ' . $ex->getMessage() . $ex->getCode() . $ex->getError());
      // dd($ex->getMessage());
      return __("Non è possibile cancellare l'abbonamento");
    } catch (\Exception $ex) {
      DB::rollBack();
      Log::error(self::class . ' (delete): ' . $ex->getMessage());
      // dd($ex->getMessage());
      return __("Errore generico");
    }
  }

  /**
   * Transform duration string into stripe recurring array
   *
   * @param $duration
   * @return array
   */
  private static function transformDurationIntoStripeIntervals($duration)
  {
    $ex = explode(':', $duration);
    return (count($ex) == 2) ? [
      'interval' => $ex[1],
      'interval_count' => $ex[0],
    ] : [];
  }

  /**
   * Transform price into stripe amount
   *
   * @param $price
   * @return int
   */
  private static function transformPriceIntoStripeAmount($price)
  {
    return intval($price * 100);
  }

  /**
   * Check if plan is promo
   *
   * @return bool
   */
  public function isPromo()
  {
      return $this->name == config('app.promo_name', '');
  }
}
