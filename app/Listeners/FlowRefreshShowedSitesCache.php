<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Classes\LandingHandler;
use App\Classes\TransitHandler;
use App\Events\Flow\FlowEdited;

class FlowRefreshShowedSitesCache
{
    /**
     * @param FlowEdited $event
     */
    public function handle(FlowEdited $event)
    {
        /**
         * @var LandingHandler $landing_handler
         */
        $landing_handler = app(LandingHandler::class);
        $landing_handler->setShowedLandingsInFlow($event->flow['id'], []);

        /**
         * @var TransitHandler $transit_handler
         */
        $transit_handler = app(TransitHandler::class);
        $transit_handler->setShowedTransitsInFlow($event->flow['id'], []);
    }
}
