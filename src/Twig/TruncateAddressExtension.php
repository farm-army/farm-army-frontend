<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TruncateAddressExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('truncate_address', [$this, 'truncateAddress']),
        ];
    }

    public function truncateAddress($address, int $prefixSuffix = 8): string
    {
        return substr($address, 0, $prefixSuffix) . '...' . substr($address, -$prefixSuffix);
    }
}