<?php

namespace App\Twig;

use App\Utils\InterestUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TokenFormatExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('format_token', [$this, 'formatToken']),
            new TwigFilter('format_currency', [$this, 'formatCurrency']),
        ];
    }

    public function formatCurrency($number, string $symbol = '$'): string
    {
        if (!is_numeric($number)) {
            return '';
        }

        if ($number < 0) {
            return '-' . $symbol . number_format($number * -1, 2);
        }

        return $symbol . number_format((float) $number, 2);
    }

    public function formatToken($rawNumber): string
    {
        if (!is_numeric($rawNumber)) {
            return '';
        }

        $number = abs($rawNumber);

        $decimals = 8;

        if ($number > 100000) {
            $decimals = 1;
        } elseif ($number > 10000) {
            $decimals = 2;
        } elseif ($number > 1000) {
            $decimals = 2;
        } elseif ($number > 100) {
            $decimals = 3;
        } elseif ($number > 10) {
            $decimals = 4;
        } elseif ($number > 1) {
            $decimals = 5;
        } elseif ($number > 0.1) {
            $decimals = 6;
        } elseif ($number > 0.01) {
            $decimals = 7;
        } elseif ($number > 0.001) {
            $decimals = 8;
        } elseif ($number > 0.0001) {
            $decimals = 9;
        }

        return number_format($rawNumber, $decimals);
    }
}