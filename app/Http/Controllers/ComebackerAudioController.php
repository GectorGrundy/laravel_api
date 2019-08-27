<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\MoveStaticFile;
use Dingo\Api\Routing\Helpers;
use App\Models\ComebackerAudio;
use App\Http\Requests\ComebackerAudio as R;
use File;

class ComebackerAudioController extends Controller
{
	use Helpers;

    public function create(R\CreateRequest $request)
    {
        $comebacker_audio = ComebackerAudio::create($request->all());

        $this->dispatch(new MoveStaticFile(
            $request->get('audion_path'),
            public_path(substr($comebacker_audio->getAudioPath(), 1))
        ));

        $comebacker_audio->load(['locale']);

        return $this->response->accepted(null, [
			'message' => trans('comebacker_audio.on_create_success'),
			'response' => $comebacker_audio,
			'status_code' => 202
		]);
	}

	public function edit(R\EditRequest $request)
	{
        $comebacker_audio = ComebackerAudio::find($request->get('id'));

        $comebacker_audio->update($request->all());

        if ($request->filled('audion_path')) {
            $this->dispatch(new MoveStaticFile(
                $request->get('audion_path'),
                public_path(substr($comebacker_audio->getAudioPath(), 1))
            ));
        }

		$comebacker_audio = ComebackerAudio::find($request->get('id'));

		return $this->response->accepted(null, [
			'message' => trans('comebacker_audio.on_edit_success'),
			'response' => $comebacker_audio,
			'status_code' => 202
		]);
	}

	public function delete(R\DeleteRequest $request)
	{
		$comebacker_audio = ComebackerAudio::find($request->get('id'));

		$comebacker_audio->delete();

		return $this->response->accepted(null, [
			'message' => trans('comebacker_audio.on_delete_success'),
			'status_code' => 202
		]);
	}

	/**
	 * Get list of comebacker audio
	 *
	 * @param R\GetListRequest $request
	 * @return array
	 */
	public function getList(R\GetListRequest $request)
	{
		$comebacker_audios = ComebackerAudio::with($request->get('with', []))->orderBy('id', 'desc')->get();

		return ['response' => $comebacker_audios, 'status_code' => 200];
	}
}
