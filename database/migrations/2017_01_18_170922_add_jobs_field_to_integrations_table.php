<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Integration;

class AddJobsFieldToIntegrationsTable extends Migration
{
	public function up()
	{
		$integrations = Integration::all();

		Schema::table('integrations', function ($table) {
			$table->dropColumn(['add_job_name', 'edit_job_name']);
			$table->jsonb('jobs', 16)->after('internal_api_key');
		});

		foreach ($integrations as $integration) {
			$jobs = [];
			if (isset($integration->add_job_name)) {
				$jobs['add_job_name'] = $integration->add_job_name;
			}
			if (isset($integration->edit_job_name)) {
				$jobs['edit_job_name'] = $integration->edit_job_name;
			}

			$integration->update(['jobs' => json_encode($jobs)]);
		}
	}

	public function down()
	{
		$integrations = Integration::all();

		Schema::table('integrations', function ($table) {
			$table->string('add_job_name');
			$table->string('edit_job_name');
			$table->dropColumn(['jobs']);
		});

		foreach ($integrations as $integration) {
			$add_job_name = json_decode($integration->jobs, true)['add_job_name'] ?? '';
			$edit_job_name = json_decode($integration->jobs, true)['edit_job_name'] ?? '';

			$integration->update([
				'add_job_name' => $add_job_name,
				'edit_job_name' => $edit_job_name,
			]);
		}
	}
}
