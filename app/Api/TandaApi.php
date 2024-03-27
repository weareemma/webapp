<?php

namespace App\Api;

use App\Services\LogService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class TandaApi
{
    /**
     * Get api token
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private static function getToken()
    {
        return config('services.tanda.token');
    }

    /**
     * Get api base url
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private static function getBaseUrl()
    {
        return config('services.tanda.api_base_url');
    }

    /**
     * Build api client
     *
     * @return Client
     */
    private static function buildClient()
    {
        return new Client([
            'base_uri' => self::getBaseUrl(),
            'headers' => [
                "Cotent-Type" => "application/json",
                "Accept" => "application/json",
                'Authorization' => 'Bearer ' . self::getToken(),
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
    private static function getResult(ResponseInterface $response, $event = null, $subject = null)
    {
        $body = $response->getBody()->getContents();

        if ($event)
        {
            LogService::logTandaApi(
                $event,
                $response->getStatusCode(),
                json_decode($body),
                $subject
            );
        }
        
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
    private static function request($method, $uri, $event = null, $body = null)
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

            $response = self::buildClient()->request($method, $uri, $payload);
            return self::getResult(
                $response,
                $event,
                null
            );
        }
        catch (\Exception $ex)
        {
            Log::error('Tanda error: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Get users
     *
     * @return mixed|null
     * @throws GuzzleException
     */
    public static function getUsers()
    {
        return self::request(
            'get',
            'users',
            'get users'
        );
    }

    /**
     * Get current roster
     *
     * @param null $date
     * @return mixed|null
     * @throws GuzzleException
     */
    public static function getRoster($date = null)
    {
        $url = ($date)
            ? 'rosters/on/' . $date
            : 'rosters/current';

        return self::request(
            'get',
            $url,
            'get roster'
        );
    }

    /**
     * Get locations (stores)
     *
     * @return mixed|null
     * @throws GuzzleException
     */
    public static function getLocations()
    {
        return self::request(
            'get',
            'locations',
            'get locations'
        );
    }

    /**
     * Get team (department)
     *
     * @param $id
     * @return mixed|null
     * @throws GuzzleException
     */
    public static function getTeam($id)
    {
        return self::request(
            'get',
            'departments/' . $id,
            'get team'
        );
    }
}