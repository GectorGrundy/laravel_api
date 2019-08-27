<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Requests\PostbackOut as R;
use App\Models\PostbackOut;
use App\Models\Postback;
use Auth;
use DB;
use Hashids;

class PostbackOutController extends Controller
{
	use Helpers;

	/**
	 * Instantiate a new DomainController instance.
	 *
	 */
	public function __construct()
	{

	}

	/**
	 * Получение списка исходящих постбеков
	 *
	 * @param R\GetListRequest $request
	 * @return array
	 */
	public function getList(R\GetListRequest $request)
	{
		$postbackout_logs = PostbackOut::select(
			'postbackout_logs.*',
			'leads.hash as lead_hash',
			'postbacks.hash as postback_hash',
			'flows.hash as flow_hash',
			'flows.title as flow_title'
		)
			->leftJoin('postbacks', 'postbacks.id', '=', 'postbackout_logs.postback_id')
			->leftJoin('flows', 'flows.id', '=', 'postbacks.flow_id')
			->leftJoin('leads', 'leads.id', '=', 'postbackout_logs.lead_id')
			->where('postbacks.publisher_id', Auth::user()->id)
			->createdBetweenDates($request->get('date_from'), $request->get('date_to'))
			->whereFlow($request->get('flow_hashes', []))
			->whereLead($request->get('lead_hash', ''))
			->wherePostback($request->get('postback_hash', ''))
			->get()
			->toArray();

		return ['response' => $postbackout_logs, 'status_code' => 200];
	}
}
