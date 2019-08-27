<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Domain;

class DomainCreated extends Event
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
