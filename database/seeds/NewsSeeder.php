<?php
declare(strict_types=1);

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public const ADMIN_ID = 1;

    public function run()
    {
        $created_at = \Carbon\Carbon::now()->subDays(5);

        for ($i = 0; $i < 150; $i++) {

            $offer_id = array_random([0, 1]);

            $created_at = $created_at->addMinutes(55);

            factory(News::class)->create([
                'offer_id' => $offer_id,
                'type' => $this->getTypeByOffer($offer_id),
                'created_at' => $created_at->toDateTimeString(),
                'updated_at' => $created_at->toDateTimeString(),
                'published_at' => $created_at->toDateTimeString(),
            ]);
        }
    }

    private function getTypeByOffer(int $offer_id): string
    {
        if (!$offer_id) {
            return array_random([News::STOCK, News::SYSTEM]);
        }

        return array_random([
            News::OFFER_EDITED, News::OFFER_STOPPED, News::OFFER_CREATED, News::PROMO_CREATED
        ]);
    }
}
