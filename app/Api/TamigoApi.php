<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Carbon;

class TamigoApi
{
    /**
     * Tamigo API base url
     *
     */
    const BASE_URL = "https://api.tamigo.com/";

    /**
     * Session token header label
     *
     */
    const SESSION_TOKEN_HEADER = "x-tamigo-token";

    /**
     * All stores symbol for api query
     *
     */
    const ALL_STORES_SYMBOL = 'all';

    /**
     * Get session token for subsequent calls
     *
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getSessionToken()
    {
        $client = self::buildClient();

        $response = $client->post('login/application', [
            "json" => [
                "Name" => config('app.tamigo_user'),
                "Key" => config('app.tamigo_key')
            ]
        ]);

        $res = self::getResult($response);

        return array_key_exists("SessionToken", $res) ? $res['SessionToken'] : null;
    }

    /**
     * Build guzzle client
     *
     * @return Client
     */
    private static function buildClient()
    {
        return new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                "Cotent-Type" => "application/json",
                "Accept" => "*/*"
            ]
        ]);
    }

    /**
     * Get result data
     *
     * @param $response
     * @return mixed
     */
    private static function getResult($response)
    {
        $xml = simplexml_load_string($response->getBody()->getContents());
        return json_decode(json_encode($xml), true);
    }

    /**
     * Get planned shifts for store
     *
     * @param $store_id
     * @param null $date_from
     * @param null $date_to
     * @param null $session_token
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getPlannedShifts($store_id, $date_from = null, $date_to = null, $session_token = null)
    {
        $client = self::buildClient();

        if (is_null($session_token)) $session_token = self::getSessionToken();

        if (is_null($date_from)) $date_from = Carbon::now();
        if (is_null($date_to)) $date_to = $date_from->copy()->addDay();

        $url = "shifts/all/". $date_from->format('Y-m-d') ."/". $date_to->format('Y-m-d') ."/";

        $response = $client->get($url, [
            'query' => [
                'departmentId' => $store_id,
                'includeDraft' => true
            ],
            'headers' => [
                self::SESSION_TOKEN_HEADER => $session_token
            ]
        ]);

        $res = self::getResult($response);

        return (count($res) > 0) ? $res['EmployeeShift'] : null;
    }
}