<?php

namespace App\Utils;

abstract class InterestUtil
{
    public static function apyToApr(float $rate): float
    {
        return (pow(10, log10($rate + 1) / 365) - 1) * 100;
    }

    public static function displayApy(float $apy): string
    {
        $order = $apy < 1 ? 0 : floor(log10($apy) / 3);;
        $units = ['', 'k', 'M', 'B', 'T', 'Q', 'Q','S','S'];
        $num = $apy / 1000 ** $order;

        if (!isset($units[$order])) {
            return '∞';
        }

        return number_format($num, 2) . $units[$order];
    }
}