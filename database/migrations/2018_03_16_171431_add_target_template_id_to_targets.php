<?php
declare(strict_types=1);

use App\Models\Target;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTargetTemplateIdToTargets extends Migration
{
    public function up()
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->tinyInteger('target_template_id')->after('locale_id');
        });

        Target::all()->each(function (Target $target) {

            $title = mb_strtolower($target['title']);

            if (str_contains($title, 'оплаченный')) {
                $target->update(['target_template_id' => 2]);

            } elseif (str_contains($title, 'валидная заявка')) {
                $target->update(['target_template_id' => 1]);

            } else {
                $target->update(['target_template_id' => 3]);
            }
        });
    }
}
