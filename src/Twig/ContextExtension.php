<?php declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContextExtension extends AbstractExtension implements GlobalsInterface
{
    private string $chain;

    public function __construct(string $chain)
    {
        $this->chain = $chain;
    }

    public function getGlobals(): array
    {
        switch ($this->chain) {
            case 'bsc':
                $title = 'Binance Smart Chain';
                $explorer = 'https://bscscan.com';
                break;
            case 'polygon':
                $title = 'Polygon';
                $explorer = 'https://polygonscan.com';
                break;
            case 'fantom':
                $title = 'Fantom';
                $explorer = 'https://ftmscan.com';
                break;
            case 'kcc':
                $title = 'KuCoin Community Chain';
                $explorer = 'https://explorer.kcc.io/en';
                break;
            default:
                throw new \InvalidArgumentException('Invalid chain');
        }

        return [
            'chain' => [
                'id' => $this->chain,
                'title' => $title,
                'explorer' => $explorer,
            ]
        ];
    }
}