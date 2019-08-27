<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\DomainCreated;
use App\Events\DomainEdited;
use App\Events\Event;
use App\Models\Domain;
use GuzzleHttp\Client;

class DomainDetectCharset
{
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Domain
     */
    private $domain;

    public function __construct(Client $client)
    {
        $this->http = $client;
    }

    /**
     * @param DomainCreated | DomainEdited $event
     * @return bool|null
     */
    public function handle(Event $event)
    {
        $this->domain = $event->domain;


        if (!$this->domain->isCloaked()) {
            return null;
        }

        $response = $this->http->get($this->domain->donor_url, [
            'verify' => false
        ]);

        if ($charset = $this->getCharsetFromContentType($response->getHeaders())) {
            return $this->updateDomainCharset($charset);
        }

        if ($charset = $this->getCharsetFromHtml((string)$response->getBody())) {
            return $this->updateDomainCharset($charset);
        }

        return $this->updateDomainCharset(Domain::DEFAULT_DONOR_CHARSET);

    }

    private function getCharsetFromContentType(array $headers): string
    {
        $content_type = $headers['Content-Type'][0] ?? '';

        preg_match('~charset=(.*?)~i', $content_type, $matches);

        return $matches[1] ?? '';
    }

    private function getCharsetFromHtml(string $html): string
    {
        preg_match('~charset=(.*?)["|\']~i', $html, $matches);

        return $matches[1] ?? '';
    }

    private function updateDomainCharset(string $charset): bool
    {
        return $this->domain->update(['donor_charset' => $charset]);
    }
}
