<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Deposit as R;
use App\Models\{
    Deposit, BalanceTransaction, StaticFile
};
use File;

class DepositController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /deposit.create deposit.create
     * @apiGroup Deposit
     * @apiPermission admin
     * @apiUse replenishment_method
     * @apiParam {Number} advertiser_id
     * @apiParam {Number} currency_id
     * @apiParam {Float} sum
     * @apiParam {String} [description] Deposit and balance's transaction description
     * @apiParam {Array=advertiser,advertiser.profile,admin} [with[]]
     * @apiParam {String} [created_at] Deposit and balance's transaction creation date in Y-m-d H:i:s format
     * @apiParam {File} [invoice] Invoice file
     * @apiParam {File} [contract] Contract file
     * @apiSampleRequest /deposit.create
     */
    public function create(R\CreateRequest $request)
    {
        $deposit = Deposit::create(array_merge($request->all(), [
            'admin_id' => Auth::id(),
        ]));

        if ($request->hasFile('invoice')) {

            $filepath = storage_path('app/temp/');
            $filename = getRandomCode(18);
            $request->file('invoice')->move($filepath, $filename);

            $invoice_file = StaticFile::create([
                'entity_type' => StaticFile::DEPOSIT,
                'entity_id' => $deposit['id'],
                'content' => File::get($filepath . $filename),
                'info' => 'invoice',
            ]);

            File::delete($filepath . $filename);

            $deposit->update([
                'invoice_file_id' => $invoice_file['id']
            ]);
        }

        if ($request->hasFile('contract')) {
            $filepath = storage_path('app/temp/');
            $filename = getRandomCode(18);
            $request->file('contract')->move($filepath, $filename);

            $contract_file = StaticFile::create([
                'entity_type' => StaticFile::DEPOSIT,
                'entity_id' => $deposit['id'],
                'content' => File::get($filepath . $filename),
                'info' => 'contract',
            ]);
            File::delete($filepath . $filename);

            $deposit->update([
                'contract_file_id' => $contract_file['id']
            ]);
        }

        $deposit = $deposit->load(['advertiser']);

        BalanceTransaction::insertAdvertiserDeposit(
            $deposit,
            $request->get('description', ''),
            $request->get('created_at')
        );

        $deposit->load($request->input('with', []));

        return $this->response->accepted(null, [
            'message' => trans('deposits.on_create_success'),
            'response' => $deposit,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /deposit.edit deposit.edit
     * @apiGroup Deposit
     * @apiPermission admin
     *
     * @apiParam {Number} id
     * @apiUse replenishment_method
     * @apiParam {String} [description] Deposit and balance's transaction description
     * @apiParam {String[]=advertiser,advertiser.profile,admin} [with[]]
     * @apiParam {String} [created_at] Format: Y-m-d H:i:s
     * @apiSampleRequest /deposit.edit
     */
    public function edit(R\EditRequest $request)
    {
        $created_at = $request->input('created_at') ?? Carbon::now()->toDateTimeString();

        $deposit = Deposit::find($request->input('id'));
        $deposit->update(array_merge([
            'created_at' => $created_at
        ],
            $request->only(['description', 'replenishment_method'])
        ));

        $deposit->balance_transaction->update([
            'created_at' => $created_at,
            'description' => $request->input('description'),
        ]);

        if ($request->hasFile('invoice')) {
            if (!\is_null($deposit->invoice_file)) {
                $deposit->invoice_file->delete();
            }
            $filepath = storage_path('app/temp/');
            $filename = getRandomCode(18);
            $request->file('invoice')->move($filepath, $filename);

            $invoice_file = StaticFile::create([
                'entity_type' => StaticFile::DEPOSIT,
                'entity_id' => $deposit['id'],
                'content' => File::get($filepath . $filename),
                'info' => 'invoice',
            ]);
            File::delete($filepath . $filename);

            $deposit->update([
                'invoice_file_id' => $invoice_file['id']
            ]);
        }

        if ($request->hasFile('contract')) {

            if (!is_null($deposit->contract_file)) {
                $deposit->contract_file->delete();
            }
            $filepath = storage_path('app/temp/');
            $filename = getRandomCode(18);
            $request->file('contract')->move($filepath, $filename);

            $contract_file = StaticFile::create([
                'entity_type' => StaticFile::DEPOSIT,
                'entity_id' => $deposit['id'],
                'content' => File::get($filepath . $filename),
                'info' => 'contract',
            ]);
            File::delete($filepath . $filename);

            $deposit->update([
                'contract_file_id' => $contract_file['id']
            ]);
        }

        $deposit->load($request->input('with', []));

        return $this->response->accepted(null, [
            'message' => trans('deposits.on_edit_success'),
            'response' => $deposit,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /deposit.getList deposit.getList
     * @apiGroup Deposit
     * @apiPermission admin
     * @apiPermission advertiser
     *
     * @apiUse date_from
     * @apiUse date_to
     * @apiUse payout_currency_ids
     * @apiParam {String[]=advertiser,advertiser.profile,admin} [with[]]
     * @apiParam {String[]} [advertiser_hashes[]]
     * @apiSampleRequest /deposit.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $deposits = Deposit::with($request->input('with', []))
            ->whereAdvertisers($request->get('advertiser_hashes', []))
            ->createdBetweenDates($request->get('date_from'), $request->get('date_to'))
            ->whereCurrencies($request->get('currency_ids', []))
            ->latest('id')
            ->get();

        return ['response' => $deposits, 'status_code' => 200];
    }
}
