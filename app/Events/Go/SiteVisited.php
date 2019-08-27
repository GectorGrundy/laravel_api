<?php
declare(strict_types=1);

namespace App\Events\Go;

use App\Events\Event;
use App\Http\GoDataContainer;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;

class SiteVisited extends Event implements GoEvent
{
    use SerializesModels;

    public $data_container;
    public $date;
    public $hour;

    public function __construct(GoDataContainer $data_container, Carbon $date = null)
    {
        $this->data_container = $data_container;

        if (\is_null($date)) {
            $date = Carbon::now();
        }
        
        $this->date = $date->format('Y-m-d');
        $this->hour = $date->format('H');
    }
}
