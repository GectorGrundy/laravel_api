<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Hashids;

trait EloquentVisitorHash
{
	/**
	 * Boot Eloquent ClickId trait for the model.
	 *
	 * @return void
	 */
	public static function bootEloquentVisitorHash()
	{
		static::created(function (Model $model) {
			$model->session_id = sprintf(
			    '%s',
                Hashids::connection('visitor')->encode([$model->id, config('env.visitor_database_id')])
            );
			$model->save();
		});
	}
}