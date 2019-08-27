<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FailedJob;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\FailedJobs as R;

class FailedJobsController extends Controller
{
    use Helpers;

    public function getList(): array
    {
        $failed_jobs = FailedJob::latest('id')->get();

        return ['response' => $failed_jobs, 'status_code' => 200];
    }

    public function retry(R\RetryRequest $request): Response
    {
        $id = $request->input('id');

        if ($id === 'all') {
            $jobs = FailedJob::all();
        } else {
            $jobs = FailedJob::where('id', $id)->get();
        }

        foreach ($jobs as $job) {
            // Refresh attemps
            $payload =  preg_replace('~"attempts":[0-9]~', '"attempts": 0', $job->payload);

            $job->update(['payload' => $payload]);

            \Artisan::call('queue:retry', ['id' => [$job->id]]);
        }

        return $this->response->accepted(null, [
            'message' => trans('failed_jobs.on_retry_success'),
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request): Response
    {
        \Artisan::call('queue:forget', ['id' => [$request->id]]);

        return $this->response->accepted(null, [
            'message' => trans('failed_jobs.on_delete_success'),
            'status_code' => 202
        ]);
    }
}
