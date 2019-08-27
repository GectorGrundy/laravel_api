<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\User\UnknownUserRole;
use Auth;
use Carbon\Carbon;
use App\Models\{
    AdvertiserProfile, BalanceTransaction, SystemTransaction, User, Lead
};
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Lead as R;
use App\Jobs\LeadExchange;
use Illuminate\Support\Collection;
use App\Strategies\Statistic\AdminReportStrategy;
use App\Strategies\LeadCreation\ManualLeadCreation;
use App\Strategies\Statistic\AdminLeadsStrategy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\Integration\IncorrectLeadStatusException;
use App\Strategies\Statistic\AdvertiserLeadsStrategy;

class LeadController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /lead.getByHash lead.getByHash
     * @apiGroup Lead
     *
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {String} hash
     * @apiParam {String[]} [with[]]
     * Admin allowed values: <code>postbackin_logs,status_log.integration,status_log</code><br>
     * Publisher allowed values: <code>postbackin_logs,status_log,status_log</code>
     *
     * @apiSampleRequest /lead.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $with = $request->input('with', []);

        return ['response' => $request->input('lead')->load($with), 'status_code' => 200];
    }

    /**
     * @api {GET} /lead.getListOnHold lead.getListOnHold
     * @apiGroup Lead
     * @apiPermission publisher
     * @apiSampleRequest /lead.getListOnHold
     */
    public function getListOnHold()
    {
        $leads = Lead::with([
            'flow' => function ($query) {
                $query->select('id', 'title', 'hash');
                return $query;
            },
            'offer' => function ($query) {
                $query->select('offers.id', 'title', 'hash');
                return $query;
            },
            'currency' => function ($query) {
                $query->select('id', 'sign', 'code');
                return $query;
            }])
            ->where('publisher_id', Auth::user()->id)
            ->where('is_hold', 1)
            ->get();

        if ($leads === null) {
            $leads = [];
        }

        return ['response' => $leads, 'status_code' => 200];
    }

    public function integrate(R\IntegrateRequest $request)
    {
        $lead = (new Lead())->getById((int)$request->get('lead_id'));
        $lead->revert();

        $job = new LeadExchange(
            $lead['id'],
            $lead['target_geo_id'],
            (int)$request->input('target_geo_rule_id')
        );

        $job->onQueue(config('queue.app.integration'));
        $this->dispatch($job);

        return $this->response->accepted(null, [
            'message' => trans('leads.on_integrate_success'),
            'status_code' => 202
        ]);
    }

    public function create(R\CreateRequest $request)
    {
        $contacts = explode("\n", $request->input('contacts'));

        /**
         * @var ManualLeadCreation $creator
         */
        $creator = app(ManualLeadCreation::class);
        foreach ($contacts as $contact) {
            [$name, $phone] = explode(',', $contact);

            $creator->handle(
                (int)$request->input('flow_id'),
                (int)$request->input('target_geo_id'),
                $name,
                $phone
            );
        }

        return $this->response->accepted(null, [
            'message' => trans('leads.on_create_success'),
            'status_code' => 202
        ]);
    }

    public function bulkEdit(R\BulkEditRequest $request)
    {
        $action = $request->input('action');
        $ids = [];

        foreach ($request['hashes'] as $lead_hash) {
            try {
                /**
                 * @var Lead $lead
                 */
                $id = (new Lead())->getIdFromHash($lead_hash);
                $lead = Lead::findOrFail($id);
                $ids[] = $id;
                $sub_status_id = (int)$request->input('sub_status_id', 0);

                switch ($action) {
                    case 'approve':
                        $lead->approve();
                        break;

                    case 'cancel':
                        if ($lead['status'] === Lead::CANCELLED) {
                            $lead->changeSubstatus($sub_status_id);
                        } else {
                            $lead->cancel((int)$request->input('sub_status_id', 0));
                        }
                        break;

                    case 'trash':
                        $lead->trash((int)$request->input('sub_status_id', 0));
                        break;
                }

            } catch (ModelNotFoundException | IncorrectLeadStatusException $e) {
                continue;
            }
        }

        $user = Auth::user();
        switch ($user['role']) {
            case User::ADMINISTRATOR:
                $with = (new AdminLeadsStrategy())->getWithForLeads();
                break;

            case User::ADVERTISER:
                $with = (new AdvertiserLeadsStrategy())->getWith($request);
                break;
        }
        $leads = Lead::with($with)->whereIn('id', $ids)->get();

        return $this->response->accepted(null, [
            'message' => trans('messages.action_added_to_progress'),
            'response' => $leads,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /lead.buildReport lead.buildReport
     * @apiGroup Lead
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiParam {Number=1,2,3} level
     * @apiParam {Date} date_from
     * @apiParam {Date} date_to
     * @apiParam {Number=1,3,5} [currency_id] Required for publisher
     *
     * @apiParam {String} group_field
     * Admin allowed values: <code>created_at,processed_at,publisher_id,advertiser_id,offer_hash,country_id</code><br>
     * Publisher allowed values: <code>created_at,processed_at,offer_hash,country_id</code><br>
     *
     * @apiParam {String=asc,desc} sorting
     *
     * @apiParam {String} sort_by
     * Admin allowed values: <code>title,total_count,approved_count,held_count,cancelled_count,trashed_count,
     * rub_approved_payout,rub_held_payout,rub_profit,usd_approved_payout,usd_held_payout,usd_profit,
     * eur_approved_payout,eur_held_payout,eur_profit</code><br>
     *
     * Publisher allowed values: <code>title,total_count,approved_count,held_count,cancelled_count,trashed_count,
     * rub_approved_payout,rub_held_payout,usd_approved_payout,usd_held_payout,eur_approved_payout,eur_held_payout</code><br>
     *
     * @apiParam {Number[]=1,3,5} [currency_ids[]] Only for admins
     *
     * @apiParam {String} [parent_field] Cannot be the same as `group_field`
     * Admin allowed values: <code>created_at,processed_at,publisher_id,advertiser_id,offer_hash,country_id</code>
     * Publisher allowed values: <code>created_at,processed_at,offer_hash,country_id</code>
     *
     * @apiParam {String} [parent_value]
     *
     * @apiParam {String} [parent_parent_field] Cannot be the same as `group_field` and `parent_value`
     * Admin allowed values: <code>created_at,processed_at,publisher_id,advertiser_id,offer_hash,country_id</code>
     * Publisher allowed values: <code>created_at,processed_at,offer_hash,country_id</code>
     *
     * @apiParam {String} [parent_parent_value]
     * @apiParam {String[]} [target_geo_country_ids[]]
     * @apiParam {String[]} [publisher_hashes[]]
     * @apiParam {String[]} [offer_hashes[]] Only for admin.
     * @apiParam {String[]} [advertiser_hashes[]] Only for admin.
     *
     * @apiSampleRequest /lead.buildReport
     */
    public function buildReport(R\BuildReportRequest $request)
    {
        switch (Auth::user()['role']) {
            case User::ADMINISTRATOR:
                $stat = (new AdminReportStrategy())->get($request);
                break;

            default:
                throw new UnknownUserRole(Auth::user()['role'], ' for leads report');
        }

        return ['response' => $stat, 'status_code' => 200];
    }

    /**
     * @api {GET} /lead.getUncompleted lead.getUncompleted
     * @apiGroup Lead
     * @apiPermission admin
     * @apiParam {Number} advertiser_id
     * @apiSampleRequest /lead.getUncompleted
     */
    public function getUncompleted(R\GetUncompletedRequest $request)
    {
        $leads = Lead::with(['offer', 'target.template', 'country', 'publisher'])
            ->where('advertiser_id', $request->input('advertiser_id'))
            ->approved()
            ->uncompleted()
            ->get();

        return ['response' => $leads, 'status_code' => 200];
    }

    /**
     * @api {POST} /lead.completeByIds lead.completeByIds
     * @apiGroup Lead
     * @apiPermission admin
     * @apiParam {Number[]} ids[]
     * @apiParam {Number} advertiser_id
     * @apiParam {Number} advertiser_currency_id
     * @apiParam {Float} rate
     * @apiParam {Date} profit_at Format: Y-m-d
     * @apiSampleRequest /lead.completeByIds
     */
    public function completeByIds(R\CompleteByIdsRequest $request)
    {
        /**
         * @var Collection $leads
         */
        $leads = $request->input('leads');
        $publisher_payout = $leads->pluck('payout')->sum() * (float)$request->input('rate');
        $total_advertiser_payout = $leads->pluck('advertiser_payout')->sum();
        $total_profit = $total_advertiser_payout - $publisher_payout;
        $profit_per_lead = $total_profit / $leads->count();
        $profit_at = Carbon::createFromFormat('Y-m-d', $request->input('profit_at'));

        \DB::transaction(function () use ($leads, $profit_per_lead, $profit_at) {
            foreach ($leads as $lead) {
                /**
                 * @var Lead $lead
                 */
                $lead->complete($profit_per_lead, Carbon::now());

                BalanceTransaction::insertAdvertiserUnholdWhenManuallyCompleted($lead);
                SystemTransaction::createProfit($lead, $profit_per_lead, $profit_at);
            }
        });

        /**
         * @var AdvertiserProfile $profile
         */
        $profile = AdvertiserProfile::where('user_id', $request->input('advertiser_id'))->firstOrFail();
        $profile->updateUnpaidLeadsCount(-$leads->count());

        return $this->response->accepted(null, [
                'response' => [
                    'charged_sum' => $total_advertiser_payout,
                ],
                'status_code' => 202]
        );
    }
}
