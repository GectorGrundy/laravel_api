<?php
declare(strict_types=1);

use App\Models\Target;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelFieldToTargets extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->string('label')->after('hash');
        });

        Target::with(['locale'])->latest()->chunk(100, function ($targets) {
            foreach ($targets as $target) {
                $target->update([
                    'label' => mb_strtoupper($target->locale['code']),
                ]);
            }
        });
    }
}
