<?php
declare(strict_types=1);

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHoldAtFieldToLeads extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('hold_at')->nullable()->after('hold_time');

            $table->index('hold_at');
        });

        Lead::approved()
            ->where('is_hold', 1)
            ->chunk(50, function (Collection $leads) {
                /**
                 * @var Lead $lead
                 */
                foreach ($leads as $lead) {
                    $hold_at = Carbon::createFromFormat('Y-m-d H:i:s', $lead['processed_at'])
                        ->addMinutes($lead['hold_time'])
                        ->toDateTimeString();

                    $lead->update(['hold_at' => $hold_at]);
                }
            });
    }
}
