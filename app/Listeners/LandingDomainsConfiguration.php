<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\{
    Event, LandingCreated, LandingEdited
};
use App\Services\LandingDomainsManager;

class LandingDomainsConfiguration
{
    private $domains_manager;

    public function __construct(LandingDomainsManager $domains_manager)
    {
        $this->domains_manager = $domains_manager;
    }

    /**
     * @param LandingCreated|LandingEdited|Event $event
     */
    public function handle(Event $event)
    {
        if ($event->landing->wasRecentlyCreated) {
            $this->domains_manager->create($event->landing, $event->realpath, $event->url);
        } else {
            $this->domains_manager->edit($event->landing, $event->original['subdomain'], $event->realpath, $event->url);
        }
    }

    /**
     * @param LandingCreated|LandingEdited|Event $event
     * @return bool
     */
    private function subdomainChanged(Event $event): bool
    {
        return $event->landing->getAttribute('subdomain') !== $event->original['subdomain'];
    }

    /**
     * @param LandingCreated|LandingEdited|Event $event
     * @return bool
     */
    private function typeChanged(Event $event): bool
    {
        return $event->landing->getAttribute('type') !== $event->original['type'];
    }
}
