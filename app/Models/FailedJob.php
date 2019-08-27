<?php
declare(strict_types=1);

namespace App\Models;

class FailedJob extends AbstractEntity
{
    protected $fillable = ['payload'];
    public $timestamps = false;
}
