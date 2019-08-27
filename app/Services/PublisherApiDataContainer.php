<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class PublisherApiDataContainer
{
    /**
     * @var User
     */
    private $publisher;

    public function getPublisher(): ?User
    {
        return $this->publisher;
    }

    public function setPublisher(User $publisher): void
    {
        $this->publisher = $publisher;
    }
}