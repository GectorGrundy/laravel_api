<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Transit;
use Illuminate\Queue\SerializesModels;

class TransitCreated extends Event
{
    use SerializesModels;

    public $transit;
    public $original;
    public $realpath;

    public function __construct(Transit $transit, array $original, string $realpath)
    {
        $this->transit = $transit;
        $this->original = $original;
        $this->realpath = $realpath;
    }
}
