<?php
declare(strict_types=1);

use App\Models\TargetTemplate;
use App\Models\Locale;
use Illuminate\Database\Seeder;

class TargetTemplateSeeder extends Seeder
{
	public function run()
	{
        if (TargetTemplate::all()->count()) {
            return;
        }

        $template = TargetTemplate::create([
            'title' => 'Валидная заявка'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Valid order', 'locale_id' => Locale::EN,
        ]);

        $template = TargetTemplate::create([
            'title' => 'Оплаченный заказ'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Paid order', 'locale_id' => Locale::EN,
        ]);

        $template = TargetTemplate::create([
            'title' => 'Подтвержденный заказ'
        ]);
        DB::table('target_template_translations')->insert([
            'target_template_id' => $template['id'], 'title' => 'Approved order', 'locale_id' => Locale::EN,
        ]);
	}
}
