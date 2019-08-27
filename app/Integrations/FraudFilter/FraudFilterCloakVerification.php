<?php
declare(strict_types=1);

namespace App\Integrations\FraudFilter;

class FraudFilterCloakVerification
{
    private $api_key;
    private $campaign_id;
    private $customer_email;

    public function __construct(string $api_key, string $campaign_id, string $customer_email)
    {
        $this->api_key = $api_key;
        $this->campaign_id = $campaign_id;
        $this->customer_email = $customer_email;
    }

    public function handle(): bool
    {
        return (new FraudFilterDetector($this->api_key, $this->campaign_id, $this->customer_email))
            ->isSafePage();
    }
}
