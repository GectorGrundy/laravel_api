<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\{
    Lead, BalanceTransaction
};

class UnholdLead
{
    /**
     * @var Lead
     */
    private $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function handle()
    {
        $this->lead = $this->lead->fresh();

        \DB::transaction(function () {

            if ($this->lead->onHold()) {
                BalanceTransaction::insertPublisherUnhold($this->lead);

                $this->lead->unhold();
            }
        });
    }
}
