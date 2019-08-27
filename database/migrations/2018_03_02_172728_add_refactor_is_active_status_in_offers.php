<?php
declare(strict_types=1);

use App\Models\Offer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefactorIsActiveStatusInOffers extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('status')->after('type');
        });

        $offers = Offer::all();
        foreach ($offers as $offer) {
            $offer->update([
                'status' => $offer['is_active'] ? Offer::ACTIVE : Offer::INACTIVE,
            ]);
        }

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
