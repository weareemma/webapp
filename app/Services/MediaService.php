<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MediaService
{
    private const USER_TEMP_COLLECTION_BASE = 'tmp-';

    /**
     * Upload file into user temp collection
     *
     * @param Request $request
     * @return void
     */
    public static function uploadTempFile(Request $request)
    {
        $user = (Auth::check()) ? Auth::user() : null;
        if ($user)
        {
            $user->clearMediaCollection(self::buildTempCollectionName($request));

            foreach (self::extractFilesFromRequest($request) as $file)
            {
                $user
                    ->addMedia($file->getPathName())
                    ->withCustomProperties(['uid' => self::extractFileUid($request)])
                    ->toMediaCollection(self::buildTempCollectionName($request));
            }
        }
    }

    /**
     * Build user temp collection name
     *
     * @param Request $request
     * @return string
     */
    private static function buildTempCollectionName(Request $request)
    {
        $model = explode(':', $request->get('mediaCollection'))[0];
        return self::USER_TEMP_COLLECTION_BASE . $model;
    }

    /**
     * Extract files from request
     *
     * @param Request $request
     * @return \Illuminate\Http\UploadedFile|mixed
     */
    private static function extractFilesFromRequest(Request $request)
    {
        return $request->file()['files'];
    }

    private static function extractFileUid(Request $request)
    {
        return $request->get('uid');
    }
}