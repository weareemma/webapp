<?php

namespace App\Api;

use GuzzleHttp\Client;

class WhatsappApi
{
    /**
     * Whatsapp API base url
     *
     */
    const BASE_URL = "https://graph.facebook.com/v15.0/";

    /**
     * Get api token
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private static function getToken()
    {
        return config('services.whatsapp.token');
    }

    /**
     * Build api client
     *
     * @return Client
     */
    private static function buildClient()
    {
        return new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                "Cotent-Type" => "application/json",
                "Accept" => "application/json",
                'Authorization' => 'Bearer ' . self::getToken(),
            ]
        ]);
    }

    /**
     * Build api payload
     *
     * @param null $receiver_phone_number
     * @param string $header
     * @param string $body
     * @return array|null
     */
    private static function buildPayload($receiver_phone_number = null, $header = '', $body = '')
    {
        if ($receiver_phone_number) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => self::buildPhoneNumber($receiver_phone_number),
                'type' => 'template',
                'template' => [
                    'name' => 'weareemma_main_template',
                    'language' => [
                        'code' => 'IT'
                    ],
                    'components' => [
                        [
                            'type' => 'header',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $header
                                ]
                            ]
                        ],
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $body
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            return $payload;
        }

        return null;
    }

    /**
     * Decode api result
     *
     * @param $response
     * @return mixed
     */
    private static function getResult($response)
    {
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Add phone prefix
     *
     * @param $phone
     * @return string
     */
    private static function buildPhoneNumber($phone)
    {
        return '39' . $phone;
    }

    /**
     * Send message
     *
     * @param $to
     * @param $header
     * @param $body
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendMessage($to, $header, $body)
    {
        $client = self::buildClient();

        $response = $client->post('106251709157040' . '/messages', [
            'json' => self::buildPayload(
                $to,
                $header,
                $body
            )
        ]);

        $res = self::getResult($response);

        return $res;
    }
}
