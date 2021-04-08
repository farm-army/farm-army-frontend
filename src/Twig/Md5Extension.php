<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Md5Extension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('md5', [$this, 'md5']),
        ];
    }

    public function md5($value): string
    {
        return md5((string) $value);
    }
}