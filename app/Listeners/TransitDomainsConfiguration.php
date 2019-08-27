<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Event;
use App\Events\TransitCreated;
use App\Events\TransitEdited;
use App\Services\TransitDomainsManager;

class TransitDomainsConfiguration
{
    private $domains_manager;

    public function __construct(TransitDomainsManager $domains_manager)
    {
        $this->domains_manager = $domains_manager;
    }

    /**
     * @param TransitCreated|TransitEdited|Event $event
     */
    public function handle(Event $event)
    {
        if ($event->transit->wasRecentlyCreated) {
            $this->domains_manager->create($event->transit, $event->realpath);
        } else {
            $this->domains_manager->edit($event->transit, $event->original['subdomain'], $event->realpath);
        }
    }

    /**
     * @param TransitCreated|TransitEdited|Event $event
     * @return bool
     */
    private function subdomainChanged(Event $event): bool
    {
        return $event->transit->getAttribute('subdomain') !== $event->original['subdomain'];
    }
}
