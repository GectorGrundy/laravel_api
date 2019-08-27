<?php

use Illuminate\Database\Seeder;
use App\Models\OfferCategory;

class OfferCategorySeeder extends Seeder
{
	public function run()
	{
		$categories = [
			['title' => 'Видео'],
			['title' => 'Музыка'],
			['title' => 'Файлы'],
			['title' => 'Игры'],
			['title' => 'Adult фото'],
			['title' => 'Adult видео']
		];

		foreach ($categories AS $category) {

			OfferCategory::create([
				'title' => $category['title']
			]);
		}
	}
}
