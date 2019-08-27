<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;

class OfferLabel extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;

    protected $fillable = ['title', 'color'];
    protected $hidden = ['pivot', 'created_at', 'updated_at'];
}
