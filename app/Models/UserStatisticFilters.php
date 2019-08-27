<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class UserStatisticFilters extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;


    public $timestamps = false;

    public $fillable = ['user_id', 'title', 'filter'];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['user_id'];

}
