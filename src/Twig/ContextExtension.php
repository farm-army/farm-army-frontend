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
        return [
            'chain' => [
                'id' => $this->chain,
                'title' => $this->chain === 'bsc' ? 'Binance Smart Chain' : 'Polygon',
            ]
        ];
    }
}