<?php

use App\Models\ApiLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSerializationToJsonInApiLogs extends Migration
{
    public function up()
    {
        $logs = ApiLog::all();

        ApiLog::query()->delete();

        DB::statement('ALTER TABLE `api_logs` CHANGE `request` `request` JSON NOT NULL, CHANGE `response` `response` JSON NOT NULL;');

        foreach ($logs as $log) {
            $response = unserialize($log['response']);
            if (is_string($response)) {
                $response = [];
            }

            ApiLog::create([
                'id' => $log['id'],
                'user_id' => $log['user_id'],
                'admin_id' => $log['admin_id'],
                'request_method' => $log['request_method'],
                'api_method' => $log['api_method'],
                'request' => json_encode(unserialize($log['request'])),
                'response_code' => $log['response_code'],
                'response' => json_encode($response),
                'user_agent' => $log['user_agent'],
                'ip' => $log['ip'],
                'created_at' => $log['created_at'],
                'updated_at' => $log['updated_at'],
            ]);
        }
    }
}
