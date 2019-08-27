<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use DB;
use Auth;
use Hashids;
use App\Events\PaymentPaid;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Payment as R;
use App\Models\{
    Payment, BalanceTransaction, PaymentSystem, Publisher
};

class PaymentsController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /payment.create payment.create
     * @apiGroup Payment
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiUse payout_currency_id
     * @apiParam {Number} payment_system_id
     * @apiParam {String} requisite_hash
     * @apiParam {Float} payout
     * @apiParam {Number} [publisher_id] Required for admins.
     * @apiParam {String[]=processed_user,paid_user,user.publisher} [with[]] For admins
     * @apiSampleRequest /payment.create
     */
    public function create(R\CreateRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {

            $balance_payout = (float)$request->input('payout');

            if (Auth::user()->isAdmin()) {
                $user = Publisher::find($request->input('publisher_id'));
            } else {
                $user = Auth::user();
            }

            $currency_id = (int)$request->input('currency_id');
            $requisite = $request->input('requisite');
            /**
             * @var PaymentSystem $payment_system
             */
            $payment_system = $request->input('payment_system');

            $comission = $payment_system->getComission($balance_payout);
            $payout = $payment_system->enableComissionToPayout($balance_payout, $comission);

            $payment = Payment::create([
                'user_id' => $user['id'],
                'user_role' => Auth::user()['role'],
                'requisite_id' => $requisite['id'],
                'requisite_type' => Payment::getRequisiteType($payment_system),
                'status' => Payment::PENDING,
                'type' => 'payment',
                'currency_id' => $currency_id,
                'payout' => $payout,
                'balance_payout' => $balance_payout,
                'comission' => -$comission,
                'description' => 'Ondemand payment'
            ]);

            BalanceTransaction::insertPublisherWithdraw($payment_system, $requisite, $payment);

            return $payment;
        });

        $payment->load(array_merge(
            $request->input('with', []), [
            'requisite' => function ($query) {
                $query->withTrashed();
            }
        ]));

        return [
            'message' => trans('payments.on_create_success'),
            'response' => $payment,
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /payment.getList payment.getList
     * @apiGroup Payment
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiUse payout_currency_ids
     * @apiUse payment_statuses
     * @apiUse payment_systems
     * @apiParam {String[]=processed_user,paid_user,user.publisher} [with[]] For admins
     * @apiParam {String[]} [publisher_hashes[]] For admins
     * @apiParam {Number{..200}} [per_page=50]
     * @apiParam {Number} [page=1]
     *
     * @apiSampleRequest /payment.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $with = ['requisite' => function ($query) {
            $query->withTrashed();
        }];
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 50);
        $offset = paginationOffset($page, $per_page);

        $query = Payment::whereStatus($request->get('status'))
            ->currencies($request->input('currency_ids', []))
            ->wherePublishers($request->input('publisher_hashes', []))
            ->paymentSystems($request->input('payment_systems', []))
            ->orderByRaw("FIELD(status, 'accepted', 'pending') DESC, `created_at` DESC");

        $total = clone $query;
        $total = (int)($total->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        $payments = $query->with(array_merge($request->input('with', []), $with))
            ->offset($offset)->limit($per_page)->latest('id')->get();

        return [
            'response' => [
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page),
                'data' => $payments,
            ],
            'status_code' => 200];
    }

    /**
     * @api {POST} /payment.cancel payment.cancel
     * @apiGroup Payment
     * @apiPermission admin
     * @apiPermission publisher
     * @apiParam {String} hash
     * @apiParam {String[..255]} description
     * @apiSampleRequest /payment.cancel
     */
    public function cancel(R\CancelRequest $request)
    {
        $payment_id = Hashids::decode($request->get('hash'))[0];
        $user = Auth::user();

        $payment = DB::transaction(function () use ($payment_id, $user, $request) {

            $payment = Payment::with(['requisite' => function ($builder) {
                $builder->withTrashed();
            }])
                ->find($payment_id);

            $fields = [
                'status' => Payment::CANCELLED,
                'processed_user_id' => $user['id'],
            ];

            if ($user->isAdmin()) {
                $fields['description'] = $request->input('description', '');
            }

            $payment->update($fields);

            BalanceTransaction::insertPublisherWithdrawCancel($payment);

            if ($user->id === $payment->user_id) {
                $payment->delete();
            }

            return $payment;
        });

        if (Auth::user()->isAdmin()) {
            $payment->load('user.publisher', 'processed_user');
        }

        return [
            'message' => trans('payments.on_cancel_success'),
            'response' => $payment,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /payment.accept payment.accept
     * @apiGroup Payment
     * @apiPermission admin
     * @apiParam {String} hash
     * @apiParam {String[..255]} description
     * @apiSampleRequest /payment.accept
     */
    public function accept(R\AcceptRequest $request)
    {
        $payment_id = Hashids::decode($request->get('hash'))[0];

        $payment = Payment::with(['requisite' => function ($query) {
            $query->withTrashed();
        }])
            ->find($payment_id);

        $payment->update([
            'status' => Payment::ACCEPTED,
            'description' => $request->input('description', ''),
            'processed_user_id' => Auth::id(),
        ]);

        $payment->load('user.publisher', 'processed_user');

        return [
            'message' => trans('payments.on_accept_success'),
            'response' => $payment,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /payment.pay payment.pay
     * @apiGroup Payment
     * @apiPermission admin
     * @apiParam {String} hash
     * @apiSampleRequest /payment.pay
     */
    public function pay(R\PayRequest $request)
    {
        $payment_id = Hashids::decode($request->get('hash'))[0];

        $payment = Payment::with(['requisite' => function ($query) {
            $query->withTrashed();
        }])
            ->find($payment_id);

        $payment->update([
            'status' => Payment::PAID,
            'paid_user_id' => Auth::id(),
        ]);

        event(new PaymentPaid($payment));

        $payment->load('user.publisher', 'processed_user', 'paid_user');

        return [
            'message' => trans('payments.on_pay_success'),
            'response' => $payment,
            'status_code' => 202
        ];
    }
}
