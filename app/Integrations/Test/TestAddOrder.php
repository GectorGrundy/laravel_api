<?php
declare(strict_types=1);

namespace App\Integrations\Test;

use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class TestAddOrder implements ShouldQueue
{
    use Queueable;

    private $lead_id;
    /**
     * @var Lead
     */
    private $lead;

    public function __construct(int $lead_id, int $integration_id)
    {
        $this->lead_id = $lead_id;
    }

    public function handle(): void
    {
        $this->lead = Lead::findOrFail($this->lead_id);
        if ($this->lead->cannotIntegrate()) {
            return;
        }

        $external_key = $this->lead->generateExternalKeyById();
        $this->lead->setAsIntegrated($external_key);
    }
}
