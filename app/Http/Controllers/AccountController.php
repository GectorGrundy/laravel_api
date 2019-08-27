<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Models\Account;
use App\Http\Requests\Account as R;

class AccountController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /account.getList account.getList
     * @apiGroup Account
     * @apiPermission admin
     * @apiParam {Number} user_id
     * @apiSampleRequest /account.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $accounts = Account::with($request->get('with', []))
            ->where('user_id', $request->input('user_id'))
            ->where('is_active', '1')
            ->latest('id')
            ->get();

        return ['response' => $accounts, 'status_code' => 200];
    }

    /**
     * @api {POST} /account.create account.create
     * @apiGroup Account
     * @apiPermission admin
     *
     * @apiParam {Number} user_id
     * @apiParam {Number} currency_id
     *
     * @apiSampleRequest /accounts.create
     */
    public function create(R\CreateRequest $request)
    {
        $account = Account::create(array_merge($request->all()));

        return $this->response->accepted(null, [
            'message' => trans('accounts.on_create_success'),
            'response' => $account,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /account.delete account.delete
     * @apiGroup Account
     * @apiPermission admin
     *
     * @apiParam {Number} id
     *
     * @apiSampleRequest /accounts.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        Account::find($request->input('id'))->update(['is_active' => 0]);

        return $this->response->accepted(null, [
            'message' => trans('accounts.on_delete_success'),
            'status_code' => 202
        ]);
    }
}
