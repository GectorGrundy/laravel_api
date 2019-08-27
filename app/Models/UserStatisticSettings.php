<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatisticSettings extends AbstractEntity
{
	public $timestamps = false;

	public $fillable = ['user_id', 'data'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['user_id'];
}
