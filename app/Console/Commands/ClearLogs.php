<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear logs until limit';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try
        {
            DB::beginTransaction();

            $days = config('app.logs_validity_days', 60);
            $limit = Carbon::now()->subDays($days);

            \App\Models\Log::query()
                ->whereDate('created_at', '<=', $limit)
                ->delete();

            DB::commit();
            return Command::SUCCESS;
        }
        catch (\Exception $ex)
        {
            DB::rollBack();
            Log::error('Clear logs command: ' . $ex->getMessage());
            return Command::FAILURE;
        }
    }
}
