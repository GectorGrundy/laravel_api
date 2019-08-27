<?php
declare(strict_types=1);

namespace App\Observers\Lead;

use App\Classes\DeviceInspector;
use App\Models\Lead;

/**
 * Определение браузера пользователя
 */
class DetectBrowser
{
    private $device_inspector;

    public function __construct(DeviceInspector $device_inspector)
    {
        $this->device_inspector = $device_inspector;
    }

    public function creating(Lead $lead)
    {
        if ($user_agent = $lead->getAttribute('user_agent')) {
            if ($this->device_inspector->isFacebookBrowser($user_agent)) {
                $lead->browser = Lead::FACEBOOK_BROWSER;
            } elseif ($this->device_inspector->isInstagramBrowser($user_agent)) {
                $lead->browser = Lead::INSTAGRAM_BROWSER;
            } else {
                $lead->browser = Lead::DEFAULT_BROWSER;
            }
        }
    }
}
