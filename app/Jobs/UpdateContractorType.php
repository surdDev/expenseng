<?php

namespace App\Jobs;

use App\Company;
use App\ContractorVotes;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/***
 * This job will run nightly to update the contractor type 
 * field based on the public vote
 */
class UpdateContractorType implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * Fetch all contractors in chunks and work on them
         */
        Company::chunk(
            200, function ($contractors) {
                foreach ($contractors as $contractor) {
                    //fetch the highest value for the contractor on the votes table
                    $vote = DB::select(
                        "select max(count) as votes, type, contractor_id 
                        from contractor_votes where contractor_id = ? 
                        order by contractor_id",
                        $contractor->id
                    );

                    $contractor->type = $vote->type->id;
                    $contractor->save();
                }
            }
        )
    }
}
