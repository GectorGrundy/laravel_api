<?php
declare(strict_types=1);

namespace App\Integrations\Keitaro;

class KeitaroCloakVerification
{
    private $api_key;
    private $alias;

    public function __construct(string $api_key, string $alias)
    {
        $this->api_key = $api_key;
        $this->alias = $alias;
    }

    public function handle(): bool
    {
        return (new KeitaroDetector($this->api_key, $this->alias))
            ->isSafePage();
    }
}
