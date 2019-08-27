<?php
declare(strict_types=1);

namespace Tests\Traits;

class RefreshDatabaseState
{
    /**
     * Indicates if the test database has been migrated.
     *
     * @var bool
     */
    public static $migrated = false;
}