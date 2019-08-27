<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class DataStat extends AbstractEntity
{
	protected $fillable = ['publisher_id', 'title', 'type', 'hits'];

    public $timestamps = false;
}
