<?php
declare(strict_types=1);

use App\Models\TargetTemplate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Locale;

class CreateTargetTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('target_templates', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('title');
            $table->softDeletes()->nullable();
        });

        //
        $template = TargetTemplate::create([
            'title' => 'Валидная заявка'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Valid order', 'locale_id' => Locale::EN,
        ]);

        //
        $template = TargetTemplate::create([
            'title' => 'Оплаченный заказ'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Paid order', 'locale_id' => Locale::EN,
        ]);

        //
        $template = TargetTemplate::create([
            'title' => 'Подтвержденный заказ'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Approved order', 'locale_id' => Locale::EN,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('target_templates');
    }
}
