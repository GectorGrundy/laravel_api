<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CashRequisite;
use App\Models\EpaymentsRequisite;
use App\Models\PaxumRequisite;
use App\Models\PaymentSystem;
use App\Models\Publisher;
use App\Models\SwiftRequisite;
use App\Models\WebmoneyRequisite;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\PaymentRequisite as R;
use Auth;

class PaymentRequisitesController extends Controller
{
    use Helpers;

    public function edit(R\EditRequest $request)
    {
        $requisites = $request->all();

        $webmoney = new WebmoneyRequisite();
        $webmoney->upgradeOrCreateWmr($requisites);
        $webmoney->upgradeOrCreateWmz($requisites);
        $webmoney->upgradeOrCreateWme($requisites);
        (new PaxumRequisite())->upgradeOrCreate($requisites);
        (new EpaymentsRequisite())->upgradeOrCreate($requisites);
        (new SwiftRequisite())->upgradeOrCreate($requisites);

        return [
            'message' => trans('payment_requisites.on_edit_success'),
            'status_code' => 202
        ];
    }

    public function getList()
    {
        $user = Auth::user();

        $webmoney = PaymentSystem::withWebmoneyRequisites($user)->get()->toArray();
        $paxum = PaymentSystem::withPaxumRequisites($user)->get()->toArray();
        $epayments = PaymentSystem::withEpaymentsRequisites($user)->get()->toArray();
        $swift = PaymentSystem::withSwiftRequisites($user)->get()->toArray();

        $requisites = array_merge($webmoney, $paxum, $epayments, $swift);

        return ['response' => $requisites, 'status_code' => 200];
    }

    /**
     * @api {GET} /payment_requisites.getListForPayment payment_requisites.getListForPayment
     * @apiGroup PaymentSystem
     * @apiPermission admin
     * @apiPermission publisher
     *
     * @apiUse payout_currency_id
     * @apiParam {Number} [publisher_id] Required for admins.
     * @apiSampleRequest /payment_requisites.getListForPayment
     */
    public function getListForPayment(R\GetListForPaymentRequest $request)
    {
        $user = Auth::user();
        $requisites = [];
        $publisher_id = (int)$request->input('publisher_id');

        // Webmoney
        $webmoney = WebmoneyRequisite::with(['payment_system'])->currency((int)$request->input('currency_id'));
        if ($user->isAdmin()) {
            $webmoney->whereUser(Publisher::find($publisher_id));
        }
        $webmoney = $webmoney->first();
        if (!\is_null($webmoney)) {
            $requisites[] = $webmoney;
        }

        // Paxum
        $paxum = PaxumRequisite::with(['payment_system'])->currency((int)$request->input('currency_id'));
        if ($user->isAdmin()) {
            $paxum->whereUser(Publisher::find($publisher_id));
        }
        $paxum = $paxum->first();
        if (!\is_null($paxum)) {
            $requisites[] = $paxum;
        }

        // Epayments
        $epayments = EpaymentsRequisite::with(['payment_system'])->currency((int)$request->input('currency_id'));
        if ($user->isAdmin()) {
            $epayments->whereUser(Publisher::find($publisher_id));
        }
        $epayments = $epayments->first();
        if (!\is_null($epayments)) {
            $requisites[] = $epayments;
        }

        // Swift
        $swift = SwiftRequisite::with(['payment_system'])->currency((int)$request->input('currency_id'));
        if ($user->isAdmin()) {
            $swift->whereUser(Publisher::find($publisher_id));
        }
        $swift = $swift->first();
        if (!\is_null($swift)) {
            $requisites[] = $swift;
        }

        // Cash
        $cash = CashRequisite::with(['payment_system'])->currency((int)$request->input('currency_id'))->first();
        if (!\is_null($cash)) {
            $requisites[] = $cash;
        }

        return ['response' => $requisites, 'status_code' => 200];
    }
}

