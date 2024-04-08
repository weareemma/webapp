<?php

namespace App\Observers;

use App\Models\HairService;
use App\Services\IpraticoService;
use Illuminate\Support\Facades\Log;

class HairServiceObserver
{
    /**
     * Handle the HairService "created" event.
     *
     * @param  \App\Models\HairService  $hairService
     * @return void
     */
    public function created(HairService $hairService)
    {
        // Flow inverted, hair services are managed by iPratico
//        try
//        {
//            (new IpraticoService())->createOrUpdateProduct($hairService);
//        }
//        catch (\Exception $ex)
//        {
//            Log::error('Hair service Created: ' . $ex->getMessage());
//        }
    }

    /**
     * Handle the HairService "updated" event.
     *
     * @param  \App\Models\HairService  $hairService
     * @return void
     */
    public function updated(HairService $hairService)
    {
        // Flow inverted, hair services are managed by iPratico
//        if (
//            $hairService->ipratico_id &&
//            $hairService->wasChanged([
//                'title',
//                'net_price',
//                'level'
//            ])
//        )
//        {
//            try
//            {
//                (new IpraticoService())->createOrUpdateProduct($hairService);
//            }
//            catch (\Exception $ex)
//            {
//                Log::error('Hair service Updated: ' . $ex->getMessage());
//            }
//        }
    }

    /**
     * Handle the HairService "deleted" event.
     *
     * @param  \App\Models\HairService  $hairService
     * @return void
     */
    public function deleted(HairService $hairService)
    {
        //
    }

    /**
     * Handle the HairService "restored" event.
     *
     * @param  \App\Models\HairService  $hairService
     * @return void
     */
    public function restored(HairService $hairService)
    {
        //
    }

    /**
     * Handle the HairService "force deleted" event.
     *
     * @param  \App\Models\HairService  $hairService
     * @return void
     */
    public function forceDeleted(HairService $hairService)
    {
        //
    }
}
