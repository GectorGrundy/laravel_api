<?php
declare(strict_types=1);

namespace App\Models;

class DomainReplacement extends AbstractEntity
{
    protected $fillable = ['domain_id', 'from', 'to'];
    protected $hidden = ['id', 'created_at', 'updated_at', 'domain_id'];
}
