<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StaticFile;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\File as R;
use File;
use Illuminate\Http\Request;

/**
 * @todo test it
 */
class FileController extends Controller
{
    use Helpers;

    public function uploadImage(R\UploadImageRequest $request)
    {
        $filepath = storage_path('app/temp/');

        $filename = generateRandomFilename($filepath);

        $request->file('preview')->move($filepath, $filename);

        return $this->response->accepted(null, [
            'message' => trans('messages.on_upload_image_success'),
            'response' => [
                'thumb_path' => $filepath . $filename,
            ],
            'status_code' => 202
        ]);
    }

    public function uploadAudio(R\UploadAudioRequest $request)
    {
        $filepath = storage_path('app/temp/');

        $filename = generateRandomFilename($filepath, 'mp3');

        $request->file('audio')->move($filepath, $filename);

        return $this->response->accepted(null, [
            'message' => trans('messages.on_upload_audio_success'),
            'response' => [
                'audion_path' => $filepath . $filename,
            ],
            'status_code' => 202
        ]);
    }

    public function show(Request $request)
    {
        $static_file = StaticFile::findOrFail($request->input('id'));

        $fullpath = storage_path('app/temp/') . getRandomCode(18);
        File::put($fullpath, $static_file->content);
        $mimetype = File::mimeType($fullpath);
        File::delete($fullpath);

        return response($static_file->content, 200, [
            'Content-type' => $mimetype
        ]);
    }
}
