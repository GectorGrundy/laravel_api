<?php
declare(strict_types=1);

use App\Models\Target;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLandingTypeTargetsTable extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->string('landing_type')->default(Target::INTERNAL_LANDING)->after('type');
        });
    }
}
