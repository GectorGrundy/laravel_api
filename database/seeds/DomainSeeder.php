<?php
declare(strict_types=1);

use App\Models\CloakDomainPath;
use App\Models\CloakDomainPathCloakSystem;
use Illuminate\Database\Seeder;
use App\Models\Domain;

class DomainSeeder extends Seeder
{
    public function run()
    {
        Domain::create([
            'domain' => 'go.affninja.local',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::TDS_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'redirect.affninja.local',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::REDIRECT_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'theblogger.local',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::TRANSIT_ENTITY_TYPE,
        ]);
        Domain::create([
            'domain' => 'hit24x7.local',
            'type' => Domain::SYSTEM_TYPE,
            'entity_type' => Domain::LANDING_ENTITY_TYPE,
        ]);

//        Domain::create([
//            'domain' => 'cloakdomain.local',
//            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
//            'type' => Domain::PARKED_TYPE,
//            'entity_type' => Domain::FLOW_ENTITY_TYPE,
//            'fallback_flow_id' => 2, // CPA поток
//        ]);
//
//        $domain = Domain::create([
//            'domain' => 'newcloakdomain.local',
//            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
//            'type' => Domain::PARKED_TYPE,
//            'entity_type' => Domain::FLOW_ENTITY_TYPE,
//            'donor_url' => 'https://elconfidencial.com',
//            'donor_charset' => Domain::DEFAULT_DONOR_CHARSET,
//            'fallback_flow_id' => 2, // CPA поток
//        ]);
//
//        $path = CloakDomainPath::create([
//            'user_id' => SeederConstants::TEST_PUBLISHER_ID,
//            'domain_id' => $domain->id,
//            'flow_id' => 2, // CPA поток
//            'path' => '/',
//            'status' => CloakDomainPath::SAFEPAGE_STATUS
//        ]);
//
//        CloakDomainPathCloakSystem::create([
//            'cloak_domain_path_id' => $path->id,
//            'cloak_system_id' => \App\Models\CloakSystem::FRAUDFILTER,
//            'is_cache_result' => 1,
//            'attributes' => json_encode([
//                'api_key' => 'test',
//                'campaign_id' => 'test',
//            ])
//        ]);

    }
}
