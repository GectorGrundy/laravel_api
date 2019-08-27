<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Auth;

class Postback extends AbstractEntity
{
	use EloquentHashids;
	use DynamicHiddenVisibleTrait;

	protected $fillable = ['hash', 'publisher_id', 'flow_id', 'url', 'on_lead_add', 'on_lead_approve', 'on_lead_cancel'];
	protected $hidden = ['id', 'publisher_id', 'flow_id'];
    public $timestamps = false;

	public static function getHashidEncodingValue(Model $model)
	{
		return [$model->id, $model->publisher_id, $model->flow_id];
	}

	public function flow()
	{
		return $this->belongsTo(Flow::class);
	}

    /**
     * Получение списка постбеков потока для заданного ивента
     *
     * @param $flow_id
     * @param $publisher_id
     * @param $event
     * @return mixed
     */
    public static function getListByEvent($flow_id, $publisher_id, $event)
    {
        $flow_postbacks = self::where('flow_id', $flow_id)->where($event, 1)->get();

        // If not exists postbacks for flow - try to find global postbacks of publisher for this event
        $global_postbacks = self::where('publisher_id', $publisher_id)
            ->where('flow_id', 0)
            ->where($event, 1)
            ->get();

        return $flow_postbacks->merge($global_postbacks);
    }
}
