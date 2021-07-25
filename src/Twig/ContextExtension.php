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
                break;
            case 'polygon':
                $title = 'Polygon';
                break;
            case 'fantom':
                $title = 'Fantom';
                break;
            default:
                throw new \InvalidArgumentException('Invalid chain');
        }

        return [
            'chain' => [
                'id' => $this->chain,
                'title' => $title,
            ]
        ];
    }
}