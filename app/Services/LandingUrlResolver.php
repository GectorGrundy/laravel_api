<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\GoDataContainer;

class LandingUrlResolver
{
    public const INDEX_PAGE = 'index.html';
    public const ORDER_PAGE = 'order.html';
    public const PRIVACY = 'privacypolicy.html';
    public const TERMS = 'terms.html';
    public const RETURNS = 'returns.html';

    public static function getUrl(array $params = []): string
    {
        return '/' . self::INDEX_PAGE . getQuesyStringFromArray($params);
    }

    public static function getOrderPageUrl(array $params = []): string
    {
        return '/' . self::ORDER_PAGE . getQuesyStringFromArray($params);
    }

    public static function getPrivacyPolicyPageUrl(): string
    {
        /**
         * @var GoDataContainer $data_container
         */
        $data_container = app(GoDataContainer::class);

        $params = [
            CloakingService::FOREIGN_PARAM => $data_container->getLanding()['hash']
        ];

        return '/' . self::PRIVACY . getQuesyStringFromArray($params);
    }

    public static function getTermsUrl(): string
    {
        $data_container = app(GoDataContainer::class);

        $params = [
            CloakingService::FOREIGN_PARAM => $data_container->getLanding()['hash']
        ];

        return '/' . self::TERMS . getQuesyStringFromArray($params);
    }

    public static function getReturnsUrl(): string
    {
        $data_container = app(GoDataContainer::class);

        $params = [
            CloakingService::FOREIGN_PARAM => $data_container->getLanding()['hash']
        ];

        return '/' . self::RETURNS . getQuesyStringFromArray($params);
    }
}