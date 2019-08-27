<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentsSeeder extends Seeder
{
    public function run()
    {
        factory(\App\Models\Payment::class, 5)->create();
        factory(\App\Models\Payment::class, Payment::ACCEPTED, 3)->create();
        factory(\App\Models\Payment::class, Payment::CANCELLED, 3)->create();
        factory(\App\Models\Payment::class, Payment::PAID, 3)->create();
    }
}
