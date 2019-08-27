<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Landing;
use Illuminate\Queue\SerializesModels;

class LandingEdited extends Event
{
    use SerializesModels;

    public $landing;
    public $original;
    public $realpath;
    public $url;

    public function __construct(Landing $landing, array $original, ?string $realpath = null, ?string $url = null)
    {
        $this->landing = $landing;
        $this->original = $original;
        $this->realpath = $realpath;
        $this->url = $url;
    }
}
