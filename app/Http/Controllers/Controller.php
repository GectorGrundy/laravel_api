<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @apiDefine admin Requests from control subdomain
     */

    /**
     * @apiDefine publisher Requests from my subdomain
     */

    /**
     * @apiDefine advertiser Requests from office subdomain
     */

    /**
     * @apiDefine support Requests from support subdomain
     */

    /**
     * @apiDefine manager Requests from manager subdomain
     */

    /**
     * @apiDefine authorized All authorized users
     */

    /**
     * @apiDefine unauthorized Unauthorized users
     */

    /**
     * @apiDefine payout_currency_id
     * @apiParam {Number=1,3,5} currency_id
     */

    /**
     * @apiDefine payout_currency_ids
     * @apiParam {Number[]=1,3,5} currency_ids[]
     */

    /**
     * @apiDefine balance_transaction_types
     * @apiParam {String[]=advertiser.hold,advertiser.unhold,advertiser.deposit,advertiser.write-off,advertiser.cancel,publisher.hold,publisher.unhold,publisher.cancel,publisher.withdraw,publisher.withdraw_cancel} types[]
     */

    /**
     * @apiDefine date_from
     * @apiParam {String} date_from Format: Y-m-d. Default: -7 days
     */

    /**
     * @apiDefine date_to
     * @apiParam {String} date_to Format: Y-m-d. Default: today
     */

    /**
     * @apiDefine replenishment_method
     * @apiParam {String=cash,swift,epayments,webmoney,paxum,privat24,bitcoin,other} replenishment_method
     */

    /**
     * @apiDefine cloak_domain_path_status
     * @apiParam {String=safepage,moneypage,moneypage_for} status
     */

    /**
     * @apiDefine cloak_domain_path_data_parameter
     * @apiParam {String=data1,data2,data3,data4} [data_parameter] Required if <code>status=moneypage_for</code>
     */

    /**
     * @apiDefine payment_statuses
     * @apiParam {String=pending,cancelled,accepted,paid} [status]
     */

    /**
     * @apiDefine payment_systems
     * @apiParam {String[]=webmoney,epayments,paxum} [payment_systems[]]
     */

    /**
     * @apiDefine news_types
     * @apiParam {String[]=offer_edited,offer_stopped,offer_created,promo_created,system,stock} type
     */

    /**
     * @apiDefine is_private
     * @apiParam {Number=0,1} is_private
     */
    /**
     * @apiDefine is_active
     * @apiParam {Number=0,1} is_active
     */ 
    /**
     * @apiDefine timezone
     * @apiParam {String[]=Pacific/Kwajalein,Pacific/Samoa,America/Adak,America/Anchorage,America/Los_Angeles,
     * US/Mountain,US/Central,US/Eastern,America/Argentina/Buenos_Aires,America/Noronha,America/La_Paz,
     * Atlantic/Cape_Verde,Europe/London,Europe/Madrid,Europe/Kiev,Europe/Moscow,Asia/Tbilisi,Asia/Yekaterinburg,
     * Asia/Almaty,Asia/Bangkok,Asia/Hong_Kong,Asia/Tokyo,Asia/Vladivostok,Asia/Magadan,Pacific/Auckland} timezone
     */
}