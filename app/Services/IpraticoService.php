<?php

namespace App\Services;

use App\Api\IpraticoApi;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\HairService;
use App\Models\Plan;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpraticoService
{

    public const SERVICE_CATEGORY_PRIMARY = 'Primario';
    public const SERVICE_CATEGORY_ADDON = 'Addon';

    public const PRODUCT_FAMILY_ID = 'product_family:services';

    public const VAT_22_RECORD_NAME = 'Rep. 2 (22%)';

    public const PRICE_LIST_NAME = 'Listino Servizi';

    public const APP_NAME = 'WeAreEmma';

    public const SOURCE_EAT = 'eat';
    public const SOURCE_WEBAPP = 'app_user';

    /**
     * Api client
     *
     * @var IpraticoApi
     */
    private $api;

    /**
     * Construct
     *
     * @param $api_key
     * @param $base_url
     */
    public function __construct($api_key = null, $base_url = null)
    {
        $this->api = new IpraticoApi(
            $api_key,
            $base_url
        );
    }

    /**
     * Get vat record by name
     *
     * @param $name
     * @return array|null
     * @throws GuzzleException
     */
    public function getVatRecordByName($name)
    {
        $vat_categories = $this->api->getVatCategories();

        foreach ($vat_categories as $category)
        {
            if ($category->value && $category->value->name == $name)
            {
                return [
                    'id' => $category->id,
                    'departmentIndex' => $category->value->departmentIndex
                ];
            }
        }

        return null;
    }

    /**
     * Get category by name
     *
     * @param $type
     * @return array|null
     * @throws GuzzleException
     */
    public function getCategoryByType($type)
    {
        $categories = $this->api->getCategories();

        foreach ($categories as $category)
        {
            if ($category->value && $category->value->name == $type)
            {
                return [
                    'id' => $category->id,
                    'sale_type' => $category->value->fiscalDefault[0]->saleTypeId,
                    'vat_category' => $category->value->fiscalDefault[0]->vatRecordCategoryId,
                ];
            }
        }

        return null;
    }

    /**
     * Get price list
     *
     * @return array|null
     * @throws GuzzleException
     */
    public function getPriceList()
    {
        $lists = $this->api->getPriceLists();

        foreach ($lists as $list)
        {
            if ($list->value && $list->value->name == self::PRICE_LIST_NAME)
            {
                return [
                    'id' => $list->id
                ];
            }
        }

        return null;
    }

    /**
     * Create price list
     *
     * @return void
     * @throws GuzzleException
     */
    public function createPriceList()
    {
        $list = $this->getPriceList();

        if (is_null($list))
        {
            $this->api->createPriceList([
                'name' => self::PRICE_LIST_NAME
            ]);
        }
    }

    /**
     * Get business actor by user model
     *
     * @param User $user
     * @return array|null
     * @throws GuzzleException
     */
    public function getBusinessActor(User $user)
    {
        if ($user->ipratico_id)
        {
            $actor = $this->api->getBusinessActorById($user->ipratico_id);

            if ($actor)
            {
                return [
                    'id' => $actor->id,
                    'cas' => $actor->cas,
                    'name' => $actor->value->personal->foreName,
                    'surname' => $actor->value->surnameOrCompanyName,
                    'email' => $actor->value->emails[0] ?? '',
                    'phone' => $actor->value->phones[0] ?? ''
                ];
            }
        }

        return null;
    }

    /**
     * Get order by booking model
     *
     * @param Booking $booking
     * @return array|null
     * @throws GuzzleException
     */
    public function getOrder(Booking $booking)
    {
        if ($booking->ipratico_id)
        {
            $order = $this->api->getOrders($booking->ipratico_id);

            return [
                'id' => $order?->id
            ];
        }

        return null;
    }

    /**
     * Get product by hair service model
     *
     * @param HairService $service
     * @return array|null
     * @throws GuzzleException
     */
    public function getProduct(HairService $service)
    {
        if ($service->ipratico_id)
        {
            $product = $this->api->getProducts($service->ipratico_id);

            return [
                'id' => $product->id,
                'cas' => $product->cas
            ];
        }

        return null;
    }

    /**
     * Create categories
     *
     * @return bool
     * @throws GuzzleException
     */
    public function createCategories()
    {
        // Get Vat record
        $vat = $this->getVatRecordByName(self::VAT_22_RECORD_NAME);

        if ($vat)
        {
            // Primary category
            $cat = $this->getCategoryByType(self::SERVICE_CATEGORY_PRIMARY);
            if (is_null($cat))
            {
                $this->api->createCategory([
                    'name' => self::SERVICE_CATEGORY_PRIMARY,
                    'familyId' => self::PRODUCT_FAMILY_ID,
                    'fiscalDefault' => [
                        [
                            'saleTypeId' => 'sale_type:take-away',
                            'vatRecordCategoryId' => $vat['id']
                        ]
                    ]
                ]);
            }

            // Addon category
            $cat = $this->getCategoryByType(self::SERVICE_CATEGORY_ADDON);
            if (is_null($cat))
            {
                $this->api->createCategory([
                    'name' => self::SERVICE_CATEGORY_ADDON,
                    'familyId' => self::PRODUCT_FAMILY_ID,
                    'fiscalDefault' => [
                        [
                            'saleTypeId' => 'sale_type:take-away',
                            'vatRecordCategoryId' => $vat['id']
                        ]
                    ]
                ]);
            }
        }
        else
        {
            return false;
        }

        return true;
    }

    /**
     * Create products
     *
     * @return bool
     * @throws GuzzleException
     */
    public function createProducts()
    {
        $primary_category = $this->getCategoryByType(self::SERVICE_CATEGORY_PRIMARY);
        $addon_category = $this->getCategoryByType(self::SERVICE_CATEGORY_ADDON);

        // Get price list
        $price_list = $this->getPriceList();

        if ($primary_category && $addon_category && $price_list)
        {
            $services = HairService::query()->get();
            foreach ($services as $service)
            {
                $data = null;

                if ($service->level == 'primary')
                {
                    $data = [
                        'name' => $service->title,
                        'productCategoryId' => $primary_category['id'],
                        'fiscality' => [
                            [
                                'saleTypeId' => $primary_category['sale_type'],
                                'vatRecordCategoryId' => $primary_category['vat_category']
                            ]
                        ],
                        'pricesArray' => [
                            [
                                'elements' => [
                                    [
                                        'price' => $service->net_price,
                                        'saleTypeId' => $primary_category['sale_type']
                                    ]
                                ],
                                'priceListId' => $price_list['id']
                            ]
                        ],
                        'externalIntegrations' => [
                            'application' => self::APP_NAME,
                            'externalId' => $service->id
                        ]
                    ];
                }
                elseif ($service->level == 'addon')
                {
                    $data = [
                        'name' => $service->title,
                        'productCategoryId' => $addon_category['id'],
                        'fiscality' => [
                            [
                                'saleTypeId' => $addon_category['sale_type'],
                                'vatRecordCategoryId' => $addon_category['vat_category']
                            ]
                        ],
                        'pricesArray' => [
                            [
                                'elements' => [
                                    [
                                        'price' => $service->net_price,
                                        'saleTypeId' => $addon_category['sale_type']
                                    ]
                                ],
                                'priceListId' => $price_list['id']
                            ]
                        ],
                        'externalIntegrations' => [
                            'application' => self::APP_NAME,
                            'externalId' => $service->id
                        ]
                    ];
                }

                if ($data)
                {
                    $prod = $this->api->createProduct($data);

                    if ($prod)
                    {
                        
                        $service->ipratico_id = $prod->id;
                        $service->ipratico_vat_id = ($service->level == 'primary')
                            ? $primary_category['vat_category']
                            : $addon_category['vat_category'];
                        $service->save();
                    }
                }
            }
        }
        else
        {
            return false;
        }

        return true;
    }

    /**
     * Create or update product
     *
     * @param HairService $service
     * @return void
     * @throws GuzzleException
     */
    public function createOrUpdateProduct(HairService $service)
    {
        $price_list = $this->getPriceList();

        $category = null;
        if ($service->level == 'primary')
            $category = $this->getCategoryByType(self::SERVICE_CATEGORY_PRIMARY);
        if ($service->level == 'addon')
            $category = $this->getCategoryByType(self::SERVICE_CATEGORY_ADDON);

        if ($category && $price_list)
        {
            $product = $this->getProduct($service);

            $data = [
                'name' => $service->title,
                'productCategoryId' => $category['id'],
                'fiscality' => [
                    [
                        'saleTypeId' => $category['sale_type'],
                        'vatRecordCategoryId' => $category['vat_category']
                    ]
                ],
                'pricesArray' => [
                    [
                        'elements' => [
                            [
                                'price' => $service->net_price,
                                'saleTypeId' => $category['sale_type']
                            ]
                        ],
                        'priceListId' => $price_list['id']
                    ]
                ],
                'externalIntegrations' => [
                    [
                        'application' => 'WeAreEmma',
                        'externalId' => $service->id
                    ]
                ]
            ];

            if (is_null($product))
            {
                $product = $this->api->createProduct($data);

                if ($product)
                {
                    $service->ipratico_id = $product->id;
                    $service->ipratico_vat_id = $category['vat_category'];
                    $service->save();
                }
            }
            else
            {
                $data['cas'] = $product['cas'];
                $this->api->updateProduct($service->ipratico_id, $data);
            }
        }
    }

    /**
     * Create or update business actor
     *
     * @param User $user
     * @return void
     * @throws GuzzleException
     */
    public function createOrUpdateBusinessActor(User $user)
    {
        $actor = $this->getBusinessActor($user);

        if (is_null($actor))
        {
            $actor = $this->api->createBusinessActor([
                'surnameOrCompanyName' => $user->surname ?? '',
                'emails' => [
                    $user->email
                ],
                'personal' => [
                    'foreName' => $user->name ?? ''
                ],
                'phones' => [
                    $user->phone
                ],
                'externalIntegrations' => [
                    [
                        'application' => self::APP_NAME,
                        'externalId' => $user->id
                    ]
                ] 
            ]);


            $user->ipratico_id = ($actor) ? $actor->id : null;
            $user->saveQuietly();
        }
        else
        {
            $this->api->updateBusinessActor($user->ipratico_id, [
                'cas' => $actor['cas'],
                'surnameOrCompanyName' => $user->surname ?? '',
                'emails' => [
                    $user->email
                ],
                'personal' => [
                    'foreName' => $user->name ?? ''
                ],
                'phones' => [
                    $user->phone
                ],
                'externalIntegrations' => [
                    [
                        'application' => 'WeAreEmma',
                        'externalId' => $user->id
                    ]
                ] 
            ]);
        }
    }

    /**
     * Create or update order
     *
     * @param Booking $booking
     * @param bool $command
     * @return void
     * @throws GuzzleException
     */
    public function createOrUpdateOrder(Booking $booking, $command = false)
    {
        // Create or update business actor
        $this->createOrUpdateBusinessActor($booking->customer);

        // Get business actor
        $actor = $this->getBusinessActor($booking->customer->refresh());

        // Get order
        $order = ($command)
            ? null
            : $this->getOrder($booking);

        // Get price list
//        $price_list = $this->getPriceList();

        if ($actor)
        {
            if ($order && isset($order['id']))
            {
                $this->api->cancelOrder($order['id']);
            }

            // Create
            $data = [
                'businessActorId' => $actor['id'],
                'toGoDetails' => [
                    'desiredDate' => $booking->start_date->toISOString(),
                    'name' => $actor['surname'],
                    'email' => $actor['email'],
                    'phone' => $actor['phone']
                ],
                'orderItems' => []
            ];

            // Get all order services
            foreach ($booking->slots as $slot)
            {
                $service = HairService::find($slot['service']['id']);

                if ($service)
                {
                    $price = $service->net_price;

                    if (
                        $booking->is_father &&
                        $booking->customer->hasAnySubscription() &&
                        $service->isPrimary() &&
                        ! in_array($service->title, config('app.primaries_not_included'))
                    )
                    {
                        $price = 0;
                    }

                    $data['orderItems'][] = [
                        'itemType' => 'product',
                        'saleItem' => [
                            'productId' => $service->ipratico_id,
                            'vatRecordCategoryId' => $service->ipratico_vat_id
                        ],
                        'quantity' => 1,
                        'computedUnitaryPrice' => round($price,2)
                    ];
                }
            }

            // Discount
            if ($booking->additional_data && isset($booking->additional_data['discount']))
            {
                $data['orderItems'][] = [
                    'itemType' => 'subtotal',
                    'computedUnitaryPrice' => $booking->total_net_price_original,
                    'finalUnitaryPriceVariation' => [
                        'isPercentage' => $booking->additional_data['discount']['typology'] !== 'fixed',
                        'variation' => ($booking->additional_data['discount']['typology'] == 'fixed')
                            ? $booking->additional_data['discount']['value'] * -1
                            : (($booking->additional_data['discount']['typology'] == 'free')
                                ? 0
                                : $booking->additional_data['discount']['percentage'] * -1
                            )
                    ]
                ];
            }

            // Source
            if ($booking->is_father)
            {
                $source = (is_null($booking->paid_at))
                    ? self::SOURCE_EAT
                    : self::SOURCE_WEBAPP;
            }
            else
            {
                $source = self::SOURCE_WEBAPP;
            }


            $order = $this->api->createOrder($data, $source);

            $booking->ipratico_id = ($order) ? $order->id : null;
            $booking->saveQuietly();
        }
    }

    /**
     * Sync hair services with ipratico products
     *
     * @return void
     * @throws GuzzleException
     */
    public function syncProducts()
    {
        try
        {
            DB::beginTransaction();

            // Fetch products from iPratico
            $products = $this->fetchProducts();
            $products_ids = $products->pluck('ipratico_id')->toArray();

            // Delete services not present in products
            HairService::query()
                ->whereNotIn('ipratico_id', $products_ids)
                ->delete();

            // Fetch services
            $services = HairService::query()
                ->get();

            // Update or create service form products array
            foreach ($products as $product)
            {
                // Validate product
                if (
                    is_null($product['ipratico_vat_id']) ||
                    is_null($product['title']) ||
                    is_null($product['level']) ||
                    is_null($product['ipratico_id'])
                )
                {
                    continue;
                }

                $service = $services
                    ->firstWhere('ipratico_id', $product['ipratico_id']);

                if ($service)
                {
                    // Update
                    $service->fill(Arr::only($product,[
                        'title',
                        'level',
                        'net_price',
                        'ipratico_vat_id',
                        'order'
                    ]));
                }
                else
                {
                    // Create
                    $service = new HairService();
                    $service->fill($product);
                }

                // Set dry style
                if ($product['title'] == 'Dry Style')
                {
                    $service->dry_style = true;
                }

                $service->save();
            }

            DB::commit();
        }
        catch (\Exception $ex)
        {
            DB::rollBack();
            Log::error('iPratico service -> sync products : ' . $ex->getMessage() . ' Line ('.$ex->getLine().')');
        }
    }

    /**
     * Fetch ipratico products
     *
     * @return \Illuminate\Support\Collection
     * @throws GuzzleException
     */
    public function fetchProducts()
    {
        $cats = $this->api->getCategories();

        $cats = collect($cats)->filter(function ($c) {
            return in_array($c->value->name, config('services.ipratico.categories', []));
        });

        $products = $this->api->getProducts();

        $products = collect($products)->filter(function ($p) use ($cats) {
            return in_array(
                $p->value->productCategoryId ?? null,
                collect($cats)->pluck('id')->toArray()
            );
        });


        return collect($products)->map(function ($p) use ($cats) {

            $cat = collect($cats)
                ->firstWhere('id', $p->value->productCategoryId ?? '');

            $level = (array_key_exists($cat->value->name, config('services.ipratico.levels', [])))
                ? config('services.ipratico.levels')[$cat->value->name]
                : null;

            return [
                'ipratico_id' => $p->id,
                'ipratico_vat_id' => $cat->value->fiscalDefault[0]->vatRecordCategoryId,
                'title' => $p->value?->name ?? '',
                'description' => $p->value?->description ?? '',
                'type' => null,
                'net_price' => (isset($p->value?->pricesArray[0])) ? $p->value?->pricesArray[0]?->elements[0]?->price : 0,
                'level' => $level,
                'active' => false,
                'dry_style' => false,
                'afro' => false,
                'execution_time' => 0,
                'order' => $p->value?->sorting ?? 0
            ];
        });
    }

    /**
     * Create subscription order
     *
     * @param User $user
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createSubscriptionOrder(User $user)
    {
        try
        {
            $price = $user->getFirstPriceForStripe();

            if (is_null($price))
            {
                throw new \Exception("No price found");
            }

            $cats = $this->api->getCategories();

            $cat = collect($cats)->firstWhere(function ($c) {
                return $c->value->name == config('services.ipratico.sub_category', null);
            });

            if (is_null($cat))
            {
                throw new \Exception("No category found");
            }

            $products = $this->api->getProducts();

            $products = collect($products)->filter(function ($p) use ($cat) {
                return ($p->value->productCategoryId ?? null) == $cat->id;
            });

            $product = $products
                ->firstWhere('value.name', $price->plan->name);

            if ($product)
            {
                // Create or update business actor
                $this->createOrUpdateBusinessActor($user);

                // Get business actor
                $actor = $this->getBusinessActor($user->refresh());

                if ($actor)
                {
                    // Create
                    $data = [
                        'businessActorId' => $actor['id'],
                        'toGoDetails' => [
                            'desiredDate' => now()->toISOString(),
                            'name' => $actor['surname'],
                            'email' => $actor['email'],
                            'phone' => $actor['phone']
                        ],
                        'orderItems' => [],
                        'externalIntegration' => [
                            'id' => $actor['id']
                        ]
                    ];

                    $vatId = collect($product->value->fiscality)
                        ->firstWhere('saleTypeId', 'sale_type:take-away')
                        ->vatRecordCategoryId;

                    $data['orderItems'][] = [
                        'itemType' => 'product',
                        'saleItem' => [
                            'productId' => $product->id,
                            'vatRecordCategoryId' => $vatId
                        ],
                        'quantity' => 1,
                        'computedUnitaryPrice' => round($price->price)
                    ];

                    return $this->api->createOrder($data);
                }
                else
                {
                    throw new \Exception("no actor found");
                }
            }
            else
            {
                throw new \Exception("No product found");
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Ipratico create subscription order: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Cancel order
     *
     * @param Booking $booking
     * @return void
     * @throws GuzzleException
     */
    public function cancelOrder(Booking $booking)
    {
        try
        {
            if ($booking && $booking->ipratico_id)
            {
                $this->api->cancelOrder($booking->ipratico_id);
            }
        }
        catch (\Exception $ex)
        {
            Log::error($ex->getMessage());
        }
    }
}
