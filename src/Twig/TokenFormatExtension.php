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
        ];
    }

    public function formatToken($number): string
    {
        if (!is_numeric($number)) {
            return '';
        }

        $number = abs($number);

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
        } elseif ($number > 0.01) {
            $decimals = 7;
        }

        return number_format($number, $decimals);
    }
}