<?php
declare(strict_types=1);

namespace App\Integrations;

use App\Exceptions\Integration\ToManyAttempts;

trait ReleaseJob
{
    private function releaseJob(): void
    {
        switch ($this->attempts()) {
            case 1:
                $this->release(config('env.first_integration_delay') * 60);
                break;

            case 2:
                $this->release(config('env.second_integration_delay') * 60);
                break;

            case 3:
                $this->release(config('env.third_integration_delay') * 60);
                break;

            default:
                throw new ToManyAttempts($this->lead_id);
        }
    }
}