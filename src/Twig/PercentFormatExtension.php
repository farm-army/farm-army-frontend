<?php

namespace App\Twig;

use App\Utils\InterestUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PercentFormatExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('format_percent', [$this, 'formatPercent']),
        ];
    }

    public function formatPercent(float $number): string
    {
        return InterestUtil::displayApy($number);
    }
}