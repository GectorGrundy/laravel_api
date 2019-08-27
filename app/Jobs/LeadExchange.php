<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\TargetGeoRuleLeadStatService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Factories\CcLeadIntegrationFactory;
use App\Models\{
    Lead, Integration, TargetGeo, TargetGeoRule
};
use App\Exceptions\{
    TargetGeoRule\NotFoundTargetGeoRule
};

class LeadExchange implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use DispatchesJobs;

    private $lead;
    /**
     * @var int
     */
    private $lead_id;
    /**
     * @var int
     */
    private $target_geo_id;
    /**
     * @var int|null
     */
    private $target_geo_rule_id;

    public function __construct(int $lead_id, int $target_geo_id, int $target_geo_rule_id = 0)
    {
        $this->lead_id = $lead_id;
        $this->target_geo_id = $target_geo_id;
        $this->target_geo_rule_id = $target_geo_rule_id;
    }

    public function handle(TargetGeoRuleLeadStatService $rule_lead_stat_service)
    {
        $this->lead = Lead::find($this->lead_id);

        if ($this->lead->cannotIntegrate() || $this->lead->target->hasExternalLandings()) {
            return true;
        }

        if ($this->target_geo_rule_id) {
            $rule = TargetGeoRule::find($this->target_geo_rule_id);

        } else {
            $leads = $this->getSimilarLeads();

            if ($leads->count()) {
                $rule = TargetGeoRule::find($leads->first()->target_geo_rule_id);

                return $this->integrateLead($rule);
            }

            $target_geo_rules = $this->getIntegrationSuitableRules();

            $target_geo_rules = (new TargetGeoRule())->rejectRulesByLimit($target_geo_rules);

            if ($target_geo_rules->count() < 1) {
                throw new NotFoundTargetGeoRule($this->target_geo_id);
            }

            switch ($target_geo_rules->first()->target_geo->target_geo_rule_sort_type) {
                case TargetGeo::RULE_WEIGHT_SORT:
                    $rule = $this->getRuleByWeight($target_geo_rules);
                    break;

                case TargetGeo::RULE_PRIOITY_SORT:
                    $rule = $this->getRuleByPrioriry($target_geo_rules);
                    break;

                default:
                    throw new \LogicException('Unknown type of target geo limit type.');
            }
        }

        $this->integrateLead($rule);
        $rule_lead_stat_service->insert((int)$rule['id']);
    }

    /**
     * Получение лидов, которые возможно дубли с текущим обрабатываемым
     *
     * @return Collection
     */
    private function getSimilarLeads(): Collection
    {
        $day_ago = Carbon::now()->subDay()->toDateTimeString();
        $today = Carbon::now()->toDateTimeString();

        $lead_info = $this->lead;

        return Lead::where('offer_id', $this->lead['offer_id'])
            ->whereHas('order', function (Builder $query) use ($lead_info) {
                return $query->where('phone', $lead_info->order['phone']);
            })
            ->createdBetweenDates($day_ago, $today)
            ->where('id', '!=', $lead_info['id'])
            ->where('target_geo_rule_id', '!=', 0)
            ->get();
    }

    /**
     * Получение гео целей, которые возможны для интеграции лида
     *
     * @return Collection
     */
    private function getIntegrationSuitableRules(): Collection
    {
        return TargetGeoRule::whereHas('integration', function (Builder $builder) {
            return $builder->available();
        })
            ->whereTargetGeoId($this->target_geo_id)
            ->get();
    }

    /**
     * Получение правила гео цели, у которого самый большой приоритет среди остальный доступных
     *
     * @param Collection $target_geo_rules
     * @return TargetGeoRule
     */
    private function getRuleByPrioriry(Collection $target_geo_rules): TargetGeoRule
    {
        return $target_geo_rules->sortByDesc('priority')->first();
    }

    /**
     * Получение правила гео цели,
     * которое не получило достаточного кол-ва лидов в процентном соотношении от общего кол-ва лидов
     *
     * @param Collection $target_geo_rules
     * @return TargetGeoRule
     */
    private function getRuleByWeight(Collection $target_geo_rules): TargetGeoRule
    {
        $total_leads = $target_geo_rules->sum('today_leads_count');
        $total_weight = $target_geo_rules->sum('weight');

        $available_rules = collect();
        foreach ($target_geo_rules as $rule) {

            if ($rule->weight === 0) {
                continue;
            }

            $available_leads_percentage = 100;
            if ($total_weight > 0) {
                $available_leads_percentage = (int)($rule->weight / $total_weight * 100);
            }
            $done_leads_percentage = 0;
            if ($total_leads > 0) {
                $done_leads_percentage = (int)($rule->today_leads_count / $total_leads * 100);
            }

            if ($available_leads_percentage >= $done_leads_percentage) {
                $available_rules->push($rule->id);
            }
        }

        if ($available_rules->count() < 1) {
            throw new NotFoundTargetGeoRule($this->target_geo_id);
        }

        $rule_id = $available_rules->shuffle()->first();

        return $target_geo_rules->where('id', $rule_id)->first();
    }

    private function integrateLead(TargetGeoRule $target_geo_rule)
    {
        $integration = (new Integration())->getById($target_geo_rule['integration_id']);

        /**
         * @var Lead $lead
         */
        $lead = Lead::find($this->lead_id);
        $lead->integrateCod($integration, $target_geo_rule);

        // Генерируем job для интеграции лида
        $job = CcLeadIntegrationFactory::getInstance($integration['title'], $this->lead_id, $integration['id']);
        $job->onQueue(config('queue.app.integration'));
        $this->dispatch($job);
    }
}
