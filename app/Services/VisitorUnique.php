<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Flow;
use Carbon\Carbon;

class VisitorUnique
{
    /**
     * Проверка уникальности посетителя для метода перехода на лендинг (showLanding)
     *
     */
    public function getLandingUnique(array $visitor_info, Flow $flow, ?string $from, int $landing_id, int $transit_id): array
    {
        $unique = [];

        if (!$landing_id) {
            $unique['transit_landing_rel_unique'] = 0;
            $unique['transit_landing_unique'] = 0;
            $unique['direct_landing_unique'] = 0;
            $unique['noback_landing_unique'] = 0;
            $unique['comeback_landing_unique'] = 0;

            return $unique;
        }

        $day_ago = $this->getDayAgoTimestamp();

        $flow = $this->getFlow($visitor_info, $flow);

        // Проверяем, заходил ли пользователь на данный лендинг за последние 24 часа
        $landing_unique = 1;
        if (isset($flow['landings'][$landing_id]['id']) && $flow['landings'][$landing_id]['date_show'] > $day_ago) {
            $landing_unique = 0;
        }

        // Проверяем, заходил ли пользователь с данного прелендинга на лендинг за последние 24 часа
        // (при условии что он перешел с прелендинга)
        $transit_landing_unique = 0;

        if ($transit_id) {

            $rel = $transit_id . '-' . $landing_id;

            if (!isset($flow['rel']) // Нету данных по переходам с прелендинга
                || !array_key_exists($rel, $flow['rel']) // Не переходил с данного прелендинга
                || $flow['rel'][$rel]['date_show'] < $day_ago // Переходил более 24 часов назад
            ) {
                $transit_landing_unique = 1;
            }
        }
        switch ($from) {
            case 'transit':
                $unique['transit_landing_rel_unique'] = $transit_landing_unique;
                $unique['transit_landing_unique'] = $landing_unique;
                $unique['direct_landing_unique'] = 0;
                $unique['noback_landing_unique'] = 0;
                $unique['comeback_landing_unique'] = 0;
                break;

            case 'noback':
                $unique['transit_landing_rel_unique'] = $transit_landing_unique;
                $unique['transit_landing_unique'] = 0;
                $unique['direct_landing_unique'] = 0;
                $unique['noback_landing_unique'] = $landing_unique;
                $unique['comeback_landing_unique'] = 0;
                break;

            case 'comeback':
                $unique['transit_landing_rel_unique'] = $transit_landing_unique;
                $unique['transit_landing_unique'] = 0;
                $unique['direct_landing_unique'] = 0;
                $unique['noback_landing_unique'] = 0;
                $unique['comeback_landing_unique'] = $landing_unique;
                break;

            case 'direct':
            default:
                $unique['transit_landing_rel_unique'] = 0;
                $unique['transit_landing_unique'] = 0;
                $unique['direct_landing_unique'] = $landing_unique;
                $unique['noback_landing_unique'] = 0;
                $unique['comeback_landing_unique'] = 0;
                break;
        }

        return $unique;
    }

    /**
     * Получение уникальности посетителя на уровне потока
     *
     * @param array $visitor_info
     * @param Flow $flow
     * @return bool
     */
    public function getFlowUnique(array $visitor_info, Flow $flow): bool
    {
        $day_ago = $this->getDayAgoTimestamp();

        $flow_last_show = $this->getFlow($visitor_info, $flow)['date_show'] ?? null;

        return $flow_last_show === null || $flow_last_show < $day_ago;
    }

    public function getPublisherUnique(array $visitor_info, Flow $flow): bool
    {
        $day_ago = $this->getDayAgoTimestamp();

        // Если это новый пользователь
        if (!isset($visitor_info['viewed_offers'][$flow['offer_id']]['flows'])) {
            return true;
        }

        // Ищем потоки паблишера, по которым переходил пользователь за последние сутки
        foreach ($visitor_info['viewed_offers'][$flow['offer_id']]['flows'] AS $flow_hash => $showed_flow) {
            $publisher_id = (int)\Hashids::decode($flow_hash)[1];

            if ($publisher_id === $flow['publisher_id'] && $showed_flow['date_show'] > $day_ago) {
                return false;
            }
        }

        return true;
    }

    public function getOfferUnique(array $visitor_info, Flow $flow): bool
    {
        $day_ago = $this->getDayAgoTimestamp();

        $offer_last_show = $this->getOffer($visitor_info, $flow)['date_show'] ?? null;

        return $offer_last_show === null || $offer_last_show < $day_ago;
    }

    public function getSystemUnique(array $visitor_info): bool
    {
        $day_ago = $this->getDayAgoTimestamp();

        // Если это новый пользователь
        if (!isset($visitor_info['viewed_offers'])) {
            return true;
        }

        foreach ($visitor_info['viewed_offers'] AS $offer) {
            if (isset($offer['date_show']) && $offer['date_show'] > $day_ago) {
                return false;
            }
        }

        return true;
    }

    public function getTransitUnique(array $visitor_info, Flow $flow, int $transit_id): bool
    {
        $day_ago = $this->getDayAgoTimestamp();

        $transit_last_show = $this->getFlow($visitor_info, $flow)['transits'][$transit_id]['date_show'] ?? null;

        return $transit_last_show === null || $transit_last_show < $day_ago;
    }

    private function getFlow(array $visitor_info, Flow $flow): array
    {
        return $this->getOffer($visitor_info, $flow)['flows'][$flow['hash']] ?? [];
    }

    private function getOffer(array $visitor_info, Flow $flow): array
    {
        return $visitor_info['viewed_offers'][$flow['offer_id']] ?? [];
    }

    private function getDayAgoTimestamp(): int
    {
        return Carbon::create()->subDay()->timestamp;
    }
}