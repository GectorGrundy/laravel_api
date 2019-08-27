<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GenerateFlowGroupsColor extends Migration
{
    public function up()
    {
        $groups = \App\Models\FlowGroup::all();

        foreach ($groups as $group) {
            event(new \App\Events\FlowGroupCreated($group));
        }
    }
}
