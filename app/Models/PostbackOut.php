<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Hashids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class PostbackOut extends AbstractEntity
{
    public const LEAD_ADD = 'lead_add';
    public const LEAD_APPROVE = 'lead_approve';
    public const LEAD_CANCEL = 'lead_cancel';

    use DispatchesJobs;
    use DynamicHiddenVisibleTrait;

    protected $table = 'postbackout_logs';
    protected $fillable = ['lead_id', 'postback_id', 'url', 'status', 'type', 'created_at'];
    protected $hidden = ['id', 'lead_id', 'postback_id', 'target_id', 'fallback_target_id'];

    /**
     * Фильтрация исходящих постбеков по хэшам потока
     *
     * @param $builder
     * @param $flows_hashes
     * @return mixed
     */
    public function scopeWhereFlow(Builder $builder, $flows_hashes)
    {
        if (\is_array($flows_hashes) && \count($flows_hashes) > 0) {

            $builder->whereIn('postbacks.flow_id', array_map(function ($hash) {

                // If hash == 0 - it's a global postback
                if ($hash === '0') {
                    return $hash;
                }
                return Hashids::decode($hash)[0];

            }, $flows_hashes));
        }
        return $builder;
    }

    /**
     * Фильтрация исходящих постбеков по hash лида
     *
     * @param $query
     * @param $lead_hash
     * @return mixed
     */
    public function scopeWhereLead($query, $lead_hash)
    {
        if ($lead_hash != '') {

            // @todo what?
            // If it can't decode lead hash - we need return emptiness
            if (!isset(Hashids::decode($lead_hash)[0])) {
                $lead_id = -1;
            } else {
                $lead_id = Hashids::decode($lead_hash)[0];
            }

            $query->where('postbackout_logs.lead_id', $lead_id);
        }

        return $query;
    }

    /**
     * Фильтрация исходящих постбеков по hash постбека
     *
     * @param $query
     * @param $postback_hash
     * @return mixed
     */
    public function scopeWherePostback($query, $postback_hash)
    {
        if ($postback_hash != '') {

            // If it can't decode postback hash - we need return emptiness
            if (!isset(Hashids::decode($postback_hash)[0])) {
                $postback_id = -1;
            } else {
                $postback_id = Hashids::decode($postback_hash)[0];
            }

            $query->where('postbackout_logs.postback_id', $postback_id);
        }

        return $query;
    }
}
