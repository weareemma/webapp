<?php

namespace App\Api;

use App\Services\IpraticoService;
use App\Services\LogService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Psr\Http\Message\ResponseInterface;

class IpraticoApi
{
    /**
     * Api key
     *
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $api_key;

    /**
     * Base url
     *
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $base_url;

    /**
     * Client Guzzle
     *
     * @var Client
     */
    private $client;

    /**
     * Construct
     *
     * @param $api_key
     * @param $base_url
     */
    public function __construct($api_key = null, $base_url = null)
    {
        $this->api_key = $api_key ?? config('services.ipratico.api_key');
        $this->base_url = $base_url ?? config('services.ipratico.api_base_url');

        $this->client = new Client([
            'verify' => false,
            'base_uri' => $this->base_url,
            'headers' => [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                'x-api-key' => $this->api_key
            ]
        ]);
    }

    /**
     * Get response
     *
     * @param Response $response
     * @param null $event
     * @param null $subject
     * @return mixed
     */
    private function getResult(ResponseInterface $response, $event = null, $subject = null)
    {
        $body = $response->getBody()->getContents();

        LogService::logIpraticoApi(
            $event,
            $response->getStatusCode(),
            json_decode($body),
            $subject
        );

        return ($response->getStatusCode() == 200)
            ? json_decode($body)
            : null;
    }

