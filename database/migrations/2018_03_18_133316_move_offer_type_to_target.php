<?php
declare(strict_types=1);

use App\Models\Target;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveOfferTypeToTarget extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->char('type', '3')->after('hash');
        });

        Target::with(['offer'])->get()->each(function (Target $target) {
            $target->update(['type' => $target->offer['type']]);
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
}
