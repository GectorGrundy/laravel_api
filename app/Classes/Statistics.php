<?php
declare(strict_types=1);

namespace App\Classes;

class Statistics
{
    /**
     * Возврат часов, которые идут после указаного часа и до конца дня
     *
     * @param $hour_orig
     * @return array
     */
    public static function getYesterdayHours($hour_orig): array
    {
        $hour_orig = (int)$hour_orig;

        $hours = [];
        if ($hour_orig == 0) {

            $hours[] = '00';

        } else {

            for (; $hour_orig < 24; $hour_orig++) {

                if ($hour_orig < 10) {
                    $hour_str = '0' . $hour_orig;
                } else {
                    $hour_str = "$hour_orig";
                }

                $hours[] = $hour_str;
            }
        }

        return $hours;
    }

    /**
     * Возврат часов, которые идут перед указанным часом
     *
     * @param $hour_orig
     * @return array
     */
    public static function getTodayHours($hour_orig): array
    {
        $hour_orig = (int)$hour_orig;

        $hours = [];
        if ($hour_orig == 0) {

            $hours[] = '00';

        } else {

            for ($hour = 0; $hour <= $hour_orig; $hour++) {

                if ($hour < 10) {
                    $hour_str = '0' . $hour;
                } else {
                    $hour_str = "$hour";
                }

                $hours[] = $hour_str;
            }
        }

        return $hours;
    }

    public static function calculateCr(int $approved_count, int $hits, ?bool $format = false)
    {
        if ($approved_count === 0 || $hits === 0) {
            return 0;
        }

        $cr = $approved_count / $hits * 100;

        return !$format ? $cr : number_format($cr, 4, '.', '');
    }

    public static function calculateCrUnique(int $approved_count, int $unique_count, ?bool $format = false)
    {
        $cr_unique = 0;
        if ($approved_count !== 0 && $unique_count !== 0) {
            $cr_unique = $approved_count / $unique_count * 100;
        }

        return !$format ? $cr_unique : number_format($cr_unique, 4, '.', '');
    }

    public static function calculateEpc(float $leads_payout, int $hits, ?bool $format = false)
    {
        $epc = 0;
        if ($hits !== 0 && $leads_payout !== 0) {
            $epc = $leads_payout / $hits;
        }

        return !$format ? $epc : number_format($epc, 4, '.', '');

    }

    public static function calculateEpcUnique(float $leads_payout, int $unique_count, ?bool $format = false)
    {
        $epc_unique = 0;
        if ($leads_payout !== 0 && $unique_count !== 0) {
            $epc_unique = $leads_payout / $unique_count;
        }

        return !$format ? $epc_unique : number_format($epc_unique, 4, '.', '');
    }

    public static function calculateRealApprove(
        int $approved_count,
        int $cancelled_count,
        int $held_count,
        ?bool $format = false
    )
    {
        $approve = 0;
        if ($approved_count !== 0 || $cancelled_count !== 0 || $held_count !== 0) {
            $approve = ($approved_count / ($approved_count + $cancelled_count + $held_count)) * 100;
        }

        return !$format ? $approve : (float)number_format($approve, 2, '.', '');

    }

    public static function calculateExpectedApprove(
        int $approved_count,
        int $cancelled_count,
        int $held_count,
        ?bool $format = false
    )
    {
        $approve = 0;
        if ($cancelled_count !== 0 || $held_count !== 0) {
            $approve = ($approved_count + $held_count) / ($approved_count + $cancelled_count + $held_count) * 100;
        }
        return !$format ? $approve : (float)number_format($approve, 2, '.', '');

    }

    public static function calculateApprove(
        int $approved_count,
        int $cancelled_count,
        int $held_count,
        int $trashed_count,
        ?bool $format = false
    )
    {
        $approve = 0;
        if ($approved_count !== 0 || $cancelled_count !== 0 || $held_count !== 0 || $trashed_count !== 0) {
            $approve = ($approved_count / ($approved_count + $cancelled_count + $held_count + $trashed_count)) * 100;
        }

        return !$format ? $approve : (float)number_format($approve, 2, '.', '');

    }

    /**
     * Расчет ROI
     *
     * @param $enrolled_payout
     * @param $traffic_cost
     * @return float|int
     */
    public static function calculateRoi($enrolled_payout, $traffic_cost)
    {

        $enrolled_payout = (float)$enrolled_payout;
        $traffic_cost = (float)$traffic_cost;

        if ($enrolled_payout > 0 && $traffic_cost == 0) {
            return '∞';
        }

        if ($traffic_cost == 0 || $enrolled_payout - $traffic_cost == 0) {
            return '0.00';
        }

        return round(
            ($enrolled_payout - $traffic_cost) / $traffic_cost * 100
            , 2);
    }

    /**
     * Получение ROI, которое будет отображаться в таблицке
     *
     * @param float $roi
     * @param float $enrolled_payout
     * @param float $traffic_cost
     * @return string
     */
    public static function getRoiTitle($roi, float $enrolled_payout, $traffic_cost): string
    {
        $enrolled_payout = (float)$enrolled_payout;
        $traffic_cost = (float)$traffic_cost;

        if ($enrolled_payout > 0 && $traffic_cost == 0) {
            return '∞';
        }

        if ($roi > 1000) {
            $roi = '>1000';
        }

        return $roi . '%';
    }

    /**
     * Получение медианы с массива значений
     *
     * @param array $values
     * @return float
     */
    public static function getMedianValue(array $values): float
    {
        if ($values) {
            $count = count($values);

            sort($values);

            $mid = (int)floor(($count - 1) / 2);

            return ($values[$mid] + $values[$mid + 1 - $count % 2]) / 2;
        }

        return 0;
    }

    /**
     * Расчет CTR
     *
     * @param $transit_hosts
     * @param $landing_hosts
     * @return float
     */
    public static function calculateCtr(int $transit_hosts, int $landing_hosts): float
    {
        if ($landing_hosts === 0 || $transit_hosts === 0) {
            return 0;
        }

        return ((int)$landing_hosts / (int)$transit_hosts) * 100;
    }
}