    /**
     * Perform request
     *
     * @param $method
     * @param $uri
     * @param null $body
     * @return mixed|null
     * @throws GuzzleException
     */
    public function request($method, $uri, $event = null, $body = null)
    {
        try
        {
            $payload = [];

            if ($body)
            {
                $payload = [
                    'body' => json_encode($body)
                ];
            }

            $response = $this->client->request($method, $uri, $payload);

            $uri_exploded = explode('/', $uri ?? '');
            $count = count($uri_exploded);
            $subject = null;

            if ($count > 1)
            {
                $subject = ($event != 'cancel order')
                    ? $uri_exploded[$count - 1]
                    : $uri_exploded[$count - 2];
            }

            return $this->getResult(
                $response,
                $event,
                $subject
            );
        }
        catch (\Exception $ex)
        {
            Log::error($this->api_key . " " .'iPratico error: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Validate data
     *
     * @param $valid
     * @param $data
     * @return array
     */
    private function validateData($valid = [], $data = [])
    {
        return array_merge($valid, $data);
    }

    /**
     * Create price list
     *
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createPriceList($data)
    {
        $valid = [
            'name' => ''
        ];

        return $this->request(
            'post',
            'price-lists',
            'create price',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Create vat record
     *
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createVatRecord($data)
    {
        $valid = [
            'name' => '',
            'rate' => 0,
            'shortName' => '',
            'departmentIndex' => 1
        ];

        return $this->request(
            'post',
            'vat-record-categories',
            'create vat',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Create category
     *
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createCategory($data)
    {
        $valid = [
            'name' => '',
            'familyId' => '',
            'fiscalDefault' => [
                [
                    'saleTypeId' => 'sale_type:take-away',
                    'vatRecordCategoryId' => ''
                ]
            ]
        ];

        return $this->request(
            'post',
            'product-categories',
            'create category',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Create product
     *
     * @param $data
     * @return mixed
     * @throws GuzzleException
     */
    public function createProduct($data)
    {
        $valid = [
            'name' => '',
            'productCategoryId' => '',
            'fiscality' => [
                'saleTypeId' => 'sale_type:take-away',
                'vatRecordCategoryId' => ''
            ],
            'externalIntegrations' => [
                'application' => '',
                'externalId' => ''
            ]
        ];

        return $this->request(
            'post',
            'products',
            'create product',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Update product
     *
     * @param $id
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function updateProduct($id, $data)
    {
        $valid = [
            'name' => '',
            'productCategoryId' => '',
            'fiscality' => [
                'saleTypeId' => 'sale_type:take-away',
                'vatRecordCategoryId' => ''
            ]
        ];

        return $this->request(
            'put',
            'products/' . $id,
            'update product',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Create order
     *
     * @param $data
     * @param null $type
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createOrder($data, $source = null)
    {
        $note = '';
        if ($source)
        {
            $note = ($source == IpraticoService::SOURCE_WEBAPP)
                ? 'Pagato online su WeAreEmma'
                : 'Da pagare in store';
        }

        $valid = [
            'status' => 'ACCEPTED',
            'businessActorId' => '',
            'note' => $note,
            'source' => [                
                'type' => 'takeaway',
                'app' => $source ?? 'generic'
            ],
            'toGoDetails' => [
                'desiredDate' => '',
                'name' => '',
                'email' => '',
                'phone' => ''
            ],
            'orderItems' => [
                [
                    'itemType' => 'product',
                    'saleItem' => [
                        'productId' => '',
                        'vatRecordCategoryId' => ''
                    ],
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->request(
            'post',
            'orders',
            'create order',
            $this->validateData($valid, $data)
        );

        return $response;
    }

    /**
     * Cancel order
     *
     * @param $id
     * @return mixed|null
     * @throws GuzzleException
     */
    public function cancelOrder($id)
    {
        $valid = [
            'status' => 'DECLINED'
        ];

        $response = $this->request(
            'post',
            'orders/' . $id . '/status',
            'cancel order',
            $this->validateData($valid)
        );

        return $response;
    }

    /**
     * Create business actor
     *
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function createBusinessActor($data)
    {
        $valid = [
            'surnameOrCompanyName' => '',
            'emails' => [
                ''
            ],
            'personal' => [
                'foreName' => ''
            ],
            'phones' => [
                ''
            ],
            'externalIntegrations' => [
                [
                    'application' => '',
                    'externalId' => ''
                ]
            ] 
        ];

        $response = $this->request(
            'post',
            'business-actors',
            'create business actor',
            $this->validateData($valid, $data)
        );

        return $response;
    }

    /**
     * Update business actor
     *
     * @param $id
     * @param $data
     * @return mixed|null
     * @throws GuzzleException
     */
    public function updateBusinessActor($id, $data)
    {
        $valid = [
            'surnameOrCompanyName' => '',
            'emails' => [
                ''
            ],
            'personal' => [
                'foreName' => ''
            ],
            'phones' => [
                ''
            ]
        ];

        return $this->request(
            'put',
            'business-actors/' . $id,
            'update business actor',
            $this->validateData($valid, $data)
        );
    }

    /**
     * Get product categories
     *
     * @param null $id
     * @return mixed
     * @throws GuzzleException
     */
    public function getCategories($id = null)
    {
        return ($id)
            ? $this->request('get', 'product-categories/' . $id, 'get category')
            : $this->request('get', 'product-categories', 'get categories');
    }

    /**
     * Get product families
     *
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getFamilies()
    {
        return $this->request('get', 'product-families', 'get families');
    }

    /**
     * Get all products
     *
     * @param null $id
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getProducts($id = null)
    {
        return ($id)
            ? $this->request('get', 'products/' . $id, 'get product')
            : $this->request('get', 'products', 'get products');
    }

    /**
     * Get all orders
     *
     * @param null $id
     * @return void
     * @throws GuzzleException
     */
    public function getOrders($id = null)
    {
        return ($id)
            ? $this->request('get', 'orders/' . $id, 'get order')
            : $this->request('get', 'orders', 'get orders');
    }

    /**
     * get vat categories
     *
     * @return void
     * @throws GuzzleException
     */
    public function getVatCategories()
    {
        return $this->request('get', 'vat-record-categories', 'get vats');
    }

    /**
     * Get price lists
     *
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getPriceLists()
    {
        return $this->request('get', 'price-lists', 'get prices');
    }

    /**
     * Get business actor by id
     *
     * @param $id
     * @return mixed|null
     * @throws GuzzleException
     */
    public function getBusinessActorById($id)
    {
        return $this->request('get', 'business-actors/' . $id, 'get business actor');
    }
}