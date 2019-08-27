<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Domain;

class DomainEdited extends Event
{
    /**
     * @var Domain
     */
    public $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }
}
