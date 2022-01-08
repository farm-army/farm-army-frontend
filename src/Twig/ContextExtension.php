<?php declare(strict_types=1);

namespace App\Twig;

use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class ContextExtension extends AbstractExtension implements GlobalsInterface
{
    private ChainGuesser $chainGuesser;

    public function __construct(ChainGuesser $chainGuesser)
    {
        $this->chainGuesser = $chainGuesser;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('chain_icon', [$this, 'chainIcon']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'chain' => ChainUtil::getChain($this->chainGuesser->getChain()),
        ];
    }

    public function chainIcon(string $chain): ?string
    {
        try {
            $chain = ChainUtil::getChain($chain);
        } catch (\InvalidArgumentException $e) {
        }

        return $chain['icon'] ?? null;
    }
}