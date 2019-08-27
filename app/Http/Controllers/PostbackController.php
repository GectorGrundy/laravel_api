<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Postback as R;
use App\Models\Postback;
use Auth;
use Hashids;

class PostbackController extends Controller
{
	use Helpers;

	public function create(R\CreateRequest $request)
	{
		$flow_id = 0;
		// If it's postback for flow - decode flow hash
		if ($request->get('flow_hash', 0) !== '0') {
			$flow_id = Hashids::decode($request->input('flow_hash'))[0];
		}

		$postback = Postback::create(array_merge(
			$request->all(),
			[
				'flow_id' => $flow_id,
				'publisher_id' => Auth::user()->id
			]
		));

		return $this->response->accepted(null, [
			'message' => trans('postbacks.on_create_success'),
			'response' => $postback,
			'status_code' => 202
		]);
	}

	public function edit(R\EditRequest $request)
	{
		$postback_id = Hashids::decode($request->input('postback_hash'))[0];

		$postback = Postback::find($postback_id);

		//update
		$postback->update($request->all());

		//Получаем актуальную информацию

		return $this->response->accepted(null, [
			'message' => trans('postbacks.on_edit_success'),
			'response' => $postback,
			'status_code' => 202
		]);
	}

	public function getByHash(R\GetByHashRequest $request)
	{
		switch (Auth::user()->role) {
			case 'administrator':
				$postback = Postback::where('postbacks.hash', $request->input('postback_hash'))->first();
				break;

			case 'publisher':
				$postback = Postback::select('postbacks.*')
					->leftJoin('flows', 'flows.id', '=', 'postbacks.flow_id')
					->where('postbacks.hash', $request->input('postback_hash'))
					->where('flows.publisher_id', Auth::user()->id)
					->first();
				break;

			case 'manager':
				$postback = Postback::select('postbacks.*')
					->leftJoin('flows', 'flows.id', '=', 'postbacks.flow_id')
					->leftJoin('publisher_profiles', 'publisher_profiles.user_id', '=', 'flows.publisher_id')
					->where('postbacks.hash', $request->input('postback_hash'))
					->where('publisher_profiles.manager_id', Auth::user()->id)
					->first();
				break;

			default:
				return [
					'message' => trans('postbacks.forbidden_error'),
					'response' => null,
					'status_code' => 403
				];
				break;
		}

		return ['response' => $postback, 'status_code' => 200];
	}

	public function getList(R\GetListRequest $request)
	{
		$postbacks = Postback::where('postbacks.publisher_id', Auth::user()->id);

		if ($request->filled('flow_hash')) {

			$flow_id = Hashids::decode($request->input('flow_hash'))[0];
			$postbacks = $postbacks->where('postbacks.flow_id', $flow_id);

		} else {

			$postbacks = $postbacks->where('postbacks.flow_id', 0);
		}

		$postbacks = $postbacks->orderBy('postbacks.id', 'desc')->get();

		return ['response' => $postbacks, 'status_code' => 200];
	}

	public function delete(R\DeleteRequest $request)
	{
		$postback_id = Hashids::decode($request->input('postback_hash'))[0];

		Postback::find($postback_id)->delete();

		return $this->response->accepted(null, [
			'message' => trans('postbacks.on_delete_success'),
			'status_code' => 202
		]);
	}
}
