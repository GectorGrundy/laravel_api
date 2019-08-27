<?php

use App\Models\PostbackinLog;
use Illuminate\Database\Migrations\Migration;

class ChangeSerializationToJsonInPostbackinLogs extends Migration
{
    public function up()
    {
        $logs = PostbackinLog::all();

        PostbackinLog::query()->delete();

        DB::statement('ALTER TABLE `postbackin_logs` CHANGE `request` `request` JSON NOT NULL;');

        foreach ($logs as $log) {
            $response = unserialize($log['response']);
            if (is_string($response)) {
                $response = [];
            }

            PostbackinLog::create([
                'id' => $log['id'],
                'api_key' => $log['api_key'],
                'lead_id' => $log['lead_id'],
                'request' => json_encode(unserialize($log['request'])),
                'ip' => $log['ip'],
                'response_code' => $log['response_code'],
                'response' => json_encode($response),
                'created_at' => $log['created_at'],
                'updated_at' => $log['updated_at'],
            ]);
        }
    }
}
