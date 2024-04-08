<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class ActiveCampaignService
{

    const TAG_NEWS = 'newsletter';

    /**
     * Build client
     *
     * @return Client
     */
    private static function buildClient()
    {
        return new Client([
            'base_uri' => config('services.active_campaign.base_url'),
            'headers' => [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
                'Api-Token' => config('services.active_campaign.api_token')
            ]
        ]);
    }

    /**
     * Get response
     *
     * @param ResponseInterface $response
     * @return mixed
     */
    private static function getResult(ResponseInterface $response)
    {
        return ($response->getStatusCode() == 200)
            ? json_decode($response->getBody()->getContents())
            : null;
    }

    /**
     * Perform request
     *
     * @param $method
     * @param $uri
     * @param null $body
     * @return mixed|null
     */
    private static function request($method, $uri, $body = null)
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
            return self::getResult($response);
        }
        catch (GuzzleException $ex)
        {
            Log::error('Active Campaign API error: ' . $ex->getMessage());
            return null;
        }
        catch (\Exception $ex)
        {
            Log::error('Active Campaign API error: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Sync contact
     *
     * @param User $user
     * @return mixed|null
     */
    private static function syncContact(User $user)
    {
        $data = [
            'contact' => [
                'email' => $user->email,
                'firstName' => $user->name,
                'lastName' => $user->surname
            ]
        ];

        $res = self::request(
            'post',
            '/api/3/contact/sync',
            $data
        );

        if ($res)
        {
            return intval($res->contact->id);
        }

        return null;
    }

    /**
     * List tags
     *
     * @return mixed|null
     */
    private static function listTags()
    {
        $res = self::request(
            'get',
            '/api/3/tags&limit=999'
        );

        $tags = [];

        if ($res)
        {
            foreach ($res->tags as $tag)
            {
                $tags[$tag->id] = $tag->tag;
            }
        }

        return $tags;
    }

    /**
     * Sync tag
     *
     * @param $name
     * @return mixed
     */
    private static function syncTag($name)
    {
        // Check if tag already exists
        $tags = self::listTags();

        foreach ($tags as $id => $tag)
        {
            if ($tag == $name)
            {
                return $id;
            }
        }

        // Create tag
        $data = [
            'tag' => [
                'tag' => $name,
                'tagType' => 'contact',
            ]
        ];
        self::request(
            'post',
            '/api/3/tags',
            $data
        );

        $tags = self::listTags();

        foreach ($tags as $id => $tag)
        {
            if ($tag == $name)
            {
                return $id;
            }
        }

        return null;
    }

    /**
     * Add tag to contact
     *
     * @param User $user
     * @param $tag
     * @return void
     */
    private static function addTagToContact(User $user, $tag)
    {
        $contact_id = self::syncContact($user);
        $tag_id = self::syncTag($tag);

        if ($contact_id && $tag_id)
        {
            $data = [
                'contactTag' => [
                    'contact' => $contact_id,
                    'tag' => $tag_id
                ]
            ];

            self::request(
                'post',
                '/api/3/contactTags',
                $data
            );
        }
        else
        {
            Log::error('Active Campaign API error: Enabled to assign tag to contact');
        }
    }

    /**
     * Detach tag from contact
     *
     * @param User $user
     * @param $tag
     * @return mixed|null
     */
    private static function detachTagFromContact(User $user, $tag)
    {
        $contact_id = self::syncContact($user);
        $tag_id = self::syncTag($tag);

        $res = self::request(
            'get',
            '/api/3/contacts/'. $contact_id .'/contactTags',
        );

        foreach ($res->contactTags as $contactTag)
        {
            if ($contactTag->tag == $tag_id)
            {
                self::request(
                    'delete',
                    '/api/3/contactTags/'. $contactTag->id,
                );
            }
        }

        return $res;
    }

    /**
     * Add newsletter tag
     *
     * @param User $user
     * @return void
     */
    public static function addNewsletterTag(User $user)
    {
        if ($user)
        {
            self::addTagToContact($user, self::TAG_NEWS);
        }
    }
}