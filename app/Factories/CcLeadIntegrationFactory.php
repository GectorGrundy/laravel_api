<?php
declare(strict_types=1);

namespace App\Factories;

use App\Integrations\Affbay\AffbayAddOrder;
use App\Integrations\Kma\KmaAddOrder;
use App\Integrations\Adcombo\AdcomboAddOrder;
use App\Integrations\Leadbit\LeadbitAddOrder;
use App\Integrations\Leadrock\LeadrockAddOrder;
use App\Integrations\LoremIpsuma\LoremIpsumaAddOrder;
use App\Integrations\Magichygeia\MagichygeiaAddOrder;
use App\Integrations\Test\TestAddOrder;
use App\Integrations\Everad\EveradAddOrder;
use App\Integrations\Cpawebnet\CpawebnetAddOrder;
use App\Integrations\Terraleads\TerraleadsAddOrder;
use App\Integrations\RocketProfit\RocketProfitAddOrder;
use App\Integrations\Finaro\FinaroOrderCreator;
use App\Integrations\Approveninja\ApproveninjaAddOrder;
use App\Integrations\Monsterleads\MonsterleadsAddOrder;
use App\Integrations\Mountainspay\MountainspayOrderCreator;
use App\Integrations\Weblab\WeblabAddOrder;
use App\Integrations\Webvork\WebvorkAddOrder;

class CcLeadIntegrationFactory
{
    public static function getInstance(string $cc_integration_title, int $lead_id, int $integration_id)
    {
        switch (strtolower($cc_integration_title)) {
            case 'approveninja':
                return new ApproveninjaAddOrder($lead_id, $integration_id);

            case 'monsterleads':
                return  new MonsterleadsAddOrder($lead_id, $integration_id);

            case 'finaro':
                return new FinaroOrderCreator([]);

            case 'mountainspay':
                return new MountainspayOrderCreator([]);

            case 'everad':
                return new EveradAddOrder($lead_id, $integration_id);

            case 'terraleads':
                return new TerraleadsAddOrder($lead_id, $integration_id);

            case 'leadbit':
                return new LeadbitAddOrder($lead_id, $integration_id);

            case 'rocketprofit':
                return new RocketProfitAddOrder($lead_id, $integration_id);

            case 'adcombo':
                return new AdcomboAddOrder($lead_id, $integration_id);

	        case 'kma':
		        return new KmaAddOrder($lead_id, $integration_id);

	        case 'cpawebnet':
		        return new CpawebnetAddOrder($lead_id, $integration_id);

	        case 'affbay':
		        return new AffbayAddOrder($lead_id, $integration_id);

            case 'test':
                return new TestAddOrder($lead_id, $integration_id);

            case 'loremipsuma':
                return new LoremIpsumaAddOrder($lead_id, $integration_id);

            case 'weblab':
                return new WeblabAddOrder($lead_id);

            case 'leadrock':
                return new LeadrockAddOrder($lead_id, $integration_id);

            case 'webvork':
                return new WebvorkAddOrder($lead_id);

            case 'magichygeia':
                return new MagichygeiaAddOrder($lead_id);

            default:
                throw new \BadMethodCallException();
        }
    }
}
