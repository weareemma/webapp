<?php

namespace App\Services;

use App\Api\WhatsappApi;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send message
     *
     * @param $to
     * @param $header
     * @param $body
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendMessage($to = null, $header = '', $body = '')
    {
        try
        {
            WhatsappApi::sendMessage($to, $header, $body);
        }
        catch (\Exception $ex)
        {
            Log::error('Whatsapp notifications: ' . $ex->getMessage());
        }
    }
}