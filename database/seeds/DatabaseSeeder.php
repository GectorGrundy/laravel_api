<?php
declare(strict_types=1);

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        /* @todo Удалить таблицы:
         * preorders, preorder_visits, offer_preorder_reason, preorder_reason_translations,
         * products, payment_methods, payment_method_integrations, payment_method_templates, target_geo_regions
         *
         * leads: payment_method_id, integration_type, lorem_ipsuma_version
         */
        $this->call(DatabaseTableSeeder::class);

        $this->call(DomainSeeder::class);

        $this->call(DeviceSeeder::class);

        $this->call(UserPermissionsSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UserGroupSeeder::class);

        $this->call(CurrencySeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(CountryTranslationSeeder::class);
        $this->call(LocaleSeeder::class);
        $this->call(PaymentSystemSeeder::class);

        $this->call(IntegrationSeeder::class);

        $this->call(OfferCategorySeeder::class);
        $this->call(OfferLabelSeeder::class);
        $this->call(OfferSourceSeeder::class);
        $this->call(OfferSourceTranslationSeeder::class);
        $this->call(TargetTemplateSeeder::class);
        $this->call(OfferSeeder::class);


        $this->call(CloakSystemSeeder::class);
        $this->call(FlowWidgetSeeder::class);
        $this->call(FlowFlowWidgetSeeder::class);
        $this->call(FlowSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(PaymentRequsitesSeeder::class);
        $this->call(CashRequisiteSeeder::class);
    }
}
