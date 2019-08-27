<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Advertiser;
use Dingo\Api\Routing\Helpers;
use App\Models\BalanceTransaction;
use App\Http\Requests\BalanceTransaction as R;
use Illuminate\Database\Eloquent\Builder;

class BalanceTransactionController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /balance_transaction.create balance_transaction.create
     * @apiDescription Create advertiser.write-off transaction.
     * @apiGroup BalanceTransaction
     * @apiPermission admin
     *
     * @apiParam {Number} user_id ID of advertiser
     * @apiParam {String=advertiser.write-off} type
     * @apiParam {Number} currency_id
     * @apiParam {Float} balance_sum Amount
     * @apiParam {String} [description]
     * @apiSampleRequest /balance_transaction.create
     */
    public function create(R\CreateRequest $request)
    {
        $advertiser = Advertiser::find((int)$request->input('user_id'));

        $transaction = BalanceTransaction::insertAdvertiserWriteOff(
            $advertiser,
            (float)$request->input('balance_sum'),
            (int)$request->input('currency_id'),
            $request->input('description')
        );

        return $this->response->accepted(null, [
            'message' => trans('balance_transactions.on_create_success'),
            'response' => $transaction,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /balance_transaction.edit balance_transaction.edit
     * @apiGroup BalanceTransaction
     * @apiPermission admin
     *
     * @apiParam {Number} id
     * @apiParam {String} [description]
     * @apiSampleRequest /balance_transaction.edit
     */
    public function edit(R\EditRequest $request)
    {
        $transaction = BalanceTransaction::find($request->input('id'));

        $transaction->update($request->only('description'));

        return $this->response->accepted(null, [
            'message' => trans('balance_transactions.on_edit_success'),
            'response' => $transaction,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /balance_transaction.getList balance_transaction.getList
     * @apiGroup BalanceTransaction
     * @apiPermission admin
     *
     * @apiUse balance_transaction_types
     * @apiUse date_from
     * @apiUse date_to
     * @apiParam {String} [date_to] Format: Y-m-d
     * @apiParam {Number[]} [users_ids[]]
     * @apiParam {String[]} [offer_hashes[]]
     * @apiParam {Number[]} [country_ids[]]
     * @apiParam {String=[transaction_hash,lead_hash]}[search_field]
     * @apiParam {String}[search]
     * @apiParam {String=currency}[group_by]
     * @apiParam {Number[]} currency_ids
     * @apiSampleRequest /balance_transaction.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $types = $request->input('types');

        $user_ids = getIdsByHashes($request->input('advertiser_hashes', []));

        /**
         * @var Builder $base_query
         */
        $base_query = BalanceTransaction::whereUsers($request->get('user_ids', []))
            ->createdBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereLeadOffers($request->get('offer_hashes', []))
            ->whereLeadCountries($request->get('country_ids', []))
            ->whereTypes($request->input('types', []))
            ->whereUsers($user_ids)
            ->whereCurrencies($request->input('currency_ids', []))
            ->search($request->get('search_field'), $request->get('search'));

        if (\Auth::user()->isAdmin()) {
            $base_query->with(['user.advertiser', 'admin.profile']);
        }

        if (\in_array(BalanceTransaction::ADVERTISER_DEPOSIT, $types)) {
            $base_query->with(['deposit']);
        }

        if (\in_array(BalanceTransaction::ADVERTISER_UNHOLD, $types)
            || \in_array(BalanceTransaction::ADVERTISER_CANCEL, $types)
        ) {
            $base_query->with(['lead.offer', 'lead.country', 'lead.target.template']);
        }

        $transactions = $base_query->latest()->get();

        $transactions = $transactions->each(function ($item) {
            if ($item['entity_type'] === BalanceTransaction::LEAD) {
                unset($item['deposit']);

            }
            if ($item['entity_type'] === BalanceTransaction::DEPOSIT) {
                unset($item['lead']);
            }

            return $item;
        });

        return ['response' => $transactions, 'status_code' => 200];
    }
}
