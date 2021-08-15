<?php declare(strict_types=1);

namespace App\Twig;

use App\Utils\ChainGuesser;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContextExtension extends AbstractExtension implements GlobalsInterface
{
    private ChainGuesser $chainGuesser;

    public function __construct(ChainGuesser $chainGuesser)
    {
        $this->chainGuesser = $chainGuesser;
    }

    public function getGlobals(): array
    {
        $chain = $this->chainGuesser->getChain();

        switch ($chain) {
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
                throw new \InvalidArgumentException('Invalid chain:' . $chain);
        }

        return [
            'chain' => [
                'id' => $this->chainGuesser->getChain(),
                'title' => $title,
                'explorer' => $explorer,
            ]
        ];
    }
}