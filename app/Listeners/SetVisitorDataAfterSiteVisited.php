<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Go\SiteVisited;
use App\Services\VisitorService;

class SetVisitorDataAfterSiteVisited
{
    private $visitor_service;

    public function __construct(VisitorService $visitor_service)
    {
        $this->visitor_service = $visitor_service;
    }

    public function handle(SiteVisited $event)
    {
        $this->visitor_service->setDataAfterSiteVisited($event->data_container);
    }
}
